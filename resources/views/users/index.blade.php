@extends('layouts.app')
@section('title', 'User Management')

@section('content')

{{-- ── Header card ── --}}
<div class="usr-page-card mb-4">
    <div class="usr-page-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div class="usr-header-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <h5 class="usr-header-title mb-0">User Accounts</h5>
                <p class="usr-header-sub mb-0">{{ $users->count() }} account{{ $users->count() !== 1 ? 's' : '' }} registered</p>
            </div>
        </div>
        <a href="{{ route('users.create') }}" class="usr-add-btn">
            <i class="bi bi-person-plus-fill"></i>
            <span>Add User</span>
        </a>
    </div>
</div>

{{-- ── Table card ── --}}
<div class="usr-table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="usr-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;"><span class="usr-th">User</span></th>
                    <th><span class="usr-th">Username</span></th>
                    <th><span class="usr-th">Role</span></th>
                    <th><span class="usr-th">Joined</span></th>
                    <th class="text-center"><span class="usr-th">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="usr-row usr-row-clickable" data-href="{{ route('users.edit', $user) }}">
                    <td style="padding-left:1.4rem;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="usr-avatar {{ $user->isOperator() ? 'usr-avatar--op' : 'usr-avatar--to' }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="usr-name">
                                    {{ $user->name }}
                                    @if($user->id === Auth::id())
                                        <span class="usr-you-badge">You</span>
                                    @endif
                                </div>
                                <div style="font-size:.67rem;color:#a8a29e;margin-top:2px;">
                                    <i class="bi bi-pencil me-1" style="font-size:.6rem;"></i>Click row to edit
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="usr-username">{{ $user->username }}</span>
                    </td>
                    <td>
                        @if($user->isAdmin())
                            <span class="usr-role-badge usr-role-admin">
                                <i class="bi bi-person-fill-gear me-1"></i>Admin
                            </span>
                        @elseif($user->isOperator())
                            <span class="usr-role-badge usr-role-op">
                                <i class="bi bi-shield-fill-check me-1"></i>Operator
                            </span>
                        @else
                            <span class="usr-role-badge usr-role-to">
                                <i class="bi bi-phone-fill me-1"></i>Traffic Officer
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="usr-date">{{ $user->created_at->format('M d, Y') }}</span>
                    </td>
                    <td class="text-center usr-act-cell">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('users.edit', $user) }}"
                               class="usr-act-btn usr-act-edit"
                               title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            @if($user->id !== Auth::id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                  class="d-inline"
                                  data-confirm="Delete user {{ $user->username }}? This cannot be undone.">
                                @csrf @method('DELETE')
                                <button class="usr-act-btn usr-act-del" title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                            @else
                            <span class="usr-act-btn usr-act-del" style="opacity:.25;cursor:not-allowed;"
                                  data-bs-toggle="tooltip" data-bs-title="You cannot delete your own account"
                                  aria-disabled="true">
                                <i class="bi bi-trash-fill"></i>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-5">
                        <div class="text-center">
                            <i class="bi bi-people" style="font-size:2rem;color:#d6d3d1;display:block;margin-bottom:.5rem;"></i>
                            <span style="color:#a8a29e;font-size:.88rem;">No user accounts found.</span>
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
.usr-page-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
.usr-page-header { padding: 1.1rem 1.4rem; }
.usr-header-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.15rem;
    box-shadow: 0 3px 10px rgba(220,38,38,.35);
    flex-shrink: 0;
}
.usr-header-title { font-size: 1rem; font-weight: 700; color: #1c1917; }
.usr-header-sub   { font-size: .74rem; color: #a8a29e; }
.usr-add-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .44rem 1.05rem;
    border-radius: 10px;
    font-size: .8rem; font-weight: 700;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff; text-decoration: none;
    box-shadow: 0 2px 8px rgba(220,38,38,.3);
    transition: all .15s; white-space: nowrap;
}
.usr-add-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(220,38,38,.45); }

/* ─── TABLE CARD ─── */
.usr-table-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
#usr-table thead tr { background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%); }
.usr-th {
    font-size: .68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em; color: #78716c;
}
#usr-table thead th {
    border-bottom: 2px solid #ece5da;
    padding-top: .9rem; padding-bottom: .9rem;
}
.usr-row { transition: background .15s; }
.usr-row:hover { background: #fffbf8 !important; }
.usr-row-clickable { cursor: pointer; }
.usr-row-clickable:hover td:first-child { position: relative; }
.usr-row-clickable:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: #dc2626;
    border-radius: 0 2px 2px 0;
}
.usr-row td {
    padding-top: .85rem; padding-bottom: .85rem;
    border-color: #f5f0ea; vertical-align: middle;
}

/* ─── AVATAR ─── */
.usr-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; font-weight: 800;
    flex-shrink: 0;
    letter-spacing: 0;
}
.usr-avatar--op { background: #fef2f2; color: #b91c1c; border: 2px solid #fca5a5; }
.usr-avatar--to { background: #f0fdf4; color: #15803d; border: 2px solid #86efac; }

/* ─── CELLS ─── */
.usr-name {
    font-size: .86rem; font-weight: 700; color: #1c1917;
    display: flex; align-items: center; gap: .4rem;
}
.usr-you-badge {
    display: inline-flex; align-items: center;
    background: #f5f3f0; color: #78716c;
    font-size: .62rem; font-weight: 700;
    padding: .1rem .42rem; border-radius: 20px;
    border: 1px solid #e7e2db;
    text-transform: uppercase; letter-spacing: .04em;
}
.usr-username {
    font-size: .8rem; font-weight: 600; color: #57534e;
    font-family: ui-monospace, 'Cascadia Code', monospace;
    background: #f5f3f0; padding: .18rem .55rem;
    border-radius: 6px; border: 1px solid #e7e2db;
}
.usr-date { font-size: .8rem; color: #a8a29e; }

/* ─── ROLE BADGES ─── */
.usr-role-badge {
    display: inline-flex; align-items: center;
    padding: .26rem .7rem; border-radius: 20px; border: 1.5px solid;
    font-size: .72rem; font-weight: 700;
}
.usr-role-admin { background:#fdf4ff;color:#7c3aed;border-color:#e9d5ff; }
.usr-role-op { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
.usr-role-to { background:#f0fdf4;color:#15803d;border-color:#86efac; }

/* ─── ACTION BUTTONS ─── */
.usr-act-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px;
    border-radius: 8px; font-size: .78rem;
    text-decoration: none; border: 1.5px solid transparent;
    cursor: pointer; background: none; transition: all .18s; padding: 0;
}
.usr-act-edit { background:#fdf8f0;color:#b45309;border-color:#fde68a; }
.usr-act-edit:hover { background:#d97706;color:#fff;border-color:#d97706;transform:translateY(-2px);box-shadow:0 4px 12px rgba(217,119,6,.3); }
.usr-act-del  { background:#fff1f2;color:#b91c1c;border-color:#fca5a5; }
.usr-act-del:hover:not([style*="cursor:not-allowed"]) { background:#dc2626;color:#fff;border-color:#dc2626;transform:translateY(-2px);box-shadow:0 4px 12px rgba(220,38,38,.3); }
</style>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.usr-row-clickable[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.usr-act-cell')) return;
        if (e.target.closest('a'))             return;
        if (e.target.closest('button'))        return;
        if (e.target.closest('form'))          return;
        window.location.href = row.dataset.href;
    });
});
</script>
@endpush
