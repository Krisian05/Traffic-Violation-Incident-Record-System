<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\Violator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProvinceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        // Aggregate violations per LGU (falls back to "Unassigned" for legacy records
        // captured before lgu_id existed, or where the location lookup found no match).
        $municipalityStats = Violation::whereYear('violations.date_of_violation', $year)
            ->leftJoin('lgus', 'lgus.id', '=', 'violations.lgu_id')
            ->selectRaw("
                COALESCE(lgus.name, 'Unassigned') as municipality_name,
                COUNT(*) as total_violations,
                SUM(CASE WHEN violations.status = 'settled' THEN 1 ELSE 0 END) as settled_violations
            ")
            ->groupBy('lgus.id', 'lgus.name')
            ->orderByDesc('total_violations')
            ->get()
            ->map(function ($item) {
                $item->settled_rate = $item->total_violations > 0
                    ? round(($item->settled_violations / $item->total_violations) * 100)
                    : 0;
                return $item;
            });

        // Overall stats
        $totalViolations = Violation::whereYear('date_of_violation', $year)->count();
        $totalViolators = Violator::count(); // All-time registered violators
        $totalActiveOfficers = User::whereIn('role', ['traffic_officer', 'operator'])->count();

        // Monthly trend across entire province
        $monthlyTrend = Violation::whereYear('date_of_violation', $year)
            ->selectRaw("EXTRACT(MONTH FROM date_of_violation) as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $chartLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = $monthlyTrend[$m] ?? 0;
        }

        return view('province.dashboard', compact(
            'year', 'municipalityStats', 'totalViolations', 'totalViolators', 
            'totalActiveOfficers', 'chartLabels', 'chartData'
        ));
    }
}
