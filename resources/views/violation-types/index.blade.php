@extends('layouts.app')
@section('title', 'Violation Types')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Violation Types</li>
@endsection

@section('content')

{{-- ── Header card ── --}}
<div class="vt-page-card mb-4">
    <div class="vt-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="vt-header-icon">
                <i class="bi bi-tags-fill"></i>
            </div>
            <div>
                <h5 class="vt-header-title mb-0">Violation Types</h5>
                <p class="vt-header-sub mb-0">{{ $types->count() }} type{{ $types->count() !== 1 ? 's' : '' }} defined</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isProvinceAdmin())
            <form method="GET" action="{{ route('violation-types.index') }}" class="d-flex align-items-center gap-2">
                <select name="lgu_id" class="form-select form-select-sm shadow-none rounded-pill" onchange="this.form.submit()" style="min-width: 180px; font-size: .82rem;">
                    <option value="">All LGUs & Global</option>
                    <option value="global" {{ request('lgu_id') === 'global' ? 'selected' : '' }}>Global Defaults Only</option>
                    @foreach($lgus as $lguItem)
                        <option value="{{ $lguItem->id }}" {{ request('lgu_id') == $lguItem->id ? 'selected' : '' }}>
                            {{ $lguItem->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif

            @if(Auth::user()->isSuperAdmin() || Auth::user()->isProvinceAdmin() || Auth::user()->isLguAdmin())
            <a href="{{ route('violation-types.create') }}" class="vt-add-btn">
                <i class="bi bi-plus-lg"></i>
                <span>Add Type</span>
            </a>
            @endif
        </div>
    </div>
</div>

{{-- ── Table card ── --}}
<div class="vt-table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="vt-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;"><span class="vt-th">Name</span></th>
                    <th><span class="vt-th">Scope / LGU</span></th>
                    <th><span class="vt-th">Description</span></th>
                    <th class="text-end"><span class="vt-th">Fine Amount</span></th>
                    @if(!Auth::user()->isCashier() && !Auth::user()->isTreasurer())<th class="text-center"><span class="vt-th">Usage</span></th>@endif
                    @if(Auth::user()->isSuperAdmin() || Auth::user()->isProvinceAdmin() || Auth::user()->isLguAdmin())<th class="text-center"><span class="vt-th">Actions</span></th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                @php
                    $canManage = Auth::user()->isSuperAdmin() || Auth::user()->isProvinceAdmin() || (Auth::user()->isLguAdmin() && (int)$type->lgu_id === (int)Auth::user()->lgu_id);
                @endphp
                <tr class="vt-row{{ $canManage ? ' vt-row-clickable' : '' }}"
                    @if($canManage) data-href="{{ route('violation-types.edit', $type) }}" @endif>
                    <td style="padding-left:1.4rem;">
                        <span class="vt-name">{{ $type->name }}</span>
                        @if($type->code)
                            <span class="badge bg-light text-secondary border ms-1" style="font-size:.68rem;">{{ $type->code }}</span>
                        @endif
                        @if($canManage)
                        <div style="font-size:.67rem;color:#a8a29e;margin-top:2px;">
                            <i class="bi bi-pencil me-1" style="font-size:.6rem;"></i>Click row to edit
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($type->lgu)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-2 py-1" style="font-size:.72rem;">
                                <i class="bi bi-building me-1"></i>{{ $type->lgu->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-medium px-2 py-1" style="font-size:.72rem;">
                                <i class="bi bi-globe me-1"></i>Global Default
                            </span>
                        @endif
                    </td>
                    <td class="vt-desc">{{ $type->description ?? '—' }}</td>
                    <td class="text-end" style="padding-right:1.4rem;">
                        @if($type->fine_amount)
                            <span class="vt-fine-pill">₱ {{ number_format($type->fine_amount, 2) }}</span>
                        @else
                            <span class="vt-no-data">—</span>
                        @endif
                    </td>
                    @if(!Auth::user()->isCashier() && !Auth::user()->isTreasurer())
                    <td class="text-center vt-act-cell">
                        @php
                            $uc    = $type->violations_count;
                            $ucCls = match(true) {
                                $uc === 0 => 'vt-use-none',
                                $uc <= 5  => 'vt-use-low',
                                $uc <= 15 => 'vt-use-mid',
                                default   => 'vt-use-high',
                            };
                        @endphp
                        @if($uc > 0)
                            <a href="{{ route('violations.index', ['type' => $type->id]) }}"
                               class="vt-use-badge {{ $ucCls }} text-decoration-none"
                               title="View {{ $uc }} violation{{ $uc !== 1 ? 's' : '' }} of this type">{{ $uc }}</a>
                        @else
                            <span class="vt-use-badge {{ $ucCls }}">{{ $uc }}</span>
                        @endif
                    </td>
                    @endif
                    @if(Auth::user()->isSuperAdmin() || Auth::user()->isProvinceAdmin() || Auth::user()->isLguAdmin())
                    <td class="text-center vt-act-cell">
                        @if($canManage)
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('violation-types.edit', $type) }}"
                               class="vt-act-btn vt-act-edit"
                               title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form method="POST" action="{{ route('violation-types.destroy', $type) }}"
                                  class="d-inline"
                                  data-confirm="Delete this violation type? This cannot be undone.">
                                @csrf @method('DELETE')
                                <button class="vt-act-btn vt-act-del"
                                        {{ $type->violations_count > 0 ? 'disabled' : '' }}
                                        title="{{ $type->violations_count > 0 ? 'Cannot delete — has violation records' : 'Delete' }}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-5">
                        <div class="text-center">
                            <i class="bi bi-tags" style="font-size:2rem;color:#d6d3d1;display:block;margin-bottom:.5rem;"></i>
                            <span style="color:#a8a29e;font-size:.88rem;">No violation types defined yet.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
/* ─── PAGE HEADER CARD ─── */
.vt-page-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
.vt-page-header { padding: 1.1rem 1.4rem; }
.vt-header-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    box-shadow: 0 3px 10px rgba(220,38,38,.35);
    flex-shrink: 0;
}
.vt-header-title { font-size: 1rem; font-weight: 700; color: #1c1917; }
.vt-header-sub   { font-size: .74rem; color: #a8a29e; }
.vt-add-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .44rem 1.05rem;
    border-radius: 10px;
    font-size: .8rem; font-weight: 700;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(220,38,38,.3);
    transition: all .15s;
    white-space: nowrap;
}
.vt-add-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(220,38,38,.45); }

/* ─── TABLE CARD ─── */
.vt-table-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
#vt-table thead tr { background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%); }
.vt-th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #78716c;
}
#vt-table thead th {
    border-bottom: 2px solid #ece5da;
    padding-top: .9rem;
    padding-bottom: .9rem;
}
.vt-row { transition: background .15s; }
.vt-row:hover { background: #fffbf8 !important; }
.vt-row-clickable { cursor: pointer; }
.vt-row-clickable:hover td:first-child { position: relative; }
.vt-row-clickable:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: #dc2626;
    border-radius: 0 2px 2px 0;
}
.vt-row td {
    padding-top: .9rem;
    padding-bottom: .9rem;
    border-color: #f5f0ea;
    vertical-align: middle;
}

