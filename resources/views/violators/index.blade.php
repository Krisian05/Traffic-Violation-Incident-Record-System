@extends('layouts.app')
@section('title', 'Motorists')

@section('topbar-sub')
    <i class="bi bi-people-fill me-1" style="color:#dc2626;"></i>
    {{ $violators->total() }} total {{ Str::plural('record', $violators->total()) }}
    @if($search || ($plate ?? ''))
        &nbsp;·&nbsp; <span style="color:#d97706;">Filtered</span>
    @endif
@endsection

@section('content')

{{-- ── Filter Card ── --}}
<div class="vlt-filter-card mb-4">
    <div class="vlt-filter-header">
        <div class="d-flex align-items-center gap-2">
            <span class="vlt-filter-icon">
                <i class="bi bi-sliders2-vertical"></i>
            </span>
            <div>
                <div class="fw-700" style="font-size:.88rem;color:#1c1917;">Search &amp; Filter</div>
                <div style="font-size:.72rem;color:#a8a29e;">Find motorist records</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto">
            @if($search || ($plate ?? ''))
                <a href="{{ route('violators.index') }}" class="vlt-clear-btn">
                    <i class="bi bi-x-lg"></i> Clear filters
                </a>
            @endif
            @if(Auth::user()->isOperator())
                <a href="{{ route('violators.create') }}" class="vlt-add-btn">
                    <i class="bi bi-person-plus-fill"></i> Add Motorist
                </a>
            @endif
        </div>
    </div>
    <div class="vlt-filter-body">
        <form method="GET" action="{{ route('violators.index') }}">
            <div class="d-flex flex-wrap align-items-end gap-2">

                <div style="flex:2.5;min-width:0;">
                    <label class="vlt-filter-label"><i class="bi bi-person-bounding-box me-1"></i>Name / License No.</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text vlt-filt-icon"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control vlt-filt-input"
                            placeholder="e.g. Juan Dela Cruz or A-1234-56"
                            value="{{ $search }}">
                    </div>
                </div>

                <div style="flex:1.6;min-width:0;">
                    <label class="vlt-filter-label"><i class="bi bi-car-front me-1"></i>Plate Number</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text vlt-filt-icon"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" name="plate" class="form-control vlt-filt-input"
                            placeholder="e.g. ABC 1234"
                            value="{{ $plate ?? '' }}">
                    </div>
                </div>

                <div style="flex-shrink:0;">
                    <button type="submit" class="vlt-btn-search">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Table Card ── --}}
