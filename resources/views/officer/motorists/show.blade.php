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
<div class="motshow-card" style="margin-bottom:.9rem;">

    {{-- Identity rows --}}
    @php $hasIdentity = $violator->date_of_birth || $violator->gender || $violator->civil_status || $violator->blood_type || $violator->height || $violator->weight || $violator->place_of_birth || $violator->valid_id; @endphp
    @if($hasIdentity)
    <div style="padding:.75rem 1rem .35rem;">
        <div style="font-size:.58rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em;margin-bottom:.55rem;">Identity</div>
    </div>
    @if($violator->date_of_birth)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-cake" style="font-size:.9rem;color:#2563eb;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Date of Birth</div>
            <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ $violator->date_of_birth->format('M d, Y') }}</div>
        </div>
        @if($age)<span style="font-size:.7rem;font-weight:700;color:#64748b;flex-shrink:0;">{{ $age }} yrs</span>@endif
    </div>
    @endif
    @if($violator->gender || $violator->civil_status)
    <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #f1f5f9;">
        @if($violator->gender)
        @if($violator->civil_status)
        <div style="display:flex;align-items:center;gap:.6rem;padding:.6rem 1rem;border-right:1px solid #f1f5f9;">
        @else
        <div style="display:flex;align-items:center;gap:.6rem;padding:.6rem 1rem;">
        @endif
            <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="ph-fill ph-user" style="font-size:.9rem;color:#16a34a;"></i>
            </div>
            <div>
                <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Sex</div>
                <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ $violator->gender }}</div>
            </div>
        </div>
        @endif
        @if($violator->civil_status)
        <div style="display:flex;align-items:center;gap:.6rem;padding:.6rem 1rem;">
            <div style="width:32px;height:32px;border-radius:10px;background:#fdf4ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="ph-fill ph-heart" style="font-size:.9rem;color:#a21caf;"></i>
            </div>
            <div>
                <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Civil Status</div>
                <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ $violator->civil_status }}</div>
            </div>
        </div>
        @endif
    </div>
    @endif
    @if($violator->blood_type || $violator->height || $violator->weight)
    <div style="display:grid;grid-template-columns:repeat(3,1fr);border-bottom:1px solid #f1f5f9;">
        @if($violator->blood_type)
        @if($violator->height || $violator->weight)
        <div style="padding:.6rem .75rem;text-align:center;border-right:1px solid #f1f5f9;">
        @else
        <div style="padding:.6rem .75rem;text-align:center;">
        @endif
            <div style="font-size:1.05rem;font-weight:800;color:#dc2626;">{{ $violator->blood_type }}</div>
            <div style="font-size:.58rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-top:.1rem;">Blood</div>
        </div>
        @endif
        @if($violator->height)
        @if($violator->weight)
        <div style="padding:.6rem .75rem;text-align:center;border-right:1px solid #f1f5f9;">
        @else
        <div style="padding:.6rem .75rem;text-align:center;">
        @endif
            <div style="font-size:1.05rem;font-weight:800;color:#0f172a;">{{ $violator->height }}</div>
            <div style="font-size:.58rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-top:.1rem;">Height</div>
        </div>
        @endif
        @if($violator->weight)
        <div style="padding:.6rem .75rem;text-align:center;">
            <div style="font-size:1.05rem;font-weight:800;color:#0f172a;">{{ $violator->weight }}</div>
            <div style="font-size:.58rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-top:.1rem;">Weight</div>
        </div>
        @endif
    </div>
    @endif
    @if($violator->place_of_birth)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-map-pin" style="font-size:.9rem;color:#ea580c;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Place of Birth</div>
            <div style="font-size:.85rem;font-weight:600;color:#334155;">{{ $violator->place_of_birth }}</div>
        </div>
    </div>
    @endif
    @if($violator->valid_id)
    @if($violator->contact_number || $violator->email || $violator->temporary_address || $violator->permanent_address)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
    @else
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;">
    @endif
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-identification-badge" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Valid ID</div>
            <div style="font-size:.85rem;font-weight:600;color:#334155;">{{ $violator->valid_id }}</div>
        </div>
    </div>
    @endif
    @endif

    {{-- Contact rows --}}
    @if($violator->contact_number || $violator->email || $violator->temporary_address || $violator->permanent_address)
    <div style="padding:.75rem 1rem .35rem;">
        <div style="font-size:.58rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em;margin-bottom:.55rem;">Contact &amp; Address</div>
    </div>
    @if($violator->contact_number)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-phone" style="font-size:.9rem;color:#2563eb;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Contact Number</div>
            <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ $violator->contact_number }}</div>
        </div>
    </div>
    @endif
    @if($violator->email)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-envelope" style="font-size:.9rem;color:#7c3aed;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Email</div>
            <div style="font-size:.82rem;font-weight:600;color:#334155;word-break:break-all;">{{ $violator->email }}</div>
        </div>
    </div>
    @endif
    @if($violator->temporary_address)
    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
            <i class="ph-fill ph-house-line" style="font-size:.9rem;color:#ea580c;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Current Address</div>
            <div style="font-size:.85rem;font-weight:600;color:#334155;line-height:1.4;">{{ $violator->temporary_address }}</div>
        </div>
    </div>
    @endif
    @if($violator->permanent_address)
    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem 1rem;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
            <i class="ph-fill ph-map-trifold" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Permanent Address</div>
            <div style="font-size:.85rem;font-weight:600;color:#334155;line-height:1.4;">{{ $violator->permanent_address }}</div>
        </div>
    </div>
    @endif
    @endif
