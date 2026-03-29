@extends('layouts.mobile')
@section('title', $violator->last_name . ', ' . $violator->first_name)
@section('back_url', route('officer.motorists.index'))

@section('content')

@php
    $vc = $violator->violations->count();
    $restrCodes = $violator->license_restriction
        ? array_filter(array_map('trim', explode(',', $violator->license_restriction)))
        : [];
    $restrDesc = [
        'A'=>'Motorcycle','A1'=>'MC w/ Sidecar','B'=>'Light Vehicle',
        'B1'=>'Light Vehicle (Prof.)','B2'=>'Light Vehicle w/ Trailer',
        'C'=>'Medium/Heavy Truck','D'=>'Bus','BE'=>'Light + Heavy Trailer','CE'=>'Large Truck + Trailer'
    ];
@endphp

{{-- ── Profile Header ── --}}
<div class="mob-profile-header">
    <div class="d-flex align-items-center gap-3">
        @if($violator->photo)
            <img src="{{ uploaded_file_url($violator->photo) }}" alt="Photo"
                 class="mob-photo-single"
                 data-full="{{ uploaded_file_url($violator->photo) }}"
                 style="width:64px;height:64px;border-radius:18px;object-fit:cover;border:3px solid rgba(255,255,255,.3);flex-shrink:0;cursor:zoom-in;">
        @else
            <div style="width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.6rem;font-weight:800;color:#fff;">
                {{ strtoupper(substr($violator->first_name, 0, 1)) }}
            </div>
        @endif
        <div style="flex:1;min-width:0;">
            <div style="font-size:1.05rem;font-weight:800;color:#fff;line-height:1.25;">
                {{ $violator->last_name }}, {{ $violator->first_name }}
                @if($violator->middle_name) {{ $violator->middle_name }} @endif
            </div>
            @if($violator->license_number)
            <div style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(255,255,255,.18);border-radius:20px;padding:.18rem .6rem;margin-top:.4rem;">
                <i class="ph ph-identification-badge" style="font-size:.68rem;color:rgba(255,255,255,.8);"></i>
                <span style="font-size:.7rem;font-weight:700;color:#fff;">{{ $violator->license_number }}</span>
            </div>
            @endif
            {{-- Status badge --}}
            <div style="margin-top:.4rem;">
                @if($vc >= 3)
                    <span style="display:inline-flex;align-items:center;gap:.25rem;background:rgba(239,68,68,.25);border:1px solid rgba(239,68,68,.4);border-radius:20px;padding:.15rem .55rem;font-size:.6rem;font-weight:800;color:#fca5a5;text-transform:uppercase;letter-spacing:.05em;">
                        <i class="ph-fill ph-fire"></i> Recidivist
                    </span>
                @elseif($vc == 2)
                    <span style="display:inline-flex;align-items:center;gap:.25rem;background:rgba(251,191,36,.2);border:1px solid rgba(251,191,36,.35);border-radius:20px;padding:.15rem .55rem;font-size:.6rem;font-weight:800;color:#fde68a;text-transform:uppercase;letter-spacing:.05em;">
                        <i class="ph-fill ph-shield-warning"></i> Repeat Offender
                    </span>
                @elseif($vc == 1)
                    <span style="display:inline-flex;align-items:center;gap:.25rem;background:rgba(147,197,253,.2);border:1px solid rgba(147,197,253,.35);border-radius:20px;padding:.15rem .55rem;font-size:.6rem;font-weight:800;color:#bfdbfe;text-transform:uppercase;letter-spacing:.05em;">
                        <i class="ph-fill ph-record"></i> 1st Violation
                    </span>
                @else
                    <span style="display:inline-flex;align-items:center;gap:.25rem;background:rgba(74,222,128,.18);border:1px solid rgba(74,222,128,.3);border-radius:20px;padding:.15rem .55rem;font-size:.6rem;font-weight:800;color:#86efac;text-transform:uppercase;letter-spacing:.05em;">
                        <i class="ph-fill ph-shield-check"></i> Clean Record
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats row --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:1rem;">
        <div style="background:rgba(255,255,255,.15);border-radius:10px;padding:.55rem .4rem;text-align:center;">
            <div style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1;">{{ $violator->violations->count() }}</div>
            <div style="font-size:.58rem;font-weight:700;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.05em;margin-top:.12rem;">Violations</div>
        </div>
        <div style="background:rgba(255,255,255,.15);border-radius:10px;padding:.55rem .4rem;text-align:center;">
            <div style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1;">{{ $violator->vehicles->count() }}</div>
            <div style="font-size:.58rem;font-weight:700;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.05em;margin-top:.12rem;">Vehicles</div>
        </div>
        <div style="background:rgba(255,255,255,.15);border-radius:10px;padding:.55rem .4rem;text-align:center;">
            <div style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1;">{{ $incidents->count() }}</div>
            <div style="font-size:.58rem;font-weight:700;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.05em;margin-top:.12rem;">Incidents</div>
        </div>
    </div>
