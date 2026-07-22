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

        // Financial Metrics across Province
        $totalRevenueCollected = Payment::whereHas('violation', function ($q) use ($year, $selectedLguId) {
                $q->whereYear('date_of_violation', $year)
                  ->when($selectedLguId, fn($sq) => $sq->where('lgu_id', $selectedLguId));
            })->sum('amount_paid');

        // If no direct payment records, fall back to settled violation fine amounts
        if ($totalRevenueCollected == 0) {
            $totalRevenueCollected = (clone $baseViolationQuery)
                ->where('status', 'settled')
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->sum('violation_types.fine_amount');
        }

        $totalUncollectedFines = (clone $baseViolationQuery)
            ->whereIn('status', ['pending', 'overdue'])
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->sum('violation_types.fine_amount');

        // Location Hotspots
        $provincialHotspots = (clone $baseViolationQuery)
            ->whereNotNull('location')->where('location', '!=', '')
            ->select('location', DB::raw('COUNT(*) as total'))
            ->groupBy('location')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Monthly Trend
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
            'provincialHotspots', 'chartLabels', 'chartData', 'categoryLabels', 'categoryData'
        ));
    }
}
