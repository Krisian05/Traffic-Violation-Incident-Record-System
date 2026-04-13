@extends('layouts.app')
@section('title', 'Violations')

@section('topbar-sub')
    <i class="bi bi-shield-exclamation me-1" style="color:#dc2626;"></i>
    {{ $violations->total() }} total {{ Str::plural('record', $violations->total()) }}
    @php
        $activeFilters = [];
        if (request('search'))  $activeFilters[] = ['label' => 'Name',   'value' => request('search')];
        if (request('plate'))   $activeFilters[] = ['label' => 'Plate',  'value' => request('plate')];
        if (request('type'))    $activeFilters[] = ['label' => 'Type',   'value' => ucfirst(request('type'))];
        if (request('status'))  $activeFilters[] = ['label' => 'Status', 'value' => ucfirst(request('status'))];
        if (request('month'))   $activeFilters[] = ['label' => 'Month',  'value' => \Carbon\Carbon::create(null, (int) request('month'))->format('F')];
        if (request('year'))    $activeFilters[] = ['label' => 'Year',   'value' => request('year')];
    @endphp
    @foreach($activeFilters as $f)
        &nbsp;·&nbsp; <span style="display:inline-flex;align-items:center;gap:3px;background:#fef9ec;color:#92400e;border:1px solid #fcd34d;border-radius:999px;padding:1px 8px;font-size:.78rem;font-weight:500;">
            <span style="color:#b45309;">{{ $f['label'] }}:</span> {{ $f['value'] }}
        </span>
    @endforeach
@endsection

@section('content')

{{-- ── Filter Card ── --}}
<div class="filter-card mb-4">
    <div class="filter-card-header">
        <div class="d-flex align-items-center gap-2">
            <span class="filter-icon-wrap">
                <i class="bi bi-sliders2-vertical"></i>
            </span>
            <div>
                <div class="fw-700" style="font-size:.88rem;color:#1c1917;">Search &amp; Filter</div>
                <div style="font-size:.72rem;color:#a8a29e;">Narrow down violation records</div>
            </div>
        </div>
        @if(request()->hasAny(['search','plate','type','status','month','year']))
            <a href="{{ route('violations.index') }}" class="filter-clear-btn ms-auto">
                <i class="bi bi-x-lg"></i> Clear filters
            </a>
        @endif
    </div>
    <div class="filter-card-body">
        <form method="GET" action="{{ route('violations.index') }}" id="vio-filter-form">
            <div class="d-flex flex-wrap align-items-end gap-2">

                <div style="flex:2.2;min-width:0;">
                    <label class="filter-label"><i class="bi bi-person-vcard me-1"></i>Violator Name</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text filt-icon"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control filt-input"
                            placeholder="e.g. Juan Dela Cruz"
                            value="{{ request('search') }}">
                    </div>
                </div>

                <div style="flex:1.4;min-width:0;">
                    <label class="filter-label"><i class="bi bi-car-front me-1"></i>Plate No.</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text filt-icon"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" name="plate" class="form-control filt-input"
                            placeholder="ABC 1234"
                            value="{{ request('plate') }}">
                    </div>
                </div>

                <div style="flex:1.8;min-width:0;">
                    <label class="filter-label"><i class="bi bi-tag me-1"></i>Violation Type</label>
                    <select name="type" class="form-select form-select-sm filt-input">
                        <option value="">All Types</option>
                        @foreach($violationTypes as $t)
                            <option value="{{ $t->id }}" {{ request('type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:1.2;min-width:0;">
                    <label class="filter-label"><i class="bi bi-activity me-1"></i>Status</label>
                    <select name="status" class="form-select form-select-sm filt-input">
                        <option value="">All Statuses</option>
                        @foreach(['pending','overdue','settled'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:1.5;min-width:0;">
                    <label class="filter-label"><i class="bi bi-calendar-month me-1"></i>Month</label>
                    <select name="month" class="form-select form-select-sm filt-input">
                        <option value="">Any Month</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m)) }}</option>
                        @endfor
                    </select>
                </div>

                <div style="flex:.9;min-width:0;">
                    <label class="filter-label"><i class="bi bi-calendar-year me-1"></i>Year</label>
                    <input type="number" name="year" class="form-control form-control-sm filt-input"
                        placeholder="{{ date('Y') }}"
                        value="{{ request('year') }}"
                        min="2000" max="{{ date('Y') }}">
                </div>

                <div style="flex-shrink:0;display:flex;gap:6px;">
                    <button type="submit" class="btn-filter-submit">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <button type="button" class="vio-print-btn no-print" onclick="window.print()">
                        <i class="bi bi-printer-fill"></i> Print
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── PRINT HEADER (hidden on screen, visible when printing) ── --}}
<div class="gov-print-hdr">
    <img src="{{ asset('images/PNP.png') }}" class="gov-ph-seal gov-ph-seal-pnp" alt="PNP Logo">
    <div class="gov-ph-agency">
        <div class="gov-ph-republic">Republic of the Philippines</div>
        <div class="gov-ph-npc">NATIONAL POLICE COMMISSION</div>
        <div class="gov-ph-pro7">PHILIPPINE NATIONAL POLICE, POLICE REGIONAL OFFICE 7</div>
        <div class="gov-ph-cebu">CEBU POLICE PROVINCIAL OFFICE</div>
        <div class="gov-ph-station">BALAMBAN MUNICIPAL POLICE STATION</div>
        <div class="gov-ph-address">Brgy. Sta Cruz-Sto Nino, Balamban, Cebu</div>
    </div>
    <img src="{{ asset('images/Balamban.png') }}" class="gov-ph-seal" alt="Balamban Seal">