</div>

{{-- ── Motorist Photo ── --}}
@if($violator->photo)
<div class="mob-card">
    <div class="mob-section-title">ID Photo</div>
    <div class="mob-card-body pt-1 text-center">
        <img src="{{ uploaded_file_url($violator->photo) }}"
             alt="Motorist Photo"
             class="mob-photo-single"
             data-full="{{ uploaded_file_url($violator->photo) }}"
             style="max-width:100%;max-height:260px;object-fit:contain;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.1);cursor:zoom-in;">
    </div>
</div>
@endif

{{-- ── Personal Information ── --}}
<div class="mob-card">
    <div class="mob-section-title">Personal Information</div>
    <div class="mob-card-body pt-0">
        <div class="mob-info-grid">
            @if($violator->date_of_birth)
            <div>
                <div class="mob-info-label">Date of Birth</div>
                <div class="mob-info-value">{{ $violator->date_of_birth->format('M d, Y') }}</div>
            </div>
            @endif
            @if($violator->gender)
            <div>
                <div class="mob-info-label">Sex</div>
                <div class="mob-info-value">{{ $violator->gender }}</div>
            </div>
            @endif
            @if($violator->civil_status)
            <div>
                <div class="mob-info-label">Civil Status</div>
                <div class="mob-info-value">{{ $violator->civil_status }}</div>
            </div>
            @endif
            @if($violator->blood_type)
            <div>
                <div class="mob-info-label">Blood Type</div>
                <div class="mob-info-value">{{ $violator->blood_type }}</div>
            </div>
            @endif
            @if($violator->height)
            <div>
                <div class="mob-info-label">Height</div>
                <div class="mob-info-value">{{ $violator->height }}</div>
            </div>
            @endif
            @if($violator->weight)
            <div>
                <div class="mob-info-label">Weight</div>
                <div class="mob-info-value">{{ $violator->weight }}</div>
            </div>
            @endif
            @if($violator->contact_number)
            <div class="mob-info-grid-full">
                <div class="mob-info-label"><i class="ph ph-phone me-1"></i>Contact</div>
                <div class="mob-info-value">{{ $violator->contact_number }}</div>
            </div>
            @endif
            @if($violator->email)
            <div class="mob-info-grid-full">
                <div class="mob-info-label"><i class="ph ph-envelope me-1"></i>Email</div>
                <div class="mob-info-value" style="word-break:break-all;">{{ $violator->email }}</div>
            </div>
            @endif
            @if($violator->valid_id)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Valid ID</div>
                <div class="mob-info-value">{{ $violator->valid_id }}</div>
            </div>
            @endif
            @if($violator->place_of_birth)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Place of Birth</div>
                <div class="mob-info-value">{{ $violator->place_of_birth }}</div>
            </div>
            @endif
            @if($violator->temporary_address)
            <div class="mob-info-grid-full">
                <div class="mob-info-label"><i class="ph ph-map-pin me-1"></i>Address</div>
                <div class="mob-info-value">{{ $violator->temporary_address }}</div>
            </div>
            @endif
            @if($violator->permanent_address)
            <div class="mob-info-grid-full">
                <div class="mob-info-label"><i class="ph ph-house me-1"></i>Permanent Address</div>
                <div class="mob-info-value">{{ $violator->permanent_address }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── License Information ── --}}
