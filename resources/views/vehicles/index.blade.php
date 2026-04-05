@extends('layouts.app')
@section('title', 'Vehicles')

@section('topbar-sub')
    <i class="bi bi-car-front-fill me-1" style="color:#0284c7;"></i>
    {{ $vehicles->total() }} total {{ Str::plural('vehicle', $vehicles->total()) }}
    @php
        $activeFilters = [];
        if (request('search')) $activeFilters[] = ['label' => 'Search', 'value' => request('search')];
        if (request('type'))   $activeFilters[] = ['label' => 'Type',   'value' => request('type') === 'MV' ? 'Motor Vehicle' : 'Motorcycle'];
    @endphp
    @foreach($activeFilters as $f)
        &nbsp;·&nbsp; <span style="display:inline-flex;align-items:center;gap:3px;background:#f0f9ff;color:#0369a1;border:1px solid #7dd3fc;border-radius:999px;padding:1px 8px;font-size:.78rem;font-weight:500;">
            <span style="color:#0284c7;">{{ $f['label'] }}:</span> {{ $f['value'] }}
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
                <div style="font-size:.72rem;color:#a8a29e;">Narrow down vehicle records</div>
            </div>
        </div>
        @if(request()->hasAny(['search','type']))
            <a href="{{ route('vehicles.index') }}" class="filter-clear-btn ms-auto">
                <i class="bi bi-x-lg"></i> Clear filters
            </a>
        @endif
    </div>
    <div class="filter-card-body">
        <form method="GET" action="{{ route('vehicles.index') }}" id="vh-filter-form">
            <div class="d-flex flex-wrap align-items-end gap-2">

                <div style="flex:3;min-width:0;">
                    <label class="filter-label"><i class="bi bi-search me-1"></i>Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text filt-icon"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control filt-input"
                            placeholder="Plate, make, model, color, or owner name"
                            value="{{ request('search') }}">
                    </div>
                </div>

                <div style="flex:1.2;min-width:0;">
                    <label class="filter-label"><i class="bi bi-car-front me-1"></i>Type</label>
                    <select name="type" class="form-select form-select-sm filt-input">
                        <option value="">All Types</option>
                        <option value="MV" {{ request('type') === 'MV' ? 'selected' : '' }}>Motor Vehicle (MV)</option>
                        <option value="MC" {{ request('type') === 'MC' ? 'selected' : '' }}>Motorcycle (MC)</option>
                    </select>
                </div>

                <div style="flex-shrink:0;">
                    <button type="submit" class="btn-filter-submit">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Table Card ── --}}
