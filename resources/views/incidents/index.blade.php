@extends('layouts.app')
@section('title', 'Incidents')

@push('styles')
<style>
.inc-status-open         { display:inline-block;padding:.1rem .5rem;border-radius:20px;font-size:.66rem;font-weight:700;background:#eff6ff;color:#1d4ed8; }
.inc-status-under_review { display:inline-block;padding:.1rem .5rem;border-radius:20px;font-size:.66rem;font-weight:700;background:#fffbeb;color:#92400e; }
.inc-status-closed       { display:inline-block;padding:.1rem .5rem;border-radius:20px;font-size:.66rem;font-weight:700;background:#f0fdf4;color:#15803d; }
.inc-status-default      { display:inline-block;padding:.1rem .5rem;border-radius:20px;font-size:.66rem;font-weight:700;background:#f3f4f6;color:#374151; }
</style>
@endpush

@section('topbar-sub')
    <i class="bi bi-flag-fill me-1" style="color:#dc2626;"></i>
    {{ $incidents->total() }} total {{ Str::plural('record', $incidents->total()) }}
    @php
        $activeFilters = [];
        if ($search)   $activeFilters[] = ['label' => 'Search',   'value' => $search];
        if ($dateFrom) $activeFilters[] = ['label' => 'From',     'value' => $dateFrom];
        if ($dateTo)   $activeFilters[] = ['label' => 'To',       'value' => $dateTo];
        if ($status) {
            $statusDisplay = match((string)$status) {
                'open'         => 'Open',
                'under_review' => 'Under Review',
                'closed'       => 'Closed',
                default        => (string)$status,
            };
            $activeFilters[] = ['label' => 'Status', 'value' => $statusDisplay];
        }
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
                <div style="font-size:.72rem;color:#a8a29e;">Narrow down incident records</div>
            </div>
        </div>
        @if($search || $dateFrom || $dateTo || $status)
            <a href="{{ route('incidents.index') }}" class="filter-clear-btn ms-auto">
                <i class="bi bi-x-lg"></i> Clear filters
            </a>
        @endif
    </div>
    <div class="filter-card-body">
        <form method="GET" action="{{ route('incidents.index') }}" id="inc-filter-form">
            <div class="d-flex flex-wrap align-items-end gap-2">

                <div style="flex:2.5;min-width:0;">
                    <label class="filter-label"><i class="bi bi-search me-1"></i>Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text filt-icon"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control filt-input"
                            placeholder="Location, incident no., or motorist name"
                            value="{{ $search }}">
                    </div>
                </div>

                <div style="flex:1.2;min-width:0;">
                    <label class="filter-label"><i class="bi bi-calendar me-1"></i>Date From</label>
                    <input type="text" name="date_from" class="form-control form-control-sm filt-input flatpickr-date"
                        placeholder="YYYY-MM-DD" value="{{ $dateFrom }}">
                </div>

                <div style="flex:1.2;min-width:0;">
                    <label class="filter-label"><i class="bi bi-calendar me-1"></i>Date To</label>
                    <input type="text" name="date_to" class="form-control form-control-sm filt-input flatpickr-date"
                        placeholder="YYYY-MM-DD" value="{{ $dateTo }}">
                </div>

                <div style="flex:1;min-width:0;">
                    <label class="filter-label"><i class="bi bi-circle-fill me-1"></i>Status</label>
                    <select name="status" class="form-select form-select-sm filt-input" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        <option value="open"         {{ $status === 'open'         ? 'selected' : '' }}>Open</option>
                        <option value="under_review" {{ $status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="closed"       {{ $status === 'closed'       ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div style="flex-shrink:0;display:flex;gap:6px;">
                    <button type="submit" class="btn-filter-submit">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <button type="button" class="inc-print-btn no-print" onclick="window.print()">
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
<div class="gov-ph-title">Incident Records</div>

{{-- ── Table Card ── --}}
<div class="inc-table-card">

    {{-- Card header with Record Incident button --}}
    <div class="inc-table-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-flag-fill" style="color:#dc2626;font-size:.95rem;"></i>
            <span class="fw-700" style="font-size:.9rem;color:#1c1917;">Incident Records</span>
        </div>
        @if(Auth::user()->isOperator())
        <a href="{{ route('incidents.create') }}" class="inc-record-btn">
            <i class="bi bi-plus-lg"></i> Record Incident
        </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" id="incidents-table">
            <thead>
                <tr>
                    <th style="padding-left:1.75rem;text-align:left;">
                        <span class="th-inner"><i class="bi bi-calendar-event-fill me-1"></i>Date</span>
                    </th>
                    <th>
                        <span class="th-inner"><i class="bi bi-geo-alt-fill me-1"></i>Location</span>
                    </th>
                    <th class="text-center">
                        <span class="th-inner"><i class="bi bi-people-fill me-1"></i>Motorists</span>
                    </th>
                    <th class="text-center d-none d-sm-table-cell">
                        <span class="th-inner"><i class="bi bi-images me-1"></i>Media</span>
                    </th>
                    <th class="d-none d-md-table-cell">
                        <span class="th-inner"><i class="bi bi-person-fill me-1"></i>Recorded By</span>
                    </th>
                    <th class="text-center inc-act-cell no-print">
                        <span class="th-inner"><i class="bi bi-lightning-charge-fill me-1"></i>Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidents as $incident)
                <tr class="inc-row" data-href="{{ route('incidents.show', $incident) }}">
                    {{-- Date + Status --}}
                    <td style="padding-left:1.75rem;">
                        <span class="date-chip">
                            <i class="bi bi-calendar-check me-1" style="color:#a8a29e;"></i>
                            {{ $incident->date_of_incident->format('M d, Y') }}
                        </span>
                        @if($incident->time_of_incident)
                            <div style="font-size:.73rem;color:#a8a29e;margin-top:.15rem;padding-left:.05rem;">
                                <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($incident->time_of_incident)->format('g:i A') }}
                            </div>
                        @endif
                        @php
                            $statusLabels = ['open' => 'Open', 'under_review' => 'Under Review', 'closed' => 'Closed'];
                            $statusClass  = in_array($incident->status, ['open','under_review','closed'])
                                ? 'inc-status-' . $incident->status
                                : 'inc-status-default';
                        @endphp
                        <div style="margin-top:.25rem;display:flex;align-items:center;gap:.4rem;">
                            <span class="{{ $statusClass }}">{{ $statusLabels[$incident->status] ?? $incident->status }}</span>
                            <span style="font-size:.64rem;color:#a8a29e;">
                                <i class="bi bi-eye" style="font-size:.6rem;"></i> Click row to view
                            </span>
                        </div>
                    </td>

                    {{-- Location --}}
                    <td>
                        <span class="inc-location" title="{{ $incident->location }}">
                            {{ $incident->location }}
                        </span>
                    </td>

                    {{-- Motorists count --}}
                    <td class="text-center">
                        <span class="count-pill count-pill-blue">
                            <i class="bi bi-people-fill me-1"></i>{{ $incident->motorists_count }}
                        </span>
                    </td>

                    {{-- Media count --}}
                    <td class="text-center d-none d-sm-table-cell">
                        <span class="count-pill count-pill-purple">
                            <i class="bi bi-images me-1"></i>{{ $incident->media_count }}
                        </span>
                    </td>

                    {{-- Recorded By --}}
                    <td class="d-none d-md-table-cell">
                        <span class="date-chip">{{ $incident->recorder->name ?? '—' }}</span>
                    </td>

                    {{-- Actions (excluded from row-click) --}}
                    <td class="text-center inc-act-cell no-print">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('incidents.show', $incident) }}"
                               class="act-btn act-view" title="View Details">
                                <i class="bi bi-eye-fill"></i>
                                <span>View</span>
                            </a>
                            @if(Auth::user()->isOperator())
                            <a href="{{ route('incidents.edit', $incident) }}"
                               class="act-btn act-edit"
                               title="Edit Record"
                               aria-label="Edit incident {{ $incident->incident_number }}">
                                <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                            </a>
                            <form method="POST" action="{{ route('incidents.destroy', $incident) }}"
                                  data-confirm="Delete incident {{ $incident->incident_number }}? All motorists, media, and related data will be permanently removed."
                                  class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="act-btn act-delete"
                                        title="Delete Incident"
                                        aria-label="Delete incident {{ $incident->incident_number }}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-5">
                        <div class="text-center">
                            <div class="empty-icon-wrap mx-auto mb-3">
                                <i class="bi bi-flag"></i>
                            </div>
                            <p class="fw-600 mb-1" style="color:#57534e;font-size:.95rem;">No incidents found</p>
                            <p class="mb-0" style="font-size:.83rem;color:#a8a29e;">
                                @if($search || $dateFrom || $dateTo)
                                    No records match your filters.
                                    <a href="{{ route('incidents.index') }}" style="color:#dc2626;">Clear all filters</a>
                                @else
                                    No incident records have been recorded yet.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($incidents->hasPages())
    <div class="vio-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="vio-footer-count">
            Showing <strong>{{ $incidents->firstItem() }}</strong>–<strong>{{ $incidents->lastItem() }}</strong>
            of <strong>{{ $incidents->total() }}</strong> records
        </div>
        <nav>
            <ul class="vio-pager">
                <li>
                    @if($incidents->onFirstPage())
                        <span class="vio-page vio-page-disabled"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a class="vio-page" href="{{ $incidents->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                    @endif
                </li>
                @php
                    $cur  = $incidents->currentPage();
                    $last = $incidents->lastPage();
                    $pages = collect();
                    for ($p = 1; $p <= $last; $p++) {
                        if ($p === 1 || $p === $last || abs($p - $cur) <= 2) $pages->push($p);
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
                            <a class="vio-page" href="{{ $incidents->url($p) }}">{{ $p }}</a>
                        @endif
                    </li>
                @endforeach
                <li>
                    @if($incidents->hasMorePages())
                        <a class="vio-page" href="{{ $incidents->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="vio-page vio-page-disabled"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
    @else
        <div class="vio-footer text-end">
            <span class="vio-footer-count">{{ $incidents->total() }} {{ Str::plural('record', $incidents->total()) }} total</span>
        </div>
    @endif
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
.filt-icon { background: #fdf8f0; border-color: #e7e0d6; color: #a8a29e; font-size: .8rem; }
.filt-input { border-color: #e7e0d6 !important; font-size: .82rem !important; transition: border-color .15s, box-shadow .15s; }
.filt-input:focus { border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220,38,38,.1) !important; }
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
.btn-filter-submit:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(220,38,38,.4); }

/* ─────────────── TABLE CARD ─────────────── */
.inc-table-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    border: 1px solid #f0ebe3;
    overflow: hidden;
}
.inc-table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .85rem 1.25rem;
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
    border-bottom: 1px solid #f0ebe3;
}
.inc-record-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .38rem 1rem;
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .78rem; font-weight: 700;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(29,78,216,.3);
    transition: transform .15s, box-shadow .15s;
}
.inc-record-btn:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(29,78,216,.4); color: #fff; }

/* ── Table header ── */
#incidents-table thead tr {
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
}
#incidents-table thead th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #78716c;
    border-bottom: 2px solid #ece5da;
    padding-top: .95rem;
    padding-bottom: .95rem;
}
.th-inner { display: inline-flex; align-items: center; }

