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

        $user = Auth::user();

        // Super Administrator has full access — superset of every other role.
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $userRole = $user->role;

        $aliasMap = [
            'admin'              => ['admin', 'super_admin'],
            'super_admin'        => ['admin', 'super_admin'],
            'operator'           => ['operator', 'lgu_admin'],
            'lgu_admin'          => ['operator', 'lgu_admin'],
            'traffic_officer'    => ['traffic_officer', 'issuing_officer'],
            'issuing_officer'    => ['traffic_officer', 'issuing_officer'],
            'traffic_supervisor' => ['traffic_supervisor', 'supervisor'],
            'supervisor'         => ['traffic_supervisor', 'supervisor'],
            'auditor'            => ['auditor', 'view_only'],
            'view_only'          => ['auditor', 'view_only'],
        ];

        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles[] = $role;
            if (isset($aliasMap[$role])) {
                $allowedRoles = array_merge($allowedRoles, $aliasMap[$role]);
            }
        }

        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        abort(403, 'Access denied. Insufficient permissions.');
    }
}