<div class="vio-table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="vehicles-table">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem;width:60px;"></th>
                    <th><span class="th-inner"><i class="bi bi-upc me-1"></i>Plate No.</span></th>
                    <th class="text-center"><span class="th-inner"><i class="bi bi-car-front-fill me-1"></i>Type</span></th>
                    <th><span class="th-inner"><i class="bi bi-tools me-1"></i>Make / Model</span></th>
                    <th><span class="th-inner"><i class="bi bi-palette me-1"></i>Color / Year</span></th>
                    <th><span class="th-inner"><i class="bi bi-person-fill me-1"></i>Owner</span></th>
                    <th class="text-center"><span class="th-inner"><i class="bi bi-shield-exclamation me-1"></i>Violations</span></th>
                    <th class="text-center vio-act-cell no-print"><span class="th-inner"><i class="bi bi-lightning-charge-fill me-1"></i>Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vh)
                <tr class="vio-row" data-href="{{ route('vehicles.show', $vh) }}">

                    {{-- Thumbnail --}}
                    <td style="padding-left:1.25rem;">
                        @if($vh->photos->isNotEmpty())
                            <img src="{{ uploaded_file_url($vh->photos->first()->photo) }}"
                                 alt="Vehicle photo"
                                 style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #e7dfd5;">
                        @else
                            <div style="width:44px;height:44px;border-radius:8px;background:#f5f0e8;border:1px solid #e7dfd5;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-car-front" style="color:#a8a29e;font-size:.9rem;"></i>
                            </div>
                        @endif
                    </td>

                    {{-- Plate --}}
                    <td>
                        <a href="{{ route('vehicles.show', $vh) }}"
                           class="vh-plate text-decoration-none">
                            <span class="plate-pill">
                                <i class="bi bi-upc me-1" style="font-size:.7rem;color:#a8a29e;"></i>
                                {{ $vh->plate_number }}
                            </span>
                        </a>
                        <div style="font-size:.69rem;color:#a8a29e;margin-top:2px;">
                            <i class="bi bi-eye me-1" style="font-size:.6rem;"></i>Click row to view vehicle
                        </div>
                    </td>

                    {{-- Type --}}
                    <td class="text-center">
                        @if($vh->vehicle_type === 'MV')
                            <span class="status-badge" style="background:#eff6ff;color:#1d4ed8;border:1.5px solid #bfdbfe;">
                                <i class="bi bi-car-front-fill"></i> MV
                            </span>
                        @else
                            <span class="status-badge" style="background:#fdf4ff;color:#7c3aed;border:1.5px solid #e9d5ff;">
                                <i class="bi bi-bicycle"></i> MC
                            </span>
                        @endif
                    </td>

                    {{-- Make / Model --}}
                    <td>
                        @if($vh->make || $vh->model)
                            <span style="font-size:.86rem;color:#292524;">{{ trim(($vh->make ?? '') . ' ' . ($vh->model ?? '')) }}</span>
                        @else
                            <span class="no-data">—</span>
                        @endif
                    </td>

                    {{-- Color / Year --}}
                    <td>
                        @if($vh->color || $vh->year)
                            <span style="font-size:.86rem;color:#44403c;">
                                {{ collect([$vh->color, $vh->year])->filter()->implode(' · ') }}
                            </span>
                        @else
                            <span class="no-data">—</span>
                        @endif
                    </td>

                    {{-- Owner --}}
                    <td>
                        @if($vh->violator)
                            <a href="{{ route('violators.show', $vh->violator) }}"
                               class="text-decoration-none fw-600"
                               style="color:#0284c7;font-size:.86rem;">
                                {{ $vh->violator->full_name }}
                            </a>
                        @else
                            <span class="no-data">—</span>
                        @endif
                    </td>

                    {{-- Violations count --}}
                    <td class="text-center">
                        @if($vh->violations_count > 0)
                            <span class="status-badge status-pending">
                                <i class="bi bi-shield-exclamation"></i>
                                {{ $vh->violations_count }}
                            </span>
                        @else
                            <span class="no-data">0</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="text-center vio-act-cell no-print">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('vehicles.show', $vh) }}"
                               class="act-btn act-view" title="View Details">
                                <i class="bi bi-eye-fill"></i>
                                <span>View</span>
                            </a>
                            @can('update', $vh)
                            <a href="{{ route('vehicles.edit', $vh) }}"
                               class="act-btn act-edit" title="Edit Vehicle">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-5">
                        <div class="text-center">
                            <div class="empty-icon-wrap mx-auto mb-3">
                                <i class="bi bi-car-front"></i>
                            </div>
                            <p class="fw-600 mb-1" style="color:#57534e;font-size:.95rem;">No vehicles found</p>
                            <p class="mb-0" style="font-size:.83rem;color:#a8a29e;">
                                @if(request()->hasAny(['search','type']))
                                    No records match your filters.
                                    <a href="{{ route('vehicles.index') }}" style="color:#0284c7;">Clear all filters</a>
                                @else
                                    No vehicle records have been added yet.
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
    @if($vehicles->hasPages())
    <div class="vio-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="vio-footer-count">
            Showing <strong>{{ $vehicles->firstItem() }}</strong>–<strong>{{ $vehicles->lastItem() }}</strong>
            of <strong>{{ $vehicles->total() }}</strong> records
        </div>
        <nav>
            <ul class="vio-pager">
                <li>
                    @if($vehicles->onFirstPage())
                        <span class="vio-page vio-page-disabled"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a class="vio-page" href="{{ $vehicles->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                    @endif
                </li>

                @php
                    $cur  = $vehicles->currentPage();
                    $last = $vehicles->lastPage();
                    $pages = collect();
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
                            <a class="vio-page" href="{{ $vehicles->url($p) }}">{{ $p }}</a>
                        @endif
                    </li>
                @endforeach

                <li>
                    @if($vehicles->hasMorePages())
                        <a class="vio-page" href="{{ $vehicles->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="vio-page vio-page-disabled"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
    @else
        <div class="vio-footer text-end">
            <span class="vio-footer-count">{{ $vehicles->total() }} {{ Str::plural('record', $vehicles->total()) }} total</span>
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
    border: 1px solid #e0f0fb;
}
.filter-card-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .9rem 1.25rem;
    background: linear-gradient(135deg, #f0f9ff 0%, #fff 100%);
    border-bottom: 1px solid #e0f0fb;
}
.filter-icon-wrap {
    width: 34px; height: 34px;
    border-radius: 9px;
    background: linear-gradient(135deg, #0284c7, #0369a1);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: .9rem;
    box-shadow: 0 3px 10px rgba(2,132,199,.3);
    flex-shrink: 0;
}
.filter-clear-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .8rem;
    border-radius: 20px;
    font-size: .75rem; font-weight: 600;
    color: #78716c;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    text-decoration: none;
    transition: all .15s;
}
.filter-clear-btn:hover { background: #0284c7; color: #fff; border-color: #0284c7; }
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
    background: #f0f9ff;
    border-color: #bae6fd;
    color: #a8a29e;
    font-size: .8rem;
}
.filt-input {
    border-color: #bae6fd !important;
    font-size: .82rem !important;
    transition: border-color .15s, box-shadow .15s;
}
.filt-input:focus {
    border-color: #0284c7 !important;
    box-shadow: 0 0 0 3px rgba(2,132,199,.1) !important;
}
.btn-filter-submit {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .42rem 1.1rem;
    background: linear-gradient(135deg, #0284c7, #0369a1);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .8rem; font-weight: 600;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(2,132,199,.3);
    transition: transform .15s, box-shadow .15s;
    white-space: nowrap;
}
.btn-filter-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(2,132,199,.4);
}