<div class="vlt-table-card">

    <div class="table-responsive">
        <table class="table align-middle mb-0" id="violators-table">
            <thead>
                <tr>
                    <th style="padding-left:1.75rem;text-align:left;">
                        <span class="vlt-th-inner"><i class="bi bi-person-fill me-1"></i>Violator</span>
                    </th>
                    <th class="text-center">
                        <span class="vlt-th-inner"><i class="bi bi-credit-card-fill me-1"></i>License No.</span>
                    </th>
                    <th class="text-center">
                        <span class="vlt-th-inner"><i class="bi bi-gender-ambiguous me-1"></i>Gender</span>
                    </th>
                    <th class="text-center">
                        <span class="vlt-th-inner"><i class="bi bi-telephone-fill me-1"></i>Contact</span>
                    </th>
                    <th class="text-center">
                        <span class="vlt-th-inner"><i class="bi bi-shield-exclamation me-1"></i>Violations</span>
                    </th>
                    <th class="text-center">
                        <span class="vlt-th-inner"><i class="bi bi-lightning-charge-fill me-1"></i>Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($violators as $v)
                <tr class="vlt-row" data-href="{{ route('violators.show', $v) }}">
                    {{-- Violator Name --}}
                    <td style="padding-left:1.75rem;text-align:left;">
                        <div class="d-flex align-items-center gap-2">
                            <div>
                            <a href="{{ route('violators.show', $v) }}"
                               class="vlt-name fw-600 text-decoration-none"
                               title="View motorist profile">
                                {{ $v->full_name }}
                            </a>
                            <div style="font-size:.69rem;color:#a8a29e;margin-top:2px;">
                                <i class="bi bi-eye me-1" style="font-size:.6rem;"></i>Click row to view profile
                            </div>
                            </div>
                            @if($v->overdue_count > 0)
                                <span class="vlt-status-dot vlt-dot-overdue" title="{{ $v->overdue_count }} overdue violation{{ $v->overdue_count > 1 ? 's' : '' }}"></span>
                            @elseif($v->pending_count > 0)
                                <span class="vlt-status-dot vlt-dot-pending" title="{{ $v->pending_count }} pending violation{{ $v->pending_count > 1 ? 's' : '' }}"></span>
                            @endif
                        </div>{{-- d-flex --}}
                    </td>

                    {{-- License No. --}}
                    <td class="text-center">
                        @if($v->license_number)
                            <span class="vlt-license-pill">
                                <i class="bi bi-upc me-1" style="font-size:.65rem;color:#a8a29e;"></i>
                                {{ $v->license_number }}
                            </span>
                        @else
                            <span class="vlt-no-data">—</span>
                        @endif
                    </td>

                    {{-- Gender --}}
                    <td class="text-center">
                        @if($v->gender)
                            <span class="vlt-gender-tag vlt-gender-{{ strtolower($v->gender) }}">
                                <i class="bi bi-{{ $v->gender === 'Male' ? 'gender-male' : ($v->gender === 'Female' ? 'gender-female' : 'gender-ambiguous') }} me-1"></i>
                                {{ $v->gender }}
                            </span>
                        @else
                            <span class="vlt-no-data">—</span>
                        @endif
                    </td>

                    {{-- Contact --}}
                    <td class="text-center">
                        @if($v->contact_number)
                            <span class="vlt-contact-chip">
                                <i class="bi bi-telephone-fill me-1" style="color:#a8a29e;font-size:.7rem;"></i>
                                {{ $v->contact_number }}
                            </span>
                        @else
                            <span class="vlt-no-data">—</span>
                        @endif
                    </td>

                    {{-- Violations Count --}}
                    <td class="text-center">
                        @if($v->violations_count >= 3)
                            <span class="vlt-vio-badge vlt-recidivist" title="Recidivist">
                                <i class="bi bi-fire me-1"></i>{{ $v->violations_count }}
                            </span>
                        @elseif($v->violations_count == 2)
                            <span class="vlt-vio-badge vlt-repeat" title="Repeat Offender">
                                <i class="bi bi-shield-exclamation me-1"></i>{{ $v->violations_count }}
                            </span>
                        @elseif($v->violations_count == 1)
                            <span class="vlt-vio-badge vlt-once">
                                <i class="bi bi-record-circle-fill me-1"></i>{{ $v->violations_count }}
                            </span>
                        @else
                            <span class="vlt-vio-badge vlt-clean">
                                <i class="bi bi-shield-fill-check me-1"></i>0
                            </span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="text-center vlt-act-cell">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('violators.show', $v) }}"
                               class="vlt-act-btn vlt-act-view" title="View Profile">
                                <i class="bi bi-eye-fill"></i>
                                <span>View</span>
                            </a>
                            @if(Auth::user()->isOperator())
                            <a href="{{ route('violators.edit', $v) }}"
                               class="vlt-act-btn vlt-act-edit"
                               title="Edit Record"
                               aria-label="Edit {{ $v->full_name }}">
                                <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-5">
                        <div class="text-center">
                            <div class="vlt-empty-icon mx-auto mb-3">
                                <i class="bi bi-person-slash"></i>
                            </div>
                            <p class="fw-600 mb-1" style="color:#57534e;font-size:.95rem;">
                                @if($search || ($plate ?? ''))
                                    No results found
                                @else
                                    No motorist records yet
                                @endif
                            </p>
                            <p class="mb-3" style="font-size:.83rem;color:#a8a29e;">
                                @if($search)
                                    No violators match "<strong>{{ $search }}</strong>".
                                    <a href="{{ route('violators.index') }}" style="color:#dc2626;">Clear search</a>
                                @elseif($plate ?? '')
                                    No violators found with plate "<strong>{{ $plate }}</strong>".
                                    <a href="{{ route('violators.index') }}" style="color:#dc2626;">Clear filter</a>
                                @else
                                    Start by adding the first motorist record.
                                @endif
                            </p>
                            @if(!$search && !($plate ?? '') && Auth::user()->isOperator())
                                <a href="{{ route('violators.create') }}" class="vlt-add-btn">
                                    <i class="bi bi-person-plus-fill"></i> Add First Motorist
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    @if($violators->hasPages())
        <div class="vlt-footer d-flex justify-content-between align-items-center">
            <div class="vlt-footer-count">
                Showing <strong>{{ $violators->firstItem() }}</strong>–<strong>{{ $violators->lastItem() }}</strong>
                of <strong>{{ $violators->total() }}</strong> records
            </div>
            {{ $violators->links() }}
        </div>
    @else
        <div class="vlt-footer text-end">
            <span class="vlt-footer-count">{{ $violators->total() }} {{ Str::plural('record', $violators->total()) }} total</span>
        </div>
    @endif