/* ─── CELLS ─── */
.vt-name {
    font-size: .86rem;
    font-weight: 700;
    color: #1c1917;
}
.vt-desc {
    font-size: .82rem;
    color: #78716c;
    max-width: 340px;
}
.vt-fine-pill {
    display: inline-flex; align-items: center;
    background: #f0fdf4; color: #15803d;
    font-size: .76rem; font-weight: 700;
    padding: .22rem .65rem;
    border-radius: 8px;
    border: 1px solid #86efac;
    font-family: ui-monospace, 'Cascadia Code', monospace;
}
.vt-no-data { color: #d6d3d1; font-size: .85rem; }

/* ─── USAGE BADGES ─── */
.vt-use-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 30px;
    padding: .22rem .6rem;
    border-radius: 20px;
    border: 1.5px solid;
    font-size: .72rem; font-weight: 700;
}
.vt-use-none { background:#f1f5f9;color:#94a3b8;border-color:#cbd5e1; }
.vt-use-low  { background:#f0f9ff;color:#0369a1;border-color:#7dd3fc; }
.vt-use-mid  { background:#fffbeb;color:#b45309;border-color:#fde68a; }
.vt-use-high { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }

/* ─── ACTION BUTTONS ─── */
.vt-act-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px;
    border-radius: 8px;
    font-size: .78rem;
    text-decoration: none;
    border: 1.5px solid transparent;
    cursor: pointer;
    background: none;
    transition: all .18s;
    padding: 0;
}
.vt-act-edit { background:#fdf8f0;color:#b45309;border-color:#fde68a; }
.vt-act-edit:hover { background:#d97706;color:#fff;border-color:#d97706;transform:translateY(-2px);box-shadow:0 4px 12px rgba(217,119,6,.3); }
.vt-act-del  { background:#fff1f2;color:#b91c1c;border-color:#fca5a5; }
.vt-act-del:hover:not(:disabled) { background:#dc2626;color:#fff;border-color:#dc2626;transform:translateY(-2px);box-shadow:0 4px 12px rgba(220,38,38,.3); }
.vt-act-del:disabled { opacity:.4;cursor:not-allowed; }
</style>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.vt-row-clickable[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.vt-act-cell')) return;
        if (e.target.closest('a'))            return;
        if (e.target.closest('button'))       return;
        if (e.target.closest('form'))         return;
        window.location.href = row.dataset.href;
    });
});
</script>
@endpush
