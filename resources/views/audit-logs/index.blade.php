@extends('layouts.app')
@section('title', 'Audit Trail & Activity Logs')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Audit Trail</li>
@endsection

@section('topbar-sub')
<span id="topbar-sub-container">
    <i class="bi bi-shield-check me-1" style="color:#0284c7;"></i>
    {{ number_format($logs->total()) }} {{ Str::plural('entry', $logs->total()) }}
    @if($selectedLgu)
        &nbsp;·&nbsp; <span style="display:inline-flex;align-items:center;gap:3px;background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;border-radius:999px;padding:1px 8px;font-size:.78rem;font-weight:600;">
            <i class="bi bi-building me-1"></i> {{ $selectedLgu->name }}
        </span>
    @elseif($isLguScoped && auth()->user()->lgu)
        &nbsp;·&nbsp; <span style="display:inline-flex;align-items:center;gap:3px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:999px;padding:1px 8px;font-size:.78rem;font-weight:600;">
            <i class="bi bi-building me-1"></i> {{ auth()->user()->lgu->name }}
        </span>
    @endif
</span>
@endsection

@section('content')

{{-- ── KPI STATS CARDS ── --}}
<div class="row g-3 mb-4 no-print">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="aud-stat-card card-total">
            <div class="aud-stat-icon text-blue">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <div class="aud-stat-val">{{ number_format($totalLogs) }}</div>
                <div class="aud-stat-lbl">Total Audit Entries</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="aud-stat-card card-today">
            <div class="aud-stat-icon text-amber">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="aud-stat-val">{{ number_format($todayLogs) }}</div>
                <div class="aud-stat-lbl">Today's System Events</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="aud-stat-card card-login">
            <div class="aud-stat-icon text-purple">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <div class="aud-stat-val">{{ number_format($loginLogs) }}</div>
                <div class="aud-stat-lbl">Auth Sessions &amp; Logins</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="aud-stat-card card-mutations">
            <div class="aud-stat-icon text-emerald">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <div class="aud-stat-val">{{ number_format($mutationLogs) }}</div>
                <div class="aud-stat-lbl">Record Mutations</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter Card ── --}}
