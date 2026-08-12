@extends('layouts.app')

@section('title', 'Collection Reports & Payment Analytics')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payment Reports</li>
@endsection

@section('content')
<style>
    .pm-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 1rem !important;
    }
    .pm-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important;
    }
    .pm-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .pm-badge-method {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.3em 0.65em;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .pm-filter-input {
        font-size: 0.85rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
    }
    .pm-filter-input:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
    }
    .pm-table th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
    }
    .pm-table td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
    }
    .pm-month-btn {
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.4rem 0.85rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .pm-month-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .pm-month-btn.active {
        background: linear-gradient(135deg, #059669, #047857);
        color: #ffffff;
        border-color: #047857;
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);
    }

    /* Print styles */
    .gov-print-hdr { display: none; }

    /* ── Quick Period Filter Buttons ── */
    .pm-period-shortcut {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.3rem 0.8rem;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }
    .pm-period-shortcut:hover {
        background: #f0fdf4;
        border-color: #059669;
        color: #047857;
    }
    .pm-period-shortcut.active {
        background: linear-gradient(135deg, #059669, #047857);
        color: #fff;
        border-color: #047857;
        box-shadow: 0 3px 10px rgba(5,150,105,0.25);
    }
    .pm-period-shortcut-clear {
        border-color: #fca5a5;
        color: #dc2626;
    }
    .pm-period-shortcut-clear:hover {
        background: #fef2f2;
        border-color: #ef4444;
        color: #dc2626;
    }
</style>

<script>
function submitFilterPeriod(period, year) {
    var form   = document.getElementById('topReportFilterForm');
    var pInput = document.getElementById('hiddenPeriodInput');
    var yInput = document.getElementById('hiddenYearInput');

    pInput.value = period;
    if (year) {
        yInput.value = year;
    }
    form.submit();
}

function setPeriodFilter(from, to) {
    var df = document.getElementById('filterDateFrom');
    var dt = document.getElementById('filterDateTo');
    if (df) df.value = from;
    if (dt) dt.value = to;
    var form = document.getElementById('reconciliationFilterForm');
    if (form) form.submit();
}
</script>

<style>
    @media print {
        .no-print, .sidebar, .sidebar-backdrop, .topbar, .hamburger-btn, form, .pagination, .btn, .tvrs-breadcrumb-nav, nav { display: none !important; }
        body { background: #fff !important; color: #000 !important; font-size: 10pt; margin: 0 !important; padding: 0 !important; }
        .content { padding: 0 !important; margin: 0 !important; }
        .main-wrapper { margin-left: 0 !important; }
        
        .gov-print-hdr {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2pt solid #047857;
            padding-bottom: 8pt;
            margin-bottom: 12pt;
        }
        
        .pm-card { border: none !important; box-shadow: none !important; margin-bottom: 1rem !important; }
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table th, .table td { border: 1px solid #cbd5e1 !important; padding: 4pt 6pt !important; }
        @page { size: A4 landscape; margin: 10mm; }
    }
</style>

<div class="container-fluid px-4 py-3">

    {{-- Official Print Header --}}
    <div class="gov-print-hdr mb-3">
        <img src="{{ asset('images/PNP.png') }}" class="gov-ph-seal" alt="PNP Logo" style="height:55px;">
        <div class="gov-ph-agency text-center flex-grow-1">
            <div class="gov-ph-republic small text-uppercase" style="letter-spacing:1px;">Republic of the Philippines</div>
            <div class="gov-ph-npc fw-bold small">NATIONAL POLICE COMMISSION</div>
            <div class="gov-ph-pro7 fw-bold small">PHILIPPINE NATIONAL POLICE, POLICE REGIONAL OFFICE 7</div>
            <div class="gov-ph-cebu fw-bold small">CEBU POLICE PROVINCIAL OFFICE</div>
            <div class="gov-ph-station fw-bold text-success text-uppercase mt-1" style="font-size:0.95rem;">
                @if(Auth::user()->lgu)
                    {{ strtoupper(Auth::user()->lgu->name) }} MUNICIPAL TREASURY &amp; TRAFFIC DIVISION
                @else
                    TRAFFIC VIOLATION INCIDENT RECORD SYSTEM (TVIRS)
                @endif
            </div>
            <div class="gov-ph-address fst-italic text-muted" style="font-size:0.75rem;">Payment Reconciliation &amp; Transaction Audit Log Report</div>
        </div>
        <img src="{{ asset('images/cebu.png') }}" class="gov-ph-seal" alt="Cebu Seal" style="height:55px;">
    </div>

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 no-print">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                 style="width:54px;height:54px;background:linear-gradient(135deg,#059669,#047857);color:#fff;flex-shrink:0;">
                <i class="bi bi-cash-coin" style="font-size:1.5rem;"></i>
            </div>
            <div>
                <div class="text-uppercase fw-bold text-success" style="font-size:.7rem;letter-spacing:.09em;">Financial Reporting</div>
                <h3 class="mb-0 fw-extrabold text-dark" style="letter-spacing:-0.02em;">Collection Reports &amp; Payment Analytics</h3>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap no-print">
            <form method="GET" action="{{ route('payments.report') }}" class="d-flex align-items-center gap-2 flex-wrap" id="topReportFilterForm">
                @if(Auth::user()->isSuperAdmin() || Auth::user()->isProvinceAdmin())
                <select name="lgu_id" class="form-select form-select-sm shadow-sm pm-filter-input fw-semibold" style="width:auto;min-width:170px;" onchange="this.form.submit()">
                    <option value="">All Municipalities / LGUs</option>
                    @foreach($lgus as $lgu)
                        <option value="{{ $lgu->id }}" {{ (string) $selectedLguId === (string) $lgu->id ? 'selected' : '' }}>{{ $lgu->name }}</option>
                    @endforeach
                </select>
                @endif

                <input type="hidden" name="period" id="hiddenPeriodInput" value="{{ $period }}">
                <input type="hidden" name="year" id="hiddenYearInput" value="{{ $year }}">

                @php
                    $currentFilterLabel = match($period) {
                        'daily'   => 'Daily (Today)',
                        'weekly'  => 'Weekly (This Week)',
                        'monthly' => 'Monthly (This Month)',
                        'custom'  => 'Custom Range',
                        default   => 'Year ' . $year,
                    };
                    $currentFilterIcon = match($period) {
                        'daily'   => 'bi-sun-fill text-warning',
                        'weekly'  => 'bi-calendar-week-fill text-primary',
                        'monthly' => 'bi-calendar-month-fill text-success',
                        'custom'  => 'bi-funnel-fill text-info',
                        default   => 'bi-calendar3 text-success',
                    };
                @endphp

                <div class="dropdown">
                    <button class="btn btn-sm bg-white border border-slate-300 fw-bold text-dark shadow-sm dropdown-toggle d-flex align-items-center gap-2 px-3 py-1-5 rounded-3"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.875rem;min-height:34px;">
                        <i class="bi {{ $currentFilterIcon }}"></i>
                        <span>{{ $currentFilterLabel }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-1 py-2" style="font-size:0.85rem;min-width:210px;z-index:1050;">
                        <li><h6 class="dropdown-header text-uppercase fw-extrabold text-muted" style="font-size:0.68rem;letter-spacing:0.05em;">Quick Periods</h6></li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 {{ $period === 'daily' ? 'active fw-bold bg-success text-white' : '' }}"
                                    onclick="submitFilterPeriod('daily')">
                                <i class="bi bi-sun-fill text-warning me-1"></i> Daily (Today)
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 {{ $period === 'weekly' ? 'active fw-bold bg-success text-white' : '' }}"
                                    onclick="submitFilterPeriod('weekly')">
                                <i class="bi bi-calendar-week-fill text-primary me-1"></i> Weekly (This Week)
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 {{ $period === 'monthly' ? 'active fw-bold bg-success text-white' : '' }}"
                                    onclick="submitFilterPeriod('monthly')">
                                <i class="bi bi-calendar-month-fill text-success me-1"></i> Monthly (This Month)
                            </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><h6 class="dropdown-header text-uppercase fw-extrabold text-muted" style="font-size:0.68rem;letter-spacing:0.05em;">Annual Reports</h6></li>
                        @for($y = date('Y'); $y >= 2020; $y--)
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 {{ $period === 'yearly' && $year == $y ? 'active fw-bold bg-success text-white' : '' }}"
                                    onclick="submitFilterPeriod('yearly', {{ $y }})">
                                <i class="bi bi-calendar3 text-secondary me-1"></i> Year {{ $y }}
                            </button>
                        </li>
                        @endfor
                    </ul>
                </div>
            </form>

            <a href="{{ route('payments.report.export', request()->query()) }}" class="btn btn-sm btn-success shadow-sm fw-bold px-3 py-1-5 rounded-3 d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
            </a>
        </div>
    </div>

    @if(Auth::user()->lgu_id && !Auth::user()->isSuperAdmin() && !Auth::user()->isProvinceAdmin())
    <div class="alert alert-emerald border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between no-print" style="background:#ecfdf5;color:#047857;border-radius:12px;font-size:.875rem;">
        <div>
            <i class="bi bi-shield-check me-2 fs-5 align-middle"></i>
            <span>Showing payment collection records for <strong>{{ Auth::user()->lgu?->name ?? 'assigned municipality' }}</strong> ({{ $periodLabel }}).</span>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 text-uppercase fw-bold" style="font-size:0.68rem;">LGU Scoped</span>
    </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4 no-print">
        {{-- Revenue Collected --}}
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm pm-card h-100 overflow-hidden" style="background:linear-gradient(135deg,#ffffff 0%,#f0fdf4 100%);border-left:5px solid #10b981 !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.06em;">Total Revenue Collected</span>
                        <div class="pm-stat-icon bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">₱{{ number_format($paidAmount, 2) }}</div>
                    <div class="d-flex align-items-center gap-1 text-muted" style="font-size:.75rem;">
                        <i class="bi bi-calendar-check text-success"></i>
                        <span>Payments settled in {{ $periodLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Outstanding Balance --}}
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm pm-card h-100 overflow-hidden" style="background:linear-gradient(135deg,#ffffff 0%,#fef2f2 100%);border-left:5px solid #ef4444 !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.06em;">Outstanding Receivables</span>
                        <div class="pm-stat-icon bg-danger-subtle text-danger"><i class="bi bi-clock-history"></i></div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">₱{{ number_format($unpaidAmount, 2) }}</div>
                    <div class="d-flex align-items-center gap-1 text-muted" style="font-size:.75rem;">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                        <span>Pending balances ({{ $periodLabel }})</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Citation Status Breakdown --}}
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm pm-card h-100 overflow-hidden" style="background:linear-gradient(135deg,#ffffff 0%,#eff6ff 100%);border-left:5px solid #3b82f6 !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.06em;">Citation Payment Status</span>
                        <div class="pm-stat-icon bg-primary-subtle text-primary"><i class="bi bi-pie-chart-fill"></i></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2" style="font-size:.8rem;">
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2.5 py-1.5 rounded-2">
                            <i class="bi bi-check-circle-fill me-1"></i> Settled: {{ $statusCounts['settled'] ?? 0 }}
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold px-2.5 py-1.5 rounded-2">
                            <i class="bi bi-hourglass-split me-1"></i> Pending: {{ $statusCounts['pending'] ?? 0 }}
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-bold px-2.5 py-1.5 rounded-2">
                            <i class="bi bi-shield-exclamation me-1"></i> Contested: {{ $statusCounts['contested'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Monthly Summary Section --}}
    <div class="card border-0 shadow-sm pm-card mb-4 overflow-hidden no-print">
        <div class="card-header bg-white border-0 pt-3.5 px-3.5 pb-2">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-extrabold text-dark mb-0 d-flex align-items-center gap-2" style="letter-spacing:-0.01em;">
                        <i class="bi bi-table text-success fs-5"></i>
                        <span>Monthly Summary - Numbers of Violations &amp; Revenue For the Month of <span id="summaryMonthName" class="text-success">June</span> <span id="summaryYearDisplay">{{ $year }}</span></span>
                    </h5>
                    <span class="text-muted" style="font-size:0.78rem;">Click any month below to inspect exact recorded violation counts and total revenue collected</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2.5 py-1.5" style="font-size:0.75rem;">
                        Year {{ $year }} Data
                    </span>
                </div>
            </div>

            {{-- 12-Month Interactive Bar --}}
            <div class="d-flex align-items-center gap-1.5 flex-wrap pb-2 border-bottom">
                @php
                    $monthsList = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    // Default to current month or June
                    $defaultMonth = (date('Y') == $year) ? (int) date('m') : 6;
                @endphp
                @foreach($monthsList as $mNum => $mName)
                    <button type="button" 
                            class="pm-month-btn {{ $mNum === $defaultMonth ? 'active' : '' }}" 
                            onclick="selectSummaryMonth({{ $mNum }}, '{{ $mName }}')">
                        {{ substr($mName, 0, 3) }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="card-body p-3.5">
            <div class="col-12">
                <div class="border rounded-3 overflow-hidden bg-white shadow-sm">
                    <div class="bg-light px-3.5 py-2.5 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size:0.88rem;">
                            <i class="bi bi-clipboard2-data-fill text-success me-1"></i> Violation Types, Recorded Violations &amp; Revenue Summary
                        </h6>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-secondary-subtle text-secondary font-monospace" id="totalViolationsCountBadge">0 Total Violations</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fw-bold" id="totalCollectibleBadge">₱0.00 Total Collectible</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fw-bold" id="totalRevenueBadge">₱0.00 Total Collection</span>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace fw-bold" id="totalBalanceBadge">₱0.00 Uncollected</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table pm-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3.5">Type of Violation</th>
                                    <th class="text-center" style="width:130px;">Total Number</th>
                                    <th class="text-end" style="width:200px;">Total Collectible Fine</th>
                                    <th class="text-end" style="width:230px;">Total Collection Collected</th>
                                    <th class="text-end pe-3.5" style="width:180px;">Uncollected Balance</th>
                                </tr>
                            </thead>
                            <tbody id="violationsSummaryBody">
                                {{-- Dynamically populated via JS --}}
                            </tbody>
                            <tfoot class="table-light border-top">
                                <tr class="fw-bold text-dark">
                                    <td class="ps-3.5">Monthly Grand Total</td>
                                    <td class="text-center" id="tfootTotalNumber">0</td>
                                    <td class="text-end text-primary font-monospace" style="font-size:0.92rem;" id="tfootTotalCollectible">₱0.00</td>
                                    <td class="text-end text-success font-monospace" style="font-size:0.92rem;" id="tfootTotalRevenue">₱0.00</td>
                                    <td class="text-end pe-3.5 text-danger font-monospace" style="font-size:0.92rem;" id="tfootTotalBalance">₱0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance & Annual Summaries Row --}}
    <div class="row g-3 mb-4 no-print">
        {{-- Annual Collection History --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm pm-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3.5">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-range text-info me-1.5"></i> Annual Revenue History</h6>
                    <span class="text-muted" style="font-size:0.75rem;">5-year historical collection total</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3.5">Year</th>
                                <th class="text-end pe-3.5">Revenue Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($annualCollection as $y => $total)
                                <tr>
                                    <td class="ps-3.5 fw-bold text-dark"><i class="bi bi-calendar3 me-1 text-muted"></i> {{ $y }}</td>
                                    <td class="text-end pe-3.5 fw-bold text-success">₱{{ number_format($total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="ps-3 py-3 text-muted text-center">No historical collection records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- LGU Performance Ranking --}}
        {{-- LGU / Barangay Performance Ranking --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm pm-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-trophy-fill text-warning me-1.5"></i> {{ $selectedLguId ? 'Barangay Collection Performance' : 'LGU Collection Performance' }} ({{ $year }})</h6>
                        <span class="text-muted" style="font-size:0.75rem;">{{ $selectedLguId ? 'Settlement rates and total revenue per barangay / location' : 'Settlement rates and total revenue per municipality' }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.84rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3.5">{{ $selectedLguId ? 'Barangay / Location' : 'Municipality / LGU' }}</th>
                                    <th class="text-center">Total Issued</th>
                                    <th class="text-center">Settled</th>
                                    <th class="text-center">Settlement Rate</th>
                                    <th class="text-end pe-3.5">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selectedLguId ? $locationPerformance : $lguPerformance as $row)
                                <tr>
                                    <td class="ps-3.5 fw-bold text-dark">
                                        <i class="bi bi-geo-alt-fill text-danger me-1" style="font-size:0.8rem;"></i>
                                        {{ $row->name }} @if(!$selectedLguId && !empty($row->code))<span class="text-muted fw-normal">({{ $row->code }})</span>@endif
                                    </td>
                                    <td class="text-center fw-semibold">{{ number_format($row->total) }}</td>
                                    <td class="text-center fw-bold text-success">{{ number_format($row->settled) }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $row->settled_rate }}%"></div>
                                            </div>
                                            <span class="fw-bold" style="font-size:0.78rem;">{{ $row->settled_rate }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3.5 fw-extrabold text-success">₱{{ number_format($row->revenue, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="py-4 text-center text-muted">No collection performance data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Payment Reconciliation Section --}}
    <div class="card border-0 shadow-sm pm-card">
        <div class="card-header bg-white border-0 pt-3.5 px-3.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-receipt-cutoff text-success me-1.5"></i> Payment Reconciliation &amp; Transaction Audit Log</h6>
                <span class="text-muted" style="font-size:0.75rem;">Verified payments issued with Official Receipts (OR)</span>
            </div>
            <div class="d-flex align-items-center gap-2 no-print">
                <a href="{{ route('payments.report.export', request()->query()) }}" class="btn btn-sm btn-outline-success fw-bold px-2.5 rounded-2 d-inline-flex align-items-center gap-1" title="Export Excel Report">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary fw-bold px-2.5 rounded-2 no-print d-inline-flex align-items-center gap-1" title="Print Transaction Log">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
        
        {{-- Print Filter Metadata Header --}}
        <div class="d-none d-print-block px-3.5 pt-2 pb-1 border-bottom bg-light text-muted" style="font-size:0.8rem;">
            <i class="bi bi-funnel-fill text-success me-1"></i> <strong>Report Filter Context:</strong>
            Payment Method: <u>{{ request('method') ? ucfirst(request('method')) : 'All Payment Methods' }}</u>
            @if(request('date_from') || request('date_to'))
                | Date Range: <u>{{ request('date_from') ?: 'Beginning' }}</u> to <u>{{ request('date_to') ?: 'Present' }}</u>
            @endif
            @if(request('or_number'))
                | Query: <u>"{{ request('or_number') }}"</u>
            @endif
            | Printed On: <u>{{ now()->format('F d, Y h:i A') }}</u>
        </div>
        <div class="card-body p-3.5">

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('payments.report') }}" class="row g-2 mb-3 bg-light p-2.5 rounded-3 border no-print" id="reconciliationFilterForm">
                <input type="hidden" name="year" value="{{ $year }}">
                @if($selectedLguId)
                    <input type="hidden" name="lgu_id" value="{{ $selectedLguId }}">
                @endif
                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">FROM DATE</label>
                    <input type="date" name="date_from" id="filterDateFrom" class="form-control form-control-sm pm-filter-input" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">TO DATE</label>
                    <input type="date" name="date_to" id="filterDateTo" class="form-control form-control-sm pm-filter-input" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">METHOD</label>
                    <select name="method" class="form-select form-select-sm pm-filter-input">
                        <option value="">All Payment Methods</option>
                        @foreach(['cash'=>'Cash','gcash'=>'GCash','maya'=>'Maya','bank'=>'Bank Transfer','other'=>'Other'] as $val => $label)
                            <option value="{{ $val }}" {{ request('method') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">SEARCH OR / TICKET</label>
                    <input type="text" name="or_number" class="form-control form-control-sm pm-filter-input" value="{{ request('or_number') }}" placeholder="OR # or search query…">
                </div>
                <div class="col-md-1 col-sm-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-success w-100 fw-bold shadow-sm" style="height:31px;" title="Apply Filter">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table pm-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3.5">Official Receipt (OR) #</th>
                            <th>Citation Ticket #</th>
                            <th>Violator Name</th>
                            <th>{{ $selectedLguId ? 'Barangay / Location' : 'LGU / Municipality' }}</th>
                            <th class="text-end">Amount Paid</th>
                            <th>Payment Method</th>
                            <th>Cashier / Collector</th>
                            <th>Transaction Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td class="ps-3.5 fw-bold text-dark font-monospace" style="font-size:0.88rem;color:#0f172a;">
                                <i class="bi bi-receipt text-success me-1"></i> {{ $p->or_number }}
                            </td>
                            <td class="fw-semibold">
                                @if($p->violation)
                                    <a href="{{ route('violations.show', $p->violation) }}" class="text-decoration-none text-primary fw-bold">
                                        {{ $p->violation->ticket_number ?: '#' . $p->violation->id }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-dark">{{ $p->violation?->violator?->full_name ?? '—' }}</td>
                            <td>
                                @if($selectedLguId)
                                    <span class="badge bg-light text-dark border" title="{{ $p->violation?->lgu?->name }}">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $p->violation?->location ?: ($p->violation?->lgu?->name ?? '—') }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ $p->violation?->lgu?->name ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-extrabold text-success" style="font-size:0.9rem;">₱{{ number_format($p->amount_paid, 2) }}</td>
                            <td>
                                @php
                                    $methodMap = [
                                        'cash' => ['bg' => '#ecfdf5', 'color' => '#047857', 'icon' => 'bi-cash-stack'],
                                        'gcash' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'icon' => 'bi-phone'],
                                        'maya' => ['bg' => '#fdf4ff', 'color' => '#86198f', 'icon' => 'bi-wallet2'],
                                        'bank' => ['bg' => '#f8fafc', 'color' => '#334155', 'icon' => 'bi-bank'],
                                    ];
                                    $mInfo = $methodMap[strtolower($p->payment_method)] ?? ['bg' => '#f1f5f9', 'color' => '#475569', 'icon' => 'bi-credit-card'];
                                @endphp
                                <span class="pm-badge-method" style="background:{{ $mInfo['bg'] }};color:{{ $mInfo['color'] }};border:1px solid {{ $mInfo['bg'] }};">
                                    <i class="bi {{ $mInfo['icon'] }} me-1"></i> {{ ucfirst($p->payment_method) }}
                                </span>
                            </td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1 text-secondary" style="font-size:0.82rem;">
                                    <i class="bi bi-person-circle"></i> {{ $p->collector?->name ?? $p->cashier_name }}
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                <i class="bi bi-clock me-1"></i> {{ $p->paid_at->format('M d, Y g:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-5 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <span>No payment transactions match the selected filters.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light border-top">
                        <tr class="fw-bold text-dark">
                            <td colspan="4" class="ps-3.5 text-uppercase" style="letter-spacing:0.04em;font-size:0.82rem;">Total Collection</td>
                            <td class="text-end text-success font-monospace" style="font-size:0.95rem;">₱{{ number_format($payments->sum('amount_paid'), 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3.5 px-2 no-print">
                {{ $payments->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const monthlyViolationsData = @json($monthlyViolationsSummary);
    const defaultMonthNum = {{ $defaultMonth }};

    function selectSummaryMonth(monthNum, monthName) {
        // 1. Update active month button
        document.querySelectorAll('.pm-month-btn').forEach(btn => btn.classList.remove('active'));
        const clickedBtn = document.querySelector(`.pm-month-btn:nth-child(${monthNum})`);
        if (clickedBtn) clickedBtn.classList.add('active');

        // 2. Update Header Title
        document.getElementById('summaryMonthName').innerText = monthName;

        // 3. Render Violations & Revenue Table Body
        const vBody = document.getElementById('violationsSummaryBody');
        const vList = monthlyViolationsData[monthNum] || [];
        let vHtml = '';
        let vTotalCount = 0;
        let vTotalCollectible = 0;
        let vTotalRevenue = 0;
        let vTotalBalance = 0;

        if (vList.length === 0) {
            vHtml = `<tr><td colspan="5" class="text-center py-4 text-muted">No violation records for ${monthName}.</td></tr>`;
        } else {
            vList.forEach(item => {
                vTotalCount       += item.total;
                vTotalCollectible += item.collectible;
                vTotalRevenue     += item.revenue;
                vTotalBalance     += item.balance;

                const countBadgeStyle = item.total > 0 
                    ? 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;' 
                    : 'background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0;';
                    
                const collectibleStyle = item.collectible > 0 
                    ? 'color:#0284c7;font-weight:700;' 
                    : 'color:#94a3b8;';

                const revStyle = item.revenue > 0 
                    ? 'color:#059669;font-weight:800;' 
                    : 'color:#94a3b8;';

                const balanceStyle = item.balance > 0 
                    ? 'color:#dc2626;font-weight:700;' 
                    : 'color:#94a3b8;';

                vHtml += `
                    <tr>
                        <td class="ps-3.5 fw-semibold text-dark">
                            ${escapeHtml(item.name)}
                            <div style="font-size:.72rem;color:#78716c;font-weight:normal;">Base Fine: ₱${formatMoney(item.fine_amount)}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge px-3 py-1 rounded-2 fw-bold" style="${countBadgeStyle}">
                                ${item.total}
                            </span>
                        </td>
                        <td class="text-end font-monospace" style="${collectibleStyle}">
                            ₱${formatMoney(item.collectible)}
                        </td>
                        <td class="text-end font-monospace" style="${revStyle}">
                            ₱${formatMoney(item.revenue)}
                        </td>
                        <td class="text-end pe-3.5 font-monospace" style="${balanceStyle}">
                            ₱${formatMoney(item.balance)}
                        </td>
                    </tr>
                `;
            });
        }
        vBody.innerHTML = vHtml;

        // 4. Update Header Badges & Footer Totals
        document.getElementById('totalViolationsCountBadge').innerText = `${vTotalCount} Total Violations`;
        document.getElementById('totalCollectibleBadge').innerText    = `₱${formatMoney(vTotalCollectible)} Total Collectible`;
        document.getElementById('totalRevenueBadge').innerText        = `₱${formatMoney(vTotalRevenue)} Total Collection`;
        document.getElementById('totalBalanceBadge').innerText        = `₱${formatMoney(vTotalBalance)} Uncollected`;

        document.getElementById('tfootTotalNumber').innerText      = vTotalCount;
        document.getElementById('tfootTotalCollectible').innerText = `₱${formatMoney(vTotalCollectible)}`;
        document.getElementById('tfootTotalRevenue').innerText     = `₱${formatMoney(vTotalRevenue)}`;
        document.getElementById('tfootTotalBalance').innerText     = `₱${formatMoney(vTotalBalance)}`;
    }

    function formatMoney(amount) {
        return Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        const monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        selectSummaryMonth(defaultMonthNum, monthNames[defaultMonthNum]);
    });
</script>
@endpush