</div>
<div class="gov-ph-title">Violation Records</div>

{{-- ── Table Card ── --}}
<div class="vio-table-card">

    <div class="table-responsive">
        <table class="table align-middle mb-0" id="violations-table">
            <thead>
                <tr>
                    <th style="padding-left:1.75rem;text-align:left;">
                        <span class="th-inner"><i class="bi bi-person-fill me-1"></i>Violator</span>
                    </th>
                    <th class="text-center">
                        <span class="th-inner"><i class="bi bi-tag-fill me-1"></i>Violation Type</span>
                    </th>
                    <th class="text-center d-none d-md-table-cell">
                        <span class="th-inner"><i class="bi bi-calendar-event-fill me-1"></i>Date Apprehended</span>
                    </th>
                    <th class="text-center d-none d-md-table-cell">
                        <span class="th-inner"><i class="bi bi-car-front-fill me-1"></i>Plate No.</span>
                    </th>
                    <th class="text-center print-only">
                        <span class="th-inner">Penalty Amount</span>
                    </th>
                    <th class="text-center">
                        <span class="th-inner"><i class="bi bi-activity me-1"></i>Status</span>
                    </th>
                    <th class="text-center vio-act-cell no-print">
                        <span class="th-inner"><i class="bi bi-lightning-charge-fill me-1"></i>Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($violations as $v)
                <tr class="vio-row" data-href="{{ route('violations.show', $v) }}">
                    {{-- Violator --}}
                    <td style="padding-left:1.75rem;">
                        @if($v->violator)
                        <a href="{{ route('violators.show', $v->violator) }}"
                           class="vio-name fw-600 text-decoration-none"
                           title="View motorist profile">
                            {{ $v->violator->full_name }}
                        </a>
                        <div style="font-size:.69rem;color:#a8a29e;margin-top:2px;">
                            <i class="bi bi-eye me-1" style="font-size:.6rem;"></i>Click row to view violation
                        </div>
                        @else
                        <span class="vio-name fw-600" style="color:#a8a29e;">(Deleted Motorist)</span>
                        @endif
                    </td>

                    {{-- Violation Type --}}
                    <td class="text-center">
                        <span class="vtype-pill">
                            <i class="bi bi-exclamation-octagon-fill me-1" style="font-size:.65rem;"></i>
                            {{ $v->violationType?->name ?? '—' }}
                        </span>
                    </td>

                    {{-- Date --}}
                    <td class="text-center d-none d-md-table-cell">
                        <span class="date-chip">
                            <i class="bi bi-calendar-check me-1" style="color:#a8a29e;"></i>
                            {{ $v->date_of_violation->format('M d, Y') }}
                        </span>
                    </td>

                    {{-- Plate --}}
                    <td class="text-center d-none d-md-table-cell">
                        @if($v->vehicle?->plate_number)
                            <span class="plate-pill">
                                <i class="bi bi-upc me-1" style="font-size:.7rem;color:#a8a29e;"></i>
                                {{ $v->vehicle->plate_number }}
                            </span>
                        @else
                            <span class="no-data">—</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="text-center">
                        @php
                            $isOverdue = $v->isOverdue();
                            $displayStatus = $isOverdue ? 'overdue' : $v->status;
                            $statusConf = match($displayStatus) {
                                'overdue'   => ['cls'=>'status-overdue',   'icon'=>'bi-exclamation-triangle-fill'],
                                'pending'   => ['cls'=>'status-pending',   'icon'=>'bi-hourglass-split'],
                                'settled'   => ['cls'=>'status-settled',   'icon'=>'bi-check2-circle'],
                                'contested' => ['cls'=>'status-contested', 'icon'=>'bi-shield-slash'],
                                default     => ['cls'=>'status-default',   'icon'=>'bi-circle'],
                            };
                        @endphp
                        <span class="status-badge {{ $statusConf['cls'] }}"
                            @if($displayStatus === 'overdue') data-bs-toggle="tooltip" data-bs-title="Pending payment for more than 72 hours" @endif>
                            <i class="bi {{ $statusConf['icon'] }}"></i>
                            {{ ucfirst($displayStatus) }}
                        </span>
                    </td>

                    {{-- Penalty Amount (print only) --}}
                    <td class="text-center print-only">
                        @if($v->violationType?->fine_amount)
                            <span style="font-weight:700;color:#b91c1c;">₱{{ number_format($v->violationType->fine_amount, 2) }}</span>
                        @else
                            <span style="color:#a8a29e;">—</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="text-center vio-act-cell no-print">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('violations.show', $v) }}"
                               class="act-btn act-view" title="View Details">
                                <i class="bi bi-eye-fill"></i>
                                <span>View</span>
                            </a>
                            @if(Auth::user()->isOperator())
                            <a href="{{ route('violations.edit', $v) }}"
                               class="act-btn act-edit"
                               title="Edit Record"
                               aria-label="Edit violation for {{ $v->violator?->full_name ?? 'motorist' }}">
                                <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                            </a>
                            @if($v->status === 'pending')
                            <button type="button" class="act-btn act-settle" title="Settle Violation"
                                data-id="{{ $v->id }}"
                                data-type="{{ $v->violationType?->name ?? '' }}"
                                data-date="{{ $v->date_of_violation->format('M d, Y') }}"
                                onclick="openSettleModal(this)">
                                <i class="bi bi-receipt"></i>
                            </button>
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-5">
                        <div class="text-center">
                            <div class="empty-icon-wrap mx-auto mb-3">
                                <i class="bi bi-clipboard-x"></i>
                            </div>
                            <p class="fw-600 mb-1" style="color:#57534e;font-size:.95rem;">No violations found</p>
                            <p class="mb-0" style="font-size:.83rem;color:#a8a29e;">
                                @if(request()->hasAny(['search','plate','type','status','month','year']))
                                    No records match your filters.
                                    <a href="{{ route('violations.index') }}" style="color:#dc2626;">Clear all filters</a>
                                @else
                                    No violation records have been recorded yet.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    @if($violations->hasPages())
    <div class="vio-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="vio-footer-count">
            Showing <strong>{{ $violations->firstItem() }}</strong>–<strong>{{ $violations->lastItem() }}</strong>
            of <strong>{{ $violations->total() }}</strong> records
        </div>
        <nav>
            <ul class="vio-pager">
                {{-- Prev --}}
                <li>
                    @if($violations->onFirstPage())
                        <span class="vio-page vio-page-disabled"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a class="vio-page" href="{{ $violations->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                    @endif
                </li>

                {{-- Page numbers --}}
                @php
                    $cur  = $violations->currentPage();
                    $last = $violations->lastPage();
                    $pages = collect();
                    // Always show first, last, current ±2, with … gaps
                    for ($p = 1; $p <= $last; $p++) {
                        if ($p === 1 || $p === $last || abs($p - $cur) <= 2) {
                            $pages->push($p);
                        }
                    }
                    $pages = $pages->unique()->sort()->values();
                @endphp

                @foreach($pages as $i => $p)
                    @if($i > 0 && $p - $pages[$i - 1] > 1)
                        <li><span class="vio-page vio-page-ellipsis">…</span></li>
                    @endif
                    <li>
                        @if($p === $cur)
                            <span class="vio-page vio-page-active">{{ $p }}</span>
                        @else
                            <a class="vio-page" href="{{ $violations->url($p) }}">{{ $p }}</a>
                        @endif
                    </li>
                @endforeach

                {{-- Next --}}
                <li>
                    @if($violations->hasMorePages())
                        <a class="vio-page" href="{{ $violations->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="vio-page vio-page-disabled"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
    @else
        <div class="vio-footer text-end">
            <span class="vio-footer-count">{{ $violations->total() }} {{ Str::plural('record', $violations->total()) }} total</span>
        </div>
    @endif
