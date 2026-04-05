@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- ── Dashboard Header: Greeting + Records Burger ── --}}
@php
    $grHour = now()->hour;
    $grWord = $grHour < 12 ? 'morning' : ($grHour < 18 ? 'afternoon' : 'evening');
@endphp
<div class="dash-header-bar mb-3">
    <div class="dash-greeting">
        <span class="dash-greeting-text">Good {{ $grWord }}, <strong>{{ Auth::user()->name }}</strong> 👋</span>
        <span class="dash-greeting-sub">{{ now()->format('l, F d, Y') }} &mdash; Traffic Violation Incident Record System</span>
    </div>
    <div class="dash-header-right d-flex align-items-center gap-2 flex-wrap">
        @if(Auth::user()->isOperator())
        <a href="{{ route('violators.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i>New Motorist
        </a>
        <a href="{{ route('incidents.create') }}" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-flag-fill me-1"></i>New Incident
        </a>
        @endif

        {{-- ════ Records Burger Dropdown ════ --}}
        <div class="records-burger-wrap position-relative">
            <button class="records-burger-btn btn btn-sm" id="recordsBurgerBtn" type="button"
                    aria-haspopup="true" aria-expanded="false" title="Browse Records & Print">
                <span class="records-burger-lines" aria-hidden="true">
                    <span></span><span></span><span></span>
                </span>
                Records
                <i class="bi bi-chevron-down records-burger-chevron" id="recordsBurgerChevron"></i>
            </button>

            <div class="records-panel" id="recordsPanel" role="menu" aria-label="Records navigation">

                <div class="records-panel-header">
                    <i class="bi bi-grid-3x3-gap-fill me-2 text-primary" style="font-size:.85rem;"></i>
                    <strong>Browse Records &amp; Print</strong>
                </div>

                <div class="records-panel-grid">

                    <a href="{{ route('violators.index') }}" class="records-tile" role="menuitem">
                        <div class="records-tile-icon" style="background:linear-gradient(135deg,#60a5fa,#3b82f6);">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <div class="records-tile-body">
                            <div class="records-tile-name">Motorists</div>
                            <div class="records-tile-sub">{{ number_format($totalViolators) }} registered</div>
                        </div>
                        <i class="bi bi-arrow-right-short records-tile-arrow"></i>
                    </a>

                    <a href="{{ route('vehicles.index') }}" class="records-tile" role="menuitem">
                        <div class="records-tile-icon" style="background:linear-gradient(135deg,#38bdf8,#0284c7);">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <div class="records-tile-body">
                            <div class="records-tile-name">Vehicles</div>
                            <div class="records-tile-sub">{{ number_format($totalVehicles) }} registered</div>
                        </div>
                        <i class="bi bi-arrow-right-short records-tile-arrow"></i>
                    </a>

                    <a href="{{ route('violations.index') }}" class="records-tile" role="menuitem">
                        <div class="records-tile-icon" style="background:linear-gradient(135deg,#fbbf24,#d97706);">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="records-tile-body">
                            <div class="records-tile-name">Violations</div>
                            <div class="records-tile-sub">
                                <span class="records-pill-pending">{{ number_format($pendingCount) }} pending</span>
                            </div>
                        </div>
                        <i class="bi bi-arrow-right-short records-tile-arrow"></i>
                    </a>

                    <a href="{{ route('incidents.index') }}" class="records-tile" role="menuitem">
                        <div class="records-tile-icon" style="background:linear-gradient(135deg,#f87171,#ef4444);">
                            <i class="bi bi-flag-fill"></i>
                        </div>
                        <div class="records-tile-body">
                            <div class="records-tile-name">Incidents</div>
                            <div class="records-tile-sub">{{ number_format($incidentsThisMonth) }} this month</div>
                        </div>
                        <i class="bi bi-arrow-right-short records-tile-arrow"></i>
                    </a>

                    <a href="{{ route('violation-types.index') }}" class="records-tile" role="menuitem">
                        <div class="records-tile-icon" style="background:linear-gradient(135deg,#a78bfa,#7c3aed);">
                            <i class="bi bi-tags-fill"></i>
                        </div>
                        <div class="records-tile-body">
                            <div class="records-tile-name">Violation Types</div>
                            <div class="records-tile-sub">Reference list</div>
                        </div>
                        <i class="bi bi-arrow-right-short records-tile-arrow"></i>
                    </a>

                    <a href="{{ route('incident-charge-types.index') }}" class="records-tile" role="menuitem">
                        <div class="records-tile-icon" style="background:linear-gradient(135deg,#34d399,#059669);">
                            <i class="bi bi-shield-exclamation"></i>
                        </div>
                        <div class="records-tile-body">
                            <div class="records-tile-name">Charge Types</div>
                            <div class="records-tile-sub">Incident charge refs</div>
                        </div>
                        <i class="bi bi-arrow-right-short records-tile-arrow"></i>
                    </a>

                    <a href="{{ route('reports.index') }}" class="records-tile records-tile-report" role="menuitem">
                        <div class="records-tile-icon" style="background:linear-gradient(135deg,#fb923c,#ea580c);">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <div class="records-tile-body">
                            <div class="records-tile-name">Reports</div>
                            <div class="records-tile-sub">Generate &amp; print</div>
                        </div>
                        <i class="bi bi-arrow-right-short records-tile-arrow"></i>
                    </a>

                </div>{{-- /records-panel-grid --}}

                <div class="records-panel-divider"></div>

                {{-- Quick Filter & Print chips --}}
                <div class="records-print-section">
                    <div class="records-print-title">
                        <i class="bi bi-printer-fill me-1"></i>Quick Filter &amp; Print
                    </div>
                    <div class="records-print-chips">
                        <a href="{{ route('violations.index', ['status' => 'pending']) }}"
                           class="records-chip records-chip-warning">
                            <i class="bi bi-hourglass-split me-1"></i>Pending Violations
                        </a>
                        <a href="{{ route('violations.index', ['status' => 'overdue']) }}"
                           class="records-chip records-chip-danger">
                            <i class="bi bi-alarm-fill me-1"></i>Overdue Violations
                        </a>
                        <a href="{{ route('violations.index', ['status' => 'settled']) }}"
                           class="records-chip records-chip-success">
                            <i class="bi bi-check-circle-fill me-1"></i>Settled Violations
                        </a>
                        <a href="{{ route('violations.index', ['month' => now()->month, 'year' => now()->year]) }}"
                           class="records-chip records-chip-blue">
                            <i class="bi bi-calendar-fill me-1"></i>This Month's Violations
                        </a>
                        <a href="{{ route('incidents.index', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->endOfMonth()->toDateString()]) }}"
                           class="records-chip records-chip-blue">
                            <i class="bi bi-flag me-1"></i>This Month's Incidents
                        </a>
                        <a href="{{ route('reports.index') }}"
                           class="records-chip records-chip-orange">
                            <i class="bi bi-printer-fill me-1"></i>Generate Full Report
                        </a>
                    </div>
                </div>{{-- /records-print-section --}}

            </div>{{-- /records-panel --}}
        </div>{{-- /records-burger-wrap --}}
    </div>{{-- /dash-header-right --}}
</div>{{-- /dash-header-bar --}}

{{-- ── Quick Search ── --}}
<div class="dash-search-wrap mb-4">
    <div class="dash-search-inner">
        <i class="bi bi-search dash-search-icon"></i>
        <input type="text" id="dashSearchInput" class="dash-search-input"
               placeholder="Search motorist, violation, incident, plate, ticket, location…" autocomplete="off">
        <span class="dash-search-kbd" id="dashSearchKbd" style="display:none;">ESC to clear</span>
    </div>
    <div id="dashSearchDropdown" class="dash-search-dropdown"></div>
</div>