</div>

{{-- ── Driver's License ── --}}
@if($violator->license_number || $violator->license_type || $violator->license_expiry_date || $restrCodes)
<div class="motshow-section">Driver's License</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    @if($violator->license_number)
    <div style="padding:1rem 1rem .75rem;border-bottom:1px solid #f1f5f9;">
        <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem;">License Number</div>
        <div style="font-size:1.05rem;font-weight:800;color:#0f172a;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:.06em;">{{ $violator->license_number }}</div>
    </div>
    @endif
    <div style="display:grid;grid-template-columns:1fr 1fr;">
        @if($violator->license_type)
        @if($violator->license_expiry_date)
        <div style="padding:.75rem 1rem;border-right:1px solid #f1f5f9;">
        @else
        <div style="padding:.75rem 1rem;">
        @endif
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Type</div>
            <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ $violator->license_type }}</div>
        </div>
        @endif
        @if($violator->license_expiry_date)
        <div style="padding:.75rem 1rem;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem;">Expiry Date</div>
            <div class="{{ $licenseExpired ? 'motshow-license-date--expired' : 'motshow-license-date--valid' }}" style="font-size:.88rem;font-weight:700;">{{ $violator->license_expiry_date->format('M d, Y') }}</div>
            <span class="motshow-license-flag {{ $licenseExpired ? 'motshow-license-flag--danger' : 'motshow-license-flag--safe' }}">
                <i class="ph-fill {{ $licenseExpired ? 'ph-warning-circle' : 'ph-check-circle' }}"></i>
                {{ $licenseExpired ? 'Expired' : 'Valid' }}
            </span>
        </div>
        @endif
    </div>
    @if($restrCodes)
    <div style="padding:.75rem 1rem;border-top:1px solid #f1f5f9;">
        <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Restriction Codes</div>
        <div style="display:flex;flex-wrap:wrap;gap:.3rem;">
            @foreach($restrCodes as $code)
                <span class="motshow-license-code" title="{{ $restrDesc[$code] ?? '' }}">{{ $code }}</span>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

{{-- ── Violation History ── --}}
<div class="motshow-section">Violation History
    @if($violationCount > 0)
    <span style="margin-left:.35rem;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;border-radius:999px;font-size:.6rem;font-weight:800;padding:.1rem .45rem;">{{ $violationCount }}</span>
    @endif
</div>
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
        $accentClass = match($viol->status) {
            'settled'   => 'motshow-accent--settled',
            'contested' => 'motshow-accent--contested',
            default     => $isOverdue ? 'motshow-accent--overdue' : 'motshow-accent--pending',
        };
    @endphp
    <a href="{{ route('officer.violations.show', $viol) }}"
       style="display:flex;align-items:flex-start;gap:.85rem;background:#fff;border-radius:16px;border:1px solid rgba(15,23,42,.06);box-shadow:0 2px 10px rgba(15,23,42,.04);margin-bottom:.65rem;overflow:hidden;text-decoration:none;color:inherit;position:relative;">
        <div class="motshow-accent-bar {{ $accentClass }}"></div>
        <div style="display:flex;align-items:flex-start;gap:.75rem;flex:1;min-width:0;padding:.85rem .85rem .85rem 0;">
            <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(220,38,38,.25);">
                <i class="ph-fill ph-warning-circle" style="font-size:1.1rem;color:#fff;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.9rem;font-weight:800;color:#0f172a;line-height:1.25;margin-bottom:.18rem;">{{ $viol->violationType?->name ?? '—' }}</div>
                <div style="font-size:.72rem;color:#64748b;display:flex;align-items:center;gap:.3rem;flex-wrap:wrap;">
                    <i class="ph ph-calendar-blank"></i>
                    {{ $viol->date_of_violation ? $viol->date_of_violation->format('M d, Y') : '—' }}
                    @if($viol->location)
                    <span style="color:#cbd5e1;">&middot;</span>
                    <i class="ph ph-map-pin"></i>
                    {{ \Illuminate\Support\Str::limit($viol->location, 24) }}
                    @endif
                </div>
                @if($viol->ticket_number)
                <div style="margin-top:.28rem;font-size:.68rem;color:#94a3b8;display:flex;align-items:center;gap:.25rem;">
                    <i class="ph ph-ticket"></i> Ticket #{{ $viol->ticket_number }}
                </div>
                @endif
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.3rem;flex-shrink:0;">
                <span class="mob-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.82rem;"></i>
            </div>
        </div>
    </a>
