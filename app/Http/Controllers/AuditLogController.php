<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $event = trim($request->input('event', ''));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $causer = $request->input('causer_id');

        $query = Activity::with(['causer', 'subject'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ilike', '%' . $search . '%')
                  ->orWhere('log_name', 'ilike', '%' . $search . '%')
                  ->orWhere('event', 'ilike', '%' . $search . '%');
            });
        }

        if ($event !== '') {
            $query->where('event', $event);
        }

        if ($causer) {
            $query->where('causer_id', $causer);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->paginate(20)->withQueryString();

        $events = Activity::select('event')->distinct()->whereNotNull('event')->pluck('event');

        return view('audit-logs.index', compact('logs', 'search', 'event', 'dateFrom', 'dateTo', 'causer', 'events'));
    }
}