</div>

<style>
/* ─────────────── FILTER CARD ─────────────── */
.vlt-filter-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden;
    border: 1px solid #f0ebe3;
}
.vlt-filter-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .9rem 1.25rem;
    background: linear-gradient(135deg, #fdf8f0 0%, #fff 100%);
    border-bottom: 1px solid #f0ebe3;
}
.vlt-filter-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: .9rem;
    box-shadow: 0 3px 10px rgba(220,38,38,.3);
    flex-shrink: 0;
}
.vlt-clear-btn {
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
.vlt-clear-btn:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
.vlt-add-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .32rem 1rem;
    border-radius: 8px;
    font-size: .8rem; font-weight: 600;
    color: #fff;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    border: none;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(220,38,38,.3);
    transition: transform .15s, box-shadow .15s;
    cursor: pointer;
}
.vlt-add-btn:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(220,38,38,.4);
}
.vlt-filter-body { padding: 1rem 1.25rem; }

.vlt-filter-label {
    display: block;
    font-size: .7rem; font-weight: 700;
    color: #78716c;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: .3rem;
}
.vlt-filt-icon {
    background: #fdf8f0;
    border-color: #e7e0d6;
    color: #a8a29e;
    font-size: .8rem;
}
.vlt-filt-input {
    border-color: #e7e0d6 !important;
    font-size: .82rem !important;
    transition: border-color .15s, box-shadow .15s;
}
.vlt-filt-input:focus {
    border-color: #dc2626 !important;
    box-shadow: 0 0 0 3px rgba(220,38,38,.1) !important;
}
.vlt-btn-search {
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
.vlt-btn-search:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(220,38,38,.4);
}

/* ─────────────── TABLE CARD ─────────────── */
.vlt-table-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    border: 1px solid #f0ebe3;
    overflow: hidden;
}

/* ── Table header ── */
#violators-table thead tr {
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
}
#violators-table thead th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #78716c;
    border-bottom: 2px solid #ece5da;
    padding-top: .95rem;
    padding-bottom: .95rem;
}
.vlt-th-inner {
    display: inline-flex;
    align-items: center;
}

/* ── Row ── */
.vlt-row {
    transition: background .18s, box-shadow .18s;
    position: relative;
}
.vlt-row[data-href] { cursor: pointer; }
.vlt-row:hover {
    background: #fff5f5 !important;
}
.vlt-row[data-href]:hover td:not(.vlt-act-cell) {
    position: relative;
}
.vlt-row[data-href]:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: #dc2626;
    border-radius: 0 2px 2px 0;
}
.vlt-row td {
    padding-top: .9rem;
    padding-bottom: .9rem;
    border-color: #f5f0ea;
    vertical-align: middle;
    text-align: center;
}

