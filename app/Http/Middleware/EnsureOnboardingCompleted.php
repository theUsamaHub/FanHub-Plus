<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\OnboardingController;
use App\Models\Category;
use Closure;
use Illuminate\Http\Request;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || $request->routeIs('onboarding.*', 'social.*', 'login', 'register', 'logout', 'password.*', 'verification.*', 'storage.serve', 'unsubscribe')
            || $request->is('login', 'register', 'forgot-password', 'reset-password', 'confirm-password')
            || ! $user->requiresOnboarding() || Category::count() < 3) {
            return $next($request);
        }

        if ($request->expectsJson()) return response()->json(['message' => 'Complete your fandom setup before continuing.'], 403);
        if (! $request->isMethod('get') || $request->header('X-Home-Section')) return redirect()->route('onboarding.create');

        return app(OnboardingController::class)->create($request);
    }
}