{{-- ── Stat Cards ── --}}
<div class="row g-4 mb-4">

    <div class="col-6 col-xl-3 stat-card" data-delay="50">
        <a href="{{ route('violators.index') }}" class="stat-card-link">
        <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative">
            <div class="stat-bg-blob" style="background:rgba(59,130,246,.06);"></div>
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#60a5fa,#3b82f6);">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Total Motorists</div>
                    <div class="stat-number" id="stat-violators" data-target="{{ $totalViolators }}">0</div>
                    <div class="stat-sub">registered profiles</div>
                </div>
            </div>
            <div class="stat-card-footer">View all <i class="bi bi-arrow-right"></i></div>
        </div>
        </a>
    </div>

    <div class="col-6 col-xl-3 stat-card" data-delay="150">
        <a href="{{ route('violations.index', ['status' => 'pending']) }}" class="stat-card-link">
        <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative">
            <div class="stat-bg-blob" style="background:rgba(245,158,11,.06);"></div>
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#fb923c,#f59e0b);">
                    <i class="bi bi-hourglass-split"></i>
                    <span class="stat-pulse" style="background:#fcd34d;"></span>
                </div>
                <div>
                    <div class="stat-label">Pending Violators</div>
                    <div class="stat-number" id="stat-pending" data-target="{{ $pendingCount }}">0</div>
                    <div class="stat-sub">with unsettled tickets</div>
                </div>
            </div>
            <div class="stat-card-footer">View all <i class="bi bi-arrow-right"></i></div>
        </div>
        </a>
    </div>

    <div class="col-6 col-xl-3 stat-card" data-delay="250">
        <a href="{{ route('violations.index', ['month' => now()->month, 'year' => now()->year]) }}" class="stat-card-link">
        <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative">
            <div class="stat-bg-blob" style="background:rgba(16,185,129,.06);"></div>
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#34d399,#10b981);">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Violations This Month</div>
                    <div class="stat-number" id="stat-violations-month" data-target="{{ $violationsThisMonth }}">0</div>
                    <div class="stat-sub">{{ now()->format('F Y') }}</div>
                </div>
            </div>
            <div class="stat-card-footer">View all <i class="bi bi-arrow-right"></i></div>
        </div>
        </a>
    </div>

    <div class="col-6 col-xl-3 stat-card" data-delay="350">
        <a href="{{ route('incidents.index', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->endOfMonth()->toDateString()]) }}" class="stat-card-link">
        <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative">
            <div class="stat-bg-blob" style="background:rgba(99,102,241,.06);"></div>
            <div class="card-body d-flex align-items-center gap-3 py-4">
                <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#818cf8,#6366f1);">
                    <i class="bi bi-car-front-fill"></i>
                </div>
                <div>
                    <div class="stat-label">Incidents This Month</div>
                    <div class="stat-number" id="stat-incidents-month" data-target="{{ $incidentsThisMonth }}">0</div>
                    <div class="stat-sub">{{ now()->format('F Y') }}</div>
                </div>
            </div>
            <div class="stat-card-footer">View all <i class="bi bi-arrow-right"></i></div>
        </div>
        </a>
    </div>

</div>

{{-- ── Analytics Overview ── --}}
<div class="analytics-section mb-4">

    {{-- Section Header with period tabs --}}
    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
        <div>
            <h6 class="analytics-section-title mb-0">Analytics Overview</h6>
            <span class="text-muted" style="font-size:.75rem;" id="aGlobalPeriodLabel">Loading…</span>
        </div>
        <div class="analytics-period-tabs" role="group" aria-label="Period selector">
            <button class="analytics-tab active" data-period="weekly"  type="button">Weekly</button>
            <button class="analytics-tab"        data-period="monthly" type="button">Monthly</button>
            <button class="analytics-tab"        data-period="yearly"  type="button">Yearly</button>
        </div>
    </div>

    {{-- Dynamic Stat Cards --}}
    <div class="row g-3 mb-4">

        {{-- Period Violations --}}
        <div class="col-sm-6 col-xl-4">
            <a href="{{ route('violations.index') }}" id="aLink-violations" class="stat-card-link analytics-card-link">
            <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative analytics-card">
                <div class="stat-bg-blob" style="background:rgba(245,158,11,.06);"></div>
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#fbbf24,#d97706);">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label" id="aLabel-violations">Weekly Violations</div>
                        <div class="stat-number analytics-number" id="aStat-violations">—</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="stat-sub" id="aSub-violations">loading…</span>
                            <span class="trend-badge" id="aTrend-violations" style="display:none;"></span>
                        </div>
                    </div>
                </div>
                <div class="stat-card-footer">View all <i class="bi bi-arrow-right"></i></div>
            </div>
            </a>
        </div>

        {{-- Period Traffic Incidents --}}
        <div class="col-sm-6 col-xl-4">
            <a href="{{ route('incidents.index') }}" id="aLink-incidents" class="stat-card-link analytics-card-link">
            <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative analytics-card">
                <div class="stat-bg-blob" style="background:rgba(99,102,241,.06);"></div>
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#818cf8,#6366f1);">
                        <i class="bi bi-car-front-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label" id="aLabel-incidents">Weekly Traffic Incidents</div>
                        <div class="stat-number analytics-number" id="aStat-incidents">—</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="stat-sub" id="aSub-incidents">loading…</span>
                            <span class="trend-badge" id="aTrend-incidents" style="display:none;"></span>
                        </div>
                    </div>
                </div>
                <div class="stat-card-footer">View all <i class="bi bi-arrow-right"></i></div>
            </div>
            </a>
        </div>

        {{-- Overdue Violations --}}
        <div class="col-sm-6 col-xl-4">
            <a href="{{ route('violations.index', ['status' => 'overdue']) }}" class="stat-card-link analytics-card-link">
            <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative analytics-card">
                <div class="stat-bg-blob" style="background:rgba(220,38,38,.06);"></div>
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#f87171,#dc2626);">
                        <i class="bi bi-alarm-fill"></i>
                        <span class="stat-pulse"></span>
                    </div>
                    <div>
                        <div class="stat-label">Overdue Violations</div>
                        <div class="stat-number analytics-number" id="aStat-overdue">—</div>
                        <div class="stat-sub">unpaid past 72 hours</div>
                    </div>
                </div>
                <div class="stat-card-footer">View overdue <i class="bi bi-arrow-right"></i></div>
            </div>
            </a>
        </div>

    </div>

    {{-- Charts Row --}}
    <div class="row g-4">

        {{-- Violations Trend Chart --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100 analytics-chart-card">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">
                        <i class="bi bi-bar-chart-line-fill me-2 text-warning"></i>
                        <span id="aChartTitle-violations">Weekly Violations Trend</span>
                    </span>
                    <span class="badge bg-warning bg-opacity-10 text-warning" id="aChartBadge-violations">This Week</span>
                </div>
                <div class="card-body position-relative" style="min-height:240px;">
                    <div id="aChartLoading-violations" class="analytics-loading-overlay" style="display:flex;">
                        <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
                    </div>
                    <canvas id="analyticsViolationsChart" height="220"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Incident Locations (Barangays) --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100 analytics-chart-card">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">
                        <i class="bi bi-geo-alt-fill me-2 text-info"></i>
                        Top Barangays by Incidents
                    </span>
                    <span class="badge bg-info bg-opacity-10 text-info" id="aChartBadge-barangay">This Week</span>
                </div>
                <div class="card-body" id="aBarangayBody" style="overflow-y:auto;max-height:290px;">
                    <div class="analytics-loading-inline">
                        <div class="spinner-border spinner-border-sm text-info" role="status"></div>
                        <span class="ms-2 text-muted" style="font-size:.85rem;">Loading…</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Violation Status Breakdown ── --}}
@php
    $pendingAllCount = $totalViolationsAll - $settledCount;
    $pendingAllRate  = $totalViolationsAll > 0 ? round($pendingAllCount / $totalViolationsAll * 100) : 0;
@endphp
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3 px-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-bold text-nowrap" style="font-size:.75rem;color:#57534e;">All-time Status:</span>
            <div class="d-flex gap-2 flex-wrap flex-grow-1">
                <a href="{{ route('violations.index', ['status' => 'settled']) }}"
                   class="settlement-pill settlement-settled text-decoration-none">
                    <i class="bi bi-check-circle-fill"></i>
                    Settled <strong>{{ $settlementRate }}%</strong>
                    <span class="opacity-75">({{ number_format($settledCount) }})</span>
                </a>
                <a href="{{ route('violations.index', ['status' => 'pending']) }}"
                   class="settlement-pill settlement-pending-pill text-decoration-none">
                    <i class="bi bi-hourglass-split"></i>
                    Pending <strong>{{ $pendingAllRate }}%</strong>
                    <span class="opacity-75">({{ number_format($pendingAllCount) }})</span>
                </a>
            </div>
            <div class="settlement-bar-wrap flex-shrink-0 d-none d-md-flex" style="width:160px;">
                <div class="settlement-bar">
                    <div class="settlement-bar-seg settlement-bar-settled" data-width="{{ $settlementRate }}" title="Settled {{ $settlementRate }}%"></div>
                    <div class="settlement-bar-seg settlement-bar-pending" data-width="{{ $pendingAllRate }}" title="Pending {{ $pendingAllRate }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Main Row: Chart + Offenders ── --}}
