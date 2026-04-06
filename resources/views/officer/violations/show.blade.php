@extends('layouts.mobile')
@section('title', $violation->ticket_number ?? 'Violation Detail')
@section('back_url', route('officer.motorists.show', $violation->violator))

@section('content')

{{-- ── Status Header ── --}}
@php
    $isOverdue = $violation->status === 'pending' && $violation->date_of_violation <= now()->subHours(72);
    $badgeClass = match($violation->status) {
        'settled'   => 'mob-badge-settled',
        'contested' => 'mob-badge-contested',
        default     => $isOverdue ? 'mob-badge-overdue' : 'mob-badge-pending',
    };
    $badgeLabel = match($violation->status) {
        'settled'   => 'Settled',
        'contested' => 'Contested',
        default     => $isOverdue ? 'Overdue' : 'Pending',
    };
@endphp

<div class="mob-card" style="border-left:4px solid #dc2626;">
    <div class="mob-card-body">
        <div class="d-flex align-items-start justify-content-between">
            <div style="flex:1;min-width:0;padding-right:.75rem;">
                <div style="font-size:.62rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;">Violation Type</div>
                <div style="font-size:1rem;font-weight:800;color:#0f172a;line-height:1.25;margin-top:.15rem;">
                    {{ $violation->violationType->name ?? '—' }}
                </div>
                @if($violation->violationType && $violation->violationType->fine_amount > 0)
                <div style="display:inline-flex;align-items:center;gap:.3rem;background:#fef2f2;border:1px solid #fca5a5;border-radius:20px;padding:.2rem .65rem;margin-top:.5rem;">
                    <i class="ph-fill ph-money" style="font-size:.72rem;color:#b91c1c;"></i>
                    <span style="font-size:.78rem;font-weight:800;color:#b91c1c;">₱{{ number_format($violation->violationType->fine_amount, 2) }}</span>
                </div>
                @endif
            </div>
            <span class="mob-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        </div>
    </div>
</div>

{{-- ── Info ── --}}
<div class="mob-card">
    <div class="mob-section-title">Details</div>
    <div class="mob-card-body pt-0">
        <div class="mob-info-grid">
            <div>
                <div class="mob-info-label">Date</div>
                <div class="mob-info-value">{{ $violation->date_of_violation ? $violation->date_of_violation->format('M d, Y') : '—' }}</div>
            </div>
            @if($violation->ticket_number)
            <div>
                <div class="mob-info-label">Ticket #</div>
                <div class="mob-info-value">{{ $violation->ticket_number }}</div>
            </div>
            @endif
            @if($violation->incident)
            <div>
                <div class="mob-info-label">Incident</div>
                <div class="mob-info-value">
                    <a href="{{ route('officer.incidents.show', $violation->incident) }}" style="color:#1d4ed8;text-decoration:none;">
                        {{ $violation->incident->incident_number }}
                    </a>
                </div>
            </div>
            @endif
            @if($violation->location)
            <div class="mob-info-grid-full">
                <div class="mob-info-label"><i class="ph ph-map-pin me-1"></i>Location</div>
                <div class="mob-info-value">{{ $violation->location }}</div>
            </div>
            @endif
            @if($violation->recorder)
            <div class="mob-info-grid-full">
                <div class="mob-info-label"><i class="ph ph-user me-1"></i>Recorded by</div>
                <div class="mob-info-value">{{ $violation->recorder->name }}</div>
            </div>
            @endif
            @if($violation->notes)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Notes</div>
                <div class="mob-info-value" style="font-weight:400;color:#334155;">{{ $violation->notes }}</div>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- ── Vehicle ── --}}
@php
    $veh   = $violation->vehicle;
    $plate = $veh ? $veh->plate_number : $violation->vehicle_plate;
    $make  = $veh ? $veh->make  : $violation->vehicle_make;
    $model = $veh ? $veh->model : $violation->vehicle_model;
    $color = $veh ? $veh->color : $violation->vehicle_color;
    $owner = $violation->vehicle_owner_name ?: ($veh?->owner_name ?: $veh?->violator?->full_name);
    $orNo  = $veh ? $veh->or_number : $violation->vehicle_or_number;
    $crNo  = $veh ? $veh->cr_number : $violation->vehicle_cr_number;
    $chaNo = $veh ? $veh->chassis_number : $violation->vehicle_chassis;
