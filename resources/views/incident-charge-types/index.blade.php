@extends('layouts.app')
@section('title', 'Incident Charge Types')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Charge Types</li>
@endsection

@section('content')

{{-- ── Header card ── --}}
<div class="ict-page-card mb-4">
    <div class="ict-page-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div class="ict-header-icon">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <div>
                <h5 class="ict-header-title mb-0">Incident Charge Types</h5>
                <p class="ict-header-sub mb-0">Criminal charges under Art. 365, Revised Penal Code &mdash; {{ $chargeTypes->count() }} type{{ $chargeTypes->count() !== 1 ? 's' : '' }} defined</p>
            </div>
        </div>
        @if(Auth::user()->isOperator())
        <a href="{{ route('incident-charge-types.create') }}" class="ict-add-btn">
            <i class="bi bi-plus-lg"></i>
            <span>Add Charge Type</span>
        </a>
        @endif
    </div>
</div>

{{-- ── Table card ── --}}
<div class="ict-table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="ict-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;"><span class="ict-th">Charge / Offense</span></th>
                    <th><span class="ict-th">Legal Basis</span></th>
                    <th class="text-center"><span class="ict-th">Usage</span></th>
                    @if(Auth::user()->isOperator())<th class="text-center"><span class="ict-th">Actions</span></th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($chargeTypes as $ct)
                <tr class="ict-row{{ Auth::user()->isOperator() ? ' ict-row-clickable' : '' }}"
                    @if(Auth::user()->isOperator()) data-href="{{ route('incident-charge-types.edit', $ct) }}" @endif>
                    <td style="padding-left:1.4rem;">
                        <span class="ict-name">{{ $ct->name }}</span>
                        @if(Auth::user()->isOperator())
                        <div style="font-size:.67rem;color:#a8a29e;margin-top:2px;">
                            <i class="bi bi-pencil me-1" style="font-size:.6rem;"></i>Click row to edit
                        </div>
                        @endif
                    </td>
                    <td class="ict-desc">{{ $ct->description ?? '—' }}</td>
                    <td class="text-center ict-act-cell">
                        @php
                            $uc    = $ct->incident_motorists_count;
                            $ucCls = match(true) {
                                $uc === 0 => 'ict-use-none',
                                $uc <= 5  => 'ict-use-low',
                                $uc <= 15 => 'ict-use-mid',
                                default   => 'ict-use-high',
                            };
                        @endphp
                        <span class="ict-use-badge {{ $ucCls }}">{{ $uc }}</span>
                    </td>
                    @if(Auth::user()->isOperator())
                    <td class="text-center ict-act-cell">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('incident-charge-types.edit', $ct) }}"
                               class="ict-act-btn ict-act-edit"
                               title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form method="POST" action="{{ route('incident-charge-types.destroy', $ct) }}"
                                  class="d-inline"
                                  data-confirm="Delete this charge type? This cannot be undone.">
                                @csrf @method('DELETE')
                                <button class="ict-act-btn ict-act-del"
                                        {{ $ct->incident_motorists_count > 0 ? 'disabled' : '' }}
                                        title="{{ $ct->incident_motorists_count > 0 ? 'Cannot delete — in use by incident records' : 'Delete' }}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::user()->isOperator() ? 4 : 3 }}" class="py-5">
                        <div class="text-center">
                            <i class="bi bi-shield-exclamation" style="font-size:2rem;color:#d6d3d1;display:block;margin-bottom:.5rem;"></i>
                            <span style="color:#a8a29e;font-size:.88rem;">No charge types defined yet.</span>
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
.ict-page-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
.ict-page-header { padding: 1.1rem 1.4rem; }
.ict-header-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #7c3aed, #5b21b6);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    box-shadow: 0 3px 10px rgba(124,58,237,.35);
    flex-shrink: 0;
}
.ict-header-title { font-size: 1rem; font-weight: 700; color: #1c1917; }
.ict-header-sub   { font-size: .74rem; color: #a8a29e; }
.ict-add-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .44rem 1.05rem;
    border-radius: 10px;
    font-size: .8rem; font-weight: 700;
    background: linear-gradient(135deg, #7c3aed, #5b21b6);
    color: #fff;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(124,58,237,.3);
    transition: all .15s;
    white-space: nowrap;
}
.ict-add-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(124,58,237,.45); }

/* ─── TABLE CARD ─── */
.ict-table-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
#ict-table thead tr { background: linear-gradient(135deg, #faf5ff 0%, #f5f0fe 100%); }
.ict-th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #78716c;
}
#ict-table thead th {
    border-bottom: 2px solid #e9d5ff;
    padding-top: .9rem;
    padding-bottom: .9rem;
}
.ict-row { transition: background .15s; }
.ict-row:hover { background: #faf5ff !important; }
.ict-row-clickable { cursor: pointer; }
.ict-row-clickable:hover td:first-child { position: relative; }
.ict-row-clickable:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: #7c3aed;
    border-radius: 0 2px 2px 0;
}
.ict-row td {
    padding-top: .9rem;
    padding-bottom: .9rem;
    border-color: #f3e8ff;
    vertical-align: middle;
}

/* ─── CELLS ─── */
.ict-name {
    font-size: .86rem;
    font-weight: 700;
    color: #1c1917;
}
.ict-desc {
    font-size: .82rem;
    color: #78716c;
    max-width: 280px;
}

/* ─── USAGE BADGES ─── */
.ict-use-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 30px;
    padding: .22rem .6rem;
    border-radius: 20px;
    border: 1.5px solid;
    font-size: .72rem; font-weight: 700;
}
.ict-use-none { background:#f1f5f9;color:#94a3b8;border-color:#cbd5e1; }
.ict-use-low  { background:#f0f9ff;color:#0369a1;border-color:#7dd3fc; }
.ict-use-mid  { background:#fffbeb;color:#b45309;border-color:#fde68a; }
.ict-use-high { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }

/* ─── ACTION BUTTONS ─── */
.ict-act-btn {
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
.ict-act-edit { background:#faf5ff;color:#7c3aed;border-color:#d8b4fe; }
.ict-act-edit:hover { background:#7c3aed;color:#fff;border-color:#7c3aed;transform:translateY(-2px);box-shadow:0 4px 12px rgba(124,58,237,.3); }
.ict-act-del  { background:#fff1f2;color:#b91c1c;border-color:#fca5a5; }
.ict-act-del:hover:not(:disabled) { background:#dc2626;color:#fff;border-color:#dc2626;transform:translateY(-2px);box-shadow:0 4px 12px rgba(220,38,38,.3); }
.ict-act-del:disabled { opacity:.4;cursor:not-allowed; }
</style>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.ict-row-clickable[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.ict-act-cell')) return;
        if (e.target.closest('a'))             return;
        if (e.target.closest('button'))        return;
        if (e.target.closest('form'))          return;
        window.location.href = row.dataset.href;
    });
});
</script>
@endpush
