@extends('layouts.mobile')
@section('title', $violator->last_name . ', ' . $violator->first_name)
@section('back_url', route('officer.motorists.index'))

@push('styles')
@include('partials.motshow-styles')
@endpush

@section('content')

@php
    $violationCount = $violator->violations->count();
    $vehicleCount = $violator->vehicles->count();
    $incidentCount = $incidents->count();
    $fullName = trim($violator->last_name . ', ' . $violator->first_name . ' ' . ($violator->middle_name ?? ''));
    $age = $violator->date_of_birth ? $violator->date_of_birth->age : null;
    $licenseExpired = $violator->license_expiry_date?->isPast() ?? false;
    $restrCodes = $violator->license_restriction
        ? array_filter(array_map('trim', explode(',', $violator->license_restriction)))
        : [];
    $restrDesc = [
        'A' => 'Motorcycle',
        'A1' => 'MC w/ Sidecar',
        'B' => 'Light Vehicle',
        'B1' => 'Light Vehicle (Prof.)',
        'B2' => 'Light Vehicle w/ Trailer',
        'C' => 'Medium/Heavy Truck',
        'D' => 'Bus',
        'BE' => 'Light + Heavy Trailer',
        'CE' => 'Large Truck + Trailer',
    ];

    $profileBadge = $violationCount >= 3
        ? ['label' => 'Recidivist', 'class' => 'motshow-status--danger', 'icon' => 'ph-fire']
        : ($violationCount === 2
            ? ['label' => 'Repeat Offender', 'class' => 'motshow-status--warn', 'icon' => 'ph-shield-warning']
            : ($violationCount === 1
                ? ['label' => '1st Violation', 'class' => 'motshow-status--info', 'icon' => 'ph-record']
                : ['label' => 'Clean Record', 'class' => 'motshow-status--safe', 'icon' => 'ph-shield-check']));
@endphp


<div class="motshow-hero">
    <div class="motshow-hero-inner">
        <div class="d-flex align-items-start gap-3">
            <div class="motshow-avatar">
                @if($violator->photo)
                    <img src="{{ uploaded_file_url($violator->photo) }}"
                         alt="Motorist photo"
                         class="mob-photo-single"
                         data-full="{{ uploaded_file_url($violator->photo) }}"
                         data-caption="{{ $fullName }}">
                @else
                    {{ strtoupper(substr($violator->first_name, 0, 1) . substr($violator->last_name, 0, 1)) }}
                @endif
            </div>

            <div style="flex:1;min-width:0;">
                <div class="motshow-chip">
                    <i class="ph-fill ph-identification-card"></i>
                    Field Profile
                </div>

                <div class="motshow-name">{{ $fullName }}</div>

                <div class="motshow-subtitle">
                    @if($violator->license_number)
                        License {{ $violator->license_number }}
                    @else
                        No license number on file
                    @endif
                </div>

                <div class="motshow-meta-row">
                    @if($violator->gender)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-user"></i>
                        {{ $violator->gender }}
                    </span>
                    @endif

                    @if($age)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-cake"></i>
                        {{ $age }} yrs old
                    </span>
                    @endif

                    @if($violator->license_type)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-cardholder"></i>
                        {{ $violator->license_type }}
                    </span>
                    @endif
                </div>

                <span class="motshow-status {{ $profileBadge['class'] }}">
                    <i class="ph-fill {{ $profileBadge['icon'] }}"></i>
                    {{ $profileBadge['label'] }}
                </span>
            </div>
        </div>

        <div class="motshow-stat-grid">
            <div class="motshow-stat">
                <div class="motshow-stat-num">{{ $violationCount }}</div>
                <div class="motshow-stat-label">Violations</div>
            </div>
            <div class="motshow-stat">
                <div class="motshow-stat-num">{{ $vehicleCount }}</div>
                <div class="motshow-stat-label">Vehicles</div>
            </div>
            <div class="motshow-stat">
                <div class="motshow-stat-num">{{ $incidentCount }}</div>
                <div class="motshow-stat-label">Incidents</div>
            </div>
        </div>
    </div>
</div>

@if($violationCount >= 2)
<div class="motshow-alert motshow-alert--danger">
    <div class="motshow-alert-icon">
        <i class="ph-fill ph-siren"></i>
    </div>
    <div>
        <div class="motshow-alert-title">Repeat-offender attention needed</div>
        <div class="motshow-alert-text">
            This motorist already has {{ $violationCount }} recorded violation{{ $violationCount === 1 ? '' : 's' }}.
            Review previous history before issuing another record.
        </div>
    </div>