@endphp
@if($plate || $make || $model || $owner)
<div class="mob-card">
    <div class="mob-section-title">Vehicle Involved</div>
    <div class="mob-card-body pt-0">
        <div class="mob-info-grid">
            @if($plate)
            <div>
                <div class="mob-info-label">Plate No.</div>
                <div class="mob-info-value" style="font-size:.95rem;">{{ $plate }}</div>
            </div>
            @endif
            @if($color)
            <div>
                <div class="mob-info-label">Color</div>
                <div class="mob-info-value">{{ $color }}</div>
            </div>
            @endif
            @if($make || $model)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Make / Model</div>
                <div class="mob-info-value">{{ trim($make . ' ' . $model) }}</div>
            </div>
            @endif
            @if($owner)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Registered Owner</div>
                <div class="mob-info-value">{{ $owner }}</div>
            </div>
            @endif
            @if($orNo)
            <div>
                <div class="mob-info-label">OR #</div>
                <div class="mob-info-value">{{ $orNo }}</div>
            </div>
            @endif
            @if($crNo)
            <div>
                <div class="mob-info-label">CR #</div>
                <div class="mob-info-value">{{ $crNo }}</div>
            </div>
            @endif
            @if($chaNo)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Chassis</div>
                <div class="mob-info-value">{{ $chaNo }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Citation Ticket Photo ── --}}
@if($violation->citation_ticket_photo)
<div class="mob-card">
    <div class="mob-section-title">Citation Ticket</div>
    <div class="mob-card-body pt-1 text-center">
        <img src="{{ uploaded_file_url($violation->citation_ticket_photo) }}"
             alt="Citation Ticket"
             class="mob-photo-thumb"
             data-full="{{ uploaded_file_url($violation->citation_ticket_photo) }}"
             data-caption="Citation Ticket — {{ $violation->violationType->name ?? 'Violation' }}"
             style="max-width:100%;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.1);cursor:zoom-in;">
        <div style="font-size:.7rem;color:#94a3b8;text-align:center;margin-top:.5rem;"><i class="ph ph-magnifying-glass-plus me-1"></i>Tap to enlarge</div>
    </div>
</div>
@endif

{{-- ── Vehicle Photos ── --}}
@if($violation->vehiclePhotos->isNotEmpty())
<div class="mob-card">
    <div class="mob-section-title">Vehicle Photos</div>
    <div class="mob-card-body pt-1">
        <div class="row g-2">
            @foreach($violation->vehiclePhotos as $photo)
            <div class="col-6">
                <img src="{{ uploaded_file_url($photo->photo) }}"
                     alt="Vehicle photo"
                     class="mob-photo-thumb"
                     data-full="{{ uploaded_file_url($photo->photo) }}"
                     data-caption="Vehicle Photo"
                     style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);cursor:zoom-in;">
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@if($violation->status === 'settled' || $violation->or_number || $violation->cashier_name || $violation->receipt_photo)
<div class="mob-card">
    <div class="mob-section-title">Settlement Details</div>
    <div class="mob-card-body pt-0">
        <div class="mob-info-grid">
            @if($violation->settled_at)
            <div>
                <div class="mob-info-label">Settled At</div>
                <div class="mob-info-value">{{ $violation->settled_at->format('M d, Y g:i A') }}</div>
            </div>
            @endif
            @if($violation->or_number)
            <div>
                <div class="mob-info-label">OR Number</div>
                <div class="mob-info-value">{{ $violation->or_number }}</div>
            </div>
            @endif
            @if($violation->cashier_name)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Cashier</div>
                <div class="mob-info-value">{{ $violation->cashier_name }}</div>
            </div>
            @endif
        </div>

        @if($violation->receipt_photo)
        <div style="margin-top:.9rem;">
            <div class="mob-info-label" style="margin-bottom:.45rem;">Receipt Photo</div>
            <img src="{{ uploaded_file_url($violation->receipt_photo) }}"
                 alt="Receipt Photo"
                 class="mob-photo-thumb"
                 data-full="{{ uploaded_file_url($violation->receipt_photo) }}"
                 data-caption="Settlement Receipt"
                 style="max-width:100%;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.1);cursor:zoom-in;">
        </div>
        @endif
    </div>
</div>
@endif

@endsection