@empty
    <div class="motshow-card" style="margin-bottom:.65rem;">
        <div class="mob-empty">
            <i class="ph ph-file-x mob-empty-icon"></i>
            <div class="mob-empty-text">No violations on record</div>
            <div class="mob-empty-sub">Tap "Record Violation" above to add one</div>
        </div>
    </div>
@endforelse

{{-- ── Vehicles on File ── --}}
<div class="motshow-section" style="margin-top:.35rem;">Vehicles on File
    @if($vehicleCount > 0)
    <span style="margin-left:.35rem;background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd;border-radius:999px;font-size:.6rem;font-weight:800;padding:.1rem .45rem;">{{ $vehicleCount }}</span>
    @endif
</div>
@forelse($violator->vehicles as $veh)
    <a href="{{ route('officer.vehicles.show', $veh) }}" style="display:flex;align-items:flex-start;gap:.85rem;background:#fff;border-radius:16px;border:1px solid rgba(15,23,42,.06);box-shadow:0 2px 10px rgba(15,23,42,.04);margin-bottom:.65rem;overflow:hidden;position:relative;text-decoration:none;color:inherit;">
        <div style="width:4px;background:linear-gradient(180deg,#2563eb,#1d4ed8);align-self:stretch;flex-shrink:0;"></div>
        <div style="display:flex;align-items:flex-start;gap:.75rem;flex:1;min-width:0;padding:.85rem .85rem .85rem 0;">
            @php
                $vehPhotos   = $veh->photos;
                $vehCaption  = 'Vehicle — ' . ($veh->plate_number ?: 'No Plate');
                $vehGallery  = $vehPhotos->map(fn($p) => uploaded_file_url($p->photo))->values()->toJson();
                $vehExtra    = $vehPhotos->count() - 1;
            @endphp
            @if($vehPhotos->isNotEmpty())
            <div style="position:relative;width:40px;height:40px;border-radius:12px;overflow:hidden;flex-shrink:0;box-shadow:0 4px 12px rgba(29,78,216,.25);cursor:zoom-in;">
                <img src="{{ uploaded_file_url($vehPhotos->first()->photo) }}"
                     alt="Vehicle photo"
                     class="mob-photo-thumb"
                     data-full="{{ uploaded_file_url($vehPhotos->first()->photo) }}"
                     data-gallery="{{ e($vehGallery) }}"
                     data-caption="{{ $vehCaption }}"
                     style="width:40px;height:40px;object-fit:cover;display:block;">
                @if($vehExtra > 0)
                <div style="position:absolute;inset:0;background:rgba(0,0,0,.48);display:flex;align-items:center;justify-content:center;pointer-events:none;">
                    <span style="color:#fff;font-size:.52rem;font-weight:800;line-height:1;">+{{ $vehExtra }}</span>
                </div>
                @endif
            </div>
            @else
            <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#2563eb,#1d4ed8);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(29,78,216,.25);">
                <i class="ph-fill ph-car-profile" style="font-size:1.1rem;color:#fff;"></i>
            </div>
            @endif
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-bottom:.18rem;">
                    <span style="font-size:.9rem;font-weight:800;color:#0f172a;">{{ $veh->plate_number ?: '—' }}</span>
                    @if($veh->vehicle_type)
                    <span style="background:#eff6ff;color:#1e40af;border-radius:6px;font-size:.62rem;font-weight:800;padding:.08rem .38rem;">{{ $veh->vehicle_type }}</span>
                    @endif
                </div>
                @if($veh->make || $veh->model || $veh->color)
                <div style="font-size:.75rem;color:#64748b;margin-bottom:.14rem;">
                    {{ trim(($veh->make ?? '') . ' ' . ($veh->model ?? '')) ?: '' }}
                    @if($veh->color) <span style="color:#cbd5e1;">&middot;</span> {{ $veh->color }} @endif
                </div>
                @endif
                @if($veh->or_number || $veh->cr_number)
                <div style="font-size:.68rem;color:#94a3b8;">
                    @if($veh->or_number)<span>OR: {{ $veh->or_number }}</span>@endif
                    @if($veh->or_number && $veh->cr_number) &middot; @endif
                    @if($veh->cr_number)<span>CR: {{ $veh->cr_number }}</span>@endif
                </div>
                @endif
                @if($veh->chassis_number)
                <div style="font-size:.68rem;color:#94a3b8;">Chassis: {{ $veh->chassis_number }}</div>
                @endif
                @if($veh->owner_name)
                <div style="margin-top:.3rem;">
                    <span style="display:inline-flex;align-items:center;gap:.22rem;background:#fef9c3;color:#92400e;border-radius:8px;font-size:.63rem;font-weight:800;padding:.1rem .4rem;">
                        <i class="ph ph-user-circle"></i> {{ $veh->owner_name }}
                    </span>
                </div>
                @endif
            </div>
            <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:1rem;flex-shrink:0;align-self:center;"></i>
        </div>
    </a>
