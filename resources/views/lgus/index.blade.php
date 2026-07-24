@extends('layouts.app')
@section('title', 'LGUs')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">LGUs</li>
@endsection

@section('content')

{{-- ── Header card ── --}}
<div class="lgu-page-card mb-4">
    <div class="lgu-page-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div class="lgu-header-icon">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <h5 class="lgu-header-title mb-0">LGUs</h5>
                <p class="lgu-header-sub mb-0">{{ $lgus->count() }} municipalit{{ $lgus->count() !== 1 ? 'ies' : 'y' }}/cit{{ $lgus->count() !== 1 ? 'ies' : 'y' }} onboarded</p>
            </div>
        </div>
        <a href="{{ route('lgus.create') }}" class="lgu-add-btn">
            <i class="bi bi-plus-lg"></i>
            <span>Add LGU</span>
        </a>
    </div>
</div>

{{-- ── Table card ── --}}
<div class="lgu-table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="lgu-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;"><span class="lgu-th">Code</span></th>
                    <th><span class="lgu-th">Name</span></th>
                    <th><span class="lgu-th">Province</span></th>
                    <th><span class="lgu-th">PSGC City Code</span></th>
                    <th class="text-center"><span class="lgu-th">Users</span></th>
                    <th class="text-center"><span class="lgu-th">Violations</span></th>
                    <th class="text-center"><span class="lgu-th">Incidents</span></th>
                    <th class="text-center"><span class="lgu-th">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($lgus as $lgu)
                <tr class="lgu-row lgu-row-clickable" data-href="{{ route('lgus.edit', $lgu) }}">
                    <td style="padding-left:1.4rem;">
                        <span class="lgu-code-pill">{{ $lgu->code }}</span>
                    </td>
                    <td>
                        <span class="lgu-name">{{ $lgu->name }}</span>
                        <div style="font-size:.67rem;color:#a8a29e;margin-top:2px;">
                            <i class="bi bi-pencil me-1" style="font-size:.6rem;"></i>Click row to edit
                        </div>
                    </td>
                    <td class="lgu-desc">{{ $lgu->province }}</td>
                    <td class="lgu-desc">{{ $lgu->psgc_city_code ?? '—' }}</td>
                    <td class="text-center"><span class="lgu-count-badge">{{ $lgu->users_count }}</span></td>
                    <td class="text-center"><span class="lgu-count-badge">{{ $lgu->violations_count }}</span></td>
                    <td class="text-center"><span class="lgu-count-badge">{{ $lgu->incidents_count }}</span></td>
                    <td class="text-center lgu-act-cell">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('lgus.edit', $lgu) }}"
                               class="lgu-act-btn lgu-act-edit"
                               title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form method="POST" action="{{ route('lgus.destroy', $lgu) }}"
                                  class="d-inline"
                                  data-confirm="Delete this LGU? This cannot be undone.">
                                @csrf @method('DELETE')
                                @php $hasLinks = $lgu->users_count > 0 || $lgu->violations_count > 0 || $lgu->incidents_count > 0; @endphp
                                <button class="lgu-act-btn lgu-act-del"
                                        {{ $hasLinks ? 'disabled' : '' }}
                                        title="{{ $hasLinks ? 'Cannot delete — has linked users or records' : 'Delete' }}">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-5">
                        <div class="text-center">
                            <i class="bi bi-building" style="font-size:2rem;color:#d6d3d1;display:block;margin-bottom:.5rem;"></i>
                            <span style="color:#a8a29e;font-size:.88rem;">No LGUs onboarded yet.</span>
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
.lgu-page-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
.lgu-page-header { padding: 1.1rem 1.4rem; }
.lgu-header-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0369a1, #075985);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    box-shadow: 0 3px 10px rgba(3,105,161,.35);
    flex-shrink: 0;
}
.lgu-header-title { font-size: 1rem; font-weight: 700; color: #1c1917; }
.lgu-header-sub   { font-size: .74rem; color: #a8a29e; }
.lgu-add-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .44rem 1.05rem;
    border-radius: 10px;
    font-size: .8rem; font-weight: 700;
    background: linear-gradient(135deg, #0369a1, #075985);
    color: #fff;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(3,105,161,.3);
    transition: all .15s;
    white-space: nowrap;
}
.lgu-add-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(3,105,161,.45); }

/* ─── TABLE CARD ─── */
.lgu-table-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
#lgu-table thead tr { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); }
.lgu-th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #78716c;
}
#lgu-table thead th {
    border-bottom: 2px solid #ece5da;
    padding-top: .9rem;
    padding-bottom: .9rem;
}
.lgu-row { transition: background .15s; }
.lgu-row:hover { background: #f8fcff !important; }
.lgu-row-clickable { cursor: pointer; }
.lgu-row td {
    padding-top: .9rem;
    padding-bottom: .9rem;
    border-color: #f5f0ea;
    vertical-align: middle;
}

/* ─── CELLS ─── */
.lgu-code-pill {
    display: inline-flex; align-items: center;
    background: #f0f9ff; color: #0369a1;
    font-size: .76rem; font-weight: 700;
    padding: .22rem .65rem;
    border-radius: 8px;
    border: 1px solid #7dd3fc;
    font-family: ui-monospace, 'Cascadia Code', monospace;
}
.lgu-name {
    font-size: .86rem;
    font-weight: 700;
    color: #1c1917;
}
.lgu-desc {
    font-size: .82rem;
    color: #78716c;
}
.lgu-count-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 30px;
    padding: .22rem .6rem;
    border-radius: 20px;
    border: 1.5px solid #cbd5e1;
    background: #f1f5f9; color: #64748b;
    font-size: .72rem; font-weight: 700;
}

/* ─── ACTION BUTTONS ─── */
.lgu-act-btn {
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
.lgu-act-edit { background:#fdf8f0;color:#b45309;border-color:#fde68a; }
.lgu-act-edit:hover { background:#d97706;color:#fff;border-color:#d97706;transform:translateY(-2px);box-shadow:0 4px 12px rgba(217,119,6,.3); }
.lgu-act-del  { background:#fff1f2;color:#b91c1c;border-color:#fca5a5; }
.lgu-act-del:hover:not(:disabled) { background:#dc2626;color:#fff;border-color:#dc2626;transform:translateY(-2px);box-shadow:0 4px 12px rgba(220,38,38,.3); }
.lgu-act-del:disabled { opacity:.4;cursor:not-allowed; }
</style>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.lgu-row-clickable[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.lgu-act-cell')) return;
        if (e.target.closest('a'))            return;
        if (e.target.closest('button'))       return;
        if (e.target.closest('form'))         return;
        window.location.href = row.dataset.href;
    });
});
</script>
@endpush
