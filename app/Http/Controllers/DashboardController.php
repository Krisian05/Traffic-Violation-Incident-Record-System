<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Vehicle;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $q  = trim($request->input('q', ''));
        if ($q === '') {
            return response()->json([]);
        }
        $ql = mb_strtolower($q); // lowercase once for all LOWER() comparisons
        $lk = "%{$ql}%";

        // Motorists — name, license, address, contact, plate number, vehicle make/model
        $motorists = Violator::with(['vehicles' => fn($q) => $q->limit(1)])
            ->where(function ($query) use ($lk) {
                $query->whereRaw('LOWER(first_name) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(last_name) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(middle_name) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(license_number) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(address) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(contact_number) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(email) LIKE ?', [$lk])
                      ->orWhereHas('vehicles', fn($vq) =>
                          $vq->whereRaw('LOWER(plate_number) LIKE ?', [$lk])
                             ->orWhereRaw('LOWER(make) LIKE ?', [$lk])
                             ->orWhereRaw('LOWER(model) LIKE ?', [$lk])
                      );
            })
            ->withCount('violations')
            ->orderBy('last_name')
            ->limit(6)
            ->get()
            ->map(function ($v) {
                $plate = $v->vehicles->first()?->plate_number;
                $sub   = collect([
                    $v->license_number ? 'License: ' . $v->license_number : null,
                    $plate             ? 'Plate: ' . $plate               : null,
                    $v->contact_number ?: null,
                ])->filter()->implode(' · ');
                return [
                    'type'  => 'motorist',
                    'id'    => $v->id,
                    'label' => $v->full_name,
                    'sub'   => $sub ?: 'No additional info',
                    'badge' => $v->violations_count . ' violation' . ($v->violations_count != 1 ? 's' : ''),
                    'url'   => route('violators.show', $v->id),
                ];
            });

        // Violations — ticket, location, plate, vehicle make, motorist name, type name, status, notes
        // "overdue" is a virtual status: pending violations older than 72 hours
        $isOverdueSearch = str_contains($ql, 'overdue');

        $violations = Violation::with(['violator', 'violationType'])
            ->where(function ($query) use ($lk, $isOverdueSearch) {
                $query->whereRaw('LOWER(ticket_number) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(location) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(vehicle_plate) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(vehicle_make) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(vehicle_model) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(vehicle_owner_name) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(notes) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(status) LIKE ?', [$lk])
                      ->orWhereHas('violator', fn($vq) =>
                          $vq->whereRaw('LOWER(first_name) LIKE ?', [$lk])
                             ->orWhereRaw('LOWER(last_name) LIKE ?', [$lk])
                             ->orWhereRaw('LOWER(license_number) LIKE ?', [$lk])
                      )
                      ->orWhereHas('violationType', fn($tq) =>
                          $tq->whereRaw('LOWER(name) LIKE ?', [$lk])
                      );

                // Virtual "overdue" keyword → pending violations past 72 hours
                if ($isOverdueSearch) {
                    $query->orWhere(function ($q) {
                        $q->overdue();
                    });
                }
            })
            ->orderByDesc('date_of_violation')
            ->limit(6)
            ->get()
            ->map(fn(\App\Models\Violation $v) => [
                'type'  => 'violation',
                'id'    => $v->id,
                'label' => ($v->violator?->full_name ?? '(Unknown)') . ' — ' . ($v->violationType?->name ?? '(Unknown type)'),
                'sub'   => collect([
                                $v->ticket_number ? 'Ticket #' . $v->ticket_number : null,
                                $v->vehicle_plate ?: null,
                                $v->date_of_violation->format('M d, Y'),
                            ])->filter()->implode(' · '),
                'badge' => $v->isOverdue() ? 'overdue' : $v->status,
                'url'   => route('violations.show', $v->id),
            ]);

        // Violation types
        $types = ViolationType::whereRaw('LOWER(name) LIKE ?', [$lk])
            ->withCount('violations')
            ->orderByDesc('violations_count')
            ->limit(4)
            ->get()
            ->map(fn($t) => [
                'type'  => 'type',
                'id'    => $t->id,
                'label' => $t->name,
                'sub'   => $t->violations_count . ' total case' . ($t->violations_count != 1 ? 's' : ''),
                'badge' => null,
                'url'   => route('violations.index', ['type' => $t->id]),
            ]);

        // Vehicles — direct plate/make/model search
        $vehicles = Vehicle::with('violator')
            ->where(function ($query) use ($lk) {
                $query->whereRaw('LOWER(plate_number) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(make) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(model) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(color) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(vehicle_type) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(chassis_number) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(or_number) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(cr_number) LIKE ?', [$lk]);
            })
            ->limit(4)
            ->get()
            ->map(fn($vh) => [
                'type'  => 'vehicle',
                'id'    => $vh->id,
                'label' => trim(($vh->make ?? '') . ' ' . ($vh->model ?? '') . ' — ' . ($vh->plate_number ?? 'No Plate')),
                'sub'   => collect([
                                $vh->color,
                                $vh->vehicle_type,
                                $vh->violator ? 'Owner: ' . $vh->violator->full_name : null,
                            ])->filter()->implode(' · '),
                'badge' => null,
                'url'   => $vh->violator ? route('violators.show', $vh->violator_id) : '#',
            ]);

        // Incidents — incident_number, location, description, status, motorist name/plate
        $incidents = Incident::with(['motorists' => fn($q) => $q->limit(2)])
            ->where(function ($query) use ($lk) {
                $query->whereRaw('LOWER(incident_number) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(location) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(description) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(status) LIKE ?', [$lk])
                      ->orWhereHas('motorists', fn($mq) =>
                          $mq->whereRaw('LOWER(motorist_name) LIKE ?', [$lk])
                             ->orWhereRaw('LOWER(motorist_license) LIKE ?', [$lk])
                             ->orWhereRaw('LOWER(vehicle_plate) LIKE ?', [$lk])
                             ->orWhereHas('violator', fn($vq) =>
                                 $vq->whereRaw('LOWER(first_name) LIKE ?', [$lk])
                                    ->orWhereRaw('LOWER(last_name) LIKE ?', [$lk])
                             )
                      );
            })
            ->orderByDesc('date_of_incident')
            ->limit(5)
            ->get()
            ->map(fn($inc) => [
                'type'  => 'incident',
                'id'    => $inc->id,
                'label' => $inc->incident_number,
                'sub'   => collect([
                                $inc->location ?: null,
                                $inc->date_of_incident->format('M d, Y'),
                            ])->filter()->implode(' · '),
                'badge' => $inc->status,
                'url'   => route('incidents.show', $inc->id),
            ]);

        return response()->json([
            'motorists'  => $motorists->values(),
            'violations' => $violations->values(),
            'incidents'  => $incidents->values(),
            'types'      => $types->values(),
            'vehicles'   => $vehicles->values(),
        ]);
    }

    public function analytics(Request $request)
    {
        $period = $request->input('period', 'weekly');
        $cacheKey = 'dashboard_analytics_' . $period . '_' . now()->format('Y-m-d');

        return response()->json(
            Cache::remember($cacheKey, 300, fn() => $this->buildAnalytics($period))
        );
    }

    private function buildAnalytics(string $period): array
    {

        switch ($period) {
            case 'monthly':
                $start     = now()->startOfMonth();
                $end       = now()->endOfMonth();
                $prevStart = now()->subMonthNoOverflow()->startOfMonth();
                $prevEnd   = now()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'yearly':
                $start     = now()->startOfYear();
                $end       = now()->endOfYear();
                $prevStart = now()->subYear()->startOfYear();
                $prevEnd   = now()->subYear()->endOfYear();
                break;
            default: // weekly
                $start     = now()->startOfWeek();
                $end       = now()->endOfWeek();
                $prevStart = now()->subWeek()->startOfWeek();
                $prevEnd   = now()->subWeek()->endOfWeek();
        }

        $startDate     = $start->toDateString();
        $endDate       = $end->toDateString();
        $prevStartDate = $prevStart->toDateString();
        $prevEndDate   = $prevEnd->toDateString();

        $violationsCount = Violation::whereBetween('date_of_violation', [$startDate, $endDate])->count();
        $incidentsCount  = Incident::whereBetween('date_of_incident',  [$startDate, $endDate])->count();
        $overdueCount    = Violation::overdue()->count();

        // Previous period for trend comparison
        $prevViolations  = Violation::whereBetween('date_of_violation', [$prevStartDate, $prevEndDate])->count();
        $prevIncidents   = Incident::whereBetween('date_of_incident',  [$prevStartDate, $prevEndDate])->count();
        $violationsDelta = $violationsCount - $prevViolations;
        $incidentsDelta  = $incidentsCount  - $prevIncidents;
        $violationsTrend = $prevViolations > 0 ? round(($violationsDelta / $prevViolations) * 100) : null;
        $incidentsTrend  = $prevIncidents  > 0 ? round(($incidentsDelta  / $prevIncidents)  * 100) : null;

        // Build chart data using a single GROUP BY query (no N+1)
        if ($period === 'weekly') {
            $rawData = Violation::selectRaw('DATE(date_of_violation) as d, COUNT(*) as cnt')
                ->whereBetween('date_of_violation', [$startDate, $endDate])
                ->groupBy('d')
                ->pluck('cnt', 'd')
                ->toArray();
            $labels = [];
            $values = [];
            for ($i = 0; $i < 7; $i++) {
                $day      = now()->startOfWeek()->addDays($i);
                $labels[] = $day->format('D');
                $values[] = (int) ($rawData[$day->toDateString()] ?? 0);
            }
        } elseif ($period === 'monthly') {
            $dayExpr = \DB::getDriverName() === 'pgsql'
                ? 'EXTRACT(DAY FROM date_of_violation)::int as d'
                : 'DAY(date_of_violation) as d';
            $rawData = Violation::selectRaw("$dayExpr, COUNT(*) as cnt")
                ->whereBetween('date_of_violation', [$startDate, $endDate])
                ->groupBy('d')
                ->pluck('cnt', 'd')
                ->toArray();
            $labels = [];
            $values = [];
            for ($i = 1; $i <= now()->daysInMonth; $i++) {
                $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT);
                $values[] = (int) ($rawData[$i] ?? 0);
            }
        } else { // yearly
            $monthExpr = \DB::getDriverName() === 'pgsql'
                ? 'EXTRACT(MONTH FROM date_of_violation)::int as m'
                : 'MONTH(date_of_violation) as m';
            $rawData = Violation::selectRaw("$monthExpr, COUNT(*) as cnt")
                ->whereYear('date_of_violation', now()->year)
                ->groupBy('m')
                ->pluck('cnt', 'm')
                ->toArray();
            $labels = [];
            $values = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = \Carbon\Carbon::create(null, $m)->format('M');
                $values[] = (int) ($rawData[$m] ?? 0);
            }
        }

        // Top locations by incident count for the period
        $topBarangays = Incident::whereBetween('date_of_incident', [$startDate, $endDate])
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->select('location', DB::raw('COUNT(*) as total'))
            ->groupBy('location')
            ->orderByDesc('total')
            ->limit(7)
            ->get()
            ->map(fn ($row) => ['label' => $row->location, 'count' => (int) $row->total]);

        $periodLabel = match ($period) {
            'monthly' => now()->format('F Y'),
            'yearly'  => 'Year ' . now()->year,
            default   => now()->startOfWeek()->format('M d') . '–' . now()->endOfWeek()->format('M d, Y'),
        };

        $violationsUrl = match ($period) {
            'monthly' => route('violations.index', ['month' => now()->month, 'year' => now()->year]),
            'yearly'  => route('violations.index', ['year' => now()->year]),
            default   => route('violations.index', ['date_from' => $startDate, 'date_to' => $endDate]),
        };

        return [
            'period'          => $period,
            'periodLabel'     => $periodLabel,
            'violationsCount' => $violationsCount,
            'incidentsCount'  => $incidentsCount,
            'overdueCount'    => $overdueCount,
            'violationsDelta' => $violationsDelta,
            'incidentsDelta'  => $incidentsDelta,
            'violationsTrend' => $violationsTrend,
            'incidentsTrend'  => $incidentsTrend,
            'violationsUrl'   => $violationsUrl,
            'incidentsUrl'    => route('incidents.index'),
            'chart'           => ['labels' => $labels, 'values' => $values],
            'topBarangays'    => $topBarangays->values()->toArray(),
        ];
    }

    public function stats()
    {
        $data = Cache::remember('dashboard_stats', 60, function () {
            return [
                'totalViolators'      => Violator::count(),
                'pendingCount'        => Violator::whereHas('violations', fn($q) => $q->pendingActive())->count(),
                'overdueCount'        => Violation::overdue()->count(),
                'violationsThisMonth' => Violation::whereMonth('date_of_violation', now()->month)
                                            ->whereYear('date_of_violation', now()->year)->count(),
                'incidentsThisMonth'  => Incident::whereMonth('date_of_incident', now()->month)
                                            ->whereYear('date_of_incident', now()->year)->count(),
            ];
        });

        return response()->json($data);
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isTrafficOfficer()) {
            return redirect()->route('officer.dashboard');
        }

        $totalViolators      = Violator::count();
        $totalVehicles       = Vehicle::count();
        $pendingCount        = Violator::whereHas('violations', fn($q) => $q->pendingActive())->count();
        $violationsThisMonth = Violation::whereMonth('date_of_violation', now()->month)
                                   ->whereYear('date_of_violation', now()->year)->count();
        $incidentsThisMonth  = Incident::whereMonth('date_of_incident', now()->month)
                                   ->whereYear('date_of_incident', now()->year)->count();

        $topViolations = ViolationType::withCount('violations')
            ->orderByDesc('violations_count')
            ->limit(5)
            ->get();

        $repeatOffenders = Violator::withCount('violations')
            ->has('violations', '>=', 2)
            ->orderByDesc('violations_count')
            ->limit(8)
            ->get();

        // Cap table at 20 rows; pass total separately for "View all" link
        $overdueTotal      = Violation::overdue()->count();
        $overdueViolations = Violation::with(['violator', 'violationType'])
            ->overdue()
            ->orderBy('date_of_violation')
            ->limit(20)
            ->get();

        $freshPendingTotal      = Violation::pendingActive()->count();
        $freshPendingViolations = Violation::with(['violator', 'violationType'])
            ->pendingActive()
            ->orderBy('date_of_violation')
            ->limit(20)
            ->get();

        // All-time settlement breakdown
        $totalViolationsAll = Violation::count();
        $settledCount       = Violation::where('status', 'settled')->count();
        $settlementRate     = $totalViolationsAll > 0
            ? round(($settledCount / $totalViolationsAll) * 100)
            : 0;

        return view('dashboard.index', compact(
            'totalViolators',
            'totalVehicles',
            'pendingCount',
            'violationsThisMonth',
            'incidentsThisMonth',
            'topViolations',
            'repeatOffenders',
            'overdueViolations',
            'overdueTotal',
            'freshPendingViolations',
            'freshPendingTotal',
            'totalViolationsAll',
            'settledCount',
            'settlementRate',
        ));
    }
}