/* ── Rows ── */
.inc-row { transition: background .18s; position: relative; }
.inc-row[data-href] { cursor: pointer; }
.inc-row:hover { background: #fff5f5 !important; }
.inc-row[data-href]:hover td:not(.inc-act-cell) { position: relative; }
.inc-row[data-href]:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: #dc2626;
    border-radius: 0 2px 2px 0;
}
.inc-row td { padding-top: .9rem; padding-bottom: .9rem; border-color: #f5f0ea; vertical-align: middle; }

/* ── Incident number ── */
.inc-number {
    font-family: ui-monospace, 'Cascadia Code', monospace;
    font-size: .82rem;
    font-weight: 700;
    color: #1d4ed8;
    transition: color .15s;
}
.inc-number:hover { color: #dc2626; }

/* ── Location ── */
.inc-location {
    display: block;
    max-width: 240px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: .84rem;
    color: #1c1917;
}

/* ── Date chip ── */
.date-chip { display: inline-flex; align-items: center; font-size: .82rem; color: #57534e; }

/* ── Count pills ── */
.count-pill {
    display: inline-flex; align-items: center;
    padding: .25rem .65rem;
    border-radius: 20px;
    font-size: .72rem; font-weight: 700;
    border: 1.5px solid;
}
.count-pill-blue  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.count-pill-purple { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }

/* ── Action buttons ── */
.act-btn {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .32rem .7rem;
    border-radius: 8px;
    font-size: .76rem; font-weight: 700;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: all .18s;
    white-space: nowrap;
    cursor: pointer;
    background: none;
}
.act-view  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; box-shadow: 0 1px 4px rgba(29,78,216,.1); }
.act-view:hover  { background: #1d4ed8; color: #fff; border-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(29,78,216,.3); }
.act-edit  { background: #fdf8f0; color: #b45309; border-color: #fde68a; box-shadow: 0 1px 4px rgba(180,83,9,.1); }
.act-edit:hover  { background: #d97706; color: #fff; border-color: #d97706; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(217,119,6,.3); }
.act-delete { background: #fff1f2; color: #be123c; border-color: #fecdd3; box-shadow: 0 1px 4px rgba(190,18,60,.1); }
.act-delete:hover { background: #be123c; color: #fff; border-color: #be123c; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(190,18,60,.3); }

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

/* ── Footer + pagination ── */
.vio-footer {
    padding: .85rem 1.5rem;
    border-top: 1px solid #f0ebe3;
    background: #fdf8f0;
}
.vio-footer-count { font-size: .8rem; color: #78716c; }
.vio-pager { display: flex; align-items: center; gap: .25rem; list-style: none; margin: 0; padding: 0; }
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
a.vio-page:hover { background: #fdf8f0; border-color: #dc2626; color: #dc2626; }
.vio-page-active { background: linear-gradient(135deg, #dc2626, #b91c1c); border-color: #dc2626; color: #fff; box-shadow: 0 2px 8px rgba(220,38,38,.3); cursor: default; }
.vio-page-disabled { color: #d6d3d1; border-color: #f0ebe3; background: #fafaf9; cursor: default; }
.vio-page-ellipsis { border-color: transparent; background: transparent; color: #a8a29e; cursor: default; font-size: .85rem; }

.fw-600 { font-weight: 600; }
.fw-700 { font-weight: 700; }

/* ─── Print button ─── */
.inc-print-btn {
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
.inc-print-btn:hover { border-color: #a8a29e; }

/* ─── Print Header (screen: hidden) ─── */
.gov-print-hdr, .gov-ph-title { display: none; }

/* ─── Print layout ─── */
@media print {
    .no-print,
    .filter-card,
    .vio-pagination,
    nav, header, footer,
    .inc-act-cell { display: none !important; }

    body, .inc-table-card {
        background: #fff !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    .inc-row { page-break-inside: avoid; }
    .inc-table-card { border: 1px solid #ccc !important; }

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

    @page { size: A4 portrait; margin: 8mm 10mm 12mm; }
}
</style>

@endsection

@push('scripts')
<script>
(function () {
    // Flatpickr on date inputs
    document.querySelectorAll('.flatpickr-date').forEach(function (el) {
        flatpickr(el, { dateFormat: 'Y-m-d', allowInput: true });
    });

    // Live search debounce
    const form = document.getElementById('inc-filter-form');
    if (!form) return;
    let timer;
    const submit = () => form.submit();
    const debounce = (fn, ms) => function () { clearTimeout(timer); timer = setTimeout(fn, ms); };
    form.querySelector('input[name="search"]').addEventListener('input', debounce(submit, 350));
})();

/* ── Clickable rows — navigate to incident detail on row click ── */
document.querySelectorAll('.inc-row[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.inc-act-cell')) return;
        if (e.target.closest('a'))             return;
        if (e.target.closest('button'))        return;
        if (e.target.closest('form'))          return;
        window.location.href = row.dataset.href;
    });
});
</script>
@endpush
