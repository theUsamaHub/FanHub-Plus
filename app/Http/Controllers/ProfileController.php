<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Media;
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

        return view('profile.edit', [
            'user' => $user,
            'images' => Media::where('media_type', 'image')->orderBy('original_filename')->get(['id', 'original_filename']),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $avatarMediaId = $user->profile?->avatar_media_id;

        if ($request->boolean('remove_avatar')) {
            $avatarMediaId = null;
        }

        if ($request->hasFile('avatar')) {
            $media = $this->fileService->upload(
                $request->file('avatar'),
                'uploads/avatars',
                null,
                $user->id,
                'Avatar for '.$user->name
            );
            $avatarMediaId = $media->id;
        }

        if ($user->hasRole('admin')) {
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                ['avatar_media_id' => $avatarMediaId]
            );
        } else {
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

        return Redirect::route('profile.edit');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
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
