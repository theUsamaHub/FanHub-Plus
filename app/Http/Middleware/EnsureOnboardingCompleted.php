<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip if user is admin (they don't need onboarding)
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check if onboarding is completed
        $profile = $user->profile;
        
        // If profile doesn't exist or onboarding not completed
        if (!$profile || !$profile->onboarding_completed_at) {
            // Allow access to onboarding routes
            if ($request->routeIs('onboarding*')) {
                return $next($request);
            }

            // Allow access to logout, password reset, email verification routes
            $allowedRoutes = [
                'logout',
                'password.request',
                'password.email',
                'password.reset',
                'password.store',
                'password.confirm',
                'password.update',
                'verification.notice',
                'verification.verify',
                'verification.send',
                'password.confirm',
            ];

            if (in_array($request->route()->getName(), $allowedRoutes)) {
                return $next($request);
            }

            // Redirect to onboarding
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}