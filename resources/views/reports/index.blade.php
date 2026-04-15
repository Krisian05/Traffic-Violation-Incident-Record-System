@extends('layouts.app')
@section('title', 'Reports')

@section('breadcrumbs')@endsection

@section('content')

@php
    $periodLabel = $showAll
        ? 'Year ' . $year
        : date('F', mktime(0,0,0,$month)) . ' ' . $year;

    // Date range for incidents (uses date_from / date_to filters)
    if ($showAll) {
        $dateFrom = $year . '-01-01';
        $dateTo   = $year . '-12-31';
    } else {
        $dateFrom = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $dateTo   = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
    }

    // Pre-built URLs for KPI cards
    $kpiViolationsUrl = route('violations.index', $showAll
        ? ['year' => $year]
        : ['year' => $year, 'month' => $month]);

    $kpiIncidentsUrl = route('incidents.index', [
        'date_from' => $dateFrom,
        'date_to'   => $dateTo,
    ]);

    $kpiSettledUrl = route('violations.index', $showAll
        ? ['year' => $year, 'status' => 'settled']
        : ['year' => $year, 'month' => $month, 'status' => 'settled']);

    $kpiOverdueUrl = route('violations.index', ['status' => 'pending']);

    $kpiContestedUrl = route('violations.index', $showAll
        ? ['year' => $year, 'status' => 'contested']
        : ['year' => $year, 'month' => $month, 'status' => 'contested']);

    $kpiPendingActiveUrl = route('violations.index', $showAll
        ? ['year' => $year, 'status' => 'pending']
        : ['year' => $year, 'month' => $month, 'status' => 'pending']);

    $kpiViolatorsUrl = route('violators.index');
@endphp

{{-- ── FILTER CARD ── --}}
<div class="rpt-filter-card mb-4">
    <div class="rpt-filter-header">
        <span class="rpt-filter-icon">
            <i class="bi bi-bar-chart-fill"></i>
        </span>
        <div>
            <div class="rpt-filter-title">Reports &amp; Statistics</div>
            <div class="rpt-filter-sub">Filter by name, violation type, month or year</div>
        </div>
    </div>
    <div class="rpt-filter-body">
        <form method="GET" action="{{ route('reports.index') }}"
              class="d-flex flex-wrap align-items-end gap-2">

            <div style="flex:2;min-width:0;position:relative;">
                <label class="rpt-flabel">Violator Name</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text rpt-ig"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" id="rptSearchInput" class="form-control rpt-finput"
                           placeholder="Search by name…" value="{{ $search }}" autocomplete="off">
                </div>
                <div id="rptSearchDropdown"></div>
            </div>

            <div style="flex:2.2;min-width:0;">
                <label class="rpt-flabel">Violation Type</label>
                <select name="type_filter" class="form-select form-select-sm rpt-finput">
                    <option value="">All Types</option>
                    @foreach($allTypes as $t)
                        <option value="{{ $t->id }}" {{ $typeFilter == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex:2;min-width:0;">
                <label class="rpt-flabel">Municipality</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text rpt-ig"><i class="bi bi-geo-alt-fill"></i></span>
                    <input type="text" name="municipality" class="form-control rpt-finput"
                           placeholder="e.g. Balamban" value="{{ $municipality }}" autocomplete="off">
                </div>
            </div>

            <div style="flex:1.5;min-width:0;">
                <label class="rpt-flabel">Month</label>
                <select name="month" class="form-select form-select-sm rpt-finput">
                    <option value="0" {{ $month == 0 ? 'selected' : '' }}>All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m)) }}</option>
                    @endfor
                </select>
            </div>

            <div style="flex:.9;min-width:0;">
                <label class="rpt-flabel">Year</label>
                <select name="year" class="form-select form-select-sm rpt-finput">
                    @for($y = date('Y'); $y >= $minYear; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div style="flex-shrink:0;" class="d-flex gap-2 align-items-end">
                <button type="submit" class="rpt-filter-btn">
                    <i class="bi bi-funnel-fill"></i> Apply
                </button>
                <a href="{{ route('reports.index') }}" class="rpt-reset-btn" title="Clear filters (ESC)">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
                {{-- ── QUICK ACCESS DROPDOWN ── --}}
                <div class="position-relative" id="rptQuickWrap">
                    <button type="button" class="rpt-quick-btn" id="rptQuickToggle"
                            onclick="rptToggleQuick()" aria-expanded="false">
                        <i class="bi bi-lightning-fill"></i> Quick Access
                        <i class="bi bi-chevron-down rpt-qchev" id="rptQChev"></i>
                    </button>
                    <div class="rpt-quick-panel" id="rptQuickPanel" style="display:none;">

                        <div class="rpt-qgroup-label">Filter &amp; View</div>
                        <a href="{{ $kpiViolationsUrl }}" class="rpt-qitem">
                            <span class="rpt-qitem-icon rpt-qi-red"><i class="bi bi-clipboard2-data-fill"></i></span>
                            <span>All Violations</span>
                            <i class="bi bi-arrow-right-short rpt-qi-arr"></i>
                        </a>
                        <a href="{{ $kpiOverdueUrl }}" class="rpt-qitem">
                            <span class="rpt-qitem-icon rpt-qi-amber"><i class="bi bi-alarm-fill"></i></span>
                            <span>Overdue Violations</span>
                            <i class="bi bi-arrow-right-short rpt-qi-arr"></i>
                        </a>
                        <a href="{{ $kpiSettledUrl }}" class="rpt-qitem">
                            <span class="rpt-qitem-icon rpt-qi-green"><i class="bi bi-check-circle-fill"></i></span>
                            <span>Settled Violations</span>
                            <i class="bi bi-arrow-right-short rpt-qi-arr"></i>
                        </a>
                        <a href="{{ $kpiIncidentsUrl }}" class="rpt-qitem">
                            <span class="rpt-qitem-icon rpt-qi-blue"><i class="bi bi-flag-fill"></i></span>
                            <span>All Incidents</span>
                            <i class="bi bi-arrow-right-short rpt-qi-arr"></i>
                        </a>

                        <div class="rpt-qdivider"></div>
                        <div class="rpt-qgroup-label">Print Section</div>
                        <button type="button" class="rpt-qitem rpt-qitem--print" onclick="rptPrintFull()">
                            <span class="rpt-qitem-icon rpt-qi-slate"><i class="bi bi-file-earmark-text-fill"></i></span>
                            <span>Full Report (All Sections)</span>
                            <i class="bi bi-printer rpt-qi-arr"></i>
                        </button>
                        <button type="button" class="rpt-qitem rpt-qitem--print" onclick="rptPrintSection('kpi')">
                            <span class="rpt-qitem-icon rpt-qi-slate"><i class="bi bi-grid-1x2-fill"></i></span>
                            <span>KPI Summary: Violations &amp; Incidents</span>
                            <i class="bi bi-printer rpt-qi-arr"></i>
                        </button>
                        <button type="button" class="rpt-qitem rpt-qitem--print" onclick="rptPrintSection('incident-analytics')">
                            <span class="rpt-qitem-icon rpt-qi-slate"><i class="bi bi-graph-up"></i></span>
                            <span>Incident Analytics (Charts)</span>
                            <i class="bi bi-printer rpt-qi-arr"></i>
                        </button>
                        <button type="button" class="rpt-qitem rpt-qitem--print" onclick="rptPrintSection('incidents')">
                            <span class="rpt-qitem-icon rpt-qi-slate"><i class="bi bi-flag-fill"></i></span>
                            <span>Incident Summary &amp; Hotspots</span>
                            <i class="bi bi-printer rpt-qi-arr"></i>
                        </button>
                        <button type="button" class="rpt-qitem rpt-qitem--print" onclick="rptPrintSection('violations')">
                            <span class="rpt-qitem-icon rpt-qi-slate"><i class="bi bi-table"></i></span>
                            <span>Violation Data (Matrix / By Type)</span>
                            <i class="bi bi-printer rpt-qi-arr"></i>
                        </button>
                        <button type="button" class="rpt-qitem rpt-qitem--print" onclick="rptPrintSection('violators')">
                            <span class="rpt-qitem-icon rpt-qi-slate"><i class="bi bi-person-lines-fill"></i></span>
                            <span>Violator Recap (Per Person)</span>
                            <i class="bi bi-printer rpt-qi-arr"></i>
                        </button>
                        <button type="button" class="rpt-qitem rpt-qitem--print" onclick="rptPrintSection('overdue')">
                            <span class="rpt-qitem-icon rpt-qi-slate"><i class="bi bi-alarm-fill"></i></span>
                            <span>72-Hour Overdue Violations</span>
                            <i class="bi bi-printer rpt-qi-arr"></i>
                        </button>
                        <button type="button" class="rpt-qitem rpt-qitem--print" onclick="rptPrintSection('offenders')">
                            <span class="rpt-qitem-icon rpt-qi-slate"><i class="bi bi-person-exclamation"></i></span>
                            <span>Repeat Offenders List</span>
                            <i class="bi bi-printer rpt-qi-arr"></i>
                        </button>

                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── PRINT HEADER (hidden on screen, visible on print) ── --}}
<div class="print-header" id="printHeader">
    <img src="{{ asset('images/PNP.png') }}" class="print-logo print-logo-pnp" alt="PNP Logo">
    <div class="print-agency-center">
        <div class="print-ph-republic">Republic of the Philippines</div>
        <div class="print-ph-npc">NATIONAL POLICE COMMISSION</div>
        <div class="print-ph-pro7">PHILIPPINE NATIONAL POLICE, POLICE REGIONAL OFFICE 7</div>
        <div class="print-ph-cebu">CEBU POLICE PROVINCIAL OFFICE</div>
        <div class="print-ph-station">BALAMBAN MUNICIPAL POLICE STATION</div>
        <div class="print-ph-address">Brgy. Sta Cruz-Sto Nino, Balamban, Cebu</div>
    </div>
    <img src="{{ asset('images/Balamban.png') }}" class="print-logo" alt="Balamban Seal">
</div>
<div class="print-report-title-block">
    <div class="print-report-title" id="printReportTitle">
        @if($showAll)
            Annual Violation Report — {{ $year }}
        @else
            Monthly Violation Report — {{ date('F', mktime(0,0,0,$month)) }} {{ $year }}
        @endif
    </div>
    <div class="print-section-note" id="printSectionNote" style="display:none;"></div>
    @if($search || $typeFilter)
    <div class="print-filter-note">
        Filtered by:
        @if($search) Name: "{{ $search }}"@endif
        @if($typeFilter && $allTypes->firstWhere('id', $typeFilter)) &nbsp;| Type: {{ $allTypes->firstWhere('id', $typeFilter)->name }}@endif
    </div>
    @endif
    <div class="print-date">Date Printed: {{ now()->format('F d, Y') }}</div>
</div>


