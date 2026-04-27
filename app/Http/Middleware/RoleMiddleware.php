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

        // Admin is a superset of operator — allow admin wherever operator is required
        $effectiveRoles = $roles;
        if (in_array('operator', $roles) && !in_array('admin', $roles)) {
            $effectiveRoles[] = 'admin';
        }

        if (!in_array($userRole, $effectiveRoles)) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