<div class="row g-4 mb-4">

    {{-- Bar Chart --}}
    <div class="col-lg-7 fade-in-up" data-delay="400">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold">
                    <i class="bi bi-bar-chart-fill me-2 text-primary"></i>Top Violation Types
                </span>
                <span class="badge bg-primary bg-opacity-10 text-primary">All Time</span>
            </div>
            <div class="card-body">
                @if($topViolations->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bar-chart fs-1 d-block mb-2 opacity-25"></i>
                        No violations recorded yet.
                    </div>
                @else
                    {{-- Chart data stored in data-attributes, read by pure JS below --}}
                    <canvas id="violationsChart" height="220"
                        data-labels='@json($topViolations->pluck('name'))'
                        data-values='@json($topViolations->pluck('violations_count'))'></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- Repeat Offenders List --}}
    <div class="col-lg-5 fade-in-up" data-delay="500">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold">
                    <i class="bi bi-person-exclamation me-2 text-danger"></i>Repeat Offenders &amp; Recidivists
                </span>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">Report</a>
            </div>
            <div class="card-body p-0" style="overflow-y:auto;max-height:390px;">
                @if($repeatOffenders->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-person-check fs-1 d-block mb-2 opacity-25"></i>
                        No repeat offenders recorded.
                    </div>
                @else
                @php $maxCount = $repeatOffenders->max('violations_count'); @endphp
                <ul class="list-group list-group-flush">
                    @foreach($repeatOffenders as $i => $violator)
                    @php
                        $isRecidivist = $violator->violations_count >= 3;
                        $initials     = strtoupper(substr($violator->first_name, 0, 1) . substr($violator->last_name, 0, 1));
                        $pct          = $maxCount > 0 ? round(($violator->violations_count / $maxCount) * 100) : 0;
                        $medalClass   = ['offender-rank-gold', 'offender-rank-silver', 'offender-rank-bronze'][$i] ?? '';
                    @endphp
                    <li class="list-group-item list-group-item-action px-3 py-2 offender-row border-0 border-bottom"
                        data-row-delay="{{ $i * 60 }}">
                        <div class="d-flex align-items-center gap-3">

                            {{-- Rank badge --}}
                            <div class="offender-rank {{ $i < 3 ? 'offender-rank-medal ' . $medalClass : '' }}">
                                @if($i === 0)<i class="bi bi-trophy-fill"></i>
                                @elseif($i === 1)<i class="bi bi-award-fill"></i>
                                @elseif($i === 2)<i class="bi bi-patch-check-fill"></i>
                                @else {{ $i + 1 }}
                                @endif
                            </div>

                            {{-- Initials avatar --}}
                            <div class="offender-avatar {{ $isRecidivist ? 'offender-avatar-danger' : 'offender-avatar-warning' }}">
                                {{ $initials }}
                            </div>

                            {{-- Name + mini bar --}}
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="d-flex justify-content-between align-items-center gap-1">
                                    <span class="fw-semibold text-truncate" style="font-size:.875rem;">
                                        {{ $violator->full_name }}
                                    </span>
                                    <span class="offender-badge {{ $isRecidivist ? 'offender-badge-danger' : 'offender-badge-warning' }} flex-shrink-0">
                                        {{ $isRecidivist ? 'Recidivist' : 'Repeat' }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <div class="offender-bar-track flex-grow-1">
                                        <div class="offender-bar {{ $isRecidivist ? 'offender-bar-danger' : 'offender-bar-warning' }}"
                                             data-width="{{ $pct }}"></div>
                                    </div>
                                    <span class="fw-bold flex-shrink-0 {{ $isRecidivist ? 'text-danger' : 'text-warning' }}"
                                          style="font-size:.75rem;">
                                        {{ $violator->violations_count }}×
                                    </span>
                                </div>
                            </div>

                            {{-- View button --}}
                            <a href="{{ route('violators.show', $violator) }}"
                               class="btn btn-sm offender-view-btn {{ $isRecidivist ? 'offender-view-danger' : 'offender-view-warning' }}"
                               title="View profile">
                                <i class="bi bi-eye-fill"></i>
                            </a>

                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Overdue Violations (72h+) ── --}}