{{-- ── KPI SUMMARY CARDS ── --}}
<div class="row g-3 mb-4 rpt-printable" data-rpt-section="kpi">

    {{-- ── Visible on screen (4 cards — dashboard-style) ── --}}
    <div class="col-6 col-md-3">
        <a href="{{ $kpiViolationsUrl }}" class="rpt-kpi-link">
            <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative rpt-kpi-card">
                <div class="stat-bg-blob" style="background:rgba(220,38,38,.06);"></div>
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#f87171,#dc2626);">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <div class="rpt-kpi-label">Violations</div>
                        <div class="rpt-kpi-num">{{ number_format($totalThisMonth) }}</div>
                        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
                    </div>
                </div>
                <div class="rpt-kpi-footer">View all <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ $kpiIncidentsUrl }}" class="rpt-kpi-link">
            <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative rpt-kpi-card">
                <div class="stat-bg-blob" style="background:rgba(99,102,241,.06);"></div>
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#818cf8,#6366f1);">
                        <i class="bi bi-flag-fill"></i>
                    </div>
                    <div>
                        <div class="rpt-kpi-label">Incidents</div>
                        <div class="rpt-kpi-num">{{ number_format($totalIncidents) }}</div>
                        <div class="rpt-kpi-sub">{{ $periodLabel }}</div>
                    </div>
                </div>
                <div class="rpt-kpi-footer">View all <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ $kpiSettledUrl }}" class="rpt-kpi-link">
            <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative rpt-kpi-card">
                <div class="stat-bg-blob" style="background:rgba(16,185,129,.06);"></div>
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#34d399,#10b981);">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <div class="rpt-kpi-label">Settled</div>
                        <div class="rpt-kpi-num" style="color:#15803d;">{{ number_format($settledCount) }}</div>
                        <div class="rpt-kpi-sub">Resolved — {{ $periodLabel }}</div>
                    </div>
                </div>
                <div class="rpt-kpi-footer">View settled <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ $kpiOverdueUrl }}" class="rpt-kpi-link">
            <div class="card border-0 shadow-sm h-100 overflow-hidden position-relative rpt-kpi-card">
                <div class="stat-bg-blob" style="background:rgba(245,158,11,.06);"></div>
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <div class="stat-icon-wrap" style="background:linear-gradient(135deg,#fbbf24,#d97706);">
                        <i class="bi bi-alarm-fill"></i>
                        <span class="stat-pulse"></span>
                    </div>
                    <div>
                        <div class="rpt-kpi-label">Overdue</div>
                        <div class="rpt-kpi-num" style="color:#92400e;">{{ number_format($overdueCount) }}</div>
                        <div class="rpt-kpi-sub">Past 72-hour deadline</div>
                    </div>
                </div>
                <div class="rpt-kpi-footer">View overdue <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>
    </div>

    {{-- ── Print-only extra cards (hidden on screen) ── --}}
    <div class="rpt-kpi-print-only">
        <div class="rpt-kpi-print-item">
            <div class="rpt-kpi-label">Pending</div>
            <div class="rpt-kpi-print-num">{{ number_format($pendingActiveCount) }}</div>
            <div class="rpt-kpi-print-sub">Within 72-hour window — {{ $periodLabel }}</div>
        </div>
    </div>
    <div class="rpt-kpi-print-only">
        <div class="rpt-kpi-print-item">
            <div class="rpt-kpi-label">Contested</div>
            <div class="rpt-kpi-print-num">{{ number_format($contestedCount) }}</div>
            <div class="rpt-kpi-print-sub">Disputed violations — {{ $periodLabel }}</div>
        </div>
    </div>
    <div class="rpt-kpi-print-only">
        <div class="rpt-kpi-print-item">
            <div class="rpt-kpi-label">Unique Violators</div>
            <div class="rpt-kpi-print-num">{{ number_format($totalViolators) }}</div>
            <div class="rpt-kpi-print-sub">Individuals ticketed — {{ $periodLabel }}</div>
        </div>
    </div>
    <div class="rpt-kpi-print-only">
        <div class="rpt-kpi-print-item">
            <div class="rpt-kpi-label">Repeat Offenders</div>
            <div class="rpt-kpi-print-num">{{ number_format($repeatOffenders->count()) }}</div>
            <div class="rpt-kpi-print-sub">2 or more violations — all time</div>
        </div>
    </div>

</div>

{{-- ── INCIDENT ANALYTICS (Charts) ── --}}
<div class="mb-4 rpt-printable" data-rpt-section="incident-analytics" id="incAnalyticsSection">
    <div class="rpt-card">
        <div class="rpt-card-header" style="flex-wrap:wrap;gap:.5rem;">
            <div class="d-flex align-items-center gap-3">
                <span class="rpt-card-icon" style="background:linear-gradient(135deg,#0d9488,#0f766e);box-shadow:0 3px 10px rgba(13,148,136,.3);">
                    <i class="bi bi-graph-up" style="color:#fff;"></i>
                </span>
                <div>
                    <div class="rpt-card-title">Traffic Related Incidents</div>
                    <div class="rpt-card-sub" id="incAnalyticsPeriodLabel">Loading…</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto flex-wrap d-print-none">
                <div id="incWeekNav" class="d-flex align-items-center gap-1" style="display:none!important;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="incPrevWeek"><i class="bi bi-chevron-left"></i></button>
                    <span id="incWeekLabel" style="font-size:.75rem;font-weight:600;color:#292524;min-width:130px;text-align:center;"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="incNextWeek"><i class="bi bi-chevron-right"></i></button>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="Period">
                    <button type="button" class="btn btn-outline-secondary inc-period-btn" id="incBtnWeek" data-period="week">Week</button>
                    <button type="button" class="btn btn-outline-secondary inc-period-btn inc-period-active" id="incBtnMonth" data-period="month">Month</button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="rptPrintSection('incident-analytics')" title="Print Incident Analytics">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div id="incAnalyticsNoData" style="display:none;text-align:center;padding:2rem;color:#a8a29e;font-style:italic;font-size:.85rem;">
                No incident data for this period.
            </div>
            <div id="incAnalyticsCharts">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="inc-chart-panel">
                            <div class="inc-chart-label">Crime Clock</div>
                            <div class="inc-chart-wrap"><canvas id="incClockChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="inc-chart-panel">
                            <div class="inc-chart-label">Offense</div>
                            <div class="inc-chart-wrap"><canvas id="incOffenseChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="inc-chart-panel">
                            <div class="inc-chart-label">Crime Days</div>
                            <div class="inc-chart-wrap"><canvas id="incDaysChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="inc-chart-panel">
                            <div class="inc-chart-label">Case Status</div>
                            <div class="inc-chart-wrap"><canvas id="incStatusChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── INCIDENT SUMMARY + HOTSPOTS ── --}}
<div class="row g-3 mb-4 rpt-printable" data-rpt-section="incidents">

    {{-- Incident Summary --}}
    <div class="col-lg-5">
        <div class="rpt-card h-100">
            <div class="rpt-card-header">
                <span class="rpt-card-icon" style="background:linear-gradient(135deg,#1d4ed8,#1e40af);box-shadow:0 3px 10px rgba(29,78,216,.3);">
                    <i class="bi bi-flag-fill" style="color:#fff;"></i>
                </span>
                <div>
                    <div class="rpt-card-title">Incident Summary</div>
                    <div class="rpt-card-sub">{{ $periodLabel }} — {{ $totalIncidents }} total</div>
                </div>
            </div>
            <div class="card-body p-3">
                @if($totalIncidents === 0)
                    <div style="color:#a8a29e;font-style:italic;font-size:.85rem;text-align:center;padding:1.5rem 0;">No incidents recorded for this period.</div>
                @else
                    @php
                        $incStatusMap = [
                            'open'         => ['label' => 'Open',         'bg' => '#eff6ff', 'color' => '#1d4ed8', 'icon' => 'bi-circle-fill'],
                            'under_review' => ['label' => 'Under Review', 'bg' => '#fffbeb', 'color' => '#92400e', 'icon' => 'bi-hourglass-split'],
                            'closed'       => ['label' => 'Closed',       'bg' => '#f0fdf4', 'color' => '#15803d', 'icon' => 'bi-check-circle-fill'],
                        ];
                    @endphp
                    <div class="d-flex flex-column gap-2">
                        @foreach($incStatusMap as $key => $s)
                        @php
                            $cnt       = $incidentsByStatus[$key] ?? 0;
                            $bgCls     = 'inc-sum-' . str_replace('_', '-', $key);
                            $fgCls     = $bgCls . '-fg';
                            $incStatUrl = route('incidents.index', [
                                'status'    => $key,
                                'date_from' => $dateFrom,
                                'date_to'   => $dateTo,
                            ]);
                        @endphp
                        <a href="{{ $incStatUrl }}"
                           class="d-flex align-items-center justify-content-between px-3 py-2 rounded {{ $bgCls }} rpt-inc-stat-row text-decoration-none">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $s['icon'] }} {{ $fgCls }}" style="font-size:.78rem;"></i>
                                <span class="{{ $fgCls }}" style="font-size:.83rem;font-weight:600;">{{ $s['label'] }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="{{ $fgCls }}" style="font-size:1.1rem;font-weight:800;">{{ $cnt }}</span>
                                @if($totalIncidents > 0)
                                <span style="font-size:.7rem;color:#a8a29e;">{{ round($cnt / $totalIncidents * 100) }}%</span>
                                @endif
                                <i class="bi bi-chevron-right {{ $fgCls }}" style="font-size:.65rem;opacity:.6;"></i>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @if($incidentHotspots->count())
                    <div class="mt-3">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#a8a29e;margin-bottom:.5rem;">Top Locations</div>
                        @php $maxInc = $incidentHotspots->max('total'); @endphp
                        @foreach($incidentHotspots as $loc)
                        @php
                            $hotspotUrl = route('incidents.index', [
                                'search'    => $loc->location,
                                'date_from' => $dateFrom,
                                'date_to'   => $dateTo,
                            ]);
                        @endphp
                        <a href="{{ $hotspotUrl }}" class="rpt-hotspot-row text-decoration-none d-block mb-1">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.78rem;">
                                <span style="color:#292524;font-weight:500;max-width:75%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $loc->location }}</span>
                                <span class="fw-700" style="color:#1d4ed8;">{{ $loc->total }} <i class="bi bi-arrow-right-short" style="font-size:.8rem;opacity:.6;"></i></span>
                            </div>
                            <div style="height:4px;background:#e2e8f0;border-radius:4px;">
                                <div class="inc-bar-fill" data-pct="{{ $maxInc > 0 ? round($loc->total / $maxInc * 100) : 0 }}"></div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Violation Hotspots --}}
    <div class="col-lg-7">
        <div class="rpt-card h-100">
            <div class="rpt-card-header">
                <span class="rpt-card-icon" style="background:linear-gradient(135deg,#d97706,#b45309);box-shadow:0 3px 10px rgba(217,119,6,.3);">
                    <i class="bi bi-pin-map-fill" style="color:#fff;"></i>
                </span>
                <div>
                    <div class="rpt-card-title">Violation Hotspots</div>
                    <div class="rpt-card-sub">Top locations by violation count — {{ $periodLabel }}</div>
                </div>
            </div>
            <div class="card-body p-3">
                @if($violationHotspots->isEmpty())
                    <div style="color:#a8a29e;font-style:italic;font-size:.85rem;text-align:center;padding:1.5rem 0;">No location data recorded for this period.</div>
                @else
                @php $maxViol = $violationHotspots->max('total'); @endphp
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
                        <thead>
                            <tr style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#a8a29e;">
                                <th class="border-0 ps-0">#</th>
                                <th class="border-0">Location</th>
                                <th class="border-0 text-center">Count</th>
                                <th class="border-0 rpt-no-print" style="min-width:100px;">Proportion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($violationHotspots as $i => $loc)
                            <tr>
                                <td class="ps-0" style="color:#a8a29e;font-weight:700;">{{ $i + 1 }}</td>
                                <td style="color:#292524;font-weight:500;">{{ $loc->location }}</td>
                                <td class="text-center">
                                    <span style="background:#fef3c7;color:#92400e;font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:10px;">{{ $loc->total }}</span>
                                </td>
                                <td class="rpt-no-print">
                                    <div style="height:6px;background:#f5f0e8;border-radius:4px;">
                                        <div class="viol-bar-fill" data-pct="{{ $maxViol > 0 ? round($loc->total / $maxViol * 100) : 0 }}"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@if($showAll)
{{-- ════════════════════════════════════════════
     ALL-MONTHS VIEW
════════════════════════════════════════════ --}}

