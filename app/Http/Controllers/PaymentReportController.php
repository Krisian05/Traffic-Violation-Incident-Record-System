<?php

namespace App\Http\Controllers;

use App\Exports\PaymentsExport;
use App\Models\Lgu;
use App\Models\Payment;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Collection Reporting & Analytics + Treasurer's Office Payment Management.
 *
 * Treasurers are forced to their own LGU (their office only monitors/reconciles
 * their own municipality's collections); admins/operators/province admins may
 * optionally filter by LGU or see the whole province.
 */
class PaymentReportController extends Controller
{
    private function resolveLguFilter(Request $request)
    {
        $user = Auth::user();
        if ($user->isTreasurer()) {
            return $user->lgu_id;
        }

        return $request->input('lgu_id') ?: null;
    }

    public function index(Request $request)
    {
        $year          = (int) $request->input('year', now()->year);
        $selectedLguId = $this->resolveLguFilter($request);
        $isPgsql       = DB::getDriverName() === 'pgsql';

        $lgus = Lgu::orderBy('name')->get();

        // ── Daily collection (last 30 days) ─────────────────────────────────
        $dailyRaw = Payment::whereBetween('paid_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->when($selectedLguId, fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $selectedLguId)))
            ->selectRaw('DATE(paid_at) as day, SUM(amount_paid) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $dailyLabels = [];
        $dailyData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $d             = now()->subDays($i);
            $dailyLabels[] = $d->format('M d');
            $dailyData[]   = (float) ($dailyRaw[$d->toDateString()] ?? 0);
        }

        // ── Monthly collection summary (selected year) ──────────────────────
        $monthExpr = $isPgsql ? 'EXTRACT(MONTH FROM paid_at)::int as m' : "CAST(strftime('%m', paid_at) AS INTEGER) as m";
        $monthlyRaw = Payment::whereYear('paid_at', $year)
            ->when($selectedLguId, fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $selectedLguId)))
            ->selectRaw("$monthExpr, SUM(amount_paid) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthlyLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthlyData   = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[] = (float) ($monthlyRaw[$m] ?? 0);
        }

        // ── Annual summary (last 5 years) ────────────────────────────────────
        $yearExpr = $isPgsql ? 'EXTRACT(YEAR FROM paid_at)::int as y' : "CAST(strftime('%Y', paid_at) AS INTEGER) as y";
        $annualCollection = Payment::when($selectedLguId, fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $selectedLguId)))
            ->selectRaw("$yearExpr, SUM(amount_paid) as total")
            ->groupBy('y')
            ->orderByDesc('y')
            ->limit(5)
            ->pluck('total', 'y');

        // ── Paid vs unpaid analysis (selected year) ──────────────────────────
        $baseViolationQuery = Violation::whereYear('date_of_violation', $year)
            ->when($selectedLguId, fn($q) => $q->where('lgu_id', $selectedLguId));

        $statusCounts = (clone $baseViolationQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $paidAmount = Payment::whereHas('violation', function ($q) use ($year, $selectedLguId) {
            $q->whereYear('date_of_violation', $year)
              ->when($selectedLguId, fn($sq) => $sq->where('lgu_id', $selectedLguId));
        })->sum('amount_paid');

        $dueAmount = (clone $baseViolationQuery)
            ->whereIn('status', ['pending', 'partial'])
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->selectRaw("COALESCE(SUM(violation_types.fine_amount + CASE WHEN violations.due_date IS NOT NULL AND violations.due_date < CURRENT_DATE THEN COALESCE(violation_types.late_penalty_amount, 0) ELSE 0 END), 0) as total")
            ->value('total');

        $paidTowardOutstanding = Payment::whereHas('violation', function ($q) use ($year, $selectedLguId) {
            $q->whereYear('date_of_violation', $year)
              ->whereIn('status', ['pending', 'partial'])
              ->when($selectedLguId, fn($sq) => $sq->where('lgu_id', $selectedLguId));
        })->sum('amount_paid');

        $unpaidAmount = max(0, $dueAmount - $paidTowardOutstanding);

        // ── LGU collection performance ranking ───────────────────────────────
        $lguPerformance = $lgus
            ->when($selectedLguId, fn($collection) => $collection->where('id', $selectedLguId))
            ->map(function ($lgu) use ($year) {
                $violations = Violation::whereYear('date_of_violation', $year)->where('lgu_id', $lgu->id);
                $total      = (clone $violations)->count();
                $settled    = (clone $violations)->where('status', 'settled')->count();
                $revenue    = Payment::whereHas('violation', fn($q) => $q->whereYear('date_of_violation', $year)->where('lgu_id', $lgu->id))->sum('amount_paid');

                return (object) [
                    'name'         => $lgu->name,
                    'code'         => $lgu->code,
                    'total'        => $total,
                    'settled'      => $settled,
                    'settled_rate' => $total > 0 ? round(($settled / $total) * 100) : 0,
                    'revenue'      => (float) $revenue,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // ── Reconciliation table ─────────────────────────────────────────────
        $payments = Payment::with(['violation.violator', 'violation.lgu', 'collector'])
            ->when($selectedLguId, fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $selectedLguId)))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('paid_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('paid_at', '<=', $request->input('date_to')))
            ->when($request->filled('method'), fn($q) => $q->where('payment_method', $request->input('method')))
            ->when($request->filled('or_number'), fn($q) => $q->where('or_number', 'like', '%' . $request->input('or_number') . '%'))
            ->orderByDesc('paid_at')
            ->paginate(25)
            ->withQueryString();

        return view('payments.report', compact(
            'year', 'lgus', 'selectedLguId',
            'dailyLabels', 'dailyData',
            'monthlyLabels', 'monthlyData',
            'annualCollection',
            'statusCounts', 'paidAmount', 'unpaidAmount',
            'lguPerformance', 'payments'
        ));
    }

    public function exportExcel(Request $request)
    {
        $selectedLguId = $this->resolveLguFilter($request);

        $filters = [
            'lgu_id'    => $selectedLguId,
            'date_from' => $request->input('date_from'),
            'date_to'   => $request->input('date_to'),
            'method'    => $request->input('method'),
            'or_number' => $request->input('or_number'),
        ];

        return (new PaymentsExport($filters))->download('TVIRS-Collections-' . now()->format('Y-m-d') . '.xlsx');
    }
}
