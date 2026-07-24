@extends('layouts.app')

@section('title', 'Active Sessions & Registered Devices')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.security.index') }}">Security Settings</a></li>
    <li class="breadcrumb-item active">Active Sessions & Devices</li>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #292524;"><i class="bi bi-laptop text-danger me-2"></i>Active User Sessions & Registered Devices</h4>
            <p class="text-muted small mb-0">Monitor live logged-in web browser sessions and authorized mobile field officer devices across all LGUs.</p>
        </div>
        <div>
            <form method="POST" action="{{ route('admin.security.sessions.terminate-all-others') }}" onsubmit="return confirm('Emergency Action: Are you sure you want to terminate ALL other active web sessions across the entire platform?');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm rounded-3 fw-bold">
                    <i class="bi bi-slash-circle me-1"></i> Terminate All Other Active Sessions
                </button>
            </form>
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

    <div class="row g-4">
        <!-- Active Web Sessions -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="bi bi-browser-chrome me-2 text-primary"></i>Active Web Sessions ({{ count($activeDbSessions) }})</h6>
                        <span class="text-muted small">Live database browser sessions</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted">
                                    <th>User</th>
                                    <th>Role & Jurisdiction</th>
                                    <th>IP Address</th>
                                    <th>Device / User Agent</th>
                                    <th>Last Active</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeDbSessions as $session)
                                    <tr class="{{ $session['is_current'] ? 'table-warning' : '' }}">
                                        <td>
                                            <div class="fw-bold text-dark">{{ $session['user']?->name ?? 'Guest / Unknown' }}</div>
                                            <div class="small text-muted">{{ $session['user']?->username ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary me-1">{{ $session['user']?->role_label ?? '-' }}</span>
                                            <span class="small text-muted">{{ $session['user']?->lgu?->name ?? 'System-Wide' }}</span>
                                        </td>
                                        <td class="small fw-mono">{{ $session['ip_address'] ?? '127.0.0.1' }}</td>
                                        <td class="small text-muted" style="max-width: 300px; word-break: break-all;">
                                            {{ Str::limit($session['user_agent'] ?? 'Unknown Agent', 70) }}
                                        </td>
                                        <td class="small text-muted">
                                            {{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}
                                            @if($session['is_current'])
                                                <span class="badge bg-success ms-1">This Session</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(!$session['is_current'])
                                                <form method="POST" action="{{ route('admin.security.sessions.terminate', $session['id']) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-2" onclick="return confirm('Terminate this session?');">
                                                        <i class="bi bi-box-arrow-right"></i> Terminate
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Current</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No active web sessions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registered Mobile Devices -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-phone-vibrate me-2 text-success"></i>Registered Mobile Devices ({{ count($registeredDevices) }})</h6>
                    <span class="text-muted small">Field officer mobile application hardware bindings</span>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted">
                                    <th>Field Officer</th>
                                    <th>Badge ID & LGU</th>
                                    <th>Device Name / Model</th>
                                    <th>Hardware Unique ID</th>
                                    <th>App Version</th>
                                    <th>Last Active</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registeredDevices as $device)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $device->user?->name ?? 'Unknown Officer' }}</div>
                                            <div class="small text-muted">{{ $device->user?->username ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $device->user?->badge_id ?? 'No Badge' }}</span>
                                            <div class="small text-muted">{{ $device->user?->lgu?->name ?? 'System-Wide' }}</div>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $device->device_name ?? 'Mobile Device' }}</td>
                                        <td class="small fw-mono text-muted">{{ Str::limit($device->device_uuid ?? '-', 24) }}</td>
                                        <td><span class="badge bg-info-subtle text-info">{{ $device->app_version ?? 'v1.0' }}</span></td>
                                        <td class="small text-muted">{{ $device->last_active_at ? $device->last_active_at->diffForHumans() : 'Never' }}</td>
                                        <td class="text-end">
                                            @if($device->user)
                                                <form method="DELETE" action="{{ route('users.devices.destroy', [$device->user_id, $device->id]) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-2" onclick="return confirm('Revoke this mobile device binding?');">
                                                        <i class="bi bi-trash"></i> Revoke Device
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No registered mobile devices found.</td>
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