/* ─────────────── TABLE CARD ─────────────── */
.vio-table-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    border: 1px solid #e0f0fb;
    overflow: hidden;
}

/* ── Table header ── */
#vehicles-table thead tr {
    background: linear-gradient(135deg, #f0f9ff 0%, #e8f4fc 100%);
}
#vehicles-table thead th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #78716c;
    border-bottom: 2px solid #bae6fd;
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
    background: #f0f9ff !important;
}
.vio-row[data-href]:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: #0284c7;
    border-radius: 0 2px 2px 0;
}
.vio-row td {
    padding-top: .9rem;
    padding-bottom: .9rem;
    border-color: #e8f4fc;
    vertical-align: middle;
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
.status-pending   { background:#fff8e6;color:#b45309;border-color:#fde68a;box-shadow:0 2px 8px rgba(180,83,9,.15); }
.status-settled   { background:#f0fdf4;color:#15803d;border-color:#86efac;box-shadow:0 2px 8px rgba(21,128,61,.15); }

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

/* ── Empty state ── */
.empty-icon-wrap {
    width: 64px; height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #e0f2fe, #dbeafe);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    color: #7dd3fc;
    box-shadow: 0 4px 16px rgba(2,132,199,.1);
}

/* ── Footer ── */
.vio-footer {
    padding: .85rem 1.5rem;
    border-top: 1px solid #e0f0fb;
    background: #f0f9ff;
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
    border: 1.5px solid #bae6fd;
    color: #57534e;
    background: #fff;
    text-decoration: none;
    transition: all .15s;
    cursor: pointer;
}
a.vio-page:hover {
    background: #f0f9ff;
    border-color: #0284c7;
    color: #0284c7;
}
.vio-page-active {
    background: linear-gradient(135deg, #0284c7, #0369a1);
    border-color: #0284c7;
    color: #fff;
    box-shadow: 0 2px 8px rgba(2,132,199,.3);
    cursor: default;
}
.vio-page-disabled {
    color: #d6d3d1;
    border-color: #e0f0fb;
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
</style>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('vh-filter-form');
    if (!form) return;

    function debounce(fn, delay) {
        let t;
        return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), delay); };
    }

    const submit = () => { form.submit(); };

    form.querySelectorAll('input[type="text"]').forEach(input => {
        input.addEventListener('input', debounce(submit, 500));
    });

    form.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', submit);
    });
})();

document.querySelectorAll('.vio-row[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.vio-act-cell')) return;
        if (e.target.closest('a'))             return;
        if (e.target.closest('button'))        return;
        if (e.target.closest('form'))          return;
        window.location.href = row.dataset.href;
    });
});
</script>
@endpush

@endsection