<div id="overdueSection" class="row g-4 mt-2 mb-4">
    <div class="col-12 fade-in-up" data-delay="650">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center"
                 style="background:linear-gradient(135deg,#fef2f2,#fff);">
                <span class="fw-bold">
                    <i class="bi bi-alarm-fill me-2" style="color:#dc2626;"></i>Overdue Violations
                    <span class="badge ms-2" style="background:rgba(220,38,38,.12);color:#dc2626;font-size:.7rem;">
                        72+ hours unpaid
                    </span>
                </span>
                <span class="badge" style="background:rgba(220,38,38,.12);color:#dc2626;border-radius:99px;font-size:.75rem;padding:.3rem .75rem;">
                    {{ $overdueTotal }} record{{ $overdueTotal !== 1 ? 's' : '' }}
                </span>
            </div>
            @if($overdueViolations->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-2 d-block mb-2 text-success opacity-50"></i>
                    No overdue violations. All pending tickets are within 72 hours.
                </div>
            @else
            <div class="table-responsive">
                <table class="table align-middle mb-0 dash-pend-table">
                    <thead>
                        <tr>
                            <th>Motorist</th>
                            <th>Violation</th>
                            <th>Date</th>
                            <th>Ticket #</th>
                            <th>Hours Overdue</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueViolations as $v)
                        @php $hrs = max(0, (int) now()->diffInHours($v->created_at) - 72); @endphp
                        <tr class="dash-pend-row dash-pend-overdue">
                            <td>
                                @if($v->violator)
                                <a href="{{ route('violators.show', $v->violator) }}" class="dash-pend-name">
                                    {{ $v->violator->full_name }}
                                </a>
                                @else
                                <span class="dash-pend-name">{{ $v->vehicle_owner_name ?? '(Unknown)' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($v->violationType)
                                <a href="{{ route('violations.index', ['type' => $v->violationType->id]) }}"
                                   class="dash-vtype-pill text-decoration-none">{{ $v->violationType->name }}</a>
                                @else
                                <span class="dash-vtype-pill">—</span>
                                @endif
                            </td>
                            <td class="dash-pend-date">{{ $v->date_of_violation->format('M d, Y') }}</td>
                            <td class="dash-pend-ticket">{{ $v->ticket_number ?? '—' }}</td>
                            <td>
                                <span class="dash-overdue-badge">
                                    <i class="bi bi-clock-fill me-1"></i>{{ $hrs }}h overdue
                                </span>
                            </td>
                            <td class="text-end" style="padding-right:.9rem;">
                                <a href="{{ route('violations.show', $v) }}" class="dash-view-btn dash-view-danger">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($overdueTotal > 20)
            <div class="card-footer text-center py-2" style="background:#fef2f2;border-top:1px solid #fecaca;">
                <a href="{{ route('violations.index', ['status' => 'overdue']) }}" class="fw-600" style="font-size:.8rem;color:#dc2626;">
                    Showing 20 of {{ $overdueTotal }} overdue violations — View all
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
            @endif
        </div>
    </div>
</div>

{{-- ── Recent Pending Violators ── --}}
<div class="row g-4 mb-4">
    <div class="col-12 fade-in-up" data-delay="700">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold">
                    <i class="bi bi-hourglass-split me-2 text-warning"></i>Recent Pending Violators
                    <span style="font-size:.7rem;font-weight:400;color:#94a3b8;margin-left:.4rem;">with unsettled tickets within 72 hours</span>
                </span>
                <span class="badge" style="background:rgba(245,158,11,.12);color:#d97706;border-radius:99px;font-size:.75rem;padding:.3rem .75rem;">
                    {{ $freshPendingTotal }} pending
                </span>
            </div>
            @if($freshPendingViolations->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check2-all fs-2 d-block mb-2 text-success opacity-50"></i>
                    No pending violations.
                </div>
            @else
            <div class="table-responsive">
                <table class="table align-middle mb-0 dash-pend-table">
                    <thead>
                        <tr>
                            <th>Motorist</th>
                            <th>Violation</th>
                            <th>Date</th>
                            <th>Ticket #</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($freshPendingViolations as $v)
                        @php $hrs = (int) now()->diffInHours($v->created_at); @endphp
                        <tr class="dash-pend-row">
                            <td>
                                @if($v->violator)
                                <a href="{{ route('violators.show', $v->violator) }}" class="dash-pend-name">
                                    {{ $v->violator->full_name }}
                                </a>
                                @else
                                <span class="dash-pend-name">{{ $v->vehicle_owner_name ?? '(Unknown)' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($v->violationType)
                                <a href="{{ route('violations.index', ['type' => $v->violationType->id]) }}"
                                   class="dash-vtype-pill text-decoration-none">{{ $v->violationType->name }}</a>
                                @else
                                <span class="dash-vtype-pill">—</span>
                                @endif
                            </td>
                            <td class="dash-pend-date">{{ $v->date_of_violation->format('M d, Y') }}</td>
                            <td class="dash-pend-ticket">{{ $v->ticket_number ?? '—' }}</td>
                            <td>
                                <span class="dash-fresh-badge"><i class="bi bi-hourglass-split me-1"></i>{{ $hrs }}h</span>
                            </td>
                            <td class="text-end" style="padding-right:.9rem;">
                                <a href="{{ route('violations.show', $v) }}" class="dash-view-btn dash-view-warning">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($freshPendingTotal > 20)
            <div class="card-footer text-center py-2">
                <a href="{{ route('violations.index', ['status' => 'pending']) }}" class="fw-600" style="font-size:.8rem;color:#d97706;">
                    Showing 20 of {{ $freshPendingTotal }} pending violations — View all
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
            @endif
        </div>
    </div>
</div>

{{-- ── Chart.js ── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
/* ── Staggered slide-up for stat cards and fade-in-up sections ── */
document.querySelectorAll('.stat-card, .fade-in-up').forEach(el => {
    const delay = parseInt(el.dataset.delay || 0, 10);
    el.style.animationDelay = delay + 'ms';
});

/* ── Staggered row animation ── */
document.querySelectorAll('.offender-row').forEach(row => {
    const delay = parseInt(row.dataset.rowDelay || 0, 10);
    row.style.animationDelay = delay + 'ms';
});

/* ── Count-up animation (initial load) ── */
function animateNumber(el, target) {
    const start = parseInt(el.textContent.replace(/,/g, ''), 10) || 0;
    if (start === target) return;
    const diff  = target - start;
    const steps = 40;
    let step    = 0;
    const timer = setInterval(() => {
        step++;
        const progress = step / steps;
        const ease     = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
        const value    = Math.round(start + diff * ease);
        el.textContent = value.toLocaleString();
        if (step >= steps) { el.textContent = target.toLocaleString(); clearInterval(timer); }
    }, 800 / steps);
}

document.querySelectorAll('.stat-number').forEach(el => {
    animateNumber(el, parseInt(el.dataset.target, 10) || 0);
});

/* ── Chart.js horizontal bar (all data from canvas data-attributes) ── */
const canvas = document.getElementById('violationsChart');
if (canvas) {
    const labels = JSON.parse(canvas.dataset.labels);
    const values = JSON.parse(canvas.dataset.values);
    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cases',
                data: values,
                backgroundColor: [
                    'rgba(14,165,233,.75)',
                    'rgba(245,158,11,.75)',
                    'rgba(239,68,68,.75)',
                    'rgba(139,92,246,.75)',
                    'rgba(16,185,129,.75)',
                ],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            animation: { duration: 1000, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(c) {
                            return ' ' + c.parsed.x + ' case' + (c.parsed.x !== 1 ? 's' : '');
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                    grid: { color: 'rgba(0,0,0,.05)' }
                },
                y: { grid: { display: false } }
            }
        }
    });
}

/* ── Offender mini-bar animation ── */
setTimeout(() => {
    document.querySelectorAll('.offender-bar[data-width]').forEach(bar => {
        bar.style.width = bar.dataset.width + '%';
    });
    document.querySelectorAll('.settlement-bar-seg[data-width]').forEach(seg => {
        seg.style.width = seg.dataset.width + '%';
    });
}, 600);

/* ── Live stats polling ── */
const STATS_URL  = '{{ route("dashboard.stats") }}';
const REFRESH_MS = 30000;

async function fetchStats() {
    try {
        const res  = await fetch(STATS_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();

        const map = {
            'stat-violators':        data.totalViolators,
            'stat-pending':          data.pendingCount,
            'aStat-overdue':         data.overdueCount,
            'stat-violations-month': data.violationsThisMonth,
            'stat-incidents-month':  data.incidentsThisMonth,
        };
        Object.entries(map).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) animateNumber(el, val);
        });

        /* flash each card briefly */
        document.querySelectorAll('.stat-card .card').forEach(card => {
            card.classList.add('stat-flash');
            setTimeout(() => card.classList.remove('stat-flash'), 600);
        });
    } catch (_) { /* silently ignore network errors */ }
}

/* Auto-refresh every 30 s */
setInterval(fetchStats, REFRESH_MS);

/* ════════════════════════════════════════════════
   Analytics Overview — Period Tabs + Dynamic Cards
   ════════════════════════════════════════════════ */
(function () {
    const ANALYTICS_URL = '{{ route("dashboard.analytics") }}';
    let   violationsChart = null;

    const PERIOD_LABELS = { weekly: 'Weekly',    monthly: 'Monthly',     yearly: 'Yearly'     };
    const PERIOD_BADGES = { weekly: 'This Week', monthly: 'This Month',  yearly: 'This Year'  };
    const BRGY_COLORS   = ['#6366f1','#8b5cf6','#06b6d4','#0ea5e9','#10b981','#f59e0b','#ef4444'];

    function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }
    function setHref(id, url) { const el = document.getElementById(id); if (el) el.href = url;        }

    function animateNum(el, target) {
        if (!el) return;
        const start = parseInt(el.textContent.replace(/[^0-9]/g, ''), 10) || 0;
        const diff  = target - start;
        const steps = 32;
        let   s     = 0;
        clearInterval(el._anim);
        el._anim = setInterval(() => {
            s++;
            const ease = 1 - Math.pow(1 - s / steps, 3);
            el.textContent = Math.round(start + diff * ease).toLocaleString();
            if (s >= steps) { el.textContent = target.toLocaleString(); clearInterval(el._anim); }
        }, 700 / steps);
    }

    function showOverlay(show) {
        const el = document.getElementById('aChartLoading-violations');
        if (el) el.style.display = show ? 'flex' : 'none';
    }

    function updateViolationsChart(labels, values) {
        const canvas = document.getElementById('analyticsViolationsChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (violationsChart) {
            violationsChart.data.labels           = labels;
            violationsChart.data.datasets[0].data = values;
            violationsChart.update({ duration: 700, easing: 'easeOutQuart' });
            return;
        }

        const gradient = ctx.createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, 'rgba(245,158,11,.45)');
        gradient.addColorStop(1, 'rgba(245,158,11,.02)');

        violationsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Violations',
                    data:  values,
                    backgroundColor: gradient,
                    borderColor:     'rgba(245,158,11,1)',
                    borderWidth:     2.5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor:     'rgba(245,158,11,1)',
                    pointBorderWidth:     2,
                    pointRadius:     4,
                    pointHoverRadius: 6,
                    fill:    true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                animation:  { duration: 950, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor:      '#1e293b',
                        bodyColor:       '#64748b',
                        borderColor:     '#e2e8f0',
                        borderWidth:     1,
                        padding:         10,
                        callbacks: { label: ctx => ' ' + ctx.parsed.y + ' violation' + (ctx.parsed.y !== 1 ? 's' : '') }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, maxTicksLimit: 14 } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: 'rgba(0,0,0,.04)' } }
                }
            }
        });
    }

    function updateBarangayList(topBarangays) {
        const body = document.getElementById('aBarangayBody');
        if (!body) return;

        if (!topBarangays || topBarangays.length === 0) {
            body.innerHTML = `<div class="text-center text-muted py-5">
                <i class="bi bi-geo-alt fs-2 d-block mb-2 opacity-25"></i>
                No incident data for this period.</div>`;
            return;
        }

        const max = Math.max(...topBarangays.map(b => b.count), 1);
        let html = '<div class="brgy-list">';
        topBarangays.forEach((b, i) => {
            const pct   = Math.round((b.count / max) * 100);
            const color = BRGY_COLORS[i % BRGY_COLORS.length];
            const safeLabel = (b.label || 'Unknown').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const url   = '{{ route("incidents.index") }}?search=' + encodeURIComponent(b.label || '');
            html += `<a href="${url}" class="brgy-row text-decoration-none">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="brgy-name" title="${safeLabel}">${safeLabel}</span>
                    <span class="brgy-count" style="color:${color};">${b.count} incident${b.count !== 1 ? 's' : ''}</span>
                </div>
                <div class="brgy-bar-track mb-2">
                    <div class="brgy-bar" style="width:0%;background:${color};" data-width="${pct}"></div>
                </div>
            </a>`;
        });
        html += '</div>';
        body.innerHTML = html;

        setTimeout(() => {
            body.querySelectorAll('.brgy-bar[data-width]').forEach(bar => {
                bar.style.transition = 'width 0.9s cubic-bezier(.4,0,.2,1)';
                bar.style.width      = bar.dataset.width + '%';
            });
        }, 80);
    }

    async function loadAnalytics(period) {
        // Update active tab
        document.querySelectorAll('.analytics-tab').forEach(btn =>
            btn.classList.toggle('active', btn.dataset.period === period)
        );

        // Update labels immediately
        setText('aLabel-violations',        PERIOD_LABELS[period] + ' Violations');
        setText('aLabel-incidents',          PERIOD_LABELS[period] + ' Traffic Incidents');
        setText('aChartTitle-violations',    PERIOD_LABELS[period] + ' Violations Trend');
        setText('aChartBadge-violations',    PERIOD_BADGES[period]);
        setText('aChartBadge-barangay',      PERIOD_BADGES[period]);

        showOverlay(true);

        try {
            const res  = await fetch(ANALYTICS_URL + '?period=' + period, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            // Animate numbers
            animateNum(document.getElementById('aStat-violations'), data.violationsCount);
            animateNum(document.getElementById('aStat-incidents'),  data.incidentsCount);
            animateNum(document.getElementById('aStat-overdue'),    data.overdueCount);

            // Sub-labels (period range) + global label
            setText('aSub-violations',    data.periodLabel);
            setText('aSub-incidents',     data.periodLabel);
            setText('aGlobalPeriodLabel', data.periodLabel);

            // Trend delta badges
            function setTrend(id, pct, delta) {
                const el = document.getElementById(id);
                if (!el) return;
                if (pct === null || pct === undefined) { el.style.display = 'none'; return; }
                const up = delta >= 0;
                el.style.display = 'inline-flex';
                el.className     = 'trend-badge ' + (up ? 'trend-up' : 'trend-down');
                el.innerHTML     = '<i class="bi bi-arrow-' + (up ? 'up' : 'down') + '-right"></i>&nbsp;'
                                 + (up ? '+' : '') + pct + '%';
            }
            setTrend('aTrend-violations', data.violationsTrend, data.violationsDelta);
            setTrend('aTrend-incidents',  data.incidentsTrend,  data.incidentsDelta);

            // Update links
            setHref('aLink-violations', data.violationsUrl);
            setHref('aLink-incidents',  data.incidentsUrl);

            // Charts
            updateViolationsChart(data.chart.labels, data.chart.values);
            updateBarangayList(data.topBarangays);

        } catch (e) {
            console.warn('Analytics load error:', e);
        } finally {
            showOverlay(false);
        }
    }

    // Period tab click handlers
    document.querySelectorAll('.analytics-tab').forEach(btn =>
        btn.addEventListener('click', () => loadAnalytics(btn.dataset.period))
    );

    // Initial load
    loadAnalytics('weekly');
})();
</script>

