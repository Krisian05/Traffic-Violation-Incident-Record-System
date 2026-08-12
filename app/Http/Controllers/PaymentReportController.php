<?php

namespace App\Http\Controllers;

use App\Exports\PaymentsExport;
use App\Models\IncidentChargeType;
use App\Models\IncidentMotorist;
use App\Models\Lgu;
use App\Models\Payment;
use App\Models\Violation;
use App\Models\ViolationType;
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
        if ($user && $user->lgu_id && !$user->isSuperAdmin() && !$user->isProvinceAdmin()) {
            return $user->lgu_id;
        }

        return $request->input('lgu_id') ?: null;
    }

    public function index(Request $request)
    {
        $period        = $request->input('period');
        $year          = (int) $request->input('year', now()->year);
        $selectedLguId = $this->resolveLguFilter($request);
        $isPgsql       = DB::getDriverName() === 'pgsql';

        $lgus = Lgu::orderBy('name')->get();

        // ── Resolve period and date range ───────────────────────────────────
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $start = \Carbon\Carbon::parse($request->input('date_from'))->startOfDay();
            $end   = \Carbon\Carbon::parse($request->input('date_to'))->endOfDay();
            if (!$period) {
                if ($start->toDateString() === now()->toDateString() && $end->toDateString() === now()->toDateString()) {
                    $period = 'daily';
                } elseif ($start->toDateString() === now()->startOfWeek()->toDateString() && $end->toDateString() === now()->endOfWeek()->toDateString()) {
                    $period = 'weekly';
                } elseif ($start->toDateString() === now()->startOfMonth()->toDateString() && $end->toDateString() === now()->endOfMonth()->toDateString()) {
                    $period = 'monthly';
                } else {
                    $period = 'custom';
                }
            }
        } else {
            $period = $period ?: 'yearly';
            switch ($period) {
                case 'daily':
                    $start = now()->startOfDay();
                    $end   = now()->endOfDay();
                    break;
                case 'weekly':
                    $start = now()->startOfWeek();
                    $end   = now()->endOfWeek();
                    break;
                case 'monthly':
                    $start = now()->startOfMonth();
                    $end   = now()->endOfMonth();
                    break;
                case 'yearly':
                default:
                    $period = 'yearly';
                    $start  = \Carbon\Carbon::create($year)->startOfYear();
                    $end    = \Carbon\Carbon::create($year)->endOfYear();
                    break;
            }
        }

        $dateFrom = $start->toDateString();
        $dateTo   = $end->toDateString();

        $periodLabel = match ($period) {
            'daily'   => 'Daily (' . $start->format('M d, Y') . ')',
            'weekly'  => 'Weekly (' . $start->format('M d') . ' – ' . $end->format('M d, Y') . ')',
            'monthly' => 'Monthly (' . $start->format('F Y') . ')',
            'custom'  => 'Custom (' . $start->format('M d, Y') . ' – ' . $end->format('M d, Y') . ')',
            default   => 'Year ' . $year,
        };

        // ── Daily collection (last 30 days) ─────────────────────────────────
        $dailyRaw = Payment::active()
            ->whereBetween('paid_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
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

        // ── Monthly collection summary (selected period/year) ───────────────
        $monthExpr = $isPgsql ? 'EXTRACT(MONTH FROM paid_at)::int as m' : "CAST(strftime('%m', paid_at) AS INTEGER) as m";
        $monthlyRaw = Payment::active()
            ->whereBetween('paid_at', [$start, $end])
            ->when($selectedLguId, fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $selectedLguId)))
            ->selectRaw("$monthExpr, SUM(amount_paid) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthlyLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthlyData   = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[] = (float) ($monthlyRaw[$m] ?? 0);
        }

        // ── Monthly Violation Breakdown & Revenue ───────────────────────────
        $violationDateExpr = $isPgsql ? 'EXTRACT(MONTH FROM date_of_violation)::int' : "CAST(strftime('%m', date_of_violation) AS INTEGER)";
        $paymentDateExpr   = $isPgsql ? 'EXTRACT(MONTH FROM violations.date_of_violation)::int' : "CAST(strftime('%m', violations.date_of_violation) AS INTEGER)";

        $violationTypes = ViolationType::orderBy('name')->get();

        $violationsByMonth = Violation::join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->whereBetween('violations.date_of_violation', [$dateFrom, $dateTo])
            ->when($selectedLguId, fn($q) => $q->where('violations.lgu_id', $selectedLguId))
            ->selectRaw("$violationDateExpr as m, violations.violation_type_id, COUNT(*) as total, SUM(violation_types.fine_amount) as total_collectible")
            ->groupBy('m', 'violations.violation_type_id')
            ->get()
            ->groupBy('m');

        $revenueByMonth = Payment::active()
            ->join('violations', 'payments.violation_id', '=', 'violations.id')
            ->whereBetween('violations.date_of_violation', [$dateFrom, $dateTo])
            ->when($selectedLguId, fn($q) => $q->where('violations.lgu_id', $selectedLguId))
            ->selectRaw("$paymentDateExpr as m, violations.violation_type_id, SUM(payments.amount_paid) as total_revenue")
            ->groupBy('m', 'violations.violation_type_id')
            ->get()
            ->groupBy('m');

        $monthlyViolationsSummary = [];

        for ($m = 1; $m <= 12; $m++) {
            $mViolationsGroup = isset($violationsByMonth[$m]) ? $violationsByMonth[$m]->keyBy('violation_type_id') : collect();
            $mRevenueGroup    = isset($revenueByMonth[$m]) ? $revenueByMonth[$m]->keyBy('violation_type_id') : collect();

            $monthlyViolationsSummary[$m] = $violationTypes->map(function ($type) use ($mViolationsGroup, $mRevenueGroup) {
                $vRow = $mViolationsGroup[$type->id] ?? null;
                $rRow = $mRevenueGroup[$type->id] ?? null;

                $total       = (int) ($vRow?->total ?? 0);
                $collectible = (float) ($vRow?->total_collectible ?? 0);
                $revenue     = (float) ($rRow?->total_revenue ?? 0);
                $balance     = max(0, $collectible - $revenue);

                return [
                    'id'          => $type->id,
                    'name'        => $type->name,
                    'fine_amount' => (float) $type->fine_amount,
                    'total'       => $total,
                    'collectible' => $collectible,
                    'revenue'     => $revenue,
                    'balance'     => $balance,
                ];
            })->values();
        }

        // ── Annual summary (last 5 years) ────────────────────────────────────
        $yearExpr = $isPgsql ? 'EXTRACT(YEAR FROM paid_at)::int as y' : "CAST(strftime('%Y', paid_at) AS INTEGER) as y";
        $annualCollection = Payment::active()
            ->when($selectedLguId, fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $selectedLguId)))
            ->selectRaw("$yearExpr, SUM(amount_paid) as total")
            ->groupBy('y')
            ->orderByDesc('y')
            ->limit(5)
            ->pluck('total', 'y');

        // ── Paid vs unpaid analysis (selected period) ──────────────────────────
        $baseViolationQuery = Violation::whereBetween('date_of_violation', [$dateFrom, $dateTo])
            ->when($selectedLguId, fn($q) => $q->where('lgu_id', $selectedLguId));

        $statusCounts = (clone $baseViolationQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $paidAmount = Payment::active()
            ->whereBetween('paid_at', [$start, $end])
            ->when($selectedLguId, fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $selectedLguId)))
            ->sum('amount_paid');

        $dueAmount = (clone $baseViolationQuery)
            ->where('status', 'pending')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->selectRaw("COALESCE(SUM(violation_types.fine_amount + CASE WHEN violations.due_date IS NOT NULL AND violations.due_date < CURRENT_DATE THEN COALESCE(violation_types.late_penalty_amount, 0) ELSE 0 END), 0) as total")
            ->value('total');

        $paidTowardOutstanding = Payment::active()
            ->whereHas('violation', function ($q) use ($dateFrom, $dateTo, $selectedLguId) {
                $q->whereBetween('date_of_violation', [$dateFrom, $dateTo])
                  ->where('status', 'pending')
                  ->when($selectedLguId, fn($sq) => $sq->where('lgu_id', $selectedLguId));
            })->sum('amount_paid');

        $unpaidAmount = max(0, $dueAmount - $paidTowardOutstanding);

        // ── LGU / Barangay collection performance ranking ─────────────────────────
        if ($selectedLguId) {
            $locationCounts = Violation::whereBetween('date_of_violation', [$dateFrom, $dateTo])
                ->where('lgu_id', $selectedLguId)
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->select(
                    'location',
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN status = 'settled' THEN 1 ELSE 0 END) as settled")
                )
                ->groupBy('location')
                ->get()
                ->keyBy('location');

            $locationRevenues = Payment::active()
                ->whereHas('violation', fn($q) => $q->whereBetween('date_of_violation', [$dateFrom, $dateTo])->where('lgu_id', $selectedLguId))
                ->join('violations', 'payments.violation_id', '=', 'violations.id')
                ->select('violations.location', DB::raw('SUM(payments.amount_paid) as revenue'))
                ->groupBy('violations.location')
                ->pluck('revenue', 'location');

            $locationPerformance = $locationCounts->map(function ($row, $loc) use ($locationRevenues) {
                $total   = (int) $row->total;
                $settled = (int) $row->settled;
                $revenue = (float) ($locationRevenues[$loc] ?? 0);

                return (object) [
                    'name'         => $loc,
                    'code'         => 'BRGY',
                    'total'        => $total,
                    'settled'      => $settled,
                    'settled_rate' => $total > 0 ? round(($settled / $total) * 100) : 0,
                    'revenue'      => $revenue,
                ];
            })->sortByDesc('revenue')->values();

            $lguPerformance = collect();
        } else {
            $locationPerformance = collect();

            $lguCounts = Violation::whereBetween('date_of_violation', [$dateFrom, $dateTo])
                ->select(
                    'lgu_id',
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN status = 'settled' THEN 1 ELSE 0 END) as settled")
                )
                ->groupBy('lgu_id')
                ->get()
                ->keyBy('lgu_id');

            $lguRevenues = Payment::active()
                ->whereHas('violation', fn($q) => $q->whereBetween('date_of_violation', [$dateFrom, $dateTo]))
                ->join('violations', 'payments.violation_id', '=', 'violations.id')
                ->select('violations.lgu_id', DB::raw('SUM(payments.amount_paid) as revenue'))
                ->groupBy('violations.lgu_id')
                ->pluck('revenue', 'lgu_id');

            $lguPerformance = $lgus->map(function ($lgu) use ($lguCounts, $lguRevenues) {
                $counts  = $lguCounts[$lgu->id] ?? null;
                $total   = (int) ($counts?->total ?? 0);
                $settled = (int) ($counts?->settled ?? 0);
                $revenue = (float) ($lguRevenues[$lgu->id] ?? 0);

                return (object) [
                    'name'         => $lgu->name,
                    'code'         => $lgu->code,
                    'total'        => $total,
                    'settled'      => $settled,
                    'settled_rate' => $total > 0 ? round(($settled / $total) * 100) : 0,
                    'revenue'      => $revenue,
                ];
            })->sortByDesc('revenue')->values();
        }

        // ── Reconciliation table ─────────────────────────────────────────────
        $payments = Payment::with(['violation.violator', 'violation.lgu', 'collector'])
            ->when($selectedLguId, fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $selectedLguId)))
            ->whereBetween('paid_at', [$start, $end])
            ->when($request->filled('method'), fn($q) => $q->where('payment_method', $request->input('method')))
            ->when($request->filled('or_number'), fn($q) => $q->where('or_number', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $request->input('or_number')) . '%'))
            ->orderByDesc('paid_at')
            ->paginate(25)
            ->withQueryString();

        return view('payments.report', compact(
            'period', 'periodLabel', 'dateFrom', 'dateTo',
            'year', 'lgus', 'selectedLguId',
            'dailyLabels', 'dailyData',
            'monthlyLabels', 'monthlyData',
            'annualCollection',
            'statusCounts', 'paidAmount', 'unpaidAmount',
            'lguPerformance', 'locationPerformance', 'payments',
            'monthlyViolationsSummary'
        ));
    }

    public function exportExcel(Request $request)
    {
        $selectedLguId = $this->resolveLguFilter($request);
        $period        = $request->input('period');
        $year          = (int) $request->input('year', now()->year);

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        if (!$dateFrom || !$dateTo) {
            $period = $period ?: 'yearly';
            switch ($period) {
                case 'daily':
                    $dateFrom = now()->startOfDay()->toDateString();
                    $dateTo   = now()->endOfDay()->toDateString();
                    break;
                case 'weekly':
                    $dateFrom = now()->startOfWeek()->toDateString();
                    $dateTo   = now()->endOfWeek()->toDateString();
                    break;
                case 'monthly':
                    $dateFrom = now()->startOfMonth()->toDateString();
                    $dateTo   = now()->endOfMonth()->toDateString();
                    break;
                case 'yearly':
                default:
                    $dateFrom = \Carbon\Carbon::create($year)->startOfYear()->toDateString();
                    $dateTo   = \Carbon\Carbon::create($year)->endOfYear()->toDateString();
                    break;
            }
        }

        $filters = [
            'lgu_id'    => $selectedLguId,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'method'    => $request->input('method'),
            'or_number' => $request->input('or_number'),
        ];

        return (new PaymentsExport($filters))->download('TVIRS-Collections-' . now()->format('Y-m-d') . '.xlsx');
    }
}
