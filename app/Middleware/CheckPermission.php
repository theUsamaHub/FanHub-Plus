<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $hasPermission = $user->roles->contains(function ($role) use ($permission) {
            return $role->hasPermission($permission);
        });

        if (!$hasPermission) {
            abort(403, 'Unauthorized. You do not have the required permission: ' . $permission);
        }

        return $next($request);
    }
}