{{-- ── Monthly × Type Matrix ── --}}
<div class="rpt-card mb-4 rpt-printable" data-rpt-section="violations">
    <div class="rpt-card-header">
        <span class="rpt-card-icon" style="background:linear-gradient(135deg,#dc2626,#b91c1c);box-shadow:0 3px 10px rgba(220,38,38,.3);">
            <i class="bi bi-table" style="color:#fff;"></i>
        </span>
        <div>
            <div class="rpt-card-title">Recap of Violations Per Month — {{ $year }}</div>
            <div class="rpt-card-sub">Breakdown by violation type across all months</div>
        </div>
        <span class="rpt-total-badge ms-auto">{{ $totalThisMonth }} total</span>
    </div>
    @php
        $activeTypes = $allTypes->filter(function($type) use ($yearMatrix) {
            foreach ($yearMatrix as $monthData) {
                if (($monthData[$type->id] ?? 0) > 0) return true;
            }
            return false;
        });
    @endphp
    <div class="table-responsive">
        <table class="table align-middle mb-0 rpt-matrix-table">
            <thead>
                <tr>
                    <th class="rpt-mth-month">Violation Type</th>
                    @for($m = 1; $m <= 12; $m++)
                        <th class="text-center rpt-mth-type">
                            <a href="{{ route('reports.index', ['month' => $m, 'year' => $year]) }}"
                               style="color:inherit;text-decoration:none;display:block;"
                               title="View {{ date('F', mktime(0,0,0,$m)) }} {{ $year }}">
                                {{ date('M', mktime(0,0,0,$m)) }}
                                <div style="font-size:.5rem;color:#94a3b8;font-weight:500;margin-top:1px;letter-spacing:.03em;">VIEW</div>
                            </a>
                        </th>
                    @endfor
                    <th class="text-center rpt-mth-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeTypes as $type)
                @php $typeTotal = array_sum(array_column($yearMatrix, $type->id)); @endphp
                <tr class="rpt-mrow rpt-mrow--active">
                    <td class="rpt-month-name">{{ $type->name }}</td>
                    @for($m = 1; $m <= 12; $m++)
                    @php $cnt = $yearMatrix[$m][$type->id] ?? 0; @endphp
                    <td class="text-center">
                        @if($cnt > 0)
                            <a href="{{ route('reports.index', ['month' => $m, 'year' => $year]) }}"
                               class="rpt-cnt-badge rpt-cnt-red" style="text-decoration:none;">{{ $cnt }}</a>
                        @else
                            <span class="rpt-cnt-dash">—</span>
                        @endif
                    </td>
                    @endfor
                    <td class="text-center">
                        <span class="rpt-cnt-badge rpt-cnt-blue">{{ $typeTotal }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="rpt-mfoot">
                    <td class="rpt-month-name">Total</td>
                    @for($m = 1; $m <= 12; $m++)
                    @php $monthTotal = $monthTotals[$m] ?? 0; @endphp
                    <td class="text-center">
                        @if($monthTotal > 0)
                            <span class="rpt-cnt-badge rpt-cnt-slate">{{ $monthTotal }}</span>
                        @else
                            <span class="rpt-cnt-zero">0</span>
                        @endif
                    </td>
                    @endfor
                    <td class="text-center">
                        <span class="rpt-cnt-badge rpt-cnt-blue">{{ $totalThisMonth }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ── Violator Recap (Year) ── --}}
