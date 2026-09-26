<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\Subscriber;
use App\Models\UserProfile;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileService
    ) {}

    public function edit(Request $request): View
    {
        $user = $request->user()->load(['profile.avatarMedia']);

        $view = $user->hasRole('admin') ? 'profile.edit' : 'user.profile';
        $data = [
            'user' => $user,
            'images' => Media::where('uploaded_by', $user->id)
                ->where('media_type', 'image')
                ->orderBy('original_filename')
                ->get(['id', 'original_filename']),
            'categories' => Category::orderBy('name')->get(),
            'selected' => $user->favoriteCategories()->pluck('categories.id')->all(),
        ];

        // Add subscriber info for newsletter preferences
        $subscriber = Subscriber::where('email', $user->email)->first();
        $preferences = $subscriber?->getPreferences() ?? [];
        $data['subscriber'] = $subscriber;
        $data['preferences'] = $preferences;
        $data['selectedCategories'] = $preferences['categories'] ?? [];

        return view($view, $data);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));
        if ($user->isDirty('email')) $user->email_verified_at = null;
        $user->save();

        $avatarMediaId = $user->profile?->avatar_media_id;

        if ($request->boolean('remove_avatar')) {
            $avatarMediaId = null;
        }

        if ($request->hasFile('avatar')) {
            try {
                $media = $this->fileService->upload(
                    $request->file('avatar'),
                    'uploads/avatars',
                    null,
                    $user->id,
                    'Avatar for '.$user->name
                );
                $avatarMediaId = $media->id;
            } catch (\RuntimeException $e) {
                report($e);

                return back()
                    ->withInput()
                    ->withErrors(['avatar' => __('Avatar upload failed. Please try again.')]);
            }
        }

        if ($user->hasRole('admin')) {
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                ['avatar_media_id' => $avatarMediaId]
            );
        } else {
            if ($request->has('favorites_present')) $user->favoriteCategories()->sync($request->validated('favorites', []));
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                array_merge(
                    $request->safe()->only([
                        'display_name',
                        'bio',
                        'theme_preference',
                        'font_size_preference',
                    ]),
                    [
                        'avatar_media_id' => $request->hasFile('avatar') || $request->boolean('remove_avatar')
                            ? $avatarMediaId
                            : ($request->input('avatar_media_id') ?: $avatarMediaId),
                    ]
                )
            );
        }

        return Redirect::route('profile.edit')->with('success', 'Your profile and preferences have been saved.');
    }

    public function updateNewsletterPreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'subscribe' => ['boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'frequency' => ['nullable', 'in:instant,daily,weekly'],
        ]);

        $user = $request->user();
        $subscribe = $request->boolean('subscribe');

        $subscriber = Subscriber::firstOrNew(['email' => $user->email]);
        
        if ($subscribe) {
            $preferences = [
                'categories' => $request->input('categories', []),
                'frequency' => $request->input('frequency', 'instant'),
            ];

            $subscriber->fill([
                'name' => $user->name,
                'subscribed_at' => $subscriber->subscribed_at ?? now(),
                'unsubscribed_at' => null,
                'status' => 'active',
                'ip_address' => $request->ip(),
                'preferences' => $preferences,
            ]);
            $subscriber->save();

            return back()->with('success', 'Newsletter preferences saved. You are now subscribed!');
        } else {
            // Unsubscribe
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);

            return back()->with('success', 'You have been unsubscribed from the newsletter.');
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
