<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\Payment;
use App\Models\User;
use App\Models\Violation;
use App\Models\Violator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProvinceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $selectedLguId = $request->input('lgu_id');

        $lgus = Lgu::orderBy('name')->get();

        // Query for every LGU's violation counts and financial totals this year
        $countsByLgu = Violation::whereYear('date_of_violation', $year)
            ->when($selectedLguId, fn($q) => $q->where('lgu_id', $selectedLguId))
            ->selectRaw("lgu_id, COUNT(*) as total_violations, SUM(CASE WHEN status = 'settled' THEN 1 ELSE 0 END) as settled_violations")
            ->groupBy('lgu_id')
            ->get()
            ->keyBy('lgu_id');

        $municipalityStats = $lgus->when($selectedLguId, fn($collection) => $collection->where('id', $selectedLguId))
            ->map(function ($lgu) use ($countsByLgu) {
                $row = $countsByLgu->get($lgu->id);
                $total = (int) ($row->total_violations ?? 0);
                $settled = (int) ($row->settled_violations ?? 0);

                return (object) [
                    'id'                 => $lgu->id,
                    'municipality_name'  => $lgu->name,
                    'code'               => $lgu->code,
                    'total_violations'   => $total,
                    'settled_violations' => $settled,
                    'settled_rate'       => $total > 0 ? round(($settled / $total) * 100) : 0,
                ];
            })->sortByDesc('total_violations')->values();

        $baseViolationQuery = Violation::whereYear('date_of_violation', $year)
            ->when($selectedLguId, fn($q) => $q->where('lgu_id', $selectedLguId));

        $totalViolationsAllTime = Violation::when($selectedLguId, fn($q) => $q->where('lgu_id', $selectedLguId))->count();
        $totalViolations        = (clone $baseViolationQuery)->count();
        $totalViolators         = Violator::when($selectedLguId, fn($q) => $q->where('lgu_id', $selectedLguId))->count();
        $totalActiveOfficers    = User::whereIn('role', ['traffic_officer', 'operator'])
                                    ->when($selectedLguId, fn($q) => $q->where('lgu_id', $selectedLguId))
                                    ->count();

        // Financial Metrics across Province — every settlement writes a Payment row
        // (see PaymentService), so this is now the single source of truth for revenue.
        $totalRevenueCollected = Payment::whereHas('violation', function ($q) use ($year, $selectedLguId) {
                $q->whereYear('date_of_violation', $year)
                  ->when($selectedLguId, fn($sq) => $sq->where('lgu_id', $selectedLguId));
            })->sum('amount_paid');

        // Outstanding = (base fine + late penalty if overdue) − amount already paid,
        // for every violation not yet fully settled.
        $totalFineAndPenaltyDue = (clone $baseViolationQuery)
            ->whereIn('status', ['pending', 'partial'])
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->selectRaw("COALESCE(SUM(violation_types.fine_amount + CASE WHEN violations.due_date IS NOT NULL AND violations.due_date < CURRENT_DATE THEN COALESCE(violation_types.late_penalty_amount, 0) ELSE 0 END), 0) as total")
            ->value('total');

        $totalPaidTowardOutstanding = Payment::whereHas('violation', function ($q) use ($year, $selectedLguId) {
                $q->whereYear('date_of_violation', $year)
                  ->whereIn('status', ['pending', 'partial'])
                  ->when($selectedLguId, fn($sq) => $sq->where('lgu_id', $selectedLguId));
            })->sum('amount_paid');

        $totalUncollectedFines = max(0, $totalFineAndPenaltyDue - $totalPaidTowardOutstanding);

        // Feature 4: Location Hotspots & GIS Map Points
        $provincialHotspots = (clone $baseViolationQuery)
            ->whereNotNull('location')->where('location', '!=', '')
            ->select('location', DB::raw('COUNT(*) as total'))
            ->groupBy('location')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $mapPoints = (clone $baseViolationQuery)
            ->whereNotNull('gps_lat')->whereNotNull('gps_lng')
            ->with(['violationType:id,name', 'lgu:id,name'])
            ->limit(50)
            ->get(['id', 'gps_lat', 'gps_lng', 'location', 'violation_type_id', 'lgu_id', 'date_of_violation']);

        // Feature 5: Repeat Offender Intelligence
        $repeatOffenders = Violator::withCount(['violations' => fn($q) =>
                $q->whereYear('date_of_violation', $year)
                  ->when($selectedLguId, fn($sq) => $sq->where('lgu_id', $selectedLguId))
            ])
            ->whereHas('violations', fn($q) =>
                $q->whereYear('date_of_violation', $year)
                  ->when($selectedLguId, fn($sq) => $sq->where('lgu_id', $selectedLguId))
            , '>', 1)
            ->orderByDesc('violations_count')
            ->limit(5)
            ->get();

        // Feature 6: Officer & Unit Productivity Monitoring
        $topOfficers = Violation::whereYear('date_of_violation', $year)
            ->when($selectedLguId, fn($q) => $q->where('lgu_id', $selectedLguId))
            ->join('users', 'violations.recorded_by', '=', 'users.id')
            ->select(
                'users.id', 'users.name', 'users.role',
                DB::raw('COUNT(*) as total_issued'),
                DB::raw("SUM(CASE WHEN violations.status = 'settled' THEN 1 ELSE 0 END) as total_settled")
            )
            ->groupBy('users.id', 'users.name', 'users.role')
            ->orderByDesc('total_issued')
            ->limit(5)
            ->get();

        // Feature 7: Monthly Enforcement Trend Analysis
        $monthlyTrend = (clone $baseViolationQuery)
            ->get(['date_of_violation'])
            ->groupBy(fn($v) => (int) ($v->date_of_violation?->format('n') ?? 0))
            ->map(fn($group) => $group->count());

        $chartLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = $monthlyTrend[$m] ?? 0;
        }

        // Violation Category Distribution
        $categoriesQuery = (clone $baseViolationQuery)
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->selectRaw('violation_types.name, COUNT(*) as total')
            ->groupBy('violation_types.name')
            ->orderByDesc('total')
            ->get();

        $categoryLabels = [];
        $categoryData = [];
        $topCategories = $categoriesQuery->take(4);
        foreach ($topCategories as $cat) {
            $categoryLabels[] = $cat->name;
            $categoryData[] = (int) $cat->total;
        }
        if ($categoriesQuery->count() > 4) {
            $otherTotal = $categoriesQuery->slice(4)->sum('total');
            $categoryLabels[] = 'Other Violations';
            $categoryData[] = (int) $otherTotal;
        }

        return view('province.dashboard', compact(
            'year', 'lgus', 'selectedLguId', 'municipalityStats', 'totalViolations', 'totalViolationsAllTime',
            'totalViolators', 'totalActiveOfficers', 'totalRevenueCollected', 'totalUncollectedFines',
            'provincialHotspots', 'mapPoints', 'repeatOffenders', 'topOfficers',
            'chartLabels', 'chartData', 'categoryLabels', 'categoryData'
        ));
    }
}
