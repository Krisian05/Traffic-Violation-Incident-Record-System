<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        $userRole = Auth::user()->role;

        // Admin has full access — treat it as a superset of every other role.
        if ($userRole === 'admin' || in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Access denied. Insufficient permissions.');

        return $next($request);
    }
}