<div class="filter-card mb-4 no-print">
    <div class="filter-card-header">
        <div class="d-flex align-items-center gap-2">
            <span class="filter-icon-wrap">
                <i class="bi bi-sliders2-vertical"></i>
            </span>
            <div>
                <div class="fw-700" style="font-size:.88rem;color:#1c1917;">Search Audit Records</div>
                <div style="font-size:.72rem;color:#a8a29e;">
                    @if(auth()->user()->isTrafficSupervisor())
                        Traffic enforcement, citation tickets &amp; field officer activity logs ({{ auth()->user()->lgu?->name ?? 'LGU' }})
                    @elseif($isLguScoped && auth()->user()->lgu)
                        Scoped to {{ auth()->user()->lgu->name }}
                    @else
                        Governance &amp; system-wide activity monitoring
                    @endif
                </div>
            </div>
        </div>
        @if(request()->hasAny(['search','event','lgu_id','date_from','date_to']))
            <a href="{{ route('audit-logs.index') }}" class="filter-clear-btn ms-auto">
                <i class="bi bi-x-lg"></i> Clear filters
            </a>
        @endif
    </div>
    <div class="filter-card-body">
        <form method="GET" action="{{ route('audit-logs.index') }}" id="aud-filter-form">
            <div class="d-flex flex-wrap align-items-end" style="gap: .65rem; row-gap: .85rem;">

                {{-- Search text --}}
                <div style="flex: 2.5 1 230px; min-width: 220px;">
                    <label class="filter-label"><i class="bi bi-search me-1"></i>Search Description / User / Action</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text filt-icon"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control filt-input"
                            placeholder="Search description, username, event..."
                            value="{{ $search }}">
                    </div>
                </div>

                {{-- LGU Selector (Super Admin & Province Admin only) --}}
                @if(!$isLguScoped && $lgus->count() > 0)
                <div style="flex: 2 1 210px; min-width: 205px;">
                    <label class="filter-label"><i class="bi bi-building me-1"></i>Filter by LGU</label>
                    <select name="lgu_id" class="form-select form-select-sm filt-input">
                        <option value="">All LGUs (Province-wide)</option>
                        @foreach($lgus as $lgu)
                            <option value="{{ $lgu->id }}" {{ (string)$lguId === (string)$lgu->id ? 'selected' : '' }}>
                                {{ $lgu->name }} ({{ $lgu->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Log Category --}}
                <div style="flex: 1.5 1 160px; min-width: 155px;">
                    <label class="filter-label"><i class="bi bi-tags me-1"></i>Record Category</label>
                    <select name="category" class="form-select form-select-sm filt-input">
                        <option value="">All Categories</option>
                        <option value="violation" {{ ($category ?? '') === 'violation' ? 'selected' : '' }}>Violations &amp; Citations</option>
                        <option value="incident" {{ ($category ?? '') === 'incident' ? 'selected' : '' }}>Traffic Incidents</option>
                        <option value="violator" {{ ($category ?? '') === 'violator' ? 'selected' : '' }}>Motorists &amp; Profiles</option>
                        <option value="vehicle" {{ ($category ?? '') === 'vehicle' ? 'selected' : '' }}>Vehicles</option>
                        <option value="auth" {{ ($category ?? '') === 'auth' ? 'selected' : '' }}>Officer Logins (Auth)</option>
                    </select>
                </div>

                {{-- Event Type --}}
                <div style="flex: 1.5 1 140px; min-width: 135px;">
                    <label class="filter-label"><i class="bi bi-lightning-charge me-1"></i>Event Type</label>
                    <select name="event" class="form-select form-select-sm filt-input">
                        <option value="">All Events</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev }}" {{ $event === $ev ? 'selected' : '' }}>{{ ucfirst($ev) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date From --}}
                <div style="flex: 1 1 130px; min-width: 125px;">
                    <label class="filter-label"><i class="bi bi-calendar-event me-1"></i>Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm filt-input"
                        value="{{ $dateFrom }}">
                </div>

                {{-- Date To --}}
                <div style="flex: 1 1 130px; min-width: 125px;">
                    <label class="filter-label"><i class="bi bi-calendar-event me-1"></i>Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm filt-input"
                        value="{{ $dateTo }}">
                </div>

                <div class="d-flex align-items-end gap-1.5 ms-auto" style="flex-shrink:0;">
                    <button type="submit" class="btn-filter-submit">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <button type="button" class="aud-print-btn no-print" onclick="window.print()">
                        <i class="bi bi-printer-fill"></i> Print
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── PRINT HEADER ── --}}
<div class="gov-print-hdr">
    <img src="{{ asset('images/PNP.png') }}" class="gov-ph-seal gov-ph-seal-pnp" alt="PNP Logo">
    <div class="gov-ph-agency">
        <div class="gov-ph-republic">Republic of the Philippines</div>
        <div class="gov-ph-npc">NATIONAL POLICE COMMISSION</div>
        <div class="gov-ph-pro7">PHILIPPINE NATIONAL POLICE, POLICE REGIONAL OFFICE 7</div>
        <div class="gov-ph-cebu">CEBU POLICE PROVINCIAL OFFICE</div>
        <div class="gov-ph-station">
            @if($selectedLgu)
                {{ strtoupper($selectedLgu->name) }} POLICE STATION
            @elseif($isLguScoped && auth()->user()->lgu)
                {{ strtoupper(auth()->user()->lgu->name) }} POLICE STATION
            @else
                PROVINCIAL AUDIT &amp; GOVERNANCE COMMAND
            @endif
        </div>
        <div class="gov-ph-address">System Audit Trail &amp; Activity Log Report</div>
    </div>
    <img src="{{ asset('images/Balamban.png') }}" class="gov-ph-seal" alt="LGU Seal">