@empty
    <div class="motshow-card" style="margin-bottom:.65rem;">
        <div class="mob-empty">
            <i class="ph-fill ph-car mob-empty-icon"></i>
            <div class="mob-empty-text">No vehicles on file</div>
            <div class="mob-empty-sub">Tap "Add Vehicle" above to register one</div>
        </div>
    </div>
@endforelse

{{-- ── Linked Incidents ── --}}
<div class="motshow-section" style="margin-top:.35rem;">Linked Incidents
    @if($incidentCount > 0)
    <span style="margin-left:.35rem;background:#f5f3ff;color:#6d28d9;border:1px solid #c4b5fd;border-radius:999px;font-size:.6rem;font-weight:800;padding:.1rem .45rem;">{{ $incidentCount }}</span>
    @endif
</div>
@forelse($incidents as $inc)
    @php
        $incAccentClass = ['under_investigation' => 'motshow-accent--open', 'cleared' => 'motshow-accent--review', 'solved' => 'motshow-accent--closed'][$inc->status] ?? 'motshow-accent--closed';
        $incIcon   = ['under_investigation' => 'motshow-item-icon--danger', 'cleared' => 'motshow-item-icon--blue', 'solved' => 'motshow-item-icon--slate'][$inc->status] ?? 'motshow-item-icon--slate';
        $sc = ['under_investigation' => 'mob-badge-open', 'cleared' => 'mob-badge-review', 'solved' => 'mob-badge-closed'][$inc->status] ?? 'mob-badge-closed';
    @endphp
    <a href="{{ route('officer.incidents.show', $inc) }}"
       style="display:flex;align-items:flex-start;gap:.85rem;background:#fff;border-radius:16px;border:1px solid rgba(15,23,42,.06);box-shadow:0 2px 10px rgba(15,23,42,.04);margin-bottom:.65rem;overflow:hidden;text-decoration:none;color:inherit;">
        <div class="motshow-accent-bar {{ $incAccentClass }}"></div>
        <div style="display:flex;align-items:flex-start;gap:.75rem;flex:1;min-width:0;padding:.85rem .85rem .85rem 0;">
            <div class="motshow-item-icon {{ $incIcon }}" style="flex-shrink:0;">
                <i class="ph-fill ph-flag"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.9rem;font-weight:800;color:#0f172a;margin-bottom:.18rem;">{{ $inc->incident_number }}</div>
                <div style="font-size:.72rem;color:#64748b;display:flex;align-items:center;gap:.3rem;flex-wrap:wrap;">
                    <i class="ph ph-calendar-blank"></i>
                    {{ $inc->date_of_incident ? \Carbon\Carbon::parse($inc->date_of_incident)->format('M d, Y') : '—' }}
                    @if($inc->location)
                    <span style="color:#cbd5e1;">&middot;</span>
                    <i class="ph ph-map-pin"></i>
                    {{ \Illuminate\Support\Str::limit($inc->location, 24) }}
                    @endif
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.3rem;flex-shrink:0;">
                <span class="mob-badge {{ $sc }}">{{ ucfirst(str_replace('_', ' ', $inc->status)) }}</span>
                <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.82rem;"></i>
            </div>
        </div>
    </a>
@empty
    <div class="motshow-card" style="margin-bottom:.65rem;">
        <div class="mob-empty">
            <i class="ph ph-flag mob-empty-icon"></i>
            <div class="mob-empty-text">No incidents linked</div>
        </div>
    </div>
@endforelse

{{-- Edit FAB --}}
<a href="{{ route('officer.motorists.edit', $violator) }}" class="mob-fab" title="Edit Motorist">
    <i class="ph-bold ph-pencil-simple"></i>
</a>

@endsection
