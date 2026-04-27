<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function suggestions(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $q = trim($request->input('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $lk = '%' . mb_strtolower($q) . '%';
        $violators = Violator::whereRaw('LOWER(first_name) LIKE ?', [$lk])
            ->orWhereRaw('LOWER(last_name) LIKE ?', [$lk])
            ->orWhereRaw('LOWER(middle_name) LIKE ?', [$lk])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(8)
            ->get(['id', 'first_name', 'middle_name', 'last_name']);

        return response()->json($violators->map(fn($v) => $v->full_name)->values());
    }

    public function index(Request $request): View
    {
        $month        = $request->input('month', 0);
        $year         = (int) $request->input('year', now()->year);
        $search       = trim($request->input('search', ''));
        $typeFilter   = (string) ($request->input('type_filter') ?? '');
        $municipality = trim($request->input('municipality', ''));
        $showAll      = $month == 0;

        $baseData   = $this->loadBaseData($municipality);
        $allTypes   = $baseData['allTypes'];
        $incBase    = Incident::whereYear('date_of_incident', $year)
                        ->when(!$showAll, fn($q) => $q->whereMonth('date_of_incident', $month));
        $commonData = $this->gatherCommonReportData($year, $month, $showAll, $allTypes, $incBase, $baseData['overdueViolations'], $municipality);
        $data       = $showAll
                        ? $this->buildYearlyData($year, $search, $typeFilter, $allTypes, $municipality)
                        : $this->buildMonthlyData((int) $month, $year, $search, $typeFilter, $municipality);

        $topViolators = collect($data['yearViolatorMatrix'] ?? [])->take(8)->mapWithKeys(
            fn($item) => [$item['violator']->full_name ?? 'Unknown' => $item['total'] ?? 0]
        );

        return view('reports.index', array_merge([
            'repeatOffenders'  => $baseData['repeatOffenders'],
            'month'            => $month,
            'year'             => $year,
            'search'           => $search,
            'typeFilter'       => $typeFilter,
            'municipality'     => $municipality,
            'showAll'          => $showAll,
            'allTypes'         => $allTypes,
            'minYear'          => $baseData['minYear'],
            'overdueViolations'=> $baseData['overdueViolations'],
            'topViolators'     => $topViolators,
        ], $commonData, $data));
    }

    private function loadBaseData(string $municipality): array
    {
        $repeatOffenders = Violator::withCount(['violations' => fn($q) =>
                $q->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
            ])
            ->whereHas('violations', fn($q) =>
                $q->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
            , '>', 1)
            ->orderByDesc('violations_count')
            ->get();

        $allTypes = ViolationType::orderBy('name')->get();
        $minDate  = Violation::min('date_of_violation');
        $minYear  = (int) ($minDate ? substr($minDate, 0, 4) : now()->year);

        $overdueViolations = Violation::with(['violator', 'violationType', 'vehicle'])
            ->overdue()
            ->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
            ->orderBy('created_at')
            ->get();

        return compact('repeatOffenders', 'allTypes', 'minYear', 'overdueViolations');
    }

    private function gatherCommonReportData(int $year, int $month, bool $showAll, $allTypes, $incBase, $overdueViolations, string $municipality = ''): array
    {
        $totalIncidents     = $incBase->count();
        $incidentsByStatus  = (clone $incBase)->select('status', DB::raw('COUNT(*) as total'))
                                ->groupBy('status')->pluck('total', 'status');

        $incidentHotspots   = (clone $incBase)->whereNotNull('location')->where('location', '!=', '')
                                ->select('location', DB::raw('COUNT(*) as total'))
                                ->groupBy('location')->orderByDesc('total')->limit(7)->get();

        $violationHotspots  = Violation::whereYear('date_of_violation', $year)
                                ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
                                ->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
                                ->whereNotNull('location')->where('location', '!=', '')
                                ->select('location', DB::raw('COUNT(*) as total'))
                                ->groupBy('location')->orderByDesc('total')->limit(7)->get();

        $aggCounts = Violation::whereYear('date_of_violation', $year)
                        ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
                        ->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
                        ->selectRaw("
                            SUM(CASE WHEN status = 'settled' THEN 1 ELSE 0 END) as settled,
                            SUM(CASE WHEN status = 'contested' THEN 1 ELSE 0 END) as contested,
                            SUM(CASE WHEN status = 'pending' AND date_of_violation > ? THEN 1 ELSE 0 END) as pending_active,
                            COUNT(DISTINCT violator_id) as total_violators
                        ", [now()->subHours(72)->toDateString()])
                        ->first();

        $settledCount       = (int) ($aggCounts->settled ?? 0);
        $contestedCount     = (int) ($aggCounts->contested ?? 0);
        $pendingActiveCount = (int) ($aggCounts->pending_active ?? 0);
        $totalViolators     = (int) ($aggCounts->total_violators ?? 0);

        $overdueCount = $overdueViolations->count();

        $violationsByType = Violation::whereYear('date_of_violation', $year)
            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
            ->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
            ->select('violation_type_id', DB::raw('COUNT(*) as total'))
            ->groupBy('violation_type_id')
            ->pluck('total', 'violation_type_id');

        $allTypesById = $allTypes->keyBy('id');
        $violationsByType = $violationsByType->mapWithKeys(function ($total, $typeId) use ($allTypesById) {
            $typeName = $allTypesById[$typeId]->name ?? 'Unknown';
            return [$typeName => $total];
        });

        $violationStatusCounts = Violation::whereYear('date_of_violation', $year)
            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
            ->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $incidentsByDate = $incBase
            ->select(DB::raw('DATE(date_of_incident) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('total', 'day');

        $roleDistribution = \App\Models\User::select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        return compact(
            'totalIncidents', 'incidentsByStatus', 'incidentHotspots', 'violationHotspots',
            'settledCount', 'contestedCount', 'pendingActiveCount', 'totalViolators',
            'overdueCount', 'violationsByType', 'violationStatusCounts', 'incidentsByDate',
            'roleDistribution'
        );
    }

    private function buildYearlyData(int $year, string $search, string $typeFilter, Collection $allTypes, string $municipality = ''): array
    {
        $yearViolations = Violation::with([
                'violator:id,first_name,middle_name,last_name',
                'violationType:id,name',
            ])
            ->whereYear('date_of_violation', $year)
            ->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
            ->get(['id', 'violator_id', 'violation_type_id', 'date_of_violation', 'location']);

        // Single pass: build month×type matrix AND group by violator simultaneously
        $yearMatrix  = [];
        $monthTotals = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthTotals[$m] = 0;
            foreach ($allTypes as $type) {
                $yearMatrix[$m][$type->id] = 0;
            }
        }

        $byViolator = []; // violator_id -> ['violator' => ..., 'monthData' => [m => [type_names]]]
        foreach ($yearViolations as $v) {
            $m   = (int) $v->date_of_violation->format('n');
            $vid = $v->violator_id;

            if (isset($yearMatrix[$m][$v->violation_type_id])) {
                $yearMatrix[$m][$v->violation_type_id]++;
            }
            $monthTotals[$m]++;

            if (!isset($byViolator[$vid])) {
                $byViolator[$vid] = ['violator' => $v->violator, 'monthData' => []];
            }
            $byViolator[$vid]['monthData'][$m][] = $v->violationType->name ?? '?';
        }

        // Build yearViolatorMatrix from pre-grouped data (no more per-violator filter loops)
        $yearViolatorMatrix = [];
        foreach ($byViolator as $data) {
            $months     = [];
            $monthTypes = [];
            $total      = 0;
            for ($m = 1; $m <= 12; $m++) {
                $types          = $data['monthData'][$m] ?? [];
                $months[$m]     = \count($types);
                $monthTypes[$m] = $types;
                $total         += \count($types);
            }
            $yearViolatorMatrix[] = [
                'violator'   => $data['violator'],
                'months'     => $months,
                'monthTypes' => $monthTypes,
                'total'      => $total,
            ];
        }
        usort($yearViolatorMatrix, fn($a, $b) => $b['total'] - $a['total']);

        if ($search !== '') {
            $yearViolatorMatrix = array_values(array_filter(
                $yearViolatorMatrix,
                fn($r) => str_contains(strtolower($r['violator']->full_name), strtolower($search))
            ));
        }

        if ($typeFilter) {
            foreach ($yearMatrix as $m => $cols) {
                foreach (array_keys($cols) as $tid) {
                    if ($tid != $typeFilter) {
                        $yearMatrix[$m][$tid] = 0;
                    }
                }
                $monthTotals[$m] = $yearMatrix[$m][$typeFilter] ?? 0;
            }
        }

        return [
            'yearMatrix'         => $yearMatrix,
            'monthTotals'        => $monthTotals,
            'yearViolatorMatrix' => $yearViolatorMatrix,
            'totalThisMonth'     => $yearViolations->count(),
            'yearOverview'       => collect(),
            'monthlySummary'     => collect(),
            'monthlyOffenders'   => collect(),
        ];
    }

    public function incidentStats(Request $request): \Illuminate\Http\JsonResponse
    {
        $period = $request->input('period', 'month');
        $year   = (int) $request->input('year', now()->year);
        $month  = (int) $request->input('month', now()->month);
        $date   = $request->input('date', now()->toDateString());

        if ($period === 'week') {
            // $date is either YYYY-Www (from type="week" input) or a plain date string
            if (preg_match('/^(\d{4})-W(\d{2})$/', $date, $m)) {
                // ISO week: Monday start — use Carbon's setISODate
                $weekStart = \Carbon\Carbon::now()->setISODate((int)$m[1], (int)$m[2], 1); // Monday
                $weekEnd   = $weekStart->copy()->addDays(6); // Sunday
            } else {
                $parsed    = \Carbon\Carbon::parse($date);
                $weekStart = $parsed->copy()->startOfWeek(\Carbon\CarbonInterface::MONDAY);
                $weekEnd   = $weekStart->copy()->addDays(6);
            }
            $from  = $weekStart->toDateString();
            $to    = $weekEnd->toDateString();
            $label = $weekStart->format('M j') . '–' . $weekEnd->format('M j, Y');
        } elseif ($period === 'year' || $month === 0) {
            $from  = $year . '-01-01';
            $to    = $year . '-12-31';
            $label = 'Year ' . $year;
        } else {
            $from  = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
            $to    = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
            $label = date('F Y', mktime(0, 0, 0, $month, 1, $year));
        }

        $incidents = Incident::with(['motorists.chargeType'])
            ->whereBetween('date_of_incident', [$from, $to])
            ->get(['id', 'date_of_incident', 'time_of_incident', 'status', 'location']);

        $byHour       = array_fill(0, 24, 0);
        $byDay        = array_fill(0, 7, 0);
        $byStatus     = ['under_investigation' => 0, 'cleared' => 0, 'solved' => 0];
        $byChargeType = [];
        $byLocation   = [];

        foreach ($incidents as $inc) {
            if ($inc->time_of_incident) {
                $hour = (int) substr($inc->time_of_incident, 0, 2);
                if ($hour >= 0 && $hour <= 23) $byHour[$hour]++;
            }
            $dow = (int) $inc->date_of_incident->dayOfWeek;
            $byDay[$dow]++;

            $key = $inc->status ?? 'under_investigation';
            if (\array_key_exists($key, $byStatus)) $byStatus[$key]++;

            if ($inc->location) {
                $byLocation[$inc->location] = ($byLocation[$inc->location] ?? 0) + 1;
            }

            foreach ($inc->motorists as $motorist) {
                $name = $motorist->chargeType?->name ?? null;
                if ($name) {
                    $short = preg_replace('/^Reckless Imprudence Resulting in /', 'RIR ', $name);
                    $byChargeType[$short] = ($byChargeType[$short] ?? 0) + 1;
                }
            }
        }

        arsort($byChargeType);
        arsort($byLocation);

        return response()->json([
            'label'        => $label,
            'total'        => $incidents->count(),
            'byHour'       => array_values($byHour),
            'byDay'        => array_values($byDay),
            'byStatus'     => $byStatus,
            'byChargeType' => $byChargeType,
            'byLocation'   => \array_slice($byLocation, 0, 7, true),
            'weekStart'    => $period === 'week' ? $from : null,
        ]);
    }

    private function buildMonthlyData(int $month, int $year, string $search, string $typeFilter, string $municipality = ''): array
    {
        $monthViolations = Violation::with(['violator', 'violationType'])
            ->whereMonth('date_of_violation', $month)
            ->whereYear('date_of_violation', $year)
            ->when($typeFilter, fn($q) => $q->where('violation_type_id', $typeFilter))
            ->when($municipality, fn($q) => $q->where('location', 'ilike', '%' . $municipality . '%'))
            ->get();

        if ($search !== '') {
            $monthViolations = $monthViolations->filter(
                fn($v) => str_contains(strtolower($v->violator->full_name ?? ''), strtolower($search))
            );
        }

        $monthlySummary = $monthViolations
            ->groupBy('violation_type_id')
            ->map(fn($group) => [
                'type'      => $group->first()->violationType,
                'count'     => $group->count(),
                'pending'   => $group->where('status', 'pending')->count(),
                'settled'   => $group->where('status', 'settled')->count(),
                'contested' => $group->where('status', 'contested')->count(),
            ])
            ->sortByDesc('count')
            ->values();

        $monthlyOffenders = $monthViolations
            ->groupBy('violator_id')
            ->map(fn($group) => [
                'violator'   => $group->first()->violator,
                'count'      => $group->count(),
                'violations' => $group->sortBy('date_of_violation')->values(),
            ])
            ->sortByDesc('count')
            ->values();

        return [
            'yearMatrix'         => [],
            'monthTotals'        => [],
            'yearViolatorMatrix' => [],
            'yearOverview'       => collect(),
            'totalThisMonth'     => $monthViolations->count(),
            'monthlySummary'     => $monthlySummary,
            'monthlyOffenders'   => $monthlyOffenders,
        ];
    }
}
