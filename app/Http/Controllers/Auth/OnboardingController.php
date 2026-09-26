<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding page for selecting favorite categories.
     */
    public function create(): View
    {
        $user = Auth::user();
        
        // Get all categories ordered by name
        $categories = Category::orderBy('name')->get(['id', 'name', 'slug', 'description', 'icon_media_id']);
        
        // Load icon media for categories
        $categories->load('iconMedia');
        
        // Get user's current selections (if any - for existing users returning)
        $selectedCategoryIds = $user->favoriteCategories()->pluck('categories.id')->toArray();
        
        // Determine which layout to use based on user role
        $layout = $user->hasRole('admin') ? 'layouts.app' : 'layouts.user.app';
        
return view('auth.onboarding', [
            'categories' => $categories,
            'selectedCategoryIds' => $selectedCategoryIds,
        ]);
    }

    /**
     * Store the user's favorite category selections.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'categories' => ['required', 'array', 'min:3', 'max:5'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ], [
            'categories.required' => 'Please select at least 3 favorite categories.',
            'categories.min' => 'You must select at least 3 favorite categories.',
            'categories.max' => 'You can select a maximum of 5 favorite categories.',
            'categories.*.exists' => 'One or more selected categories are invalid.',
        ]);

        // Sync the user's favorite categories
        $user->favoriteCategories()->sync($request->input('categories'));

        // Mark onboarding as completed
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['onboarding_completed_at' => now()]
        );

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Welcome to FanHub+! Your favorite fandoms have been saved.');
    }
}