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

        $violators = Violator::where('first_name', 'like', "%{$q}%")
            ->orWhere('last_name', 'like', "%{$q}%")
            ->orWhere('middle_name', 'like', "%{$q}%")
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(8)
            ->get(['id', 'first_name', 'middle_name', 'last_name']);

        return response()->json($violators->map(fn($v) => $v->full_name)->values());
    }

    public function index(Request $request): View
    {
        $month      = $request->input('month', 0);
        $year       = (int) $request->input('year', now()->year);
        $search     = trim($request->input('search', ''));
        $typeFilter = (string) ($request->input('type_filter') ?? '');
        $showAll    = ($month == 0);

        $repeatOffenders = Violator::withCount('violations')
            ->has('violations', '>', 1)
            ->orderByDesc('violations_count')
            ->get();

        $allTypes = ViolationType::orderBy('name')->get();
        $minYear  = (int) (Violation::min('date_of_violation')
                        ? substr(Violation::min('date_of_violation'), 0, 4)
                        : now()->year);

        $overdueViolations = Violation::with(['violator', 'violationType', 'vehicle'])
            ->overdue()
            ->orderBy('created_at')
            ->get();

        $incBase = Incident::whereYear('date_of_incident', $year)
            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_incident', $month));

        $commonData = $this->gatherCommonReportData($year, $month, $showAll, $allTypes, $incBase, $overdueViolations);

        $data = $showAll
            ? $this->buildYearlyData($year, $search, $typeFilter, $allTypes)
            : $this->buildMonthlyData((int) $month, $year, $search, $typeFilter);

        $topViolators = collect($data['yearViolatorMatrix'] ?? [])->take(8)->mapWithKeys(function ($item) {
            return [$item['violator']->full_name ?? 'Unknown' => $item['total'] ?? 0];
        });

        return view('reports.index', array_merge([
            'repeatOffenders' => $repeatOffenders,
            'month' => $month,
            'year' => $year,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'showAll' => $showAll,
            'allTypes' => $allTypes,
            'minYear' => $minYear,
            'overdueViolations' => $overdueViolations,
            'topViolators' => $topViolators,
        ], $commonData, $data));
    }

    private function gatherCommonReportData(int $year, int $month, bool $showAll, $allTypes, $incBase, $overdueViolations): array
    {
        $totalIncidents     = $incBase->count();
        $incidentsByStatus  = (clone $incBase)->select('status', DB::raw('COUNT(*) as total'))
                                ->groupBy('status')->pluck('total', 'status');

        $incidentHotspots   = (clone $incBase)->whereNotNull('location')->where('location', '!=', '')
                                ->select('location', DB::raw('COUNT(*) as total'))
                                ->groupBy('location')->orderByDesc('total')->limit(7)->get();

        $violationHotspots  = Violation::whereYear('date_of_violation', $year)
                                ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
                                ->whereNotNull('location')->where('location', '!=', '')
                                ->select('location', DB::raw('COUNT(*) as total'))
                                ->groupBy('location')->orderByDesc('total')->limit(7)->get();

        $settledCount = Violation::whereYear('date_of_violation', $year)
                            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
                            ->where('status', 'settled')
                            ->count();

        $contestedCount = Violation::whereYear('date_of_violation', $year)
                            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
                            ->where('status', 'contested')
                            ->count();

        $pendingActiveCount = Violation::whereYear('date_of_violation', $year)
                            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
                            ->pendingActive()
                            ->count();

        $totalViolators = Violation::whereYear('date_of_violation', $year)
                            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
                            ->distinct('violator_id')
                            ->count('violator_id');

        $overdueCount = $overdueViolations->count();

        $violationsByType = Violation::whereYear('date_of_violation', $year)
            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
            ->select('violation_type_id', DB::raw('COUNT(*) as total'))
            ->groupBy('violation_type_id')
            ->pluck('total', 'violation_type_id');

        $violationsByType = $violationsByType->mapWithKeys(function ($total, $typeId) use ($allTypes) {
            $typeName = $allTypes->firstWhere('id', $typeId)->name ?? 'Unknown';
            return [$typeName => $total];
        });

        $violationStatusCounts = Violation::whereYear('date_of_violation', $year)
            ->when(!$showAll, fn($q) => $q->whereMonth('date_of_violation', $month))
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

    private function buildYearlyData(int $year, string $search, string $typeFilter, Collection $allTypes): array
    {
        $yearViolations = Violation::with([
                'violator:id,first_name,middle_name,last_name',
                'violationType:id,name',
            ])
            ->whereYear('date_of_violation', $year)
            ->get(['id', 'violator_id', 'violation_type_id', 'date_of_violation']);

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

    private function buildMonthlyData(int $month, int $year, string $search, string $typeFilter): array
    {
        $monthViolations = Violation::with(['violator', 'violationType'])
            ->whereMonth('date_of_violation', $month)
            ->whereYear('date_of_violation', $year)
            ->when($typeFilter, fn($q) => $q->where('violation_type_id', $typeFilter))
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
