<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantDatabaseSwitcher
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if ($route) {
            // 1. Resolve dynamic tenant schemas for specific model requests (guests and authenticated)
            if ($violationId = $route->parameter('violation')) {
                $id = is_object($violationId) ? $violationId->id : $violationId;
                Tenant::switchToModel('violation', $id);
                return $next($request);
            }

            if ($incidentId = $route->parameter('incident')) {
                $id = is_object($incidentId) ? $incidentId->id : $incidentId;
                Tenant::switchToModel('incident', $id);
                return $next($request);
            }

            if ($violatorId = $route->parameter('violator')) {
                $id = is_object($violatorId) ? $violatorId->id : $violatorId;
                Tenant::switchToModel('violator', $id);
                return $next($request);
            }

            if ($vehicleId = $route->parameter('vehicle')) {
                $id = is_object($vehicleId) ? $vehicleId->id : $vehicleId;
                Tenant::switchToModel('vehicle', $id);
                return $next($request);
            }
        }

        // 2. Otherwise scope schema by authenticated user's LGU
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->lgu_id && !$user->isAdmin() && !$user->isProvinceAdmin()) {
                Tenant::switchTo($user->lgu?->code);
                return $next($request);
            }
        }

        // 3. Fallback to central/public schema
        Tenant::switchTo(null);

        return $next($request);
    }
}
