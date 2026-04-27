@extends('layouts.app')
@section('title', 'Edit User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" style="color:#dc2626;text-decoration:none;">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $user->name }}</li>
@endsection

@section('content')

<div class="usr-form-card">

    <div class="usr-form-header">
        <div class="usr-form-avatar {{ $user->isOperator() ? 'usr-avatar--op' : 'usr-avatar--to' }}">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <div class="usr-form-title">Edit User</div>
            <div class="usr-form-sub">{{ $user->username }}</div>
        </div>
        @if($user->isAdmin())
            <span class="usr-role-chip usr-role-admin ms-auto"><i class="bi bi-person-fill-gear me-1"></i>Admin</span>
        @elseif($user->isOperator())
            <span class="usr-role-chip usr-role-op ms-auto"><i class="bi bi-shield-fill-check me-1"></i>Operator</span>
        @else
            <span class="usr-role-chip usr-role-to ms-auto"><i class="bi bi-phone-fill me-1"></i>Traffic Officer</span>
        @endif
    </div>

    <div class="usr-form-body">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="usr-label">Full Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text usr-ig-icon" style="background:#eff6ff;border-color:#bfdbfe;">
                        <i class="bi bi-person-fill" style="color:#1d4ed8;"></i>
                    </span>
                    <input type="text" name="name"
                           class="form-control usr-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="usr-label">Username <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text usr-ig-icon" style="background:#f5f3f0;border-color:#e7e2db;">
                        <i class="bi bi-at" style="color:#78716c;font-size:1rem;"></i>
                    </span>
                    <input type="text" name="username"
                           class="form-control usr-input @error('username') is-invalid @enderror"
                           value="{{ old('username', $user->username) }}" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="usr-label">Role <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text usr-ig-icon" style="background:#fdf4ff;border-color:#e9d5ff;">
                        <i class="bi bi-shield-fill-check" style="color:#7c3aed;"></i>
                    </span>
                    <select name="role" class="form-select usr-input @error('role') is-invalid @enderror" required>
                        <option value="traffic_officer" {{ old('role', $user->role) == 'traffic_officer' ? 'selected' : '' }}>Traffic Officer — Mobile</option>
                        <option value="operator"        {{ old('role', $user->role) == 'operator'        ? 'selected' : '' }}>Operator — Full Access</option>
                        <option value="admin"           {{ old('role', $user->role) == 'admin'           ? 'selected' : '' }}>Admin — Full Access + User Management</option>
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="usr-pw-divider">
                <span>Password</span>
            </div>
            <p class="usr-pw-hint">Leave both fields blank to keep the current password.</p>

            <div class="mb-3">
                <label class="usr-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text usr-ig-icon" style="background:#f0fdf4;border-color:#86efac;">
                        <i class="bi bi-lock-fill" style="color:#15803d;"></i>
                    </span>
                    <input type="password" name="password"
                           class="form-control usr-input @error('password') is-invalid @enderror"
                           autocomplete="new-password"
                           placeholder="Min. 6 characters">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="usr-label">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text usr-ig-icon" style="background:#f0fdf4;border-color:#86efac;">
                        <i class="bi bi-lock-fill" style="color:#15803d;"></i>
                    </span>
                    <input type="password" name="password_confirmation"
                           class="form-control usr-input"
                           autocomplete="new-password"
                           placeholder="Repeat new password">
                </div>
            </div>

            <div class="d-flex gap-2 pt-2">
                <button type="submit" class="usr-submit-btn">
                    <i class="bi bi-check-lg"></i> Update User
                </button>
                <a href="{{ route('users.index') }}"
                   class="btn d-inline-flex align-items-center gap-2 rounded-pill"
                   style="border:1.5px solid #d6d3d1;color:#78716c;background:#fff;font-weight:500;">
                    <i class="bi bi-x-circle" style="font-size:.85rem;"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.usr-form-card {
    max-width: 520px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 6px 24px rgba(0,0,0,.06);
    overflow: hidden;
}
.usr-form-header {
    display: flex; align-items: center; gap: 1rem;
    padding: 1.1rem 1.4rem;
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
    border-bottom: 1.5px solid #ece5da;
}
.usr-form-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; font-weight: 800;
    flex-shrink: 0;
}
.usr-avatar--op { background:#fef2f2;color:#b91c1c;border:2px solid #fca5a5; }
.usr-avatar--to { background:#f0fdf4;color:#15803d;border:2px solid #86efac; }
.usr-form-title { font-size: .95rem; font-weight: 700; color: #1c1917; }
.usr-form-sub   { font-size: .74rem; color: #a8a29e; margin-top: .1rem; font-family: ui-monospace, monospace; }
.usr-form-body  { padding: 1.4rem; }

.usr-role-chip {
    display: inline-flex; align-items: center;
    padding: .24rem .65rem; border-radius: 20px; border: 1.5px solid;
    font-size: .7rem; font-weight: 700; white-space: nowrap;
}
.usr-role-admin { background:#fdf4ff;color:#7c3aed;border-color:#e9d5ff; }
.usr-role-op { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
.usr-role-to { background:#f0fdf4;color:#15803d;border-color:#86efac; }

.usr-label {
    font-size: .72rem; font-weight: 700;
    color: #78716c; text-transform: uppercase; letter-spacing: .05em;
    margin-bottom: .4rem; display: block;
}
.usr-ig-icon {
    border-right: none;
    padding: .45rem .75rem;
    border-radius: 10px 0 0 10px !important;
}
.usr-input {
    border-left: none;
    border-radius: 0 10px 10px 0 !important;
    font-size: .875rem;
}
.usr-input:focus { box-shadow: none; border-color: #e2d9cf; }

.usr-pw-divider {
    display: flex; align-items: center; gap: .75rem;
    margin: 1.1rem 0 .4rem;
    font-size: .7rem; font-weight: 700;
    color: #a8a29e; text-transform: uppercase; letter-spacing: .06em;
}
.usr-pw-divider::before, .usr-pw-divider::after {
    content: ''; flex: 1; height: 1px; background: #f0ebe3;
}
.usr-pw-hint {
    font-size: .76rem; color: #a8a29e; margin-bottom: .9rem;
}

.usr-submit-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1.25rem;
    border-radius: 10px; font-size: .84rem; font-weight: 700;
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #fff; border: none;
    box-shadow: 0 2px 8px rgba(180,83,9,.3);
    cursor: pointer; transition: all .15s;
}
.usr-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(180,83,9,.45); }
</style>

@endsection
