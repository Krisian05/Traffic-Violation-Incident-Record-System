@extends('layouts.app')
@section('title', 'Officer Profile & Activity History')

@section('content')
<div class="container py-4" style="max-width:900px;">
    {{-- Officer Profile Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-700 fs-3" style="width:64px;height:64px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h4 class="fw-700 mb-1" style="color:#0f172a;">{{ $user->name }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-2" style="font-size:.88rem;">
                        <span class="badge bg-primary text-white">{{ $user->role_label }}</span>
                        @if($user->badge_id)
                            <span class="badge bg-secondary text-white"><i class="bi bi-shield-shaded me-1"></i>Badge #{{ $user->badge_id }}</span>
                        @endif
                        @if($user->agency)
                            <span class="badge bg-dark text-white"><i class="bi bi-building me-1"></i>{{ $user->agency }}</span>
                        @endif
                        <span class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $user->lgu ? $user->lgu->name : 'Province-Wide' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-3" id="profileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-600" id="citations-tab" data-bs-toggle="pill" data-bs-target="#citations" type="button" role="tab">
                <i class="bi bi-receipt me-1"></i> Issued Citations ({{ $issuedViolations->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-600" id="incidents-tab" data-bs-toggle="pill" data-bs-target="#incidents" type="button" role="tab">
                <i class="bi bi-flag me-1"></i> Incidents ({{ $submittedIncidents->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-600" id="devices-tab" data-bs-toggle="pill" data-bs-target="#devices" type="button" role="tab">
                <i class="bi bi-phone me-1"></i> Registered Devices ({{ $user->devices->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-600" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">
                <i class="bi bi-key me-1"></i> Security & Password
            </button>
        </li>
    </ul>

    <div class="tab-content" id="profileTabContent">
        {{-- Citations Tab --}}
        <div class="tab-pane fade show active" id="citations" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="font-size:.88rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Ticket No.</th>
                                    <th>Motorist</th>
                                    <th>Violation</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($issuedViolations as $violation)
                                    <tr>
                                        <td class="fw-600">{{ $violation->ticket_number }}</td>
                                        <td>{{ $violation->violator ? $violation->violator->full_name : 'N/A' }}</td>
                                        <td>{{ $violation->violationType ? $violation->violationType->name : 'N/A' }}</td>
                                        <td>{{ $violation->date_of_violation->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge {{ $violation->status === 'settled' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ ucfirst($violation->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('officer.violations.show', $violation) }}" class="btn btn-xs btn-outline-primary py-0">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No citations issued yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Incidents Tab --}}
        <div class="tab-pane fade" id="incidents" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="font-size:.88rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Incident No.</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($submittedIncidents as $incident)
                                    <tr>
                                        <td class="fw-600">{{ $incident->incident_number }}</td>
                                        <td>{{ $incident->location }}</td>
                                        <td>{{ $incident->date_of_incident->format('M d, Y') }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($incident->status) }}</span></td>
                                        <td>
                                            <a href="{{ route('officer.incidents.show', $incident) }}" class="btn btn-xs btn-outline-primary py-0">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No incidents submitted yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Devices Tab --}}
        <div class="tab-pane fade" id="devices" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h6 class="fw-700 mb-3">Registered Mobile Enforcement Devices</h6>
                    <div class="list-group list-group-flush" style="font-size:.88rem;">
                        @forelse($user->devices as $device)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-600">
                                        <i class="bi {{ $device->device_icon }} me-1 text-primary"></i>
                                        {{ $device->formatted_label }}
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem;">IP: {{ $device->ip_address }} · Last used: {{ $device->last_used_at ? $device->last_used_at->diffForHumans() : 'Never' }}</div>
                                </div>
                                <span class="badge bg-success text-white"><i class="bi bi-check-circle me-1"></i>Active</span>
                            </div>
                        @empty
                            <div class="text-muted text-center py-3">No mobile devices registered yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Security Tab --}}
        <div class="tab-pane fade" id="security" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3">Change Account Password</h6>
                    <form method="POST" action="{{ route('officer.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.85rem;">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.85rem;">New Password (Min 8 chars)</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.85rem;">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary fw-600">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