</div>

{{-- ── PRINT SIGNATURE FOOTER (hidden on screen) ── --}}
<div class="print-sig-footer">
    <div class="print-sig-block">
        <div class="print-sig-label">Prepared by:</div>
        <div class="print-sig-line"></div>
        <div class="print-sig-name">{{ Auth::user()->name ?? 'N/A' }}</div>
        <div class="print-sig-title">Operation PNCO</div>
    </div>
    <div class="print-sig-block">
        <div class="print-sig-label">Noted by:</div>
        <div class="print-sig-line"></div>
        <div class="print-sig-name">PLTCOL RUEL L BURLAT</div>
        <div class="print-sig-title">Chief of Police</div>
    </div>
</div>
<div class="print-doc-footer" style="display:none;">
    Generated: {{ now()->format('Y-m-d H:i:s') }} &nbsp;|&nbsp; {{ $violations->total() }} {{ Str::plural('record', $violations->total()) }} total<br>
    This document is confidential and for official use only.
</div>

<style>
/* ─────────────── FILTER CARD ─────────────── */
.filter-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden;
    border: 1px solid #f0ebe3;
}
.filter-card-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .9rem 1.25rem;
    background: linear-gradient(135deg, #fdf8f0 0%, #fff 100%);
    border-bottom: 1px solid #f0ebe3;
}
.filter-icon-wrap {
    width: 34px; height: 34px;
    border-radius: 9px;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: .9rem;
    box-shadow: 0 3px 10px rgba(220,38,38,.3);
    flex-shrink: 0;
}
.filter-clear-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .8rem;
    border-radius: 20px;
    font-size: .75rem; font-weight: 600;
    color: #78716c;
    background: #f5f0e8;
    border: 1px solid #e7e0d6;
    text-decoration: none;
    transition: all .15s;
}
.filter-clear-btn:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
.filter-card-body { padding: 1rem 1.25rem; }

