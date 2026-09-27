<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function create(Request $request)
    {
        if (! $request->user()->requiresOnboarding() || Category::count() < 3) {
            return redirect()->route('dashboard');
        }

        return response()->view('auth.onboarding', [
            'categories' => Category::with('iconMedia')->orderBy('name')->get(),
            'selected' => $request->user()->favoriteCategoryIds(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request)
    {
        if (! $request->user()->requiresOnboarding()) return redirect()->route('dashboard');
        $data = $request->validate([
            'favorites' => ['required', 'array', 'min:3', 'max:5'],
            'favorites.*' => ['required', 'integer', 'distinct', Rule::exists('categories', 'id')->whereNull('deleted_at')],
        ], [
            'favorites.required' => 'Choose at least 3 fandoms to make this space yours.',
            'favorites.min' => 'Choose at least 3 fandoms to continue.',
            'favorites.max' => 'Choose up to 5 fandoms. Deselect one to make room.',
        ]);

        DB::transaction(function () use ($request, $data) {
            // Serialize double submissions so a completed setup cannot be overwritten.
            $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            if (! $user->requiresOnboarding()) return;
            $user->favoriteCategories()->sync($data['favorites']);
            $user->profile()->updateOrCreate([], ['onboarding_completed_at' => now()]);
        });
        $request->user()->unsetRelation('profile')->unsetRelation('favoriteCategories');

        return redirect()->route('dashboard')->with('onboarding-success', 'Your universe is ready. Welcome to FanHub Plus!');
    }
}
