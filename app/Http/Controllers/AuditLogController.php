<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\Violator;
use App\Models\Vehicle;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $user     = Auth::user();
        $search   = trim($request->input('search', ''));
        $event    = trim($request->input('event', ''));
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $causer   = $request->input('causer_id');

        // Determine LGU scope
        $lguId = null;
        $isLguScoped = false;
        if ($user && $user->lgu_id && !$user->isSuperAdmin() && !$user->isProvinceAdmin()) {
            $lguId = (int) $user->lgu_id;
            $isLguScoped = true;
        } elseif ($request->filled('lgu_id')) {
            $lguId = (int) $request->input('lgu_id');
        }

        $query = Activity::with(['causer.lgu', 'subject'])->latest();

        if ($lguId) {
            $query->where(function ($q) use ($lguId) {
                // Causer user's LGU
                $q->whereHasMorph('causer', [User::class], function ($uq) use ($lguId) {
                    $uq->where('lgu_id', $lguId);
                })
                // Subject models with lgu_id
                ->orWhereHasMorph('subject', [Violation::class], fn($sq) => $sq->where('lgu_id', $lguId))
                ->orWhereHasMorph('subject', [Violator::class],  fn($sq) => $sq->where('lgu_id', $lguId))
                ->orWhereHasMorph('subject', [Vehicle::class],   fn($sq) => $sq->where('lgu_id', $lguId))
                ->orWhereHasMorph('subject', [Incident::class],  fn($sq) => $sq->where('lgu_id', $lguId))
                ->orWhereHasMorph('subject', [User::class],      fn($sq) => $sq->where('lgu_id', $lguId))
                // JSON property lgu_id
                ->orWhere('properties->lgu_id', $lguId)
                ->orWhere('properties->attributes->lgu_id', $lguId);
            });
        }

        // Scope Police Traffic Supervisor logs to enforcement, incident, motorist, vehicle, and officer auth categories
        if ($user && $user->isTrafficSupervisor()) {
            $query->where(function ($sq) {
                $sq->whereIn('log_name', ['violation', 'incident', 'violator', 'vehicle', 'auth', 'default'])
                   ->orWhereNull('log_name');
            });
        }

        if ($search !== '') {
            $lk = '%' . mb_strtolower($search) . '%';
            $query->where(function ($q) use ($lk) {
                $q->whereRaw('LOWER(description) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(log_name) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(event) LIKE ?', [$lk])
                  ->orWhereHasMorph('causer', [User::class], function ($uq) use ($lk) {
                      $uq->whereRaw('LOWER(name) LIKE ?', [$lk])
                        ->orWhereRaw('LOWER(username) LIKE ?', [$lk]);
                  });
            });
        }

        if ($event !== '') {
            $query->where('event', $event);
        }

        if ($causer) {
            $query->where('causer_id', $causer);
        }

        if ($dateFrom) {
            try {
                $parsed = \Carbon\Carbon::parse($dateFrom)->toDateString();
                $query->whereDate('created_at', '>=', $parsed);
            } catch (\Throwable $e) {}
        }

        if ($dateTo) {
            try {
                $parsed = \Carbon\Carbon::parse($dateTo)->toDateString();
                $query->whereDate('created_at', '<=', $parsed);
            } catch (\Throwable $e) {}
        }

        // Stats calculation
        $statsBase    = clone $query;
        $totalLogs    = (clone $statsBase)->count();
        $todayLogs    = (clone $statsBase)->whereDate('created_at', now()->toDateString())->count();
        $loginLogs    = (clone $statsBase)->whereIn('event', ['login', 'logout'])->count();
        $mutationLogs = (clone $statsBase)->whereIn('event', ['created', 'updated', 'deleted'])->count();

        $logs = $query->paginate(25)->withQueryString();

        $events = Activity::select('event')->distinct()->whereNotNull('event')->pluck('event');
        $lgus   = ($user && ($user->isSuperAdmin() || $user->isProvinceAdmin())) ? Lgu::orderBy('name')->get() : collect();
        $selectedLgu = $lguId ? Lgu::find($lguId) : null;

        return view('audit-logs.index', compact(
            'logs', 'search', 'event', 'dateFrom', 'dateTo', 'causer', 'events',
            'lguId', 'lgus', 'isLguScoped', 'selectedLgu',
            'totalLogs', 'todayLogs', 'loginLogs', 'mutationLogs'
        ));
    }
}