</div>
<div class="gov-ph-title">System Audit Log Report</div>

{{-- ── Table Card ── --}}
<div class="aud-table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="audit-table">
            <thead>
                <tr>
                    <th style="padding-left:1.5rem;text-align:left;width:150px;">
                        <span class="th-inner"><i class="bi bi-clock-history me-1"></i>Timestamp</span>
                    </th>
                    <th style="width:220px;">
                        <span class="th-inner"><i class="bi bi-person-badge-fill me-1"></i>Actor / User</span>
                    </th>
                    <th class="text-center" style="width:130px;">
                        <span class="th-inner"><i class="bi bi-tag-fill me-1"></i>Event</span>
                    </th>
                    <th style="width:170px;">
                        <span class="th-inner"><i class="bi bi-box-seam me-1"></i>Subject Target</span>
                    </th>
                    <th style="padding-right:1.5rem;">
                        <span class="th-inner"><i class="bi bi-card-text me-1"></i>Activity Description &amp; Changes</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="aud-row">
                    {{-- Timestamp --}}
                    <td style="padding-left:1.5rem;vertical-align:top;">
                        @php
                            $tz = config('app.timezone', 'Asia/Manila');
                            $logTime = $log->created_at ? $log->created_at->copy()->setTimezone($tz) : null;
                        @endphp
                        @if($logTime)
                            <div class="fw-700" style="font-size:.82rem;color:#1c1917;">
                                {{ $logTime->format('M d, Y') }}
                            </div>
                            <div style="font-size:.72rem;color:#78716c;">
                                {{ $logTime->format('h:i:s A') }}
                            </div>
                            <div style="font-size:.67rem;color:#a8a29e;margin-top:1px;">
                                {{ $logTime->diffForHumans() }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Actor / User --}}
                    <td style="vertical-align:top;">
                        @if($log->causer)
                            <div class="d-flex align-items-center gap-2">
                                <div class="actor-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-700 text-dark" style="font-size:.84rem;">
                                        {{ $log->causer->name }}
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-wrap" style="margin-top:1px;">
                                        <span class="badge rounded-pill bg-dark text-white px-2 py-0" style="font-size:.63rem;font-weight:600;">
                                            {{ $log->causer->role_label }}
                                        </span>
                                        @if($log->causer->lgu)
                                            <span class="badge rounded-pill bg-light text-secondary border px-2 py-0" style="font-size:.62rem;font-weight:600;" title="Assigned to {{ $log->causer->lgu->name }}">
                                                <i class="bi bi-building me-1"></i>{{ $log->causer->lgu->code ?? $log->causer->lgu->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-2">
                                <div class="actor-avatar system-avatar">
                                    <i class="bi bi-gear-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-700 text-secondary" style="font-size:.82rem;">System / Automated</div>
                                    <span class="badge rounded-pill bg-light text-muted border" style="font-size:.62rem;">Background Task</span>
                                </div>
                            </div>
                        @endif
                    </td>

                    {{-- Event Badge --}}
                    <td class="text-center" style="vertical-align:top;">
                        @php
                            $eventKey = strtolower($log->event ?? $log->log_name ?? 'default');
                            $eventConf = match($eventKey) {
                                'created' => ['cls' => 'evt-created', 'icon' => 'bi-plus-circle-fill'],
                                'updated' => ['cls' => 'evt-updated', 'icon' => 'bi-pencil-fill'],
                                'deleted' => ['cls' => 'evt-deleted', 'icon' => 'bi-trash3-fill'],
                                'login'   => ['cls' => 'evt-login',   'icon' => 'bi-box-arrow-in-right'],
                                'logout'  => ['cls' => 'evt-logout',  'icon' => 'bi-box-arrow-left'],
                                default   => ['cls' => 'evt-default', 'icon' => 'bi-info-circle-fill'],
                            };
                        @endphp
                        <span class="evt-badge {{ $eventConf['cls'] }}">
                            <i class="bi {{ $eventConf['icon'] }} me-1" style="font-size:.65rem;"></i>
                            {{ strtoupper($log->event ?? $log->log_name ?? 'ACTION') }}
                        </span>
                    </td>

                    {{-- Subject Model Target --}}
                    <td style="vertical-align:top;">
                        @if($log->subject_type)
                            @php
                                $baseModel = class_basename($log->subject_type);
                            @endphp
                            <span class="subject-pill">
                                <i class="bi bi-box-seam me-1" style="color:#64748b;font-size:.7rem;"></i>
                                {{ $baseModel }} #{{ $log->subject_id ?? '—' }}
                            </span>
                        @else
                            <span class="text-muted" style="font-size:.78rem;">—</span>
                        @endif
                    </td>

                    {{-- Description & Property Changes --}}
                    <td style="padding-right:1.5rem;vertical-align:top;">
                        <div class="fw-600" style="color:#1c1917;font-size:.85rem;">
                            {{ $log->description }}
                        </div>

                        @if($log->properties && count($log->properties) > 0)
                            <details class="aud-changes-drawer mt-2">
                                <summary class="aud-changes-btn">
                                    <i class="bi bi-code-slash me-1"></i> Inspect Properties &amp; Changes
                                </summary>
                                <div class="aud-changes-box mt-2 p-2">
                                    @php
                                        $props = $log->properties;
                                        $old   = $props['old'] ?? null;
                                        $attributes = $props['attributes'] ?? null;
                                    @endphp
                                    @if($old || $attributes)
                                        <div class="row g-2">
                                            @if($old)
                                                <div class="col-md-6">
                                                    <div class="fw-700 text-danger mb-1" style="font-size:.7rem;"><i class="bi bi-dash-circle me-1"></i>Old Values</div>
                                                    <pre class="json-block">{{ json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            @endif
                                            @if($attributes)
                                                <div class="col-md-6">
                                                    <div class="fw-700 text-success mb-1" style="font-size:.7rem;"><i class="bi bi-plus-circle me-1"></i>New Values</div>
                                                    <pre class="json-block">{{ json_encode($attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <pre class="json-block mb-0">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @endif
                                </div>
                            </details>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-5 text-center">
                        <div class="empty-icon-wrap mx-auto mb-3">
                            <i class="bi bi-shield-x"></i>
                        </div>
                        <p class="fw-600 mb-1" style="color:#57534e;font-size:.95rem;">No audit logs found</p>
                        <p class="mb-0" style="font-size:.83rem;color:#a8a29e;">
                            No activity records matched your filter criteria.
                            <a href="{{ route('audit-logs.index') }}" style="color:#0284c7;font-weight:600;">Clear all filters</a>
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer --}}
    @if($logs->hasPages())
    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
        <div class="text-muted" style="font-size:0.78rem;font-weight:500;">
            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ number_format($logs->total()) }} audit entries
        </div>
        <nav>
            <ul class="aud-pager">
                <li>
                    @if($logs->onFirstPage())
                        <span class="aud-page aud-page-disabled"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a class="aud-page" href="{{ $logs->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                    @endif
                </li>
                @php
                    $cur  = $logs->currentPage();
                    $last = $logs->lastPage();
                    $pages = collect();
                    for ($p = 1; $p <= $last; $p++) {
                        if ($p === 1 || $p === $last || abs($p - $cur) <= 2) $pages->push($p);
                    }
                    $pages = $pages->unique()->sort()->values();
                @endphp
                @foreach($pages as $i => $p)
                    @if($i > 0 && $p - $pages[$i - 1] > 1)
                        <li><span class="aud-page aud-page-ellipsis">&hellip;</span></li>
                    @endif
                    <li>
                        @if($p === $cur)
                            <span class="aud-page aud-page-active">{{ $p }}</span>
                        @else
                            <a class="aud-page" href="{{ $logs->url($p) }}">{{ $p }}</a>
                        @endif
                    </li>
                @endforeach
                <li>
                    @if($logs->hasMorePages())
                        <a class="aud-page" href="{{ $logs->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="aud-page aud-page-disabled"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
/* ── Stat Cards ── */
.aud-stat-card {
    background: #fff;
    border: 1px solid #e7e5e4;
    border-radius: 14px;
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    transition: transform .15s ease, box-shadow .15s ease;
}
.aud-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}
.aud-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.text-blue { background: #e0f2fe; color: #0284c7; }
.text-amber { background: #fef3c7; color: #d97706; }
.text-purple { background: #f3e8ff; color: #9333ea; }
.text-emerald { background: #dcfce7; color: #16a34a; }

.aud-stat-val {
    font-size: 1.4rem;
    font-weight: 800;
    color: #1c1917;
    line-height: 1.1;
}
.aud-stat-lbl {
    font-size: .76rem;
    font-weight: 600;
    color: #78716c;
    margin-top: 2px;
}

/* ── Filter Card ── */
.filter-card {
    background: #fff;
    border: 1px solid #e7e5e4;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    overflow: hidden;
}
.filter-card-header {
    padding: .85rem 1.25rem;
    border-bottom: 1px solid #f5f5f4;
    background: #fafaf9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.filter-icon-wrap {
    width: 32px;
    height: 32px;
    background: #f0f9ff;
    color: #0284c7;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
}
.filter-clear-btn {
    font-size: .76rem;
    font-weight: 600;
    color: #dc2626;
    text-decoration: none;
    padding: 3px 10px;
    border-radius: 6px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    transition: all .15s;
}
.filter-clear-btn:hover {
    background: #dc2626;
    color: #fff;
    border-color: #dc2626;
}
.filter-card-body {
    padding: 1rem 1.25rem;
}
.filter-label {
    font-size: .72rem;
    font-weight: 700;
    color: #57534e;
    text-transform: uppercase;
    letter-spacing: .03em;
    margin-bottom: 4px;
    display: block;
}
.filt-input {
    border-color: #e7e5e4;
    border-radius: 8px;
    font-size: .82rem;
}
.filt-input:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2,132,199,0.12);
}
.filt-icon {
    background: #f5f5f4;
    border-color: #e7e5e4;
    color: #a8a29e;
}
.btn-filter-submit {
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0 16px;
    height: 31px;
    font-size: .8rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(2,132,199,0.25);
    transition: opacity .15s;
}
.btn-filter-submit:hover { opacity: .9; }
.aud-print-btn {
    background: #f5f5f4;
    color: #44403c;
    border: 1px solid #d6d3d1;
    border-radius: 8px;
    padding: 0 12px;
    height: 31px;
    font-size: .8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.aud-print-btn:hover { background: #e7e5e4; }

/* ── Table Card ── */
.aud-table-card {
    background: #fff;
    border: 1px solid #e7e5e4;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    overflow: hidden;
}
#audit-table thead th {
    background: #fafaf9;
    padding: .85rem 1rem;
    border-bottom: 1px solid #e7e5e4;
}
.th-inner {
    font-size: .73rem;
    font-weight: 700;
    color: #78716c;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.aud-row {
    border-bottom: 1px solid #f5f5f4;
    transition: background .12s ease;
}
.aud-row:hover {
    background: #fcfcfb;
}
.actor-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
    flex-shrink: 0;
    border: 1px solid #e2e8f0;
}
.system-avatar {
    background: #fef3c7;
    color: #b45309;
    border-color: #fde68a;
}

/* Event badges */
.evt-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .03em;
}
.evt-created { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.evt-updated { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.evt-deleted { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.evt-login   { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
.evt-logout  { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.evt-default { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }

/* Subject Target pill */
.subject-pill {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 2px 8px;
    font-size: .75rem;
    font-weight: 600;
}

/* Property changes drawer */
.aud-changes-drawer summary {
    list-style: none;
}
.aud-changes-drawer summary::-webkit-details-marker {
    display: none;
}
.aud-changes-btn {
    display: inline-flex;
    align-items: center;
    font-size: .72rem;
    font-weight: 700;
    color: #0284c7;
    cursor: pointer;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    padding: 2px 8px;
    transition: all .15s;
}
.aud-changes-btn:hover {
    background: #0284c7;
    color: #fff;
}
.aud-changes-box {
    background: #0f172a;
    color: #f8fafc;
    border-radius: 8px;
    box-shadow: inset 0 2px 6px rgba(0,0,0,0.3);
}
.json-block {
    background: transparent;
    color: #38bdf8;
    font-family: monospace;
    font-size: .7rem;
    line-height: 1.4;
    margin: 0;
    max-height: 160px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
}

/* Empty state */
.empty-icon-wrap {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #f5f5f4;
    color: #a8a29e;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

/* Pagination */
.aud-pager { display: flex; align-items: center; gap: .25rem; list-style: none; margin: 0; padding: 0; }
.aud-page {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 .55rem;
    border-radius: 8px;
    font-size: .78rem; font-weight: 600;
    border: 1.5px solid #e7e5e4;
    color: #57534e;
    background: #fff;
    text-decoration: none;
    transition: all .15s;
    cursor: pointer;
}
a.aud-page:hover { background: #f0f9ff; border-color: #0284c7; color: #0284c7; }
.aud-page-active { background: linear-gradient(135deg, #0284c7, #0369a1); border-color: #0284c7; color: #fff; box-shadow: 0 2px 8px rgba(2,132,199,0.3); cursor: default; }
.aud-page-disabled { opacity: .4; cursor: not-allowed; border-color: #e7e5e4; color: #a8a29e; }
.aud-page-ellipsis { border: none; background: transparent; color: #a8a29e; cursor: default; }

/* Print styles */
.gov-print-hdr { display: none; }
.gov-ph-title { display: none; }

@media print {
    .no-print, .sidebar, .topbar, .filter-card, .aud-stat-card, .card-footer, .aud-pager { display: none !important; }
    body { background: #fff !important; color: #000 !important; font-size: 10pt; }
    .content { padding: 0 !important; margin: 0 !important; }
    
    .gov-print-hdr {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2pt solid #1c1917;
        padding-bottom: 8pt;
        margin-bottom: 10pt;
    }
    .gov-ph-seal { height: 50pt; width: auto; }
    .gov-ph-agency { text-align: center; flex: 1; }
    .gov-ph-republic { font-size: 8pt; text-transform: uppercase; letter-spacing: 1pt; }
    .gov-ph-npc { font-size: 9pt; font-weight: bold; }
    .gov-ph-pro7 { font-size: 8pt; font-weight: bold; }
    .gov-ph-cebu { font-size: 8.5pt; font-weight: bold; }
    .gov-ph-station { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-top: 2pt; }
    .gov-ph-address { font-size: 7.5pt; font-style: italic; color: #444; }

    .gov-ph-title {
        display: block !important;
        text-align: center;
        font-size: 13pt;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5pt;
        margin-bottom: 10pt;
    }

    .aud-table-card { border: none !important; box-shadow: none !important; }
    #audit-table { width: 100% !important; border-collapse: collapse !important; }
    #audit-table th, #audit-table td { border: 1px solid #d6d3d1 !important; padding: 4pt 6pt !important; }
    .aud-changes-drawer { display: none !important; }
    @page { size: A4 landscape; margin: 10mm; }
}
</style>
@endpush
