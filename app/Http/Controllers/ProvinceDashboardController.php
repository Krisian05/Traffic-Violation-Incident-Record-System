<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\Violator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Lgu;
use App\Support\Tenant;

class ProvinceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        // Fetch all registered LGUs
        $lgus = Lgu::orderBy('name')->get();

        $municipalityStats = collect();
        $totalViolations = 0;
        $totalViolators = 0;
        $monthlyTrendSum = array_fill(1, 12, 0);

        // Aggregate statistics from each isolated tenant schema
        foreach ($lgus as $lgu) {
            Tenant::switchTo($lgu->code);

            // 1. Violations Count & Settled Rate for this LGU
            $lguViolationsCount = Violation::whereYear('date_of_violation', $year)->count();
            $lguSettledCount = Violation::whereYear('date_of_violation', $year)->where('status', 'settled')->count();

            $municipalityStats->push((object)[
                'municipality_name'  => $lgu->name,
                'total_violations'   => $lguViolationsCount,
                'settled_violations' => $lguSettledCount,
                'settled_rate'       => $lguViolationsCount > 0 ? round(($lguSettledCount / $lguViolationsCount) * 100) : 0,
            ]);

            // 2. Add to overall sums
            $totalViolations += $lguViolationsCount;
            $totalViolators  += Violator::count();

            // 3. Add to monthly trend sums
            $lguMonthlyTrend = Violation::whereYear('date_of_violation', $year)
                ->selectRaw("EXTRACT(MONTH FROM date_of_violation) as month, COUNT(*) as total")
                ->groupBy('month')
                ->pluck('total', 'month');

            foreach ($lguMonthlyTrend as $month => $total) {
                $monthlyTrendSum[(int) $month] += (int) $total;
            }
        }

        // Restore active schema context back to public/landlord
        Tenant::switchTo(null);

        // Order municipal list descending by activity/violations count
        $municipalityStats = $municipalityStats->sortByDesc('total_violations')->values();

        // Overall active officers (stored globally in public schema)
        $totalActiveOfficers = User::whereIn('role', ['traffic_officer', 'operator'])->count();

        // Format chart data arrays
        $chartLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = $monthlyTrendSum[$m];
        }

        return view('province.dashboard', compact(
            'year', 'municipalityStats', 'totalViolations', 'totalViolators', 
            'totalActiveOfficers', 'chartLabels', 'chartData'
        ));
    }
}