.filter-label {
    display: block;
    font-size: .7rem; font-weight: 700;
    color: #78716c;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: .3rem;
}
.filt-icon {
    background: #fdf8f0;
    border-color: #e7e0d6;
    color: #a8a29e;
    font-size: .8rem;
}
.filt-input {
    border-color: #e7e0d6 !important;
    font-size: .82rem !important;
    transition: border-color .15s, box-shadow .15s;
}
.filt-input:focus {
    border-color: #dc2626 !important;
    box-shadow: 0 0 0 3px rgba(220,38,38,.1) !important;
}
.btn-filter-submit {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .42rem 1.1rem;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .8rem; font-weight: 600;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(220,38,38,.3);
    transition: transform .15s, box-shadow .15s;
    white-space: nowrap;
}
.btn-filter-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(220,38,38,.4);
}

/* ─────────────── TABLE CARD ─────────────── */
.vio-table-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    border: 1px solid #f0ebe3;
    overflow: hidden;
}

/* ── Table header ── */
#violations-table thead tr {
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
}
#violations-table thead th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #78716c;
    border-bottom: 2px solid #ece5da;
    padding-top: .95rem;
    padding-bottom: .95rem;
}
.th-inner {
    display: inline-flex;
    align-items: center;
}

/* ── Row ── */
.vio-row {
    transition: background .18s, box-shadow .18s;
    position: relative;
}
.vio-row[data-href] { cursor: pointer; }
.vio-row:hover {
    background: #fff5f5 !important;
}
.vio-row[data-href]:hover td:not(.vio-act-cell) {
    position: relative;
}
.vio-row[data-href]:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: #dc2626;
    border-radius: 0 2px 2px 0;
}
.vio-row td {
    padding-top: .9rem;
    padding-bottom: .9rem;
    border-color: #f5f0ea;
    vertical-align: middle;
}