</div>
@elseif($licenseExpired)
<div class="motshow-alert motshow-alert--amber">
    <div class="motshow-alert-icon">
        <i class="ph-fill ph-warning-diamond"></i>
    </div>
    <div>
        <div class="motshow-alert-title">License already expired</div>
        <div class="motshow-alert-text">
            The stored license expiry date has already passed. Double-check the presented license during field verification.
        </div>
    </div>
</div>
@endif

{{-- ── Action Buttons ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin-bottom:1rem;">
    <a href="{{ route('officer.violations.create', $violator) }}" class="motshow-action motshow-action--danger">
        <i class="ph-fill ph-file-plus"></i>
        <span>Record Violation</span>
    </a>
    <a href="{{ route('officer.motorists.vehicles.create', $violator) }}" class="motshow-action motshow-action--blue">
        <i class="ph-fill ph-car-simple"></i>
        <span>Add Vehicle</span>
    </a>
</div>

{{-- ── Personal Info ── --}}
<div class="motshow-section">Personal Info</div>
<div class="motshow-card">
    <div class="motshow-card-body">
        <div class="motshow-feature-box">
            <div class="motshow-info-grid">
                @if($violator->date_of_birth)
                <div>
                    <div class="motshow-label"><i class="ph ph-cake me-1"></i>Date of Birth</div>
                    <div class="motshow-value">{{ $violator->date_of_birth->format('M d, Y') }}</div>
                    @if($age)<div class="motshow-item-submeta">{{ $age }} years old</div>@endif
                </div>
                @endif

                @if($violator->gender)
                <div>
                    <div class="motshow-label"><i class="ph ph-gender-intersex me-1"></i>Sex</div>
                    <div class="motshow-value">{{ $violator->gender }}</div>
                </div>
                @endif

                @if($violator->civil_status)
                <div>
                    <div class="motshow-label"><i class="ph ph-heart me-1"></i>Civil Status</div>
                    <div class="motshow-value">{{ $violator->civil_status }}</div>
                </div>
                @endif

                @if($violator->blood_type)
                <div>
                    <div class="motshow-label"><i class="ph ph-drop me-1"></i>Blood Type</div>
                    <div class="motshow-value">{{ $violator->blood_type }}</div>
                </div>
                @endif

                @if($violator->height)
                <div>
                    <div class="motshow-label"><i class="ph ph-arrows-vertical me-1"></i>Height</div>
                    <div class="motshow-value">{{ $violator->height }}</div>
                </div>
                @endif

                @if($violator->weight)
                <div>
                    <div class="motshow-label"><i class="ph ph-barbell me-1"></i>Weight</div>
                    <div class="motshow-value">{{ $violator->weight }}</div>
                </div>
                @endif

                @if($violator->place_of_birth)
                <div class="motshow-info-full">
                    <div class="motshow-label"><i class="ph ph-map-pin me-1"></i>Place of Birth</div>
                    <div class="motshow-value motshow-value--soft">{{ $violator->place_of_birth }}</div>
                </div>
                @endif

                @if($violator->valid_id)
                <div class="motshow-info-full">
                    <div class="motshow-label"><i class="ph ph-identification-badge me-1"></i>Valid ID</div>
                    <div class="motshow-value motshow-value--soft">{{ $violator->valid_id }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($violator->contact_number || $violator->email || $violator->temporary_address || $violator->permanent_address)
        <div style="height:1px;background:#e2e8f0;margin:.9rem 0;"></div>
        <div class="motshow-info-grid">
            @if($violator->contact_number)
            <div>
                <div class="motshow-label"><i class="ph ph-phone me-1"></i>Contact</div>
                <div class="motshow-value">{{ $violator->contact_number }}</div>
            </div>
            @endif

            @if($violator->email)
            <div class="{{ $violator->contact_number ? '' : 'motshow-info-full' }}">
                <div class="motshow-label"><i class="ph ph-envelope me-1"></i>Email</div>
                <div class="motshow-value motshow-value--soft" style="word-break:break-all;font-size:.82rem;">{{ $violator->email }}</div>
            </div>
            @endif

            @if($violator->temporary_address)
            <div class="motshow-info-full">
                <div class="motshow-label"><i class="ph ph-house-line me-1"></i>Current Address</div>
                <div class="motshow-value motshow-value--soft">{{ $violator->temporary_address }}</div>
            </div>
            @endif

            @if($violator->permanent_address)
            <div class="motshow-info-full">
                <div class="motshow-label"><i class="ph ph-map-trifold me-1"></i>Permanent Address</div>
                <div class="motshow-value motshow-value--soft">{{ $violator->permanent_address }}</div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- ── License ── --}}
@if($violator->license_number || $violator->license_type || $violator->license_expiry_date || $restrCodes)
<div class="motshow-section">Driver's License</div>
<div class="motshow-card">
    <div class="motshow-card-body">
        <div class="motshow-feature-box">
            <div class="motshow-info-grid">
                @if($violator->license_number)
                <div class="motshow-info-full">
                    <div class="motshow-label"><i class="ph ph-identification-card me-1"></i>License Number</div>
                    <div class="motshow-value" style="font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:.04em;">
                        {{ $violator->license_number }}
                    </div>
                </div>
                @endif

                @if($violator->license_type)
                <div>
                    <div class="motshow-label"><i class="ph ph-cardholder me-1"></i>Type</div>
                    <div class="motshow-value">{{ $violator->license_type }}</div>
                </div>
                @endif

                @if($violator->license_expiry_date)
                <div>
                    <div class="motshow-label"><i class="ph ph-calendar-x me-1"></i>Expiry</div>
                    <div class="motshow-value {{ $licenseExpired ? 'text-danger fw-bold' : '' }}">
                        {{ $violator->license_expiry_date->format('M d, Y') }}
                    </div>
                    <span class="motshow-license-flag {{ $licenseExpired ? 'motshow-license-flag--danger' : 'motshow-license-flag--safe' }}">
                        <i class="ph-fill {{ $licenseExpired ? 'ph-warning-circle' : 'ph-check-circle' }}"></i>
                        {{ $licenseExpired ? 'Expired' : 'Valid on file' }}
                    </span>
                </div>
                @endif

                @if($restrCodes)
                <div class="motshow-info-full">
                    <div class="motshow-label"><i class="ph ph-list-checks me-1"></i>Restrictions</div>
                    <div style="display:flex;flex-wrap:wrap;gap:.3rem;margin-top:.2rem;">
                        @foreach($restrCodes as $code)
                            <span class="motshow-license-code" title="{{ $restrDesc[$code] ?? '' }}">{{ $code }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Violation History ── --}}