<style>
/* ── Entry animations ── */
.stat-card,
.fade-in-up {
    animation: slideUp 0.55s cubic-bezier(.22,1,.36,1) both;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Stat card link wrapper ── */
.stat-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
    height: 100%;
}

/* ── Stat card hover — lift + glow ── */
.stat-card .card {
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    cursor: pointer;
}
.stat-card .card:hover,
.stat-card-link:hover .card {
    transform: translateY(-5px);
    box-shadow: 0 10px 28px rgba(120,80,20,0.13) !important;
    border-color: #c8b99a !important;
}

/* ── Stat icon — hover glow + gentle spin ── */
.stat-icon-wrap {
    position: relative;
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    font-size: 1.35rem;
    box-shadow: 0 3px 10px rgba(0,0,0,.12);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.stat-card .card:hover .stat-icon-wrap,
.stat-card-link:hover .stat-icon-wrap {
    transform: rotate(-8deg) scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,.2);
}

/* ── Stat number — subtle breathing when idle ── */
.stat-number {
    font-size: 1.65rem;
    font-weight: 700;
    line-height: 1.1;
    color: #292524;
    display: inline-block;
    animation: breathe 4s ease-in-out infinite;
}
@keyframes breathe {
    0%, 100% { opacity: 1; }
    50%       { opacity: .82; }
}

/* ── Stat label + sub — fade transition on hover ── */
.stat-label {
    font-size: .75rem;
    color: #78716c;
    font-weight: 500;
    transition: color 0.2s ease;
}
.stat-sub {
    font-size: .7rem;
    color: #a8a29e;
    margin-top: 2px;
    transition: color 0.2s ease;
}
.stat-card .card:hover .stat-label { color: #57534e; }
.stat-card .card:hover .stat-sub   { color: #78716c; }

/* ── Stat card footer "View all" strip ── */
.stat-card-footer {
    font-size: .7rem;
    font-weight: 600;
    color: #a8a29e;
    text-align: right;
    padding: .3rem .85rem .45rem;
    border-top: 1px solid #f5f5f4;
    letter-spacing: .02em;
    transition: color 0.2s ease, background 0.2s ease;
}
.stat-card-link:hover .stat-card-footer {
    color: #57534e;
    background: #fafaf9;
}

/* ── Background blob — slow float ── */
.stat-bg-blob {
    position: absolute;
    top: -20px;
    right: -20px;
    width: 90px;
    height: 90px;
    border-radius: 50%;
    pointer-events: none;
    animation: floatBlob 6s ease-in-out infinite;
}
@keyframes floatBlob {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33%       { transform: translate(-6px, 4px) scale(1.05); }
    66%       { transform: translate(4px, -6px) scale(.96); }
}

/* ── Pulse dot on repeat-offender icon ── */
.stat-pulse {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #fca5a5;
    animation: pulse 1.8s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1);   opacity: 1; }
    50%       { transform: scale(1.6); opacity: .4; }
}

/* ── Chart card + breakdown card hover ── */
.fade-in-up .card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.fade-in-up .card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(120,80,20,0.1) !important;
}

/* ── Progress bar shimmer ── */
.progress-bar.progress-animated {
    position: relative;
    overflow: hidden;
}
.progress-bar.progress-animated::after {
    content: '';
    position: absolute;
    top: 0; left: -60%;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
    animation: shimmer 2.2s ease-in-out infinite;
}
@keyframes shimmer {
    0%   { left: -60%; }
    100% { left: 120%; }
}

/* ── Bar label — slide color on hover ── */
.bar-label {
    transition: opacity 0.2s ease;
}
.bar-label:hover { opacity: .7; }