/* ── Violator name ── */
.vlt-name {
    color: #1c1917;
    font-size: .88rem;
    font-weight: 600;
    transition: color .15s;
}
.vlt-name:hover { color: #dc2626; }

/* ── License pill ── */
.vlt-license-pill {
    display: inline-flex;
    align-items: center;
    background: #f5f0e8;
    color: #57534e;
    font-size: .73rem;
    font-weight: 700;
    padding: .25rem .65rem;
    border-radius: 6px;
    border: 1px solid #ddd0be;
    font-family: ui-monospace, 'Cascadia Code', monospace;
    letter-spacing: .04em;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}

/* ── Gender tag ── */
.vlt-gender-tag {
    display: inline-flex;
    align-items: center;
    font-size: .74rem;
    font-weight: 600;
    padding: .25rem .6rem;
    border-radius: 20px;
    border: 1.5px solid;
}
.vlt-gender-male   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.vlt-gender-female { background: #fdf2f8; color: #be185d; border-color: #fbcfe8; }
.vlt-gender-other  { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }

/* ── Contact chip ── */
.vlt-contact-chip {
    display: inline-flex;
    align-items: center;
    font-size: .82rem;
    color: #57534e;
}

/* ── Violation badges ── */
.vlt-vio-badge {
    display: inline-flex;
    align-items: center;
    font-size: .72rem;
    font-weight: 700;
    padding: .28rem .7rem;
    border-radius: 20px;
    border: 1.5px solid;
    transition: transform .15s;
}
.vlt-vio-badge:hover { transform: scale(1.06); }
.vlt-recidivist { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; box-shadow: 0 2px 8px rgba(185,28,28,.15); }
.vlt-repeat     { background: #fffbeb; color: #92400e; border-color: #fcd34d; box-shadow: 0 2px 8px rgba(146,64,14,.12); }
.vlt-once       { background: #f0f9ff; color: #0369a1; border-color: #7dd3fc; box-shadow: 0 2px 8px rgba(3,105,161,.12); }
.vlt-clean      { background: #f0fdf4; color: #15803d; border-color: #86efac; box-shadow: 0 2px 8px rgba(21,128,61,.12); }

/* ── Status indicator dots ── */
.vlt-status-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.vlt-dot-overdue {
    background: #dc2626;
    box-shadow: 0 0 0 3px rgba(220,38,38,.2);
    animation: pulse-dot 1.5s infinite;
}
.vlt-dot-pending {
    background: #d97706;
    box-shadow: 0 0 0 3px rgba(217,119,6,.2);
}
@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 3px rgba(220,38,38,.2); }
    50%       { box-shadow: 0 0 0 5px rgba(220,38,38,.0); }
}

/* ── No data ── */
.vlt-no-data { color: #c4b8a8; font-weight: 600; }

/* ── Action buttons ── */
.vlt-act-btn {
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
.vlt-act-view {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
    box-shadow: 0 1px 4px rgba(29,78,216,.1);
}
.vlt-act-view:hover {
    background: #1d4ed8;
    color: #fff;
    border-color: #1d4ed8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(29,78,216,.3);
}
.vlt-act-edit {
    background: #fdf8f0;
    color: #b45309;
    border-color: #fde68a;
    box-shadow: 0 1px 4px rgba(180,83,9,.1);
}
.vlt-act-edit:hover {
    background: #d97706;
    color: #fff;
    border-color: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(217,119,6,.3);
}

/* ── Empty state ── */
.vlt-empty-icon {
    width: 64px; height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #fff1f2, #fce7f3);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    color: #fca5a5;
    box-shadow: 0 4px 16px rgba(220,38,38,.1);
}

/* ── Footer ── */
.vlt-footer {
    padding: .85rem 1.5rem;
    border-top: 1px solid #f0ebe3;
    background: #fdf8f0;
}
.vlt-footer-count {
    font-size: .8rem;
    color: #78716c;
}

.fw-600 { font-weight: 600; }
.fw-700 { font-weight: 700; }
</style>

<script>
(function () {
    var form   = document.querySelector('.vlt-filter-body form');
    var inputs = form ? form.querySelectorAll('input[type="text"]') : [];
    var timer;

    inputs.forEach(function (input) {
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                form.submit();
            }, 350);
        });
    });
})();
</script>

<script>
/* ── Clickable rows — navigate to motorist profile on row click ── */
document.querySelectorAll('.vlt-row[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.vlt-act-cell')) return;
        if (e.target.closest('a'))             return;
        if (e.target.closest('button'))        return;
        window.location.href = row.dataset.href;
    });
});
</script>

@endsection
