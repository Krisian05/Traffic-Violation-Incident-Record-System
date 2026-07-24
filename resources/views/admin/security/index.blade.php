@extends('layouts.app')

@section('title', 'Security Administration')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Security Settings</li>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #292524;"><i class="bi bi-shield-lock-fill text-danger me-2"></i>Security Settings & Policies</h4>
            <p class="text-muted small mb-0">System-wide 2FA policy enforcement, session security rules, active user session management, and security audit metrics.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.security.sessions') }}" class="btn btn-outline-danger btn-sm rounded-3">
                <i class="bi bi-laptop me-1"></i> Active User Sessions & Devices
            </a>
            <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="bi bi-clock-history me-1"></i> Full Audit Trail
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-danger-subtle text-danger rounded-3"><i class="bi bi-shield-check fs-4"></i></div>
                    <div>
                        <div class="text-muted small">2FA Adoption Rate</div>
                        <div class="fs-4 fw-bold text-dark">{{ $twoFaAdoptionRate }}%</div>
                        <div class="small text-muted">{{ $usersWith2fa }} of {{ $totalUsers }} users</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-primary-subtle text-primary rounded-3"><i class="bi bi-phone fs-4"></i></div>
                    <div>
                        <div class="text-muted small">Registered Mobile Devices</div>
                        <div class="fs-4 fw-bold text-dark">{{ $activeRegisteredDevicesCount }}</div>
                        <div class="small text-muted">Active mobile app tokens</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-warning-subtle text-warning rounded-3"><i class="bi bi-clock-history fs-4"></i></div>
                    <div>
                        <div class="text-muted small">Session Timeout</div>
                        <div class="fs-4 fw-bold text-dark">{{ $sessionTimeout }}m</div>
                        <div class="small text-muted">Auto-logout inactivity limit</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-info-subtle text-info rounded-3"><i class="bi bi-key fs-4"></i></div>
                    <div>
                        <div class="text-muted small">Account Lockout Policy</div>
                        <div class="fs-4 fw-bold text-dark">{{ $maxLoginAttempts }} Tries</div>
                        <div class="small text-muted">{{ $lockoutDuration }} minutes lock</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Security Policy Configuration Form -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-gear-fill me-2 text-danger"></i>System Security Policy Settings</h6>
                    <span class="text-muted small">Configure global authentication and session policy parameters</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.security.policy.update') }}">
                        @csrf
                        <div class="form-check form-switch mb-4 p-3 bg-light rounded-3">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="enforce_2fa_admin" name="enforce_2fa_admin" value="1" {{ $enforce2faAdmin ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="enforce_2fa_admin">Enforce Mandatory Two-Factor Authentication (2FA) for Admins</label>
                            <div class="text-muted small mt-1">Requires all Super Administrators and LGU Admins to enable 2FA before executing sensitive operations.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Inactive Session Timeout (Minutes)</label>
                            <input type="number" name="session_timeout_minutes" class="form-control @error('session_timeout_minutes') is-invalid @enderror" value="{{ old('session_timeout_minutes', $sessionTimeout) }}" min="5" max="10080" required>
                            @error('session_timeout_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Max Failed Login Attempts</label>
                                <input type="number" name="max_login_attempts" class="form-control @error('max_login_attempts') is-invalid @enderror" value="{{ old('max_login_attempts', $maxLoginAttempts) }}" min="1" max="20" required>
                                @error('max_login_attempts') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lockout Duration (Minutes)</label>
                                <input type="number" name="lockout_duration_minutes" class="form-control @error('lockout_duration_minutes') is-invalid @enderror" value="{{ old('lockout_duration_minutes', $lockoutDuration) }}" min="1" max="1440" required>
                                @error('lockout_duration_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-danger text-white fw-bold w-100 py-2 rounded-3">
                            <i class="bi bi-shield-check me-1"></i> Update Security Policy
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Security Activity Log Stream -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="bi bi-activity me-2 text-primary"></i>Recent Security Audit Stream</h6>
                        <span class="text-muted small">Live security and user activity trail</span>
                    </div>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-link text-decoration-none small p-0 fw-semibold">View All</a>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted">
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSecurityLogs as $log)
                                    <tr>
                                        <td class="small text-muted">{{ $log->created_at->format('M d, H:i:s') }}</td>
                                        <td class="small fw-semibold">
                                            {{ $log->causer?->name ?? 'System' }}
                                        </td>
                                        <td class="small">
                                            <span class="badge bg-secondary-subtle text-secondary me-1">{{ $log->log_name }}</span>
                                            {{ $log->description }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4 small">No security log events recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