/* ── Offender row — smooth slide + highlight ── */
.offender-row {
    animation: fadeInRight 0.4s ease both;
    transition: background 0.2s ease, transform 0.2s ease;
}
.offender-row:hover {
    background: rgba(180,120,40,0.06) !important;
    transform: translateX(3px);
}
@keyframes fadeInRight {
    from { opacity: 0; transform: translateX(12px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* ── Rank badge ── */
.offender-rank {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: .7rem;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
}
.offender-rank-medal {
    color: #fff;
    font-size: .8rem;
    box-shadow: 0 2px 6px rgba(0,0,0,.18);
}
.offender-rank-gold   { background: #f59e0b; }
.offender-rank-silver { background: #94a3b8; }
.offender-rank-bronze { background: #b45309; }

/* ── Initials avatar ── */
.offender-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .5px;
}
.offender-avatar-danger  { background: rgba(239,68,68,.12);  color: #dc2626; }
.offender-avatar-warning { background: rgba(245,158,11,.12); color: #d97706; }

/* ── Mini progress bar ── */
.offender-bar-track {
    height: 5px;
    border-radius: 99px;
    background: #f1f5f9;
    overflow: hidden;
}
.offender-bar {
    height: 100%;
    width: 0%;
    border-radius: 99px;
    transition: width 0.9s cubic-bezier(.4,0,.2,1);
}
.offender-bar-danger  { background: linear-gradient(90deg, #f87171, #ef4444); }
.offender-bar-warning { background: linear-gradient(90deg, #fbbf24, #f59e0b); }

/* ── Status badge ── */
.offender-badge {
    font-size: .65rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 99px;
    letter-spacing: .3px;
    text-transform: uppercase;
}
.offender-badge-danger  { background: rgba(239,68,68,.12);  color: #dc2626; }
.offender-badge-warning { background: rgba(245,158,11,.12); color: #b45309; }

/* ── View button ── */
.offender-view-btn {
    width: 30px;
    height: 30px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: none;
    font-size: .8rem;
    flex-shrink: 0;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.offender-view-danger  { background: rgba(239,68,68,.1);  color: #dc2626; }
.offender-view-warning { background: rgba(245,158,11,.1); color: #d97706; }
.offender-view-btn:hover {
    transform: scale(1.12);
    box-shadow: 0 2px 8px rgba(0,0,0,.14);
}
.offender-view-danger:hover  { background: rgba(239,68,68,.18);  color: #b91c1c; }
.offender-view-warning:hover { background: rgba(245,158,11,.18); color: #b45309; }

/* ── Card flash on live data update ── */
.stat-flash {
    box-shadow: 0 0 0 2px rgba(34,197,94,0.5) !important;
    transition: box-shadow 0.6s ease;
}

/* ── Pending / Overdue Tables ── */
.dash-pend-table thead th {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: #94a3b8;
    padding: .55rem .9rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.dash-pend-table tbody td {
    padding: .6rem .9rem;
    font-size: .845rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.dash-pend-row:last-child td { border-bottom: none; }
.dash-pend-row:hover { background: #f8fafc; }
.dash-pend-overdue { background: rgba(254,242,242,.5); }
.dash-pend-overdue:hover { background: rgba(254,226,226,.5) !important; }
.dash-pend-name {
    font-weight: 600;
    color: #1e293b;
    text-decoration: none;
}
.dash-pend-name:hover { color: #3b82f6; text-decoration: underline; }
.dash-pend-date, .dash-pend-ticket {
    color: #64748b;
    font-size: .8rem;
}
.dash-vtype-pill {
    font-size: .72rem;
    font-weight: 600;
    padding: 2px 9px;
    border-radius: 99px;
    background: rgba(99,102,241,.1);
    color: #4f46e5;
    white-space: nowrap;
    display: inline-block;
}
a.dash-vtype-pill { transition: background .15s; }
a.dash-vtype-pill:hover { background: rgba(99,102,241,.22); }
.dash-overdue-badge {
    display: inline-flex;
    align-items: center;
    font-size: .7rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 99px;
    background: rgba(220,38,38,.1);
    color: #dc2626;
    white-space: nowrap;
}
.dash-fresh-badge {
    display: inline-flex;
    align-items: center;
    font-size: .7rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 99px;
    background: rgba(245,158,11,.1);
    color: #d97706;
    white-space: nowrap;
}
.dash-view-btn {
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 8px;
    font-size: .8rem;
    text-decoration: none;
    transition: transform .15s, box-shadow .15s;
}
.dash-view-btn:hover { transform: scale(1.12); box-shadow: 0 2px 8px rgba(0,0,0,.14); }
.dash-view-danger  { background: rgba(220,38,38,.1);  color: #dc2626; }
.dash-view-danger:hover  { background: rgba(220,38,38,.18); color: #b91c1c; }
.dash-view-warning { background: rgba(245,158,11,.1); color: #d97706; }
.dash-view-warning:hover { background: rgba(245,158,11,.18); color: #b45309; }

/* ════════════════════════════════════════════════
   Analytics Overview styles
   ════════════════════════════════════════════════ */

/* Section header */
.analytics-section-title {
    font-size: .9rem;
    font-weight: 700;
    color: #374151;
    letter-spacing: .01em;
}

/* Period toggle pill group — sits inside the search bar */
.analytics-period-tabs {
    display: flex;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 2px;
    gap: 1px;
    flex-shrink: 0;
}
.analytics-tab {
    border: none;
    background: transparent;
    border-radius: 6px;
    padding: 3px 11px;
    font-size: .72rem;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all .2s ease;
    letter-spacing: .02em;
    white-space: nowrap;
}
.analytics-tab.active {
    background: #fff;
    color: #1e293b;
    box-shadow: 0 1px 4px rgba(0,0,0,.1);
}
.analytics-tab:hover:not(.active) {
    color: #374151;
    background: rgba(255,255,255,.6);
}

/* Analytics stat cards */
.analytics-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
    height: 100%;
}
.analytics-card {
    transition: transform .25s ease, box-shadow .25s ease;
    cursor: pointer;
}
.analytics-card-link:hover .analytics-card {
    transform: translateY(-5px);
    box-shadow: 0 10px 28px rgba(120,80,20,0.13) !important;
}
.analytics-card-link:hover .stat-icon-wrap {
    transform: rotate(-8deg) scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,.2);
}
.analytics-number {
    font-size: 1.65rem;
    font-weight: 700;
    line-height: 1.1;
    color: #292524;
    animation: breathe 4s ease-in-out infinite;
}

/* Chart cards */
.analytics-chart-card {
    transition: transform .25s ease, box-shadow .25s ease;
}
.analytics-chart-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(120,80,20,0.1) !important;
}

/* Loading overlay on chart */
.analytics-loading-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.78);
    border-radius: 0 0 8px 8px;
    z-index: 5;
}
.analytics-loading-inline {
    display: flex;
    align-items: center;
    padding: 2rem;
}

/* Barangay list */
.brgy-list { padding: .1rem 0; }
.brgy-row {
    display: block;
    padding: .42rem .35rem;
    border-radius: 8px;
    transition: background .15s ease;
    color: inherit;
}
.brgy-row:hover { background: #f0f7ff; }
.brgy-name {
    font-size: .79rem;
    font-weight: 600;
    color: #374151;
    max-width: 200px;
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: bottom;
}
.brgy-count {
    font-size: .75rem;
    font-weight: 700;
    flex-shrink: 0;
    white-space: nowrap;
}
.brgy-bar-track {
    height: 6px;
    border-radius: 99px;
    background: #f1f5f9;
    overflow: hidden;
}
.brgy-bar {
    height: 100%;
    border-radius: 99px;
    width: 0%;
}

/* ── Quick Search ── */
.dash-search-wrap {
    position: relative;
}
.dash-search-inner {
    display: flex;
    align-items: center;
    gap: .6rem;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: .55rem 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transition: border-color .2s, box-shadow .2s;
}
.dash-search-inner:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.dash-search-icon {
    color: #94a3b8;
    font-size: 1rem;
    flex-shrink: 0;
}
.dash-search-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: .9rem;
    color: #1e293b;
}
.dash-search-input::placeholder { color: #94a3b8; }
.dash-search-kbd {
    font-size: .7rem;
    color: #cbd5e1;
    flex-shrink: 0;
    white-space: nowrap;
}
.dash-search-divider {
    width: 1px;
    height: 22px;
    background: #e2e8f0;
    flex-shrink: 0;
    margin: 0 4px;
}
.dash-search-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0; right: 0;
    z-index: 1050;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 8px 28px rgba(0,0,0,.12);
    overflow: hidden;
}
.dash-search-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .6rem 1rem;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    transition: background .15s;
    border-bottom: 1px solid #f1f5f9;
}
.dash-search-item:last-child { border-bottom: none; }
.dash-search-item:hover, .dash-search-item.active { background: #f0f7ff; }
.dash-search-avatar {
    width: 34px; height: 34px;
    border-radius: 9px;
    background: linear-gradient(135deg,#60a5fa,#3b82f6);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; font-weight: 700;
    flex-shrink: 0;
}
.dash-search-name {
    font-size: .875rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.dash-search-meta {
    font-size: .72rem;
    color: #94a3b8;
    margin-top: 1px;
}
.dash-search-vcount {
    margin-left: auto;
    flex-shrink: 0;
    font-size: .7rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 99px;
    background: rgba(239,68,68,.1);
    color: #dc2626;
}
.dash-search-empty {
    padding: .9rem 1rem;
    font-size: .875rem;
    color: #94a3b8;
    text-align: center;
}
.dash-search-group-label {
    padding: .45rem 1rem .3rem;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #94a3b8;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.dash-search-badge {
    margin-left: auto;
    flex-shrink: 0;
    font-size: .65rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 99px;
    text-transform: uppercase;
    letter-spacing: .2px;
}
.dash-search-badge-pending  { background: rgba(245,158,11,.12); color: #d97706; }
.dash-search-badge-overdue  { background: rgba(220,38,38,.12);  color: #dc2626; }
.dash-search-badge-settled  { background: rgba(16,185,129,.12); color: #059669; }
.dash-search-badge-contested{ background: rgba(99,102,241,.12); color: #4f46e5; }
.dash-search-badge-open     { background: rgba(239,68,68,.12);  color: #dc2626; }
.dash-search-badge-closed   { background: rgba(16,185,129,.12); color: #059669; }
.dash-search-badge-resolved { background: rgba(99,102,241,.12); color: #4f46e5; }
.dash-search-icon-motorist  { color: #3b82f6; }
.dash-search-icon-vehicle   { color: #10b981; }
.dash-search-icon-violation { color: #f59e0b; }
.dash-search-icon-incident  { color: #ef4444; }
.dash-search-icon-type      { color: #8b5cf6; }

/* ── Trend delta badges ── */
.trend-badge {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-size: .65rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 99px;
    white-space: nowrap;
    flex-shrink: 0;
}
.trend-up   { background: rgba(16,185,129,.12); color: #059669; }
.trend-down { background: rgba(239,68,68,.12);  color: #dc2626; }

/* ── Settlement status breakdown ── */
.settlement-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: .75rem;
    font-weight: 500;
    padding: 4px 10px;
    border-radius: 99px;
    white-space: nowrap;
}
.settlement-settled      { background: rgba(16,185,129,.1);  color: #059669; }
.settlement-contested    { background: rgba(99,102,241,.1);  color: #4f46e5; }
.settlement-pending-pill { background: rgba(245,158,11,.1);  color: #d97706; }
a.settlement-pill { transition: filter .15s, transform .12s; cursor: pointer; }
a.settlement-pill:hover { filter: brightness(.93); transform: translateY(-1px); }
.settlement-bar-wrap { align-items: center; }
.settlement-bar {
    display: flex;
    height: 8px;
    border-radius: 99px;
    overflow: hidden;
    background: #f1f5f9;
    width: 100%;
}
.settlement-bar-seg { height: 100%; min-width: 0; }
.settlement-bar-settled   { background: #10b981; }
.settlement-bar-contested { background: #6366f1; }
.settlement-bar-pending   { background: #f59e0b; }

/* ══════════════════════════════════════════════
   Dashboard Header Bar
   ══════════════════════════════════════════════ */
.dash-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    background: linear-gradient(135deg, #fff9f0 0%, #fffdf9 100%);
    border: 1px solid #ddd0be;
    border-radius: 14px;
    padding: .9rem 1.25rem;
    box-shadow: 0 2px 10px rgba(120,80,20,.08);
}
.dash-greeting {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.dash-greeting-text {
    font-size: .95rem;
    color: #292524;
    font-weight: 500;
    line-height: 1.3;
}
.dash-greeting-text strong { color: #1d4ed8; font-weight: 700; }
.dash-greeting-sub {
    font-size: .73rem;
    color: #a8a29e;
}
.dash-header-right { flex-shrink: 0; }

/* ══════════════════════════════════════════════
   Records Burger Button
   ══════════════════════════════════════════════ */
.records-burger-btn {
    display: inline-flex !important;
    align-items: center;
    gap: .4rem;
    background: #1d4ed8 !important;
    color: #fff !important;
    border-color: transparent !important;
    border-radius: 9px !important;
    padding-left: .85rem !important;
    padding-right: .85rem !important;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s ease, transform .15s ease, box-shadow .2s ease;
    white-space: nowrap;
    letter-spacing: .01em;
}
.records-burger-btn:hover {
    background: #1e40af !important;
    color: #fff !important;
    box-shadow: 0 4px 16px rgba(29,78,216,.32);
    transform: translateY(-1px);
}
.records-burger-btn:active { transform: translateY(0); box-shadow: none; }
.records-burger-btn[aria-expanded="true"] {
    background: #1e40af !important;
    box-shadow: 0 4px 16px rgba(29,78,216,.28);
}
.records-burger-lines {
    display: flex;
    flex-direction: column;
    gap: 3.5px;
    width: 14px;
    flex-shrink: 0;
}
.records-burger-lines span {
    display: block;
    height: 2px;
    background: rgba(255,255,255,.9);
    border-radius: 1px;
    transition: all .22s ease;
}
.records-burger-btn[aria-expanded="true"] .records-burger-lines span:nth-child(1) {
    transform: translateY(5.5px) rotate(45deg);
}
.records-burger-btn[aria-expanded="true"] .records-burger-lines span:nth-child(2) {
    opacity: 0;
    transform: scaleX(0);
}
.records-burger-btn[aria-expanded="true"] .records-burger-lines span:nth-child(3) {
    transform: translateY(-5.5px) rotate(-45deg);
}
.records-burger-chevron {
    font-size: .7rem;
    transition: transform .25s cubic-bezier(.4,0,.2,1);
}
.records-burger-btn[aria-expanded="true"] .records-burger-chevron {
    transform: rotate(180deg);
}

/* ══════════════════════════════════════════════
   Records Panel (Dropdown)
   ══════════════════════════════════════════════ */
.records-panel {
    display: none;
    position: absolute;
    top: calc(100% + 9px);
    right: 0;
    z-index: 1100;
    background: #fffdf9;
    border: 1px solid #ddd0be;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(120,80,20,.18), 0 4px 16px rgba(0,0,0,.09);
    width: 450px;
    max-width: calc(100vw - 2rem);
    overflow: hidden;
}
.records-panel.open {
    display: block;
    animation: recordsPanelIn .22s cubic-bezier(.22,1,.36,1) both;
}
@keyframes recordsPanelIn {
    from { opacity: 0; transform: translateY(-12px) scale(.97); }
    to   { opacity: 1; transform: translateY(0)     scale(1);   }
}
.records-panel-header {
    display: flex;
    align-items: center;
    padding: .75rem 1rem .65rem;
    font-size: .78rem;
    font-weight: 700;
    color: #374151;
    letter-spacing: .01em;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}

/* ── Tile Grid ── */
.records-panel-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    padding: .45rem;
}
.records-tile {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .6rem .7rem;
    border-radius: 10px;
    text-decoration: none;
    color: #1e293b;
    transition: background .15s ease, transform .15s ease;
    cursor: pointer;
}
.records-tile:hover {
    background: #f0f7ff;
    transform: translateX(2px);
    text-decoration: none;
    color: #1e293b;
}
.records-tile-report:hover { background: #fff7ed; }
.records-tile-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .95rem;
    flex-shrink: 0;
    box-shadow: 0 2px 7px rgba(0,0,0,.13);
    transition: transform .2s ease, box-shadow .2s ease;
}
.records-tile:hover .records-tile-icon {
    transform: scale(1.08) rotate(-5deg);
    box-shadow: 0 4px 14px rgba(0,0,0,.18);
}
.records-tile-body { flex: 1; min-width: 0; }
.records-tile-name {
    font-size: .82rem;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.records-tile-sub {
    font-size: .69rem;
    color: #94a3b8;
    margin-top: 1px;
}
.records-tile-arrow {
    color: #cbd5e1;
    font-size: 1.05rem;
    flex-shrink: 0;
    transition: color .15s ease, transform .15s ease;
}
.records-tile:hover .records-tile-arrow {
    color: #3b82f6;
    transform: translateX(2px);
}
.records-pill-pending {
    display: inline-flex;
    align-items: center;
    font-size: .65rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 99px;
    background: rgba(245,158,11,.14);
    color: #d97706;
}

/* ── Divider ── */
.records-panel-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 0 .85rem;
}

/* ── Quick Print Section ── */
.records-print-section {
    padding: .6rem .85rem .8rem;
}
.records-print-title {
    font-size: .68rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .5rem;
    display: flex;
    align-items: center;
}
.records-print-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .32rem;
}
.records-chip {
    display: inline-flex;
    align-items: center;
    font-size: .69rem;
    font-weight: 600;
    padding: .28rem .65rem;
    border-radius: 99px;
    text-decoration: none;
    transition: filter .15s ease, transform .15s ease;
    white-space: nowrap;
    letter-spacing: .01em;
}
.records-chip:hover {
    filter: brightness(.92);
    transform: translateY(-1px);
    text-decoration: none;
}
.records-chip-warning { background: rgba(245,158,11,.12); color: #b45309; }
.records-chip-danger  { background: rgba(220,38,38,.1);   color: #dc2626; }
.records-chip-success { background: rgba(16,185,129,.1);  color: #059669; }
.records-chip-blue    { background: rgba(59,130,246,.1);  color: #2563eb; }
.records-chip-orange  { background: rgba(251,146,60,.13); color: #ea580c; }

/* Mobile: collapse to full-width panel */
@media (max-width: 575.98px) {
    .records-panel {
        right: auto;
        left: 0;
        width: calc(100vw - 3rem);
    }
    .dash-header-bar { gap: .6rem; padding: .7rem 1rem; }
    .dash-header-right { width: 100%; justify-content: flex-end; }
    /* Hide the long subtitle on very small screens */
    .dash-greeting-sub { display: none; }
    /* Compact greeting text */
    .dash-greeting-text { font-size: .88rem; }
    /* Compact action buttons */
    .dash-header-right .btn { font-size: .78rem; padding: .3rem .65rem; }
    /* Records panel grid: 1 column on very small screens */
    .records-panel-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
    const SEARCH_URL = '{{ route("dashboard.search") }}';
    const input    = document.getElementById('dashSearchInput');
    const dropdown = document.getElementById('dashSearchDropdown');
    const kbdHint  = document.getElementById('dashSearchKbd');
    if (!input || !dropdown) return;

    let timer, activeIdx = -1, items = [];

    function hide() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        activeIdx = -1;
        items = [];
        if (kbdHint) kbdHint.style.display = 'none';
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function makeAvatar(label, bgClass) {
        const initials = label.split(' ').slice(0,2).map(function(w){return w[0]||'';}).join('').toUpperCase();
        return '<div class="dash-search-avatar ' + bgClass + '">' + escHtml(initials) + '</div>';
    }

    function makeBadge(badge) {
        if (!badge) return '';
        const cls = badge === 'overdue'   ? 'dash-search-badge-overdue'
                  : badge === 'pending'   ? 'dash-search-badge-pending'
                  : badge === 'settled'   ? 'dash-search-badge-settled'
                  : badge === 'contested' ? 'dash-search-badge-contested'
                  : badge === 'open'      ? 'dash-search-badge-open'
                  : badge === 'closed'    ? 'dash-search-badge-closed'
                  : badge === 'resolved'  ? 'dash-search-badge-resolved'
                  : 'dash-search-vcount';
        return '<span class="dash-search-badge ' + cls + '">' + escHtml(badge) + '</span>';
    }

    function buildGroup(title, icon, iconClass, rows) {
        if (!rows.length) return null;
        const frag = document.createDocumentFragment();

        const lbl = document.createElement('div');
        lbl.className = 'dash-search-group-label';
        lbl.innerHTML = '<i class="bi ' + icon + ' ' + iconClass + '"></i>' + escHtml(title);
        frag.appendChild(lbl);

        rows.forEach(function(r) {
            const a = document.createElement('a');
            a.className = 'dash-search-item';
            a.href = r.url;
            const avatarBg = iconClass === 'dash-search-icon-motorist'  ? '' :
                             iconClass === 'dash-search-icon-vehicle'   ? 'style="background:linear-gradient(135deg,#34d399,#059669);"' :
                             iconClass === 'dash-search-icon-violation' ? 'style="background:linear-gradient(135deg,#fbbf24,#d97706);"' :
                             iconClass === 'dash-search-icon-incident'  ? 'style="background:linear-gradient(135deg,#f87171,#ef4444);"' :
                                                                          'style="background:linear-gradient(135deg,#a78bfa,#7c3aed);"';
            const initials = r.label.split(' ').slice(0,2).map(function(w){return w[0]||'';}).join('').toUpperCase();
            a.innerHTML =
                '<div class="dash-search-avatar" ' + avatarBg + '>' + escHtml(initials) + '</div>' +
                '<div style="min-width:0;flex:1;">' +
                    '<div class="dash-search-name">' + escHtml(r.label) + '</div>' +
                    '<div class="dash-search-meta">' + escHtml(r.sub) + '</div>' +
                '</div>' +
                (r.badge ? makeBadge(r.badge) : '');
            frag.appendChild(a);
            items.push(a);
        });

        return frag;
    }

    function show(data) {
        dropdown.innerHTML = '';
        activeIdx = -1;
        items = [];

        const hasAny = data.motorists.length || data.violations.length || data.incidents.length || data.types.length || data.vehicles.length;
        if (!hasAny) {
            dropdown.innerHTML = '<div class="dash-search-empty"><i class="bi bi-search me-1"></i>No results found.</div>';
            dropdown.style.display = 'block';
            return;
        }

        const mGroup  = buildGroup('Motorists',       'bi-person-fill',                'dash-search-icon-motorist',  data.motorists);
        const vhGroup = buildGroup('Vehicles',         'bi-car-front-fill',             'dash-search-icon-vehicle',   data.vehicles);
        const vGroup  = buildGroup('Violations',       'bi-exclamation-triangle-fill',  'dash-search-icon-violation', data.violations);
        const iGroup  = buildGroup('Incidents',        'bi-cone-striped',               'dash-search-icon-incident',  data.incidents);
        const tGroup  = buildGroup('Violation Types',  'bi-tags-fill',                  'dash-search-icon-type',      data.types);

        if (mGroup)  dropdown.appendChild(mGroup);
        if (vhGroup) dropdown.appendChild(vhGroup);
        if (vGroup)  dropdown.appendChild(vGroup);
        if (iGroup)  dropdown.appendChild(iGroup);
        if (tGroup)  dropdown.appendChild(tGroup);

        dropdown.style.display = 'block';
    }

    function setActive(idx) {
        items.forEach(function(a, i) { a.classList.toggle('active', i === idx); });
        activeIdx = idx;
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (kbdHint) kbdHint.style.display = q ? 'inline' : 'none';
        if (!q) { hide(); return; }
        timer = setTimeout(function () {
            fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) { show(data); })
            .catch(function () { hide(); });
        }, 80);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(Math.min(activeIdx + 1, items.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(Math.max(activeIdx - 1, 0));
        } else if (e.key === 'Enter' && activeIdx >= 0) {
            e.preventDefault();
            window.location.href = items[activeIdx].href;
        } else if (e.key === 'Escape') {
            input.value = '';
            hide();
        }
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== input) hide();
    });
})();
</script>

<script>
/* ══════════════════════════════════════════════
   Records Burger Dropdown
   ══════════════════════════════════════════════ */
(function () {
    var btn   = document.getElementById('recordsBurgerBtn');
    var panel = document.getElementById('recordsPanel');
    if (!btn || !panel) return;

    function openPanel() {
        panel.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
    }
    function closePanel() {
        panel.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
    }
    function togglePanel() {
        panel.classList.contains('open') ? closePanel() : openPanel();
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        togglePanel();
    });

    /* Close on outside click */
    document.addEventListener('click', function (e) {
        if (!btn.contains(e.target) && !panel.contains(e.target)) {
            closePanel();
        }
    });

    /* Close on Escape */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePanel();
    });

    /* Close when navigating from a tile or chip */
    panel.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closePanel);
    });
})();
</script>
@endsection