<div class="motshow-section">Violation History</div>
<div class="motshow-list">
    @forelse($violator->violations as $viol)
        @php
            $isOverdue = $viol->status === 'pending' && $viol->date_of_violation && $viol->date_of_violation <= now()->subHours(72);
            $badgeClass = match($viol->status) {
                'settled'   => 'mob-badge-settled',
                'contested' => 'mob-badge-contested',
                default     => $isOverdue ? 'mob-badge-overdue' : 'mob-badge-pending',
            };
            $badgeLabel = match($viol->status) {
                'settled'   => 'Settled',
                'contested' => 'Contested',
                default     => $isOverdue ? 'Overdue' : 'Pending',
            };
        @endphp
        <a href="{{ route('officer.violations.show', $viol) }}" class="motshow-item">
            <div class="motshow-item-icon motshow-item-icon--danger">
                <i class="ph-fill ph-warning-circle"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="motshow-item-title">{{ $viol->violationType?->name ?? '—' }}</div>
                <div class="motshow-item-meta">
                    <i class="ph ph-calendar-blank" style="font-size:.65rem;"></i>
                    {{ $viol->date_of_violation ? $viol->date_of_violation->format('M d, Y') : '—' }}
                    @if($viol->location)
                        &middot; {{ \Illuminate\Support\Str::limit($viol->location, 28) }}
                    @endif
                </div>
                @if($viol->ticket_number)
                <div class="motshow-item-submeta"><i class="ph ph-ticket" style="font-size:.62rem;"></i> #{{ $viol->ticket_number }}</div>
                @endif
            </div>
            <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
                <span class="mob-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.82rem;"></i>
            </div>
        </a>
    @empty
        <div class="motshow-card">
            <div class="mob-empty">
                <i class="ph ph-file-x mob-empty-icon"></i>
                <div class="mob-empty-text">No violations on record</div>
                <div class="mob-empty-sub">Tap "Record Violation" above to add one</div>
            </div>
        </div>
    @endforelse