@if($violator->license_number || $violator->license_type || $violator->license_expiry_date || $restrCodes)
<div class="mob-card">
    <div class="mob-section-title">License Information</div>
    <div class="mob-card-body pt-0">
        <div class="mob-info-grid">
            @if($violator->license_number)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">License No.</div>
                <div class="mob-info-value" style="font-family:ui-monospace,monospace;">{{ $violator->license_number }}</div>
            </div>
            @endif
            @if($violator->license_type)
            <div>
                <div class="mob-info-label">Type</div>
                <div class="mob-info-value">{{ $violator->license_type }}</div>
            </div>
            @endif
            @if($violator->license_expiry_date)
            <div>
                <div class="mob-info-label">Expiry</div>
                <div class="mob-info-value {{ $violator->license_expiry_date->isPast() ? 'text-danger' : '' }}">
                    {{ $violator->license_expiry_date->format('M d, Y') }}
                    @if($violator->license_expiry_date->isPast())
                        <span style="font-size:.63rem;background:#fee2e2;color:#dc2626;padding:.1rem .35rem;border-radius:6px;margin-left:.25rem;">Expired</span>
                    @endif
                </div>
            </div>
            @endif
            @if($restrCodes)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Restrictions</div>
                <div class="mob-info-value">
                    @foreach($restrCodes as $code)
                        <span title="{{ $restrDesc[$code] ?? '' }}"
                              style="display:inline-block;background:#fef3c7;color:#92400e;border-radius:8px;padding:.1rem .4rem;font-size:.7rem;font-weight:700;margin:.1rem .15rem 0 0;">{{ $code }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Actions ── --}}
<div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.875rem;">
    <a href="{{ route('officer.violations.create', $violator) }}" class="mob-btn-primary mob-btn-danger" style="display:flex;">
        <i class="ph-fill ph-file-plus"></i> Record Violation
    </a>
    <a href="{{ route('officer.motorists.edit', $violator) }}" class="mob-btn-outline">
        <i class="ph ph-pencil"></i> Edit Motorist
    </a>
</div>

{{-- ── Violations ── --}}
<div class="mob-card">
    <div class="mob-section-title">Violations ({{ $violator->violations->count() }})</div>

    @forelse($violator->violations as $viol)
    @php
        $isOverdue = $viol->status === 'pending' && $viol->created_at->lte(now()->subHours(72));
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
    <a href="{{ route('officer.violations.show', $viol) }}" class="mob-list-item">
        <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:.85rem;">
            <i class="ph-fill ph-warning-circle" style="color:#dc2626;font-size:.9rem;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.875rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $viol->violationType->name ?? '—' }}
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-top:.05rem;">
                {{ $viol->date_of_violation ? $viol->date_of_violation->format('M d, Y') : '—' }}
                @if($viol->location) · {{ Str::limit($viol->location, 28) }} @endif
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
            <span class="mob-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
            <i class="ph ph-caret-right" style="color:#d6d3d1;font-size:.75rem;"></i>
        </div>
    </a>
    @empty
    <div class="mob-empty">
        <i class="ph ph-file-x mob-empty-icon"></i>
        <div class="mob-empty-text">No violations recorded</div>
    </div>
    @endforelse
</div>

