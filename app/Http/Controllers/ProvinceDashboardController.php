<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\Violator;
use Illuminate\Http\Request;

class ProvinceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        $lgus = Lgu::orderBy('name')->get();

        // One grouped query for every LGU's violation counts this year
        $countsByLgu = Violation::whereYear('date_of_violation', $year)
            ->selectRaw("lgu_id, COUNT(*) as total_violations, SUM(CASE WHEN status = 'settled' THEN 1 ELSE 0 END) as settled_violations")
            ->groupBy('lgu_id')
            ->get()
            ->keyBy('lgu_id');

        $municipalityStats = $lgus->map(function ($lgu) use ($countsByLgu) {
            $row = $countsByLgu->get($lgu->id);
            $total = (int) ($row->total_violations ?? 0);
            $settled = (int) ($row->settled_violations ?? 0);

            return (object) [
                'municipality_name'  => $lgu->name,
                'total_violations'   => $total,
                'settled_violations' => $settled,
                'settled_rate'       => $total > 0 ? round(($settled / $total) * 100) : 0,
            ];
        })->sortByDesc('total_violations')->values();

        $totalViolations     = Violation::whereYear('date_of_violation', $year)->count();
        $totalViolators      = Violator::count();
        $totalActiveOfficers = User::whereIn('role', ['traffic_officer', 'operator'])->count();

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
