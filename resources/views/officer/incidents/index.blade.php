@extends('layouts.mobile')
@section('title', 'Incidents')

@push('styles')
@include('partials.motshow-styles')
<style>
.inc-search-shell {
    background: #fff;
    border: 1px solid rgba(15,23,42,.05);
    border-radius: 18px;
    padding: .75rem;
    margin-bottom: 1rem;
    box-shadow: 0 6px 20px rgba(15,23,42,.06);
}
.inc-search-bar {
    display: flex;
    align-items: center;
    gap: .65rem;
    background: #f8fafc;
    border: 1px solid #dbe5f1;
    border-radius: 16px;
    padding: .2rem .22rem .2rem .8rem;
}
.inc-search-icon {
    color: #64748b;
    font-size: 1rem;
    flex-shrink: 0;
}
.inc-search-input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    min-height: 44px;
    font-size: .92rem;
    padding: 0 !important;
}
.inc-search-input::placeholder {
    color: #94a3b8;
}
.inc-search-submit {
    border: none;
    border-radius: 14px;
    min-height: 44px;
    padding: 0 1rem;
    font-size: .84rem;
    font-weight: 800;
    color: #fff;
    background: linear-gradient(135deg,#dc2626,#b91c1c);
    box-shadow: 0 6px 16px rgba(220,38,38,.24);
    flex-shrink: 0;
}
.inc-filter-row {
    display: flex;
    gap: .6rem;
    margin-top: .75rem;
}
.inc-filter-btn {
    min-height: 44px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #334155;
    padding: 0 .9rem;
    font-size: .84rem;
    font-weight: 700;
    box-shadow: 0 1px 3px rgba(15,23,42,.04);
    flex-shrink: 0;
}
.inc-filter-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .7rem;
    margin-top: .75rem;
    flex-wrap: wrap;
}
.inc-filter-pills {
    display: flex;
    gap: .4rem;
    flex-wrap: wrap;
}
.inc-pill {
    display: inline-flex;
    align-items: center;
    gap: .28rem;
    border-radius: 999px;
    padding: .22rem .62rem;
    font-size: .68rem;
    font-weight: 700;
}
.inc-pill--search {
    background: #fff1f2;
    color: #dc2626;
    border: 1px solid #fecdd3;
}
.inc-pill--status {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.inc-filter-meta {
    font-size: .72rem;
    color: #64748b;
    font-weight: 600;
}
.inc-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .6rem;
    margin-bottom: 1rem;
}
.inc-stat-card {
    background: #fff;
    border: 1px solid rgba(15,23,42,.05);
    border-radius: 16px;
    padding: .82rem .45rem;
    text-align: center;
    box-shadow: 0 3px 14px rgba(15,23,42,.04);
}
.inc-stat-num {
    font-size: 1.25rem;
    font-weight: 800;
    line-height: 1;
}
.inc-stat-num--open {
    color: #dc2626;
}
.inc-stat-num--review {
    color: #1d4ed8;
}
.inc-stat-num--closed {
    color: #475569;
}
.inc-stat-lbl {
    font-size: .55rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-top: .2rem;
}
.inc-list-card {
    display: flex;
    align-items: center;
    gap: .9rem;
    background: #fff;
    border-radius: 18px;
    padding: .95rem 1rem .95rem .95rem;
    text-decoration: none;
    color: inherit;
    margin-bottom: .72rem;
    border: 1px solid rgba(15,23,42,.045);
    box-shadow: 0 3px 14px rgba(15,23,42,.06);
    position: relative;
    overflow: hidden;
}
.inc-list-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
}
.inc-list-card--open::before {
    background: linear-gradient(180deg,#dc2626,#b91c1c);
}
.inc-list-card--review::before {
    background: linear-gradient(180deg,#2563eb,#1d4ed8);
}
.inc-list-card--closed::before {
    background: linear-gradient(180deg,#64748b,#475569);
}
.inc-list-icon {
    width: 46px;
    height: 46px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 5px 14px rgba(15,23,42,.14);
}
.inc-list-icon--open {
    background: linear-gradient(135deg,#dc2626,#b91c1c);
}
.inc-list-icon--review {
    background: linear-gradient(135deg,#2563eb,#1d4ed8);
}
.inc-list-icon--closed {
    background: linear-gradient(135deg,#64748b,#475569);
}
.inc-list-title {
    font-size: .88rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
}
.inc-list-meta {
    font-size: .71rem;
    color: #64748b;
    margin-top: .14rem;
    line-height: 1.46;
}
.inc-list-submeta {
    font-size: .67rem;
    color: #94a3b8;
    margin-top: .2rem;
}

/* ── Pagination (matches violations page exactly) ── */
.inc-pager-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .55rem;
    padding: .75rem 0 5rem;
}
.inc-pager-count {
    font-size: .75rem;
    color: #78716c;
    font-weight: 600;
}
.inc-pager {
    display: flex; align-items: center; gap: .25rem;
    list-style: none; margin: 0; padding: 0;
}
.inc-page {
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
a.inc-page:hover {
    background: #fff7f7;
    border-color: #dc2626;
    color: #dc2626;
}
.inc-page-active {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    border-color: #dc2626;
    color: #fff !important;
    box-shadow: 0 2px 8px rgba(220,38,38,.3);
    cursor: default;
}
.inc-page-disabled {
    color: #d6d3d1;
    border-color: #f0ebe3;
    background: #fafaf9;
    cursor: default;
}
.inc-page-ellipsis {
    border-color: transparent;
    background: transparent;
    color: #a8a29e;
    cursor: default;
    font-size: .85rem;
}
</style>
@endpush

@section('content')

@php
    $openCount = $incidents->getCollection()->where('status', 'open')->count();
    $reviewCount = $incidents->getCollection()->where('status', 'under_review')->count();
    $closedCount = $incidents->getCollection()->where('status', 'closed')->count();
@endphp

<div class="motshow-section">Search &amp; Filter</div>

<form method="GET" action="{{ route('officer.incidents.index') }}" class="inc-search-shell">
    <div class="inc-search-bar">
        <i class="ph ph-magnifying-glass inc-search-icon"></i>
        <input
            type="text"
            name="search"
            value="{{ $search }}"
            class="form-control inc-search-input"
            placeholder="Incident #, location, or motorist..."
            autocomplete="off"
        >
        <button class="inc-search-submit" type="submit">Search</button>
    </div>

    <div class="inc-filter-row">
        <select name="status" class="form-select mob-select" style="font-size:.85rem;">
            <option value="">All statuses</option>
            <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open</option>
            <option value="under_review" {{ $status === 'under_review' ? 'selected' : '' }}>Under Review</option>
            <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
        <button type="submit" class="inc-filter-btn">
            <i class="ph ph-funnel-simple me-1"></i> Apply
        </button>
    </div>

    <div class="inc-filter-summary">
        <div class="inc-filter-pills">
            @if($search)
                <span class="inc-pill inc-pill--search">
                    <i class="ph ph-magnifying-glass"></i>
                    {{ $search }}
                </span>
            @endif

            @if($status)
                <span class="inc-pill inc-pill--status">
                    <i class="ph ph-funnel-simple"></i>
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
            @endif

            @if($search || $status)
                <a href="{{ route('officer.incidents.index') }}" class="inc-pill" style="text-decoration:none;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;">
                    <i class="ph ph-x-circle"></i>
                    Clear
                </a>
            @endif
        </div>

        <div class="inc-filter-meta">
            {{ $incidents->total() }} incident{{ $incidents->total() !== 1 ? 's' : '' }} found
        </div>
    </div>
</form>

<div class="inc-stat-grid">
    <div class="inc-stat-card">
        <div class="inc-stat-num inc-stat-num--open">{{ $openCount }}</div>
        <div class="inc-stat-lbl">Open</div>
    </div>
    <div class="inc-stat-card">
        <div class="inc-stat-num inc-stat-num--review">{{ $reviewCount }}</div>
        <div class="inc-stat-lbl">Review</div>
    </div>
    <div class="inc-stat-card">
        <div class="inc-stat-num inc-stat-num--closed">{{ $closedCount }}</div>
        <div class="inc-stat-lbl">Closed</div>
    </div>
</div>

<div class="motshow-section">Incident Records</div>

@if($incidents->isEmpty())
    <div class="mob-card">
        <div class="mob-empty">
            <i class="ph ph-flag mob-empty-icon"></i>
            <div class="mob-empty-text">No incidents found</div>
            @if($search || $status)
                <div class="mob-empty-sub">
                    <a href="{{ route('officer.incidents.index') }}" style="color:#dc2626;font-weight:700;text-decoration:none;">Clear filters</a>
                </div>
            @endif
        </div>
    </div>
@else
    @foreach($incidents as $inc)
        @php
            $variant = match($inc->status) {
                'open' => 'open',
                'under_review' => 'review',
                default => 'closed',
            };
            $badgeClass = match($inc->status) {
                'open' => 'mob-badge-open',
                'under_review' => 'mob-badge-review',
                default => 'mob-badge-closed',
            };
        @endphp
        <a href="{{ route('officer.incidents.show', $inc) }}" class="inc-list-card inc-list-card--{{ $variant }}">
            <div class="inc-list-icon inc-list-icon--{{ $variant }}">
                <i class="ph-fill ph-flag" style="font-size:1rem;"></i>
            </div>

            <div style="flex:1;min-width:0;">
                <div class="inc-list-title">{{ $inc->incident_number }}</div>
                <div class="inc-list-meta">
                    {{ $inc->date_of_incident ? \Carbon\Carbon::parse($inc->date_of_incident)->format('M d, Y') : '—' }}
                    @if($inc->location)
                        · {{ Str::limit($inc->location, 34) }}
                    @endif
                </div>
                <div class="inc-list-submeta">
                    {{ $inc->motorists_count }} motorist{{ $inc->motorists_count !== 1 ? 's' : '' }} involved
                </div>
            </div>

            <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
                <span class="mob-badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $inc->status)) }}</span>
                <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.82rem;"></i>
            </div>
        </a>
    @endforeach

    @if($incidents->hasPages())
    @php
        $cur  = $incidents->currentPage();
        $last = $incidents->lastPage();
        $pages = collect();
        for ($p = 1; $p <= $last; $p++) {
            if ($p === 1 || $p === $last || abs($p - $cur) <= 2) {
                $pages->push($p);
            }
        }
        $pages = $pages->unique()->sort()->values();
    @endphp
    <div class="inc-pager-wrap">
        <div class="inc-pager-count">
            Showing <strong>{{ $incidents->firstItem() }}</strong>–<strong>{{ $incidents->lastItem() }}</strong> of <strong>{{ $incidents->total() }}</strong> results
        </div>
        <ul class="inc-pager">
            <li>
                @if($incidents->onFirstPage())
                    <span class="inc-page inc-page-disabled"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a class="inc-page" href="{{ $incidents->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                @endif
            </li>
            @foreach($pages as $i => $p)
                @if($i > 0 && $p - $pages[$i - 1] > 1)
                    <li><span class="inc-page inc-page-ellipsis">…</span></li>
                @endif
                <li>
                    @if($p === $cur)
                        <span class="inc-page inc-page-active">{{ $p }}</span>
                    @else
                        <a class="inc-page" href="{{ $incidents->url($p) }}">{{ $p }}</a>
                    @endif
                </li>
            @endforeach
            <li>
                @if($incidents->hasMorePages())
                    <a class="inc-page" href="{{ $incidents->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="inc-page inc-page-disabled"><i class="bi bi-chevron-right"></i></span>
                @endif
            </li>
        </ul>
    </div>
    @endif
@endif

<a href="{{ route('officer.incidents.create') }}" class="mob-fab mob-fab--red" title="Record Incident">
    <i class="ph-bold ph-plus"></i>
</a>

@endsection
