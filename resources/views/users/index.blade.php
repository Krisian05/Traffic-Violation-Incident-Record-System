@extends('layouts.app')
@section('title', 'User Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">User Management</li>
@endsection

@section('content')

{{-- ── Header card ── --}}
<div class="usr-page-card mb-4">
    <div class="usr-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="usr-header-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <h5 class="usr-header-title mb-0">System User Management</h5>
                <p class="usr-header-sub mb-0">{{ $users->total() }} account{{ $users->total() !== 1 ? 's' : '' }} registered across jurisdictions</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.create') }}" class="usr-add-btn">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add User Account</span>
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- ── Filter card ── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, username, email, badge..." value="{{ $search }}">
            </div>
            @if(Auth::user()->isSuperAdmin())
            <div class="col-md-3">
                <select name="lgu_id" class="form-select form-select-sm">
                    <option value="">All LGUs / System-Wide</option>
                    @foreach($lgus as $lgu)
                        <option value="{{ $lgu->id }}" {{ (string)$lguId === (string)$lgu->id ? 'selected' : '' }}>{{ $lgu->name }} ({{ $lgu->code }})</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    @foreach(\App\Models\User::ROLES as $roleKey => $roleLabel)
                        <option value="{{ $roleKey }}" {{ $role === $roleKey ? 'selected' : '' }}>{{ $roleLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- ── Table card ── --}}
<div class="usr-table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="usr-table">
            <thead>
                <tr>
                    <th style="padding-left:1.4rem;"><span class="usr-th">User</span></th>
                    <th><span class="usr-th">Username & Contact</span></th>
                    <th><span class="usr-th">Role</span></th>
                    <th><span class="usr-th">LGU Jurisdiction</span></th>
                    <th><span class="usr-th">Status</span></th>
                    <th class="text-center"><span class="usr-th">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="usr-row">
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
                                <div class="small text-muted">
                                    @if($user->badge_id)
                                        <span class="badge bg-light text-dark border me-1"><i class="bi bi-shield me-1"></i>Badge: {{ $user->badge_id }}</span>
                                    @endif
                                    @if($user->agency)
                                        <span>{{ $user->agency }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="usr-username mb-1">{{ $user->username }}</div>
                        <div class="small text-muted">{{ $user->email ?? 'No email set' }}</div>
                    </td>
                    <td>
                        <span class="usr-role-badge usr-role-admin">
                            <i class="bi bi-shield-fill-check me-1"></i>{{ $user->role_label }}
                        </span>
                    </td>
                    <td>
                        @if($user->lgu)
                            <span class="fw-semibold text-dark">{{ $user->lgu->name }}</span>
                            <div class="small text-muted">{{ $user->lgu->code }}</div>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">System-Wide</span>
                        @endif
                    </td>
                    <td>
                        @if($user->status === 'inactive')
                            <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                        @else
                            <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Active</span>
                        @endif
                    </td>
                    <td class="text-center usr-act-cell">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('users.edit', $user) }}" class="usr-act-btn usr-act-edit" title="Edit Account">
                                <i class="bi bi-pencil-fill"></i>
                            </a>

                            @if($user->id !== Auth::id())
                                <!-- Status Toggle -->
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="usr-act-btn {{ $user->status === 'inactive' ? 'btn-outline-success' : 'btn-outline-warning' }}" title="{{ $user->status === 'inactive' ? 'Activate Account' : 'Deactivate Account' }}">
                                        <i class="bi {{ $user->status === 'inactive' ? 'bi-play-circle-fill' : 'bi-pause-circle-fill' }}"></i>
                                    </button>
                                </form>

                                <!-- Session Revoke -->
                                <form method="POST" action="{{ route('users.revoke-sessions', $user) }}" class="d-inline" onsubmit="return confirm('Revoke all sessions and registered devices for {{ $user->username }}?');">
                                    @csrf
                                    <button type="submit" class="usr-act-btn btn-outline-secondary text-secondary" title="Revoke Active Sessions & Devices">
                                        <i class="bi bi-slash-circle-fill"></i>
                                    </button>
                                </form>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete user account {{ $user->username }}?');">
                                    @csrf @method('DELETE')
                                    <button class="usr-act-btn usr-act-del" title="Delete Account">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-5 text-center text-muted">
                        <i class="bi bi-people fs-2 d-block mb-2 text-secondary"></i>
                        No user accounts match the selected filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="p-3 border-top">
        {{ $users->links() }}
    </div>
    @endif
</div>

<style>
.usr-page-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.usr-page-header { padding: 1.1rem 1.4rem; }
.usr-header-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #dc2626, #b91c1c); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.15rem; }
.usr-header-title { font-size: 1rem; font-weight: 700; color: #1c1917; }
.usr-header-sub   { font-size: .74rem; color: #a8a29e; }
.usr-add-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .44rem 1.05rem; border-radius: 10px; font-size: .8rem; font-weight: 700; background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; text-decoration: none; }
.usr-table-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
#usr-table thead tr { background: #fdf8f0; }
.usr-th { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: #78716c; }
.usr-row td { padding-top: .85rem; padding-bottom: .85rem; border-color: #f5f0ea; vertical-align: middle; }
.usr-avatar { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .9rem; font-weight: 800; }
.usr-avatar--op { background: #fef2f2; color: #b91c1c; border: 2px solid #fca5a5; }
.usr-avatar--to { background: #f0fdf4; color: #15803d; border: 2px solid #86efac; }
.usr-name { font-size: .86rem; font-weight: 700; color: #1c1917; display: flex; align-items: center; gap: .4rem; }
.usr-you-badge { background: #f5f3f0; color: #78716c; font-size: .62rem; font-weight: 700; padding: .1rem .42rem; border-radius: 20px; border: 1px solid #e7e2db; }
.usr-username { font-size: .8rem; font-weight: 600; color: #57534e; font-family: monospace; background: #f5f3f0; padding: .18rem .55rem; border-radius: 6px; display: inline-block; }
.usr-role-badge { display: inline-flex; align-items: center; padding: .26rem .7rem; border-radius: 20px; border: 1.5px solid; font-size: .72rem; font-weight: 700; background:#fdf4ff; color:#7c3aed; border-color:#e9d5ff; }
.usr-act-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; font-size: .78rem; text-decoration: none; border: 1.5px solid transparent; cursor: pointer; background: none; transition: all .18s; padding: 0; }
.usr-act-edit { background:#fdf8f0; color:#b45309; border-color:#fde68a; }
.usr-act-del  { background:#fff1f2; color:#b91c1c; border-color:#fca5a5; }
</style>
@endsection
