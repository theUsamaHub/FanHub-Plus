<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. You do not have permission to access this resource.',
                ], 403);
            }

            $home = match (true) {
                $user->hasRole('admin') => 'admin.dashboard',
                $user->hasRole('registered-user') => 'user.dashboard',
                default => 'profile.edit',
            };

            if ($request->routeIs($home)) {
                abort(403, 'Your account does not have a role assigned. Please contact an administrator.');
            }

            return redirect()
                ->route($home)
                ->with('error', __('You do not have permission to access that page.'));
        }

        return $next($request);
    }
}