/* ── Violator name ── */
.vio-name {
    color: #1c1917;
    font-size: .88rem;
    font-weight: 600;
    transition: color .15s;
}
.vio-name:hover { color: #dc2626; }

/* ── Violation type pill ── */
.vtype-pill {
    display: inline-flex;
    align-items: center;
    background: #fff1f2;
    color: #be123c;
    font-size: .71rem;
    font-weight: 700;
    padding: .28rem .7rem;
    border-radius: 20px;
    border: 1px solid #fecdd3;
    box-shadow: 0 1px 4px rgba(190,18,60,.12);
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Date chip ── */
.date-chip {
    display: inline-flex;
    align-items: center;
    font-size: .82rem;
    color: #57534e;
}

/* ── Plate pill ── */
.plate-pill {
    display: inline-flex;
    align-items: center;
    background: #f5f0e8;
    color: #57534e;
    font-size: .74rem;
    font-weight: 700;
    padding: .25rem .65rem;
    border-radius: 6px;
    border: 1px solid #ddd0be;
    font-family: ui-monospace, 'Cascadia Code', monospace;
    letter-spacing: .04em;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}

.no-data { color: #c4b8a8; font-weight: 600; }

/* ── Status badge ── */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .8rem;
    border-radius: 20px;
    border: 1.5px solid;
    font-size: .71rem;
    font-weight: 700;
    letter-spacing: .04em;
    transition: transform .15s;
}
.status-badge:hover { transform: scale(1.04); }
.status-overdue   { background:#fef2f2;color:#b91c1c;border-color:#fca5a5;box-shadow:0 2px 8px rgba(185,28,28,.15); }
.status-pending   { background:#fff8e6;color:#b45309;border-color:#fde68a;box-shadow:0 2px 8px rgba(180,83,9,.15); }
.status-settled   { background:#f0fdf4;color:#15803d;border-color:#86efac;box-shadow:0 2px 8px rgba(21,128,61,.15); }
.status-contested { background:#f8fafc;color:#475569;border-color:#cbd5e1;box-shadow:0 2px 8px rgba(71,85,105,.12); }
.status-default   { background:#f8fafc;color:#475569;border-color:#e2e8f0; }

/* ── Action buttons ── */
.act-btn {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .32rem .7rem;
    border-radius: 8px;
    font-size: .76rem;
    font-weight: 700;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: all .18s;
    white-space: nowrap;
}
.act-view {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
    box-shadow: 0 1px 4px rgba(29,78,216,.1);
}
.act-view:hover {
    background: #1d4ed8;
    color: #fff;
    border-color: #1d4ed8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(29,78,216,.3);
}
.act-edit {
    background: #fdf8f0;
    color: #b45309;
    border-color: #fde68a;
    box-shadow: 0 1px 4px rgba(180,83,9,.1);
}
.act-edit:hover {
    background: #d97706;
    color: #fff;
    border-color: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(217,119,6,.3);
}

.act-settle {
    background: #f0fdf4;
    color: #15803d;
    border-color: #86efac;
    box-shadow: 0 1px 4px rgba(21,128,61,.1);
}
.act-settle:hover {
    background: #15803d;
    color: #fff;
    border-color: #15803d;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(21,128,61,.3);
}

/* ── Empty state ── */
.empty-icon-wrap {
    width: 64px; height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #fff1f2, #fce7f3);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    color: #fca5a5;
    box-shadow: 0 4px 16px rgba(220,38,38,.1);
}

/* ── Footer ── */
.vio-footer {
    padding: .85rem 1.5rem;
    border-top: 1px solid #f0ebe3;
    background: #fdf8f0;
}
.vio-footer-count {
    font-size: .8rem;
    color: #78716c;
}

.fw-600 { font-weight: 600; }
.fw-700 { font-weight: 700; }

/* ── Pagination ── */
.vio-pager {
    display: flex; align-items: center; gap: .25rem;
    list-style: none; margin: 0; padding: 0;
}
.vio-page {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 .55rem;
    border-radius: 8px;
    font-size: .78rem; font-weight: 600;
    border: 1.5px solid #e7dfd5;
    color: #57534e;
    background: #fff;
    text-decoration: none;
    transition: all .15s;
    cursor: pointer;
}
a.vio-page:hover {
    background: #fdf8f0;
    border-color: #dc2626;
    color: #dc2626;
}
.vio-page-active {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    border-color: #dc2626;
    color: #fff;
    box-shadow: 0 2px 8px rgba(220,38,38,.3);
    cursor: default;
}
.vio-page-disabled {
    color: #d6d3d1;
    border-color: #f0ebe3;
    background: #fafaf9;
    cursor: default;
}
.vio-page-ellipsis {
    border-color: transparent;
    background: transparent;
    color: #a8a29e;
    cursor: default;
    font-size: .85rem;
}

/* ─── Print-only columns ─── */
.print-only { display: none; }

/* ─── Print signature footer ─── */
.print-sig-footer { display: none; }

/* ─── Print button ─── */
.vio-print-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .42rem 1rem;
    background: #fff; color: #44403c;
    border: 1.5px solid #d6d3d1;
    border-radius: 8px;
    font-size: .8rem; font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: border-color .15s;
}
.vio-print-btn:hover { border-color: #a8a29e; }

/* ─── Print Header (screen: hidden) ─── */
.gov-print-hdr, .gov-ph-title { display: none; }

/* ─── Print layout ─── */
@media print {
    .no-print,
    .filter-card,
    .vio-pagination,
    nav, header, footer,
    .vio-act-cell { display: none !important; }

    body, .vio-table-card {
        background: #fff !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    .vio-row { page-break-inside: avoid; }
    .vio-table-card { border: 1px solid #ccc !important; }

    .gov-print-hdr {
        display: flex !important;
        align-items: center; gap: 14px;
        border-bottom: 3px double #b91c1c;
        padding-bottom: 8px; margin-bottom: 4px;
        page-break-inside: avoid;
    }
    .gov-ph-seal { width: 100px; height: 100px; flex-shrink: 0; object-fit: contain; }
    .gov-ph-seal-pnp { object-fit: cover; }
    .gov-ph-agency { flex: 1; text-align: center; line-height: 1.35; }
    .gov-ph-republic { font-size: 8pt; color: #111; }
    .gov-ph-npc      { font-size: 8pt; color: #111; }
    .gov-ph-pro7     { font-size: 9pt; font-weight: 600; color: #111; }
    .gov-ph-cebu     { font-size: 10.5pt; font-weight: 800; color: #111; text-transform: uppercase; letter-spacing: .03em; }
    .gov-ph-station  { font-size: 12pt; font-weight: 900; color: #111; text-transform: uppercase; letter-spacing: .03em; }
    .gov-ph-address  { font-size: 7.5pt; color: #111; margin-top: 1px; }
    .gov-ph-title {
        display: block !important;
        text-align: center; font-size: 9pt; font-weight: 900;
        text-transform: uppercase; letter-spacing: .1em; color: #b91c1c;
        border-bottom: 2px solid #b91c1c; padding: 3px 0 5px; margin-bottom: 10px;
    }

    .print-only { display: table-cell !important; }
    .d-none.d-md-table-cell { display: table-cell !important; }

    .print-sig-footer {
        display: flex !important;
        justify-content: space-between;
        margin-top: 40pt;
        margin-bottom: 10pt;
        padding: 0 4px;
    }
    .print-sig-block { text-align: center; }
    .print-sig-label { font-size: 9pt; text-align: left; margin-bottom: 26pt; }
    .print-sig-line  { border-bottom: 1.5pt solid #1c1917; width: 160pt; margin: 0 auto; }
    .print-sig-name  { font-size: 10pt; font-weight: 700; margin-top: 3pt; }
    .print-sig-title { font-size: 8.5pt; font-style: italic; color: #57534e; margin-top: 1pt; }

    .print-doc-footer {
        display: block !important;
        border-top: 1px solid #d6d3d1;
        padding-top: 6pt;
        margin-top: 10pt;
        font-size: 7.5pt;
        color: #a8a29e;
    }

    @page { size: A4 portrait; margin: 8mm 10mm 12mm; }
}
</style>

{{-- ── Settle Modal ── --}}
<div class="modal fade" id="settleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#15803d,#166534);padding:1rem 1.25rem;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-white" style="font-size:1.1rem;"></i>
                    <h6 class="modal-title text-white fw-bold mb-0">Settle Violation</h6>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div style="background:#f0fdf4;padding:.65rem 1.25rem;border-bottom:1px solid #bbf7d0;font-size:.8rem;">
                <span class="fw-bold" style="color:#15803d;" id="settleViolationType">—</span>
                <span style="color:#a8a29e;margin:0 .4rem;">·</span>
                <span style="color:#57534e;" id="settleViolationDate">—</span>
            </div>
            <form id="settleForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="modal-body" style="padding:1.25rem;">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.82rem;">OR Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash" style="color:#15803d;font-size:.8rem;"></i></span>
                            <input type="text" name="or_number" class="form-control" placeholder="e.g. 1234567"
                                style="font-family:ui-monospace,monospace;" required maxlength="50">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:.82rem;">Cashier Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge-fill" style="color:#15803d;font-size:.8rem;"></i></span>
                            <input type="text" name="cashier_name" class="form-control" placeholder="Full name of cashier" required maxlength="150">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold" style="font-size:.82rem;">Receipt Photo <span style="color:#a8a29e;font-weight:400;">(optional)</span></label>
                        <input type="file" name="receipt_photo" id="idx_receipt_photo" class="form-control" accept="image/jpg,image/jpeg,image/png">
                        <small style="font-size:.72rem;color:#a8a29e;">JPG/PNG, max 5 MB.</small>
                        <div id="idxReceiptPreviewWrap" class="d-none mt-2 text-center">
                            <img id="idxReceiptPreview" src="" alt="Receipt"
                                style="max-width:100%;max-height:180px;border-radius:8px;border:1px solid #bbf7d0;object-fit:contain;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:.75rem 1.25rem;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size:.82rem;">Cancel</button>
                    <button type="submit" class="btn" style="background:linear-gradient(135deg,#15803d,#166534);color:#fff;font-size:.82rem;font-weight:700;padding:.45rem 1.2rem;border-radius:8px;">
                        <i class="bi bi-check2-circle me-1"></i> Mark as Settled
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openSettleModal(btn) {
    document.getElementById('settleForm').action = '/violations/' + btn.dataset.id + '/settle';
    document.getElementById('settleViolationType').textContent = btn.dataset.type;
    document.getElementById('settleViolationDate').textContent = btn.dataset.date;
    document.getElementById('settleForm').reset();
    document.getElementById('idxReceiptPreview').src = '';
    document.getElementById('idxReceiptPreviewWrap').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('settleModal')).show();
}
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('idx_receipt_photo').addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('idxReceiptPreview').src = e.target.result;
            document.getElementById('idxReceiptPreviewWrap').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush

<script>
(function () {
    const form = document.getElementById('vio-filter-form');
    if (!form) return;

    // Debounce helper
    function debounce(fn, delay) {
        let t;
        return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), delay); };
    }

    const submit = () => { form.submit(); };

    // Text inputs — debounced 500ms
    form.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => {
        input.addEventListener('input', debounce(submit, 500));
    });

    // Selects — immediate on change
    form.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', submit);
    });
})();
</script>

<script>
/* ── Clickable rows — navigate to violation detail on row click ── */
document.querySelectorAll('.vio-row[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        // Ignore clicks on: action buttons, existing links, form elements
        if (e.target.closest('.vio-act-cell')) return;
        if (e.target.closest('a'))             return;
        if (e.target.closest('button'))        return;
        if (e.target.closest('form'))          return;
        window.location.href = row.dataset.href;
    });
});
</script>

@endsection