<div class="rpt-card mb-4 rpt-printable" id="violator-recap" data-rpt-section="violators">
    <div class="rpt-card-header">
        <span class="rpt-card-icon" style="background:linear-gradient(135deg,#1d4ed8,#1e40af);box-shadow:0 3px 10px rgba(29,78,216,.3);">
            <i class="bi bi-person-lines-fill" style="color:#fff;"></i>
        </span>
        <div>
            <div class="rpt-card-title">Violator Recap — {{ $year }}</div>
            <div class="rpt-card-sub">Monthly violation breakdown per individual</div>
        </div>
        <span class="rpt-total-badge ms-auto" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">
            {{ count($yearViolatorMatrix) }} violator{{ count($yearViolatorMatrix) !== 1 ? 's' : '' }}
        </span>
    </div>
    @if(empty($yearViolatorMatrix))
        <div class="rpt-empty-state">
            <i class="bi bi-person-slash"></i>
            <span>No violations recorded for {{ $year }}.</span>
        </div>
    @else
    <div class="table-responsive">
        <table class="table align-middle mb-0 rpt-matrix-table">
            <thead>
                <tr>
                    <th style="min-width:160px;padding-left:.9rem;">Violator</th>
                    <th style="min-width:130px;">License No.</th>
                    @for($m = 1; $m <= 12; $m++)
                        <th class="text-center rpt-mth-type">{{ date('M', mktime(0,0,0,$m)) }}</th>
                    @endfor
                    <th class="text-center rpt-mth-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($yearViolatorMatrix as $i => $row)
                <tr class="rpt-mrow rpt-mrow--active align-top{{ $loop->index >= 20 ? ' rpt-show-more-row' : '' }}">
                    <td style="padding-left:.9rem;">
                        <a href="{{ route('violators.show', $row['violator']) }}"
                           class="rpt-violator-link">
                            {{ $row['violator']->full_name }}
                        </a>
                    </td>
                    <td>
                        <span class="rpt-license">{{ $row['violator']->license_number ?? '—' }}</span>
                    </td>
                    @for($m = 1; $m <= 12; $m++)
                    @php
                        $cnt   = $row['months'][$m] ?? 0;
                        $types = $row['monthTypes'][$m] ?? [];
                    @endphp
                    <td class="text-center" style="min-width:100px;">
                        @if($cnt > 0)
                            @foreach(array_count_values($types) as $typeName => $typeCount)
                                <span class="rpt-vtype-pill {{ $cnt > 1 ? 'rpt-vtype-red' : 'rpt-vtype-slate' }}">
                                    {{ $typeName }}@if($typeCount > 1)<span class="rpt-vtype-count">({{ $typeCount }})</span>@endif
                                </span>
                            @endforeach
                        @else
                            <span class="rpt-cnt-dash">—</span>
                        @endif
                    </td>
                    @endfor
                    <td class="text-center">
                        <span class="rpt-cnt-badge {{ $row['total'] > 1 ? 'rpt-cnt-red' : 'rpt-cnt-blue' }}">
                            {{ $row['total'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="rpt-mfoot">
                    <td colspan="2" style="padding-left:.9rem;">Violators per Month</td>
                    @for($m = 1; $m <= 12; $m++)
                    @php $mCount = collect($yearViolatorMatrix)->filter(fn($r) => ($r['months'][$m] ?? 0) > 0)->count(); @endphp
                    <td class="text-center">
                        @if($mCount > 0)
                            <span class="rpt-cnt-badge rpt-cnt-slate">{{ $mCount }}</span>
                        @else
                            <span class="rpt-cnt-zero">0</span>
                        @endif
                    </td>
                    @endfor
                    <td class="text-center">
                        <span class="rpt-cnt-badge rpt-cnt-blue">{{ count($yearViolatorMatrix) }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @if(count($yearViolatorMatrix) > 20)
    <div class="rpt-show-more-wrap rpt-no-print">
        <button type="button" class="rpt-show-more-btn" data-hidden="{{ count($yearViolatorMatrix) - 20 }}" onclick="rptToggleShowMore(this)">
            Show {{ count($yearViolatorMatrix) - 20 }} more <i class="bi bi-chevron-down ms-1"></i>
        </button>
    </div>
    @endif
    @endif
</div>

@else
{{-- ════════════════════════════════════════════
     SPECIFIC MONTH VIEW
════════════════════════════════════════════ --}}

{{-- ── By Violation Type ── --}}
<div class="rpt-card mb-4 rpt-printable" data-rpt-section="violations">
    <div class="rpt-card-header">
        <span class="rpt-card-icon" style="background:linear-gradient(135deg,#dc2626,#b91c1c);box-shadow:0 3px 10px rgba(220,38,38,.3);">
            <i class="bi bi-calendar3" style="color:#fff;"></i>
        </span>
        <div>
            <div class="rpt-card-title">{{ date('F', mktime(0,0,0,$month)) }} {{ $year }} — By Violation Type</div>
            <div class="rpt-card-sub">Summary of violations recorded this month</div>
        </div>
        <span class="rpt-total-badge ms-auto">{{ $totalThisMonth }} total</span>
    </div>
    @if($monthlySummary->isEmpty())
        <div class="rpt-empty-state">
            <i class="bi bi-calendar-x"></i>
            <span>No violations recorded for {{ date('F', mktime(0,0,0,$month)) }} {{ $year }}.</span>
        </div>
    @else
    <div class="table-responsive">
        <table class="table align-middle mb-0 rpt-data-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;">Violation Type</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Pending</th>
                    <th class="text-center">Settled</th>
                    <th class="text-center">Contested</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlySummary as $row)
                <tr class="rpt-drow">
                    <td style="padding-left:1.4rem;">
                        <span class="rpt-type-name">{{ $row['type']->name }}</span>
                    </td>
                    <td class="text-center">
                        <span class="rpt-cnt-badge rpt-cnt-blue">{{ $row['count'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="rpt-cnt-badge rpt-cnt-amber">{{ $row['pending'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="rpt-cnt-badge rpt-cnt-green">{{ $row['settled'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="rpt-cnt-badge rpt-cnt-slate">{{ $row['contested'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ── Violator Recap This Month ── --}}
<div class="rpt-card mb-4 rpt-printable" id="violator-recap" data-rpt-section="violators">
    <div class="rpt-card-header">
        <span class="rpt-card-icon" style="background:linear-gradient(135deg,#1d4ed8,#1e40af);box-shadow:0 3px 10px rgba(29,78,216,.3);">
            <i class="bi bi-person-lines-fill" style="color:#fff;"></i>
        </span>
        <div>
            <div class="rpt-card-title">Violator Recap — {{ date('F', mktime(0,0,0,$month)) }} {{ $year }}</div>
            <div class="rpt-card-sub">Individual violations recorded this month</div>
        </div>
        <span class="rpt-total-badge ms-auto" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;">
            {{ $monthlyOffenders->count() }} violator{{ $monthlyOffenders->count() !== 1 ? 's' : '' }}
        </span>
    </div>
    @if($monthlyOffenders->isEmpty())
        <div class="rpt-empty-state">
            <i class="bi bi-person-slash"></i>
            <span>No violations recorded for {{ date('F', mktime(0,0,0,$month)) }} {{ $year }}.</span>
        </div>
    @else
    <div class="table-responsive">
        <table class="table align-middle mb-0 rpt-data-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;">Violator</th>
                    <th>License No.</th>
                    <th>Violation(s)</th>
                    <th class="text-center" style="width:80px;">Count</th>
                    <th class="text-center rpt-no-print" style="width:90px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyOffenders as $i => $row)
                <tr class="rpt-drow align-top{{ $loop->index >= 20 ? ' rpt-show-more-row' : '' }}">
                    <td style="padding-left:1.4rem;">
                        <a href="{{ route('violators.show', $row['violator']) }}" class="rpt-violator-link">
                            {{ $row['violator']->full_name }}
                        </a>
                    </td>
                    <td>
                        <span class="rpt-license">{{ $row['violator']->license_number ?? '—' }}</span>
                    </td>
                    <td>
                        @foreach($row['violations'] as $viol)
                            <span class="rpt-vtype-pill {{ $row['count'] > 1 ? 'rpt-vtype-red' : 'rpt-vtype-slate' }} me-1 mb-1">
                                {{ $viol->violationType?->name ?? '—' }}
                                <span class="rpt-vtype-count">{{ $viol->date_of_violation->format('M d') }}</span>
                            </span>
                        @endforeach
                    </td>
                    <td class="text-center">
                        <span class="rpt-cnt-badge {{ $row['count'] > 1 ? 'rpt-cnt-red' : 'rpt-cnt-blue' }}">
                            {{ $row['count'] }}
                        </span>
                    </td>
                    <td class="text-center rpt-no-print">
                        <a href="{{ route('violators.show', $row['violator']) }}" class="rpt-act-btn">
                            <i class="bi bi-eye-fill"></i> Profile
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($monthlyOffenders->count() > 20)
    <div class="rpt-show-more-wrap rpt-no-print">
        <button type="button" class="rpt-show-more-btn" data-hidden="{{ $monthlyOffenders->count() - 20 }}" onclick="rptToggleShowMore(this)">
            Show {{ $monthlyOffenders->count() - 20 }} more <i class="bi bi-chevron-down ms-1"></i>
        </button>
    </div>
    @endif
    @endif
</div>
@endif


{{-- ── 72-Hour Overdue Violations (always shown) ── --}}
<div class="rpt-card mb-4 rpt-printable" data-rpt-section="overdue">
    <div class="rpt-card-header">
        <span class="rpt-card-icon" style="background:linear-gradient(135deg,#dc2626,#7f1d1d);box-shadow:0 3px 10px rgba(220,38,38,.35);">
            <i class="bi bi-alarm-fill" style="color:#fff;"></i>
        </span>
        <div>
            <div class="rpt-card-title">72-Hour Overdue Violations</div>
            <div class="rpt-card-sub">Pending violations recorded more than 72 hours ago with no settlement</div>
        </div>
        <span class="rpt-total-badge ms-auto" style="background:#fef2f2;color:#b91c1c;border-color:#fca5a5;">
            {{ $overdueViolations->count() }} overdue
        </span>
        <button type="button" onclick="rptPrintSection('overdue')" class="rpt-print-btn ms-2" style="background:linear-gradient(135deg,#dc2626,#b91c1c);box-shadow:0 2px 8px rgba(220,38,38,.3);">
            <i class="bi bi-printer-fill"></i> Print
        </button>
    </div>
    @if($overdueViolations->isEmpty())
        <div class="rpt-empty-state">
            <i class="bi bi-patch-check"></i>
            <span>No overdue violations at this time.</span>
        </div>
    @else
    <div class="table-responsive">
        <table class="table align-middle mb-0 rpt-data-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;">#</th>
                    <th>Violator</th>
                    <th>License No.</th>
                    <th>Violation Type</th>
                    <th>Plate No.</th>
                    <th class="text-center">Date Filed</th>
                    <th class="text-center">Hours Overdue</th>
                    <th class="text-center rpt-no-print" style="width:90px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($overdueViolations as $i => $ov)
                @php $isExtra = $loop->index >= 20; @endphp
                @php
                    $hoursOverdue = (int) $ov->date_of_violation->diffInHours(now());
                    $daysOverdue  = floor($hoursOverdue / 24);
                    $remHours     = $hoursOverdue % 24;
                    $overdueBadge = $hoursOverdue >= 168 ? 'rpt-cnt-red' : 'rpt-cnt-amber';
                @endphp
                <tr class="rpt-drow{{ $isExtra ? ' rpt-show-more-row' : '' }}">
                    <td style="padding-left:1.4rem;color:#a8a29e;font-size:.75rem;font-weight:600;">{{ $i + 1 }}</td>
                    <td>
                        @if($ov->violator)
                        <a href="{{ route('violators.show', $ov->violator) }}" class="rpt-violator-link">
                            {{ $ov->violator->full_name }}
                        </a>
                        @else
                        <span class="rpt-violator-link" style="color:#a8a29e;">(Deleted Motorist)</span>
                        @endif
                    </td>
                    <td>
                        <span class="rpt-license">{{ $ov->violator?->license_number ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="rpt-type-name">{{ $ov->violationType?->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="rpt-license">
                            {{ $ov->vehicle?->plate_number ?? $ov->vehicle_plate ?? '—' }}
                        </span>
                    </td>
                    <td class="text-center" style="font-size:.8rem;color:#57534e;">
                        {{ $ov->created_at->format('M d, Y g:i A') }}
                    </td>
                    <td class="text-center">
                        <span class="rpt-cnt-badge {{ $overdueBadge }}">
                            @if($daysOverdue > 0)
                                {{ $daysOverdue }}d {{ $remHours }}h
                            @else
                                {{ $hoursOverdue }}h
                            @endif
                        </span>
                    </td>
                    <td class="text-center rpt-no-print">
                        <a href="{{ route('violations.show', $ov) }}" class="rpt-act-btn">
                            <i class="bi bi-eye-fill"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($overdueViolations->count() > 20)
    <div class="rpt-show-more-wrap rpt-no-print">
        <button type="button" class="rpt-show-more-btn" data-hidden="{{ $overdueViolations->count() - 20 }}" onclick="rptToggleShowMore(this)">
            Show {{ $overdueViolations->count() - 20 }} more <i class="bi bi-chevron-down ms-1"></i>
        </button>
    </div>
    @endif
    @endif
</div>

{{-- ── Repeat Offenders (always shown) ── --}}
<div class="rpt-card rpt-printable" data-rpt-section="offenders">
    <div class="rpt-card-header">
        <span class="rpt-card-icon" style="background:linear-gradient(135deg,#b91c1c,#991b1b);box-shadow:0 3px 10px rgba(185,28,28,.35);">
            <i class="bi bi-person-exclamation" style="color:#fff;"></i>
        </span>
        <div>
            <div class="rpt-card-title">Repeat Offenders &amp; Recidivists — All Time</div>
            <div class="rpt-card-sub">Individuals with 2 or more violations on record</div>
        </div>
        <span class="rpt-total-badge ms-auto" style="background:#fef2f2;color:#b91c1c;border-color:#fca5a5;">
            {{ $repeatOffenders->count() }}
        </span>
    </div>
    @if($repeatOffenders->isEmpty())
        <div class="rpt-empty-state">
            <i class="bi bi-patch-check"></i>
            <span>No repeat offenders or recidivists recorded yet.</span>
        </div>
    @else
    <div class="table-responsive">
        <table class="table align-middle mb-0 rpt-data-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;">Name</th>
                    <th>License No.</th>
                    <th class="text-center">Total Violations</th>
                    <th class="text-center rpt-no-print" style="width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($repeatOffenders as $v)
                <tr class="rpt-drow{{ $loop->index >= 20 ? ' rpt-show-more-row' : '' }}">
                    <td style="padding-left:1.4rem;">
                        <a href="{{ route('violators.show', $v) }}" class="rpt-violator-link">
                            {{ $v->full_name }}
                        </a>
                    </td>
                    <td>
                        <span class="rpt-license">{{ $v->license_number ?? '—' }}</span>
                    </td>
                    <td class="text-center">
                        @if($v->violations_count >= 3)
                            <span class="rpt-offender-badge rpt-offender-red">
                                <i class="bi bi-fire me-1"></i>{{ $v->violations_count }} — Recidivist
                            </span>
                        @else
                            <span class="rpt-offender-badge rpt-offender-amber">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $v->violations_count }} — Repeat Offender
                            </span>
                        @endif
                    </td>
                    <td class="text-center rpt-no-print">
                        <a href="{{ route('violators.show', $v) }}" class="rpt-act-btn">
                            <i class="bi bi-eye-fill"></i> Profile
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($repeatOffenders->count() > 20)
    <div class="rpt-show-more-wrap rpt-no-print">
        <button type="button" class="rpt-show-more-btn" data-hidden="{{ $repeatOffenders->count() - 20 }}" onclick="rptToggleShowMore(this)">
            Show {{ $repeatOffenders->count() - 20 }} more <i class="bi bi-chevron-down ms-1"></i>
        </button>
    </div>
    @endif
    @endif
</div>

{{-- Signature block — print only --}}
<div class="rpt-print-signatures">
    <div class="rpt-sig-col">
        <div class="rpt-sig-label">Prepared by:</div>
        <div class="rpt-sig-line"></div>
        <div class="rpt-sig-name">{{ Auth::user()->name ?? 'N/A' }}</div>
        <div class="rpt-sig-title">Operation PNCO</div>
    </div>
    <div class="rpt-sig-col">
        <div class="rpt-sig-label">Noted by:</div>
        <div class="rpt-sig-line"></div>
        <div class="rpt-sig-name">PLTCOL RUEL L BURLAT</div>
        <div class="rpt-sig-title">Chief of Police</div>
    </div>
</div>

@if($search !== '')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('violator-recap');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
@endif

<script>
(function () {
    const form     = document.querySelector('.rpt-filter-body form');
    const input    = document.getElementById('rptSearchInput');
    const dropdown = document.getElementById('rptSearchDropdown');
    if (!form || !input || !dropdown) return;

    // Instant submit when any dropdown (select) changes
    form.querySelectorAll('select').forEach(function (sel) {
        sel.addEventListener('change', function () { form.submit(); });
    });

    // ── Autocomplete ──────────────────────────────────────────────
    let timer;

    function hide() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    }

    function show(names) {
        if (!names.length) { hide(); return; }
        dropdown.innerHTML = names.map(function (n) {
            const safe = n.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            return '<div class="rpt-sug" data-name="' + safe + '">'
                 + '<i class="bi bi-person-fill me-2"></i>' + safe + '</div>';
        }).join('');
        dropdown.style.display = 'block';
        dropdown.querySelectorAll('.rpt-sug').forEach(function (item) {
            item.addEventListener('mousedown', function (e) {
                e.preventDefault();
                input.value = this.dataset.name;
                hide();
                form.submit();
            });
        });
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (!q) { hide(); return; }
        timer = setTimeout(function () {
            fetch('{{ route("reports.suggestions") }}?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) { show(data); })
            .catch(function () { hide(); });
        }, 220);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hide();
    });

    input.addEventListener('blur', function () {
        setTimeout(hide, 160);
    });
})();
</script>

<style>
/* ─── QUICK ACCESS DROPDOWN ─── */
.rpt-quick-btn {
    display: inline-flex; align-items: center; gap: .38rem;
    padding: .38rem .9rem;
    border-radius: 9px; font-size: .8rem; font-weight: 700;
    background: linear-gradient(135deg, #292524, #1c1917);
    color: #fff; border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,.22);
    cursor: pointer; transition: all .15s; white-space: nowrap;
}
.rpt-quick-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,.32); }
.rpt-qchev { font-size: .65rem; transition: transform .2s; }

.rpt-quick-panel {
    position: absolute;
    right: 0; top: calc(100% + 8px);
    z-index: 1100;
    background: #fff;
    border: 1.5px solid #ece5da;
    border-radius: 14px;
    box-shadow: 0 14px 40px rgba(0,0,0,.14), 0 4px 14px rgba(0,0,0,.07);
    min-width: 230px;
    max-height: 360px;
    overflow-y: auto;
    overflow-x: hidden;
    padding: .5rem 0;
    scrollbar-width: thin;
    scrollbar-color: #d6cfc5 transparent;
}
.rpt-quick-panel::-webkit-scrollbar { width: 5px; }
.rpt-quick-panel::-webkit-scrollbar-track { background: transparent; }
.rpt-quick-panel::-webkit-scrollbar-thumb { background: #d6cfc5; border-radius: 10px; }
.rpt-qgroup-label {
    font-size: .6rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .1em;
    color: #a8a29e;
    padding: .45rem 1rem .2rem;
}
.rpt-qdivider { height: 1px; background: #f0ebe3; margin: .4rem 0; }
.rpt-qitem {
    display: flex; align-items: center; gap: .6rem;
    width: 100%; padding: .48rem 1rem;
    font-size: .8rem; font-weight: 600; color: #1c1917;
    background: none; border: none; text-decoration: none;
    cursor: pointer; transition: background .1s, color .1s;
    text-align: left; line-height: 1.3;
}
.rpt-qitem:hover { background: #fdf8f0; color: #b91c1c; }
.rpt-qitem--print:hover { background: #f0f9ff; color: #1d4ed8; }
.rpt-qitem-icon {
    width: 22px; height: 22px; border-radius: 6px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .7rem;
}
.rpt-qi-red   { background: #fef2f2; color: #b91c1c; }
.rpt-qi-amber { background: #fffbeb; color: #b45309; }
.rpt-qi-green { background: #f0fdf4; color: #15803d; }
.rpt-qi-blue  { background: #eff6ff; color: #1d4ed8; }
.rpt-qi-slate { background: #f8fafc; color: #475569; }
.rpt-qi-arr { margin-left: auto; font-size: .9rem; color: #d6d3d1; flex-shrink: 0; }
.rpt-qitem:hover .rpt-qi-arr { color: #b91c1c; }
.rpt-qitem--print:hover .rpt-qi-arr { color: #1d4ed8; }

/* ─── SECTION-ONLY PRINT (CSS-driven via body attribute) ─── */
@media print {
    /* When a specific section is chosen, hide every printable block … */
    body[data-print-section] .rpt-printable { display: none !important; }
    /* … then reveal only the targeted one */
    body[data-print-section="kpi"]                 .rpt-printable[data-rpt-section="kpi"]                 { display: flex  !important; }
    body[data-print-section="incidents"]           .rpt-printable[data-rpt-section="incidents"]           { display: flex  !important; }
    body[data-print-section="incident-analytics"]  .rpt-printable[data-rpt-section="incident-analytics"]  { display: block !important; }
    body[data-print-section="violations"]          .rpt-printable[data-rpt-section="violations"]          { display: block !important; }
    body[data-print-section="violators"]           .rpt-printable[data-rpt-section="violators"]           { display: block !important; }
    body[data-print-section="overdue"]             .rpt-printable[data-rpt-section="overdue"]             { display: block !important; }
    body[data-print-section="offenders"]           .rpt-printable[data-rpt-section="offenders"]           { display: block !important; }
    /* Charts: force fixed height so they render during print */
    .inc-chart-wrap { height: 160px !important; }
    .inc-period-btn, #incWeekNav { display: none !important; }
}

/* ─── Incident Analytics Charts ─── */
.inc-chart-panel {
    background: #f8fffe;
    border: 1px solid #ccfbf1;
    border-radius: 10px;
    padding: .9rem .9rem .6rem;
}
.inc-chart-label {
    font-size: .77rem;
    font-weight: 700;
    color: #0f766e;
    text-align: center;
    margin-bottom: .4rem;
    letter-spacing: .05em;
    text-transform: uppercase;
}
.inc-chart-wrap {
    position: relative;
    height: 210px;
}
.inc-period-active {
    background: #0d9488 !important;
    border-color: #0d9488 !important;
    color: #fff !important;
}

/* ─── KPI CARDS — dashboard-style ─── */
.rpt-kpi-link {
    display: block;
    text-decoration: none;
    color: inherit;
    height: 100%;
}
.rpt-kpi-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    cursor: pointer;
}
.rpt-kpi-link:hover .rpt-kpi-card {
    transform: translateY(-5px);
    box-shadow: 0 10px 28px rgba(120,80,20,0.13) !important;
}
/* Icon wrap */
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
.rpt-kpi-link:hover .stat-icon-wrap {
    transform: rotate(-8deg) scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,.2);
}
/* Number */
.rpt-kpi-num {
    font-size: 1.65rem;
    font-weight: 700;
    line-height: 1.1;
    color: #292524;
    display: inline-block;
    animation: rptBreathe 4s ease-in-out infinite;
}
@keyframes rptBreathe {
    0%, 100% { opacity: 1; }
    50%       { opacity: .82; }
}
/* Label + sub */
.rpt-kpi-label {
    font-size: .75rem;
    color: #78716c;
    font-weight: 500;
    transition: color 0.2s ease;
}
.rpt-kpi-sub {
    font-size: .7rem;
    color: #a8a29e;
    margin-top: 2px;
    transition: color 0.2s ease;
}
.rpt-kpi-link:hover .rpt-kpi-label { color: #57534e; }
.rpt-kpi-link:hover .rpt-kpi-sub   { color: #78716c; }
/* Footer strip */
.rpt-kpi-footer {
    font-size: .7rem;
    font-weight: 600;
    color: #a8a29e;
    text-align: right;
    padding: .3rem .85rem .45rem;
    border-top: 1px solid #f5f5f4;
    letter-spacing: .02em;
    transition: color 0.2s ease, background 0.2s ease;
}
.rpt-kpi-link:hover .rpt-kpi-footer {
    color: #57534e;
    background: #fafaf9;
}
/* Background blob */
.stat-bg-blob {
    position: absolute;
    top: -20px;
    right: -20px;
    width: 90px;
    height: 90px;
    border-radius: 50%;
    pointer-events: none;
    animation: rptFloatBlob 6s ease-in-out infinite;
}
@keyframes rptFloatBlob {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33%       { transform: translate(-6px, 4px) scale(1.05); }
    66%       { transform: translate(4px, -6px) scale(.96); }
}
/* Pulse dot */
.stat-pulse {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #fca5a5;
    animation: rptPulse 1.8s ease-in-out infinite;
}
@keyframes rptPulse {
    0%, 100% { transform: scale(1);   opacity: 1; }
    50%       { transform: scale(1.6); opacity: .4; }
}

/* Print-only KPI cards: hidden on screen */
.rpt-kpi-print-only { display: none; }

/* ─── CLICKABLE INCIDENT STATUS ROWS ─── */
.rpt-inc-stat-row {
    transition: filter .13s, transform .13s;
    cursor: pointer;
}
.rpt-inc-stat-row:hover {
    filter: brightness(.95);
    transform: translateX(3px);
}

/* ─── CLICKABLE HOTSPOT ROWS ─── */
.rpt-hotspot-row {
    padding: .2rem .3rem;
    border-radius: 6px;
    transition: background .13s, transform .13s;
    cursor: pointer;
}
.rpt-hotspot-row:hover {
    background: #eff6ff;
    transform: translateX(3px);
}

/* ─── SEARCH AUTOCOMPLETE DROPDOWN ─── */
#rptSearchDropdown {
    display: none;
    position: absolute;
    top: 100%; left: 0; right: 0;
    z-index: 1050;
    background: #fff;
    border: 1.5px solid #e7dfd5;
    border-radius: 10px;
    box-shadow: 0 8px 28px rgba(0,0,0,.13);
    margin-top: 3px;
    overflow: hidden;
}
.rpt-sug {
    padding: .48rem 1rem;
    font-size: .82rem;
    color: #1c1917;
    cursor: pointer;
    transition: background .1s;
    display: flex;
    align-items: center;
}
.rpt-sug i { color: #a8a29e; font-size: .78rem; flex-shrink: 0; }
.rpt-sug:hover { background: #fdf8f0; color: #b91c1c; }
.rpt-sug:hover i { color: #b91c1c; }
.rpt-sug + .rpt-sug { border-top: 1px solid #f5f0e8; }

/* ─── FILTER CARD ─── */
.rpt-filter-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: visible;
}
.rpt-filter-header {
    display: flex; align-items: center; gap: 1rem;
    padding: .9rem 1.3rem;
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
    border-bottom: 1.5px solid #ece5da;
    border-radius: 16px 16px 0 0;
}
.rpt-filter-icon {
    width: 40px; height: 40px;
    border-radius: 11px;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1rem;
    box-shadow: 0 3px 10px rgba(220,38,38,.35);
    flex-shrink: 0;
}
.rpt-filter-title { font-size: .92rem; font-weight: 700; color: #1c1917; }
.rpt-filter-sub   { font-size: .72rem; color: #a8a29e; }
.rpt-filter-body  { padding: .85rem 1.3rem; }

.rpt-flabel {
    display: block;
    font-size: .65rem; font-weight: 700;
    color: #a8a29e; text-transform: uppercase; letter-spacing: .05em;
    margin-bottom: .3rem;
}
.rpt-ig {
    background: #fdf8f0; border-color: #e7dfd5; color: #a8a29e;
    font-size: .78rem; padding: .28rem .6rem;
}
.rpt-finput { font-size: .82rem; }
.rpt-filter-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .38rem .9rem;
    border-radius: 9px; font-size: .8rem; font-weight: 700;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff; border: none;
    box-shadow: 0 2px 8px rgba(220,38,38,.3);
    cursor: pointer; transition: all .15s; white-space: nowrap;
}
.rpt-filter-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,.4); }
.rpt-reset-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px;
    border-radius: 9px; font-size: .88rem;
    border: 1.5px solid #e7dfd5; color: #a8a29e; background: #fff;
    text-decoration: none; transition: all .15s;
}
.rpt-reset-btn:hover { border-color: #dc2626; color: #dc2626; background: #fff1f2; }
@keyframes rptResetFlash {
    0%   { background: #dc2626; color: #fff; border-color: #dc2626; transform: scale(1.15); }
    100% { background: transparent; color: inherit; transform: scale(1); }
}
.rpt-reset-flash { animation: rptResetFlash 0.2s ease-out forwards; }

/* ─── SECTION CARDS ─── */
.rpt-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
.rpt-card-header {
    display: flex; align-items: center; gap: 1rem;
    padding: .9rem 1.3rem;
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
    border-bottom: 1.5px solid #ece5da;
}
.rpt-card-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; flex-shrink: 0;
}
.rpt-card-title { font-size: .88rem; font-weight: 700; color: #1c1917; }
.rpt-card-sub   { font-size: .71rem; color: #a8a29e; margin-top: .05rem; }

/* ─── Incident summary status row colors ─── */
.inc-sum-open         { background: #eff6ff; border: 1px solid #eff6ff; }
.inc-sum-open-fg      { color: #1d4ed8; }
.inc-sum-under-review { background: #fffbeb; border: 1px solid #fffbeb; }
.inc-sum-under-review-fg { color: #92400e; }
.inc-sum-closed       { background: #f0fdf4; border: 1px solid #f0fdf4; }
.inc-sum-closed-fg    { color: #15803d; }

/* ─── Proportion bars ─── */
.inc-bar-fill  { height: 4px; background: #1d4ed8; border-radius: 4px; width: 0; }
.viol-bar-fill { height: 6px; background: linear-gradient(90deg, #d97706, #f59e0b); border-radius: 4px; width: 0; }

.rpt-total-badge {
    display: inline-flex; align-items: center;
    padding: .22rem .7rem;
    border-radius: 20px; border: 1.5px solid #fca5a5;
    background: #fef2f2; color: #b91c1c;
    font-size: .72rem; font-weight: 700;
    white-space: nowrap;
}

/* ─── EMPTY STATE ─── */
.rpt-empty-state {
    display: flex; align-items: center; gap: .6rem;
    padding: 2.2rem 1.4rem;
    color: #a8a29e; font-size: .86rem;
}
.rpt-empty-state i { font-size: 1.5rem; color: #d6d3d1; }

/* ─── MATRIX TABLE (month×type or violator×month) ─── */
.rpt-matrix-table thead tr { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
.rpt-matrix-table thead th {
    color: #94a3b8;
    font-size: .64rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    border-bottom: none; padding-top: .75rem; padding-bottom: .75rem;
    white-space: nowrap;
}
.rpt-mth-month { padding-left: 1.1rem; min-width: 110px; }
.rpt-mth-type  { min-width: 110px; }
.rpt-mth-total { min-width: 70px; }

.rpt-mrow td { border-color: #f5f0ea; padding-top: .7rem; padding-bottom: .7rem; vertical-align: middle; }
.rpt-mrow--active:hover { background: #fffbf8 !important; }
.rpt-mrow--empty { opacity: .55; }
.rpt-month-name { font-size: .82rem; font-weight: 600; color: #44403c; padding-left: 1.1rem; }

.rpt-mfoot { background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%); }
.rpt-mfoot td {
    font-size: .78rem; font-weight: 700; color: #78716c;
    border-top: 2px solid #ece5da; border-bottom: none;
    padding-top: .7rem; padding-bottom: .7rem;
}

/* ─── STANDARD DATA TABLE (monthly summary, repeat offenders) ─── */
.rpt-data-table thead tr { background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%); }
.rpt-data-table thead th {
    font-size: .68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    color: #78716c; border-bottom: 2px solid #ece5da;
    padding-top: .9rem; padding-bottom: .9rem;
}
.rpt-drow { transition: background .15s; }
.rpt-drow:hover { background: #fffbf8 !important; }
.rpt-drow td {
    padding-top: .85rem; padding-bottom: .85rem;
    border-color: #f5f0ea; vertical-align: middle;
}

/* ─── SHARED CELLS ─── */
.rpt-row-num  { font-size: .75rem; color: #a8a29e; font-weight: 600; }
.rpt-violator-link {
    font-size: .86rem; font-weight: 700; color: #1c1917;
    text-decoration: none;
}
.rpt-violator-link:hover { color: #dc2626; text-decoration: underline; }
.rpt-license {
    font-size: .76rem; color: #78716c;
    font-family: ui-monospace, 'Cascadia Code', monospace;
}
.rpt-type-name { font-size: .85rem; font-weight: 600; color: #1c1917; }

/* ─── COUNT BADGES ─── */
.rpt-cnt-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 26px; padding: .18rem .55rem;
    border-radius: 20px; border: 1.5px solid;
    font-size: .71rem; font-weight: 700;
}
.rpt-cnt-red   { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
.rpt-cnt-blue  { background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe; }
.rpt-cnt-green { background:#f0fdf4;color:#15803d;border-color:#86efac; }
.rpt-cnt-amber { background:#fffbeb;color:#b45309;border-color:#fde68a; }
.rpt-cnt-slate { background:#f8fafc;color:#475569;border-color:#cbd5e1; }
.rpt-cnt-dash  { color: #d6d3d1; font-size: .82rem; }
.rpt-cnt-zero  { color: #d6d3d1; font-size: .78rem; font-weight: 600; }

/* ─── VIOLATION TYPE PILLS (in matrix cells) ─── */
.rpt-vtype-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .68rem; font-weight: 700;
    padding: .18rem .5rem;
    border-radius: 6px; border: 1px solid;
    white-space: nowrap; margin-bottom: .2rem;
    line-height: 1.4;
}
.rpt-vtype-red   { background:#fff1f2;color:#b91c1c;border-color:#fecdd3; }
.rpt-vtype-slate { background:#f8fafc;color:#475569;border-color:#cbd5e1; }
.rpt-vtype-count { font-weight: 500; opacity: .75; font-size: .65rem; }

/* ─── OFFENDER BADGES ─── */
.rpt-offender-badge {
    display: inline-flex; align-items: center;
    padding: .28rem .75rem;
    border-radius: 20px; border: 1.5px solid;
    font-size: .73rem; font-weight: 700;
}
.rpt-offender-red   { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
.rpt-offender-amber { background:#fffbeb;color:#b45309;border-color:#fde68a; }

/* ─── PRINT BUTTON ─── */
.rpt-print-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .38rem .9rem;
    border-radius: 9px; font-size: .8rem; font-weight: 700;
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    color: #fff; border: none;
    box-shadow: 0 2px 8px rgba(29,78,216,.3);
    cursor: pointer; transition: all .15s; white-space: nowrap;
}
.rpt-print-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(29,78,216,.4); }

/* ─── PRINT HEADER (screen: hidden) ─── */
.print-header, .print-report-title-block { display: none; }

/* ─── ACTION BUTTON ─── */
.rpt-act-btn {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .26rem .65rem;
    border-radius: 8px; font-size: .76rem; font-weight: 700;
    background: #eff6ff; color: #1d4ed8;
    border: 1.5px solid #bfdbfe;
    text-decoration: none; transition: all .18s;
}
.rpt-act-btn:hover { background: #1d4ed8; color: #fff; border-color: #1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(29,78,216,.3); }

/* ─── DETAIL BUTTON (matrix) ─── */
.rpt-detail-btn {
    display: inline-flex; align-items: center; gap: .28rem;
    padding: .22rem .6rem;
    border-radius: 7px; font-size: .72rem; font-weight: 700;
    background: #eff6ff; color: #1d4ed8;
    border: 1.5px solid #bfdbfe;
    text-decoration: none; white-space: nowrap; transition: all .15s;
}
.rpt-detail-btn:hover { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }

/* ════════════════════════════════════════════
   PRINT STYLES
════════════════════════════════════════════ */
@media print {
    @page { size: A4 landscape; margin: 10mm 16mm 12mm; }

    /* ── Page & body ── */
    body          { background: #fff !important; color: #000 !important; font-size: 11pt; font-family: Arial, sans-serif; }
    .main-wrapper { margin-left: 0 !important; }
    .content      { padding: 0 !important; }

    /* ── Reset Bootstrap row negative gutters (prevent content clipping at page edge) ── */
    .row { margin-left: 0 !important; margin-right: 0 !important; }

    /* ── Hide ALL interactive / screen-only elements ── */
    .sidebar, .topbar, .rpt-filter-card,
    .rpt-print-btn, .rpt-reset-btn, .rpt-filter-btn,
    .rpt-quick-btn, .rpt-quick-panel,
    .rpt-act-btn, .rpt-detail-btn,
    .rpt-kpi-hint, .inc-bar-fill, .viol-bar-fill,
    .bi-chevron-right, .bi-arrow-right-short,
    .alert { display: none !important; }

    /* ── Letterhead ── */
    .print-header {
        display: flex !important; align-items: center; gap: 10pt;
        border-bottom: 3pt double #b91c1c;
        padding-bottom: 6pt; margin-bottom: 4pt;
        page-break-inside: avoid;
    }
    .print-logo          { width: 80pt; height: 80pt; flex-shrink: 0; object-fit: contain; }
    .print-logo-pnp      { object-fit: cover; }
    .print-agency-center { flex: 1; text-align: center; line-height: 1.45; }
    .print-ph-republic   { font-size: 8pt; color: #111; }
    .print-ph-npc        { font-size: 8pt; color: #111; }
    .print-ph-pro7       { font-size: 9pt; font-weight: 600; color: #111; }
    .print-ph-cebu       { font-size: 10.5pt; font-weight: 800; color: #111; text-transform: uppercase; letter-spacing: .03em; }
    .print-ph-station    { font-size: 12.5pt; font-weight: 900; color: #111; text-transform: uppercase; letter-spacing: .03em; }
    .print-ph-address    { font-size: 8pt; color: #111; margin-top: 1pt; }
    .print-report-title-block {
        display: block !important; text-align: center;
        padding: 3pt 0 5pt; border-bottom: 1.5pt solid #b91c1c; margin-bottom: 10pt;
    }
    .print-report-title { font-size: 13pt; font-weight: 900; text-transform: uppercase; letter-spacing: .07em; color: #b91c1c; }
    .print-section-note { font-size: 9.5pt; color: #333; margin-top: 3pt; font-weight: 600; }
    .print-filter-note  { font-size: 8pt; color: #666; margin-top: 2pt; font-style: italic; }
    .print-date         { font-size: 8pt; color: #555; margin-top: 1pt; }

    /* ── Printable section spacing ── */
    .rpt-printable { margin-bottom: 10pt !important; }
    /* Only avoid page breaks inside small sections, not large tables */
    .rpt-printable .rpt-card-header { page-break-inside: avoid; page-break-after: avoid; }
    /* Show ALL rows in print — screen hides rows beyond 20 via .rpt-show-more-row */
    .rpt-show-more-row { display: table-row !important; }

    /* ── Strip ALL card chrome ── */
    .rpt-card, .card {
        border: none !important; border-radius: 0 !important;
        box-shadow: none !important; background: transparent !important;
        overflow: visible !important; margin-bottom: 8pt !important;
    }
    .table-responsive { overflow: visible !important; }

    /* ── Section heading: bold underlined title ── */
    .rpt-card-header {
        display: flex !important; align-items: baseline !important;
        background: transparent !important; border: none !important;
        border-bottom: 1.5pt solid #111 !important;
        padding: 0 0 3pt !important; margin-bottom: 5pt !important; gap: 5pt !important;
    }
    .rpt-card-icon  { display: none !important; }
    .rpt-card-title {
        font-size: 10.5pt !important; font-weight: 800 !important;
        text-transform: uppercase; letter-spacing: .05em; color: #111 !important;
        flex-shrink: 0 !important;
    }
    .rpt-card-sub {
        font-size: 8pt !important; color: #555 !important; font-weight: 400 !important;
    }
    .rpt-total-badge {
        font-size: 8pt !important; margin-left: auto !important;
        border: 1pt solid #999 !important; background: #eee !important;
        color: #222 !important; padding: 1pt 5pt !important; border-radius: 2pt !important;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .card-body { padding: 4pt 0 0 !important; }

    /* ── KPI: 2-row grid of 4, no card chrome ── */
    .rpt-printable[data-rpt-section="kpi"] {
        display: flex !important; flex-wrap: wrap !important; gap: 0 !important;
        border: 1.5pt solid #999 !important; margin-bottom: 10pt !important;
        width: 100% !important; box-sizing: border-box !important;
    }
    .rpt-printable[data-rpt-section="kpi"] > .col-6,
    .rpt-printable[data-rpt-section="kpi"] > .col-md-3 {
        flex: 0 0 25% !important; width: 25% !important; max-width: 25% !important;
        padding: 7pt 10pt !important; margin: 0 !important; box-sizing: border-box !important;
        border-right: 1pt solid #bbb !important; border-bottom: 1pt solid #bbb !important;
        text-align: center !important; vertical-align: top !important;
        background: transparent !important;
    }
    /* Remove right border on every 4th card */
    .rpt-printable[data-rpt-section="kpi"] > .col-6:nth-child(4n),
    .rpt-printable[data-rpt-section="kpi"] > .col-md-3:nth-child(4n) { border-right: none !important; }
    /* Remove bottom border on last row (cards 5–8) */
    .rpt-printable[data-rpt-section="kpi"] > .col-6:nth-child(n+5),
    .rpt-printable[data-rpt-section="kpi"] > .col-md-3:nth-child(n+5) { border-bottom: none !important; }
    .rpt-kpi-card  { border: none !important; box-shadow: none !important; background: transparent !important; height: auto !important; }
    .rpt-kpi-link  { display: block !important; text-decoration: none !important; color: inherit !important; pointer-events: none !important; }
    .rpt-kpi-card .card-body { display: block !important; padding: 0 !important; }
    .stat-icon-wrap, .stat-bg-blob, .stat-pulse, .rpt-kpi-footer { display: none !important; }
    .rpt-kpi-label { font-size: 7.5pt !important; font-weight: 700 !important; color: #444 !important; text-transform: uppercase !important; letter-spacing: .07em !important; margin-bottom: 3pt !important; border-bottom: 1pt solid #ccc !important; padding-bottom: 2pt !important; }
    .rpt-kpi-num   { font-size: 20pt !important; font-weight: 900 !important; color: #111 !important; line-height: 1.1 !important; animation: none !important; display: block !important; }
    .rpt-kpi-sub   { font-size: 7.5pt !important; color: #555 !important; margin-top: 2pt !important; display: block !important; }
    /* Show print-only KPI cards as flex cells in the grid */
    .rpt-kpi-print-only {
        display: flex !important; flex: 0 0 25% !important; width: 25% !important; max-width: 25% !important;
        padding: 7pt 10pt !important; margin: 0 !important; box-sizing: border-box !important;
        border-right: 1pt solid #bbb !important; border-bottom: none !important;
        text-align: center !important; align-items: stretch !important;
    }
    .rpt-kpi-print-only:nth-child(4n) { border-right: none !important; }
    .rpt-kpi-print-item { width: 100% !important; }
    .rpt-kpi-print-num  { font-size: 20pt !important; font-weight: 900 !important; color: #111 !important; line-height: 1.1 !important; }
    .rpt-kpi-print-sub  { font-size: 7.5pt !important; color: #555 !important; margin-top: 2pt !important; }

    /* ── Incident section: two panels side by side ── */
    .rpt-printable[data-rpt-section="incidents"] {
        display: flex !important; gap: 12pt !important; align-items: flex-start !important;
    }
    .rpt-printable[data-rpt-section="incidents"] > .col-lg-5 {
        width: 37% !important; flex-shrink: 0 !important; padding: 0 !important;
    }
    .rpt-printable[data-rpt-section="incidents"] > .col-lg-7 {
        flex: 1 !important; padding: 0 !important;
    }

    /* Incident status rows: clean list lines ── */
    .rpt-inc-stat-row {
        display: flex !important; border-radius: 0 !important;
        background: transparent !important; text-decoration: none !important; color: #000 !important;
        border: none !important; border-bottom: 1pt solid #ddd !important;
        padding: 5pt 2pt !important;
    }
    .rpt-inc-stat-row * { color: #000 !important; font-size: 9pt !important; white-space: normal !important; }
    .rpt-inc-stat-row .fw-bold, .rpt-inc-stat-row [style*="font-weight"] { font-weight: 700 !important; }
    .inc-sum-open, .inc-sum-under-review, .inc-sum-closed {
        background: transparent !important; border: none !important;
        border-bottom: 1pt solid #ddd !important;
    }
    .inc-sum-open-fg, .inc-sum-under-review-fg, .inc-sum-closed-fg { color: #000 !important; }

    /* Top locations: plain list ── */
    .rpt-hotspot-row {
        display: block !important; background: transparent !important;
        border-radius: 0 !important; border-bottom: 1pt solid #ddd !important;
        padding: 4pt 0 !important;
    }
    .rpt-hotspot-row * {
        color: #000 !important; font-size: 9pt !important;
        white-space: normal !important; overflow: visible !important;
        text-overflow: clip !important; max-width: none !important;
    }
    /* "Top Locations" label heading */
    div[style*="text-transform:uppercase"][style*="letter-spacing"] { font-size: 8pt !important; color: #444 !important; }

    /* Hide all proportion bars and their wrappers */
    div[style*="height:4px"], div[style*="height:6px"] { display: none !important; }

    /* ── All links: plain black text ── */
    a, a:visited { color: #000 !important; text-decoration: none !important; pointer-events: none !important; }

    /* Hide "VIEW" sub-text inside matrix column header links */
    .rpt-matrix-table thead th a > div { display: none !important; }
    .rpt-matrix-table thead th a       { color: #111 !important; font-weight: 700 !important; display: inline !important; }

    /* ── table-responsive: never clip table content ── */
    .table-responsive { overflow: visible !important; }

    /* ── Tables: full borders, proper sizing ── */
    table { border-collapse: collapse !important; width: 100% !important; }
    .rpt-matrix-table { table-layout: fixed !important; }
    .rpt-data-table   { table-layout: auto !important; }

    .rpt-matrix-table thead tr,
    .rpt-data-table   thead tr {
        background: #e0e0e0 !important;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .rpt-matrix-table thead th,
    .rpt-data-table   thead th {
        font-size: 8pt !important; font-weight: 700 !important; color: #111 !important;
        text-transform: uppercase; letter-spacing: .04em;
        padding: 5pt 4pt !important; border: 1pt solid #aaa !important;
        white-space: normal !important; vertical-align: middle !important;
    }
    .rpt-mrow td, .rpt-drow td {
        padding: 5pt 6pt !important; font-size: 9pt !important;
        border: 1pt solid #d5d5d5 !important; vertical-align: middle !important;
    }
    /* Alternating rows */
    .rpt-drow:nth-child(even) td,
    .rpt-mrow:nth-child(even) td {
        background: #f7f7f7 !important;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .rpt-mfoot td {
        font-size: 9pt !important; font-weight: 700 !important;
        background: #e0e0e0 !important; border: 1pt solid #aaa !important;
        border-top: 1.5pt solid #666 !important; padding: 5pt 6pt !important;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .rpt-mrow--empty { opacity: 1 !important; }

    /* Matrix column widths */
    .rpt-matrix-table thead th:first-child,
    .rpt-mth-month { width: 20% !important; white-space: normal !important; word-break: break-word; }
    .rpt-mth-type  { width: 5.5% !important; white-space: normal !important; }
    .rpt-mth-total { width: 5% !important; }
    .rpt-month-name { font-size: 9pt !important; padding-left: 3pt !important; white-space: normal !important; word-break: break-word !important; }

    /* Violator Recap columns */
    #violator-recap .rpt-matrix-table                         { table-layout: fixed !important; }
    #violator-recap .rpt-matrix-table thead th:first-child    { width: 16% !important; }
    #violator-recap .rpt-matrix-table thead th:nth-child(2)   { width: 10% !important; }
    #violator-recap .rpt-matrix-table thead th.rpt-mth-type   { width: 5% !important; }
    #violator-recap .rpt-matrix-table thead th.rpt-mth-total  { width: 4% !important; }
    #violator-recap .rpt-matrix-table td .rpt-vtype-pill {
        display: inline-block !important; font-size: 7.5pt !important;
        padding: 1pt 3pt !important; line-height: 1.4 !important;
        white-space: normal !important; word-break: break-word !important;
    }

    /* Fix inline padding-left on table cells */
    td[style*="padding-left"] { padding-left: 4pt !important; }
    th[style*="padding-left"] { padding-left: 4pt !important; }

    /* ── Text ── */
    .rpt-violator-link { font-size: 9.5pt !important; font-weight: 700 !important; color: #000 !important; word-break: break-word; }
    .rpt-license       { font-size: 8.5pt !important; color: #333 !important; font-family: 'Courier New', monospace !important; word-break: break-all; }
    .rpt-type-name     { font-size: 9pt !important; color: #000 !important; }
    .rpt-row-num       { font-size: 8.5pt !important; color: #444 !important; }

    /* ── Badges & pills: neutral grey for readability ── */
    .rpt-cnt-badge, .rpt-vtype-pill, .rpt-offender-badge, .rpt-total-badge {
        font-size: 8pt !important; padding: 2pt 5pt !important;
        border: 1pt solid #999 !important; background: #eeeeee !important;
        color: #111 !important; border-radius: 2pt !important;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }

    /* ── Hide action/detail columns ── */
    .rpt-no-print { display: none !important; }

    /* ── Empty state ── */
    .rpt-empty-state { font-size: 10pt !important; color: #555 !important; padding: 5pt 0 !important; }
    .rpt-empty-state i { display: none !important; }

    /* ── Override any remaining inline font-size / color styles ── */
    .rpt-mrow td *, .rpt-drow td * { font-size: inherit !important; }
    table td span[style*="font-size"], table th span[style*="font-size"] { font-size: 8pt !important; }
    table td[style*="font-size"] { font-size: 9pt !important; }
    table td[style*="color"] { color: #000 !important; }
    table td[style*="font-weight:700"], table td[style*="font-weight: 700"] { font-weight: 700 !important; }

    /* ── Signature block ── */
    .rpt-print-signatures {
        display: flex !important;
        justify-content: space-between;
        margin-top: 60pt;
        margin-bottom: 10pt;
        page-break-inside: avoid;
    }
    .rpt-sig-col { min-width: 180pt; }
    .rpt-sig-label { font-size: 8.5pt; color: #111; margin-bottom: 26pt; }
    .rpt-sig-name  { font-size: 9pt; font-weight: 700; text-align: center; }
    .rpt-sig-line  { border-bottom: 1pt solid #000; }
    .rpt-sig-title { font-size: 8pt; font-style: italic; text-align: center; margin-top: 2pt; color: #333; }
}

/* Hidden on screen */
.rpt-print-signatures { display: none; }

/* Show-more rows */
.rpt-show-more-row { display: none; }
.rpt-show-more-wrap {
    text-align: center;
    padding: .6rem 1rem;
    border-top: 1px solid #f0ebe3;
}
.rpt-show-more-btn {
    background: none;
    border: 1px solid #d6cfc4;
    border-radius: .4rem;
    padding: .35rem 1rem;
    font-size: .78rem;
    font-weight: 600;
    color: #78716c;
    cursor: pointer;
    transition: background .15s, color .15s;
}
.rpt-show-more-btn:hover { background: #f5f0ea; color: #44403c; }
</style>

<script>
// Set proportion bar widths from data-pct attributes
document.querySelectorAll('.inc-bar-fill[data-pct], .viol-bar-fill[data-pct]').forEach(function(el) {
    el.style.width = (el.dataset.pct || 0) + '%';
});

// ── Quick Access Dropdown ──────────────────────────────────
function rptToggleQuick() {
    var panel = document.getElementById('rptQuickPanel');
    var chev  = document.getElementById('rptQChev');
    var btn   = document.getElementById('rptQuickToggle');
    var open  = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : 'block';
    chev.style.transform = open ? '' : 'rotate(180deg)';
    btn.setAttribute('aria-expanded', String(!open));
}
function rptCloseQuick() {
    var panel = document.getElementById('rptQuickPanel');
    var chev  = document.getElementById('rptQChev');
    var btn   = document.getElementById('rptQuickToggle');
    if (!panel) return;
    panel.style.display = 'none';
    chev.style.transform = '';
    if (btn) btn.setAttribute('aria-expanded', 'false');
}
document.addEventListener('click', function(e) {
    var wrap = document.getElementById('rptQuickWrap');
    if (wrap && !wrap.contains(e.target)) rptCloseQuick();
});
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;

    // 1. Close quick-access panel if open
    var panel = document.getElementById('rptQuickPanel');
    if (panel && panel.style.display !== 'none') {
        rptCloseQuick();
        return;
    }

    // 2. If the name-search suggestion dropdown is visible, let its own handler close it
    var dd = document.getElementById('rptSearchDropdown');
    if (dd && dd.style.display === 'block') return;

    // 3. Clear all filters — navigate to clean reports URL
    var resetBtn = document.querySelector('.rpt-reset-btn');
    if (resetBtn) {
        resetBtn.classList.add('rpt-reset-flash');
        setTimeout(function () { window.location.href = resetBtn.href; }, 200);
    } else {
        window.location.href = "{{ route('reports.index') }}";
    }
});

// ── Full-report Print ──────────────────────────────────────
function rptPrintFull() {
    rptCloseQuick();
    window.onafterprint = function () { window.onafterprint = null; };
    window.print();
}

// ── Section-only Print ────────────────────────────────────
function rptPrintSection(sectionKey) {
    rptCloseQuick();

    var periodLabel = '{{ $periodLabel }}';

    var sectionTitles = {
        'kpi':                 'KPI Summary Report',
        'incident-analytics':  'Traffic Related Incidents — Analytics',
        'incidents':           'Incident Summary Report',
        'violations':          'Violation Data Report',
        'violators':           'Violator Recap Report',
        'overdue':             '72-Hour Overdue Violations Report',
        'offenders':           'Repeat Offenders Report'
    };

    var sectionNotes = {
        'kpi':                 'Violations, Incidents, Settled & Overdue counts — ' + periodLabel,
        'incident-analytics':  'Crime Clock, Offense, Crime Days & Case Status charts — ' + (window._incAnalyticsPeriodLabel || periodLabel),
        'incidents':           'Incident status breakdown and top locations — ' + periodLabel,
        'violations':          'Violation data by type — ' + periodLabel,
        'violators':           'Individual violator breakdown — ' + periodLabel,
        'overdue':             'Overdue violations filed more than 72 hours ago — as of {{ now()->format("F d, Y") }}',
        'offenders':           'Motorists with 2 or more violations on record — all time'
    };

    // Update print title
    var titleEl  = document.getElementById('printReportTitle');
    var noteEl   = document.getElementById('printSectionNote');
    var origTitle = titleEl ? titleEl.innerHTML : '';
    var origNote  = noteEl  ? noteEl.innerHTML  : '';

    if (titleEl) titleEl.textContent = sectionTitles[sectionKey] || sectionKey;
    if (noteEl)  { noteEl.textContent = sectionNotes[sectionKey] || ''; noteEl.style.display = ''; }

    // Set body attribute — CSS hides all other sections automatically
    document.body.setAttribute('data-print-section', sectionKey);

    // Restore everything cleanly after the print dialog is dismissed
    window.onafterprint = function () {
        document.body.removeAttribute('data-print-section');
        if (titleEl) titleEl.innerHTML = origTitle;
        if (noteEl)  { noteEl.innerHTML = origNote; noteEl.style.display = 'none'; }
        window.onafterprint = null;
    };

    window.print();
}

function rptToggleShowMore(btn) {
    const card = btn.closest('.rpt-card');
    const rows = card.querySelectorAll('.rpt-show-more-row');
    const hiddenCount = btn.dataset.hidden;
    const expanded = btn.dataset.expanded === '1';
    rows.forEach(r => r.style.display = expanded ? '' : 'table-row');
    btn.dataset.expanded = expanded ? '' : '1';
    btn.innerHTML = expanded
        ? 'Show ' + hiddenCount + ' more <i class="bi bi-chevron-down ms-1"></i>'
        : 'Show less <i class="bi bi-chevron-up ms-1"></i>';
}
</script>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    // ── State ──────────────────────────────────────────────────────────────────
    var _period    = 'month';
    var _weekStart = null; // YYYY-MM-DD (Sunday)
    var _pageYear  = {{ (int)$year }};
    var _pageMonth = {{ ($month > 0 ? (int)$month : 'new Date().getMonth()+1') }};

    // ── Chart instances ────────────────────────────────────────────────────────
    var _clockChart   = null;
    var _offenseChart = null;
    var _daysChart    = null;
    var _statusChart  = null;

    var TEAL        = 'rgba(13,148,136,0.85)';
    var TEAL_BORDER = 'rgba(13,148,136,1)';
    var TEAL_FILL   = 'rgba(13,148,136,0.15)';
    var CYAN        = 'rgba(6,182,212,0.85)';

    var HOUR_LABELS = [
        '12 AM','1 AM','2 AM','3 AM','4 AM','5 AM','6 AM','7 AM',
        '8 AM','9 AM','10 AM','11 AM','12 PM','1 PM','2 PM','3 PM',
        '4 PM','5 PM','6 PM','7 PM','8 PM','9 PM','10 PM','11 PM'
    ];
    var DAY_LABELS  = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    // ── Helpers ────────────────────────────────────────────────────────────────
    function formatWeekStart(dateStr) {
        // dateStr = YYYY-MM-DD (Sunday)
        var d = new Date(dateStr + 'T00:00:00');
        var end = new Date(d); end.setDate(end.getDate() + 6);
        var opts = { month: 'short', day: 'numeric' };
        return d.toLocaleDateString('en-US', opts) + '–' + end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function todaySundayStr() {
        var d = new Date();
        d.setDate(d.getDate() - d.getDay()); // go to Sunday
        return d.toISOString().slice(0, 10);
    }

    function addWeeks(dateStr, n) {
        var d = new Date(dateStr + 'T00:00:00');
        d.setDate(d.getDate() + n * 7);
        return d.toISOString().slice(0, 10);
    }

    // ── Chart factory helpers ──────────────────────────────────────────────────
    function makeLineChart(ctx, labels, data) {
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    borderColor: TEAL_BORDER,
                    backgroundColor: TEAL_FILL,
                    borderWidth: 2,
                    pointRadius: 2,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { font: { size: 9 }, maxRotation: 45, autoSkip: true, maxTicksLimit: 12 }, grid: { color: '#f1f5f9' } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } }, grid: { color: '#f1f5f9' } }
                }
            }
        });
    }

    function makeBarChart(ctx, labels, data, color) {
        color = color || TEAL;
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: color,
                    borderColor: 'transparent',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { font: { size: 9 }, maxRotation: 30, autoSkip: false }, grid: { color: '#f1f5f9' } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } }, grid: { color: '#f1f5f9' } }
                }
            }
        });
    }

    function makeHBarChart(ctx, labels, data) {
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: CYAN,
                    borderColor: 'transparent',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 9 } }, grid: { color: '#f1f5f9' } },
                    y: { ticks: { font: { size: 9 } }, grid: { color: '#f1f5f9' } }
                }
            }
        });
    }

    // ── Render / update ────────────────────────────────────────────────────────
    function renderCharts(data) {
        window._incAnalyticsPeriodLabel = data.label;
        document.getElementById('incAnalyticsPeriodLabel').textContent = data.label + ' — ' + data.total + ' incident' + (data.total !== 1 ? 's' : '');

        var noData  = document.getElementById('incAnalyticsNoData');
        var chartsEl = document.getElementById('incAnalyticsCharts');

        if (data.total === 0) {
            noData.style.display  = '';
            chartsEl.style.display = 'none';
            return;
        }
        noData.style.display  = 'none';
        chartsEl.style.display = '';

        // Offense labels (wrap long names)
        var offenseLabels = Object.keys(data.byChargeType);
        var offenseData   = Object.values(data.byChargeType);

        var statusLabels = ['Open', 'Under Review', 'Closed'];
        var statusData   = [data.byStatus.open || 0, data.byStatus.under_review || 0, data.byStatus.closed || 0];

        if (_clockChart)   { _clockChart.data.datasets[0].data   = data.byHour; _clockChart.update(); }
        else               { _clockChart   = makeLineChart(document.getElementById('incClockChart'),   HOUR_LABELS, data.byHour); }

        if (_offenseChart) { _offenseChart.data.labels = offenseLabels; _offenseChart.data.datasets[0].data = offenseData; _offenseChart.update(); }
        else               { _offenseChart = offenseLabels.length ? makeHBarChart(document.getElementById('incOffenseChart'), offenseLabels, offenseData) : makeBarChart(document.getElementById('incOffenseChart'), ['No data'], [0]); }

        if (_daysChart)    { _daysChart.data.datasets[0].data    = data.byDay;  _daysChart.update(); }
        else               { _daysChart    = makeBarChart(document.getElementById('incDaysChart'),   DAY_LABELS, data.byDay, TEAL); }

        if (_statusChart)  { _statusChart.data.datasets[0].data  = statusData;  _statusChart.update(); }
        else               { _statusChart  = makeBarChart(document.getElementById('incStatusChart'), statusLabels, statusData, CYAN); }
    }

    // ── Fetch ──────────────────────────────────────────────────────────────────
    function fetchStats() {
        var url = '{{ route("reports.incidentStats") }}?';
        if (_period === 'week') {
            url += 'period=week&date=' + encodeURIComponent(_weekStart);
        } else {
            url += 'period=month&year=' + _pageYear + '&month=' + _pageMonth;
        }
        document.getElementById('incAnalyticsPeriodLabel').textContent = 'Loading…';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { renderCharts(data); })
            .catch(function () { document.getElementById('incAnalyticsPeriodLabel').textContent = 'Error loading data.'; });
    }

    // ── Week nav UI ────────────────────────────────────────────────────────────
    function updateWeekNav() {
        var nav = document.getElementById('incWeekNav');
        if (_period === 'week') {
            nav.style.removeProperty('display');
            document.getElementById('incWeekLabel').textContent = formatWeekStart(_weekStart);
        } else {
            nav.style.setProperty('display', 'none', 'important');
        }
    }

    // ── Toggle buttons ─────────────────────────────────────────────────────────
    document.querySelectorAll('.inc-period-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            _period = this.dataset.period;
            if (_period === 'week' && !_weekStart) _weekStart = todaySundayStr();
            document.querySelectorAll('.inc-period-btn').forEach(function (b) { b.classList.remove('inc-period-active'); });
            this.classList.add('inc-period-active');
            updateWeekNav();
            fetchStats();
        });
    });

    document.getElementById('incPrevWeek').addEventListener('click', function () {
        _weekStart = addWeeks(_weekStart, -1);
        document.getElementById('incWeekLabel').textContent = formatWeekStart(_weekStart);
        fetchStats();
    });

    document.getElementById('incNextWeek').addEventListener('click', function () {
        _weekStart = addWeeks(_weekStart, 1);
        document.getElementById('incWeekLabel').textContent = formatWeekStart(_weekStart);
        fetchStats();
    });

    // ── Init ───────────────────────────────────────────────────────────────────
    fetchStats();
})();
</script>

@endsection