{{-- ── Vehicles ── --}}
<div class="mob-card">
    <div class="d-flex align-items-center justify-content-between pe-3">
        <div class="mob-section-title">Vehicles ({{ $violator->vehicles->count() }})</div>
        <a href="{{ route('officer.motorists.vehicles.create', $violator) }}"
           style="font-size:.72rem;font-weight:700;color:#1d4ed8;text-decoration:none;">
            <i class="ph ph-plus-circle me-1"></i>Add
        </a>
    </div>

    @forelse($violator->vehicles as $veh)
    <div class="mob-list-item" style="cursor:default;align-items:flex-start;padding-top:.9rem;padding-bottom:.9rem;">
        <div style="width:36px;height:36px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:.875rem;margin-top:.1rem;">
            <i class="ph-fill ph-truck" style="color:#64748b;font-size:.9rem;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.875rem;font-weight:700;color:#0f172a;">
                {{ $veh->plate_number }}
                @if($veh->vehicle_type)
                <span style="display:inline-block;background:#dbeafe;color:#1e40af;border-radius:4px;padding:0 .35rem;font-size:.63rem;font-weight:700;margin-left:.25rem;">{{ $veh->vehicle_type }}</span>
                @endif
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-top:.05rem;">
                {{ trim($veh->make . ' ' . $veh->model) ?: '—' }}
                @if($veh->color) · {{ $veh->color }} @endif
            </div>
            @if($veh->or_number || $veh->cr_number)
            <div style="display:flex;gap:.75rem;margin-top:.3rem;font-size:.7rem;color:#64748b;">
                @if($veh->or_number)
                <span><span style="color:#94a3b8;">OR:</span> <span style="font-family:ui-monospace,monospace;font-weight:600;">{{ $veh->or_number }}</span></span>
                @endif
                @if($veh->cr_number)
                <span><span style="color:#94a3b8;">CR:</span> <span style="font-family:ui-monospace,monospace;font-weight:600;">{{ $veh->cr_number }}</span></span>
                @endif
            </div>
            @endif
            @if($veh->chassis_number)
            <div style="font-size:.7rem;color:#64748b;margin-top:.15rem;">
                <span style="color:#94a3b8;">Chassis:</span> <span style="font-family:ui-monospace,monospace;font-weight:600;">{{ $veh->chassis_number }}</span>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="mob-empty">
        <i class="ph-fill ph-truck mob-empty-icon"></i>
        <div class="mob-empty-text">No vehicles on file</div>
    </div>
    @endforelse
</div>

{{-- ── Incidents ── --}}
<div class="mob-card">
    <div class="mob-section-title">Incidents ({{ $incidents->count() }})</div>

    @forelse($incidents as $inc)
    @php $sc = ['open'=>'mob-badge-open','under_review'=>'mob-badge-review','closed'=>'mob-badge-closed'][$inc->status] ?? 'mob-badge-closed' @endphp
    <a href="{{ route('officer.incidents.show', $inc) }}" class="mob-list-item">
        <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:.85rem;">
            <i class="ph-fill ph-flag" style="color:#dc2626;font-size:.8rem;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.875rem;font-weight:700;color:#0f172a;">{{ $inc->incident_number }}</div>
            <div style="font-size:.72rem;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:.05rem;">
                {{ $inc->date_of_incident ? \Carbon\Carbon::parse($inc->date_of_incident)->format('M d, Y') : '—' }}
                @if($inc->location) · {{ Str::limit($inc->location, 28) }} @endif
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
            <span class="mob-badge {{ $sc }}">{{ ucfirst(str_replace('_',' ',$inc->status)) }}</span>
            <i class="ph ph-caret-right" style="color:#d6d3d1;font-size:.75rem;"></i>
        </div>
    </a>
    @empty
    <div class="mob-empty">
        <i class="ph ph-flag mob-empty-icon"></i>
        <div class="mob-empty-text">No incidents recorded</div>
    </div>
    @endforelse
</div>

@endsection