</div>

{{-- ── Vehicles on File ── --}}
<div class="motshow-section" style="margin-top:.75rem;">Vehicles on File</div>
<div class="motshow-list">
    @forelse($violator->vehicles as $veh)
        <div class="motshow-item motshow-item--static">
            <div class="motshow-item-icon motshow-item-icon--blue">
                <i class="ph-fill ph-car-profile"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="motshow-item-title">
                    {{ $veh->plate_number ?: '—' }}
                    @if($veh->vehicle_type)
                        <span class="motshow-tag motshow-tag--plate">{{ $veh->vehicle_type }}</span>
                    @endif
                </div>
                <div class="motshow-item-meta">
                    {{ trim(($veh->make ?? '') . ' ' . ($veh->model ?? '')) ?: '—' }}
                    @if($veh->color) &middot; {{ $veh->color }} @endif
                </div>
                @if($veh->or_number || $veh->cr_number)
                <div class="motshow-item-submeta">
                    @if($veh->or_number) OR: {{ $veh->or_number }} @endif
                    @if($veh->or_number && $veh->cr_number) &middot; @endif
                    @if($veh->cr_number) CR: {{ $veh->cr_number }} @endif
                </div>
                @endif
                @if($veh->chassis_number)
                <div class="motshow-item-submeta">Chassis: {{ $veh->chassis_number }}</div>
                @endif
                @if($veh->owner_name)
                <div style="margin-top:.3rem;">
                    <span class="motshow-tag motshow-tag--owner">
                        <i class="ph ph-user-circle"></i> {{ $veh->owner_name }}
                    </span>
                </div>
                @endif
                @if($veh->photos->count() > 0)
                <div class="motshow-inline-photos">
                    @foreach($veh->photos->take(4) as $photo)
                    <img src="{{ uploaded_file_url($photo->photo) }}"
                         alt="Vehicle photo"
                         class="mob-photo-thumb"
                         data-full="{{ uploaded_file_url($photo->photo) }}"
                         data-caption="Vehicle — {{ $veh->plate_number }}">
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="motshow-card">
            <div class="mob-empty">
                <i class="ph-fill ph-car mob-empty-icon"></i>
                <div class="mob-empty-text">No vehicles on file</div>
                <div class="mob-empty-sub">Tap "Add Vehicle" above to register one</div>
            </div>
        </div>
    @endforelse
</div>

{{-- ── Linked Incidents ── --}}
<div class="motshow-section" style="margin-top:.75rem;">Linked Incidents</div>
<div class="motshow-list">
    @forelse($incidents as $inc)
        @php
            $sc = ['open' => 'mob-badge-open', 'under_review' => 'mob-badge-review', 'closed' => 'mob-badge-closed'][$inc->status] ?? 'mob-badge-closed';
        @endphp
        <a href="{{ route('officer.incidents.show', $inc) }}" class="motshow-item">
            <div class="motshow-item-icon motshow-item-icon--slate">
                <i class="ph-fill ph-flag"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="motshow-item-title">{{ $inc->incident_number }}</div>
                <div class="motshow-item-meta">
                    <i class="ph ph-calendar-blank" style="font-size:.65rem;"></i>
                    {{ $inc->date_of_incident ? \Carbon\Carbon::parse($inc->date_of_incident)->format('M d, Y') : '—' }}
                    @if($inc->location) &middot; {{ \Illuminate\Support\Str::limit($inc->location, 26) }} @endif
                </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
                <span class="mob-badge {{ $sc }}">{{ ucfirst(str_replace('_', ' ', $inc->status)) }}</span>
                <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.82rem;"></i>
            </div>
        </a>
    @empty
        <div class="motshow-card">
            <div class="mob-empty">
                <i class="ph ph-flag mob-empty-icon"></i>
                <div class="mob-empty-text">No incidents linked</div>
            </div>
        </div>
    @endforelse
</div>

{{-- Edit FAB --}}
<a href="{{ route('officer.motorists.edit', $violator) }}" class="mob-fab" title="Edit Motorist">
    <i class="ph-bold ph-pencil-simple"></i>
</a>

@endsection
