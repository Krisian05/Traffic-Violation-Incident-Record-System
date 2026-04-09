@extends('layouts.mobile')
@section('title', $violation->ticket_number ?? 'Violation Detail')
@section('back_url', $violation->violator ? route('officer.motorists.show', $violation->violator) : route('officer.motorists.index'))

@push('styles')
<style>
.motshow-hero{position:relative;overflow:hidden;background:linear-gradient(160deg,#0f2167 0%,#1d4ed8 56%,#1e40af 100%);border-radius:24px;padding:1.15rem;margin-bottom:1rem;box-shadow:0 14px 36px rgba(15,33,103,.36)}.motshow-hero::before{content:'';position:absolute;top:-78px;right:-46px;width:176px;height:176px;border-radius:50%;background:rgba(255,255,255,.08)}.motshow-hero::after{content:'';position:absolute;left:-22px;bottom:-62px;width:138px;height:138px;border-radius:50%;background:rgba(255,255,255,.05)}.motshow-hero-inner{position:relative;z-index:1}.motshow-chip{display:inline-flex;align-items:center;gap:.34rem;padding:.22rem .62rem;border-radius:999px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.88);font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.motshow-name{margin-top:.65rem;font-size:1.12rem;font-weight:800;line-height:1.2;color:#fff}.motshow-subtitle{margin-top:.2rem;color:rgba(255,255,255,.72);font-size:.73rem;line-height:1.4}.motshow-meta-row{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.6rem}.motshow-meta-chip{display:inline-flex;align-items:center;gap:.24rem;padding:.18rem .48rem;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.86);font-size:.62rem;font-weight:700}.motshow-stat{text-align:center;padding:.78rem .42rem;border-radius:15px;background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.16)}.motshow-stat-num{font-size:1.3rem;line-height:1;font-weight:800;color:#fff}.motshow-stat-label{margin-top:.22rem;font-size:.56rem;font-weight:800;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.08em}.motshow-alert{display:flex;align-items:flex-start;gap:.75rem;border-radius:16px;padding:.9rem 1rem;margin-bottom:.95rem;border:1px solid transparent}.motshow-alert-icon{width:42px;height:42px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.05rem}.motshow-alert--danger{background:linear-gradient(135deg,#fef2f2,#fff);border-color:#fecaca}.motshow-alert--danger .motshow-alert-icon{background:#fee2e2;color:#dc2626}.motshow-alert-title{font-size:.9rem;font-weight:800;color:#0f172a}.motshow-alert-text{margin-top:.16rem;font-size:.74rem;line-height:1.45;color:#64748b}.motshow-section{display:flex;align-items:center;gap:.5rem;margin-bottom:.65rem;font-size:.6rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em}.motshow-section::after{content:'';flex:1;height:1px;background:#e2e8f0}.motshow-card{background:#fff;border-radius:18px;border:1px solid rgba(15,23,42,.05);box-shadow:0 3px 16px rgba(15,23,42,.06);overflow:hidden;margin-bottom:.9rem}.motshow-card-head{padding:.88rem 1rem .32rem;font-size:.64rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em}.motshow-card-body{padding:0 1rem 1rem}.motshow-feature-box{background:linear-gradient(135deg,#eff6ff,#f8fbff);border:1px solid #dbeafe;border-radius:16px;padding:.9rem}.motshow-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem .8rem}.motshow-info-full{grid-column:1/-1}.motshow-label{margin-bottom:.18rem;font-size:.63rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em}.motshow-value{font-size:.88rem;line-height:1.38;font-weight:700;color:#0f172a}.motshow-value--soft{font-weight:600;color:#334155}.motshow-item{display:flex;align-items:flex-start;gap:.85rem;padding:.92rem;background:#fff;border:1px solid rgba(15,23,42,.05);border-radius:16px;color:inherit;text-decoration:none;box-shadow:0 2px 10px rgba(15,23,42,.04)}.motshow-item--static{cursor:default}.motshow-item-icon{width:42px;height:42px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;box-shadow:0 6px 16px rgba(15,23,42,.12)}.motshow-item-icon--blue{background:linear-gradient(135deg,#2563eb,#1d4ed8)}.motshow-item-title{font-size:.88rem;line-height:1.3;font-weight:800;color:#0f172a}.motshow-item-meta{margin-top:.14rem;font-size:.71rem;line-height:1.46;color:#64748b}.motshow-item-submeta{margin-top:.2rem;font-size:.67rem;line-height:1.45;color:#94a3b8}.motshow-tag{display:inline-flex;align-items:center;gap:.22rem;padding:.12rem .44rem;border-radius:8px;font-size:.63rem;font-weight:800}.motshow-tag--plate{background:#eff6ff;color:#1e40af}.motshow-tag--owner{background:#fef9c3;color:#92400e}
</style>
@endpush

@section('content')

@php
    $isOverdue = $violation->status === 'pending'
        && $violation->date_of_violation
        && $violation->date_of_violation <= now()->subHours(72);
    $status = $violation->status ?? 'pending';

    $statusLabel = match(true) {
        $status === 'settled'   => 'Settled',
        $status === 'contested' => 'Contested',
        $isOverdue              => 'Overdue',
        default                 => 'Pending',
    };
    $statusIcon = match(true) {
        $status === 'settled'   => 'ph-check-circle',
        $status === 'contested' => 'ph-scales',
        $isOverdue              => 'ph-warning-octagon',
        default                 => 'ph-clock',
    };

    $veh   = $violation->vehicle;
    $plate = $veh ? $veh->plate_number : $violation->vehicle_plate;
    $make  = $veh ? $veh->make  : $violation->vehicle_make;
    $model = $veh ? $veh->model : $violation->vehicle_model;
    $color = $veh ? $veh->color : $violation->vehicle_color;
    $owner = $violation->vehicle_owner_name ?: ($veh?->owner_name ?: $veh?->violator?->full_name);
    $orNo  = $veh ? $veh->or_number : $violation->vehicle_or_number;
    $crNo  = $veh ? $veh->cr_number : $violation->vehicle_cr_number;
    $chaNo = $veh ? $veh->chassis_number : $violation->vehicle_chassis;
    $hasVehicle = $plate || $make || $model || $owner;
@endphp

{{-- ── Hero Header ── --}}
<div class="motshow-hero" style="background:linear-gradient(160deg,#7f1d1d 0%,#dc2626 55%,#b91c1c 100%);box-shadow:0 14px 36px rgba(127,29,29,.36);">
    <div class="motshow-hero::before" style="display:none;"></div>
    <div class="motshow-hero-inner">
        <div class="d-flex align-items-start gap-3">

            {{-- Violation type icon --}}
            <div style="width:72px;height:72px;border-radius:20px;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 10px 22px rgba(0,0,0,.18);">
                <i class="ph-fill ph-warning-circle" style="font-size:2rem;color:#fff;"></i>
            </div>

            <div style="flex:1;min-width:0;">
                <div class="motshow-chip">
                    <i class="ph-fill ph-ticket"></i>
                    Violation Record
                </div>
                <div class="motshow-name" style="font-size:1rem;">
                    {{ $violation->violationType?->name ?? 'Unknown Violation' }}
                </div>
                @if($violation->ticket_number)
                <div class="motshow-subtitle">Ticket #{{ $violation->ticket_number }}</div>
                @endif
                <div class="motshow-meta-row">
                    @if($violation->date_of_violation)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-calendar-blank"></i>
                        {{ $violation->date_of_violation->format('M d, Y') }}
                    </span>
                    @endif
                    @if($violation->location)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-map-pin"></i>
                        {{ \Illuminate\Support\Str::limit($violation->location, 22) }}
                    </span>
                    @endif
                </div>
                {{-- Status badge --}}
                <span style="display:inline-flex;align-items:center;gap:.28rem;margin-top:.55rem;padding:.2rem .62rem;border-radius:999px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.25);color:#fff;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;">
                    <i class="ph-fill {{ $statusIcon }}"></i>
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        {{-- Fine amount stat --}}
        @if($violation->violationType && $violation->violationType->fine_amount > 0)
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin-top:1rem;">
            <div class="motshow-stat">
                <div class="motshow-stat-num" style="font-size:1.1rem;">₱{{ number_format($violation->violationType->fine_amount, 0) }}</div>
                <div class="motshow-stat-label">Fine Amount</div>
            </div>
            <div class="motshow-stat">
                <div class="motshow-stat-num" style="font-size:1.1rem;">{{ $violation->date_of_violation?->format('Y') ?? '—' }}</div>
                <div class="motshow-stat-label">Year</div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ── Motorist card ── --}}
@php $motorist = $violation->violator; @endphp
@if($motorist)
<a href="{{ route('officer.motorists.show', $motorist) }}" style="display:flex;align-items:center;gap:.85rem;padding:.9rem 1rem;background:#fff;border-radius:18px;border:1px solid rgba(15,23,42,.05);box-shadow:0 3px 16px rgba(15,23,42,.06);margin-bottom:.9rem;text-decoration:none;color:inherit;">
    <div style="width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#1d4ed8,#1e40af);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;box-shadow:0 4px 12px rgba(29,78,216,.3);">
        @if($motorist->photo)
            <img src="{{ uploaded_file_url($motorist->photo) }}" style="width:100%;height:100%;object-fit:cover;">
        @else
            <span style="font-size:.88rem;font-weight:800;color:#fff;">{{ strtoupper(substr($motorist->first_name,0,1).substr($motorist->last_name,0,1)) }}</span>
        @endif
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:.62rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Motorist</div>
        <div style="font-size:.92rem;font-weight:800;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $motorist->full_name }}</div>
        @if($motorist->license_number)
        <div style="font-size:.72rem;color:#64748b;margin-top:.05rem;">License {{ $motorist->license_number }}</div>
        @endif
    </div>
    <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:1rem;flex-shrink:0;"></i>
</a>
@endif

{{-- ── Overdue alert ── --}}
@if($isOverdue && $status === 'pending')
<div class="motshow-alert motshow-alert--danger" style="margin-bottom:.9rem;">
    <div class="motshow-alert-icon"><i class="ph-fill ph-warning-octagon"></i></div>
    <div>
        <div class="motshow-alert-title">Overdue Violation</div>
        <div class="motshow-alert-text">This violation is past 72 hours and remains unsettled. Follow-up action may be required.</div>
    </div>
</div>
@endif

{{-- ── Violation Details ── --}}
<div class="motshow-section">Violation Details</div>
<div class="motshow-card">
    <div class="motshow-card-head">Record Info</div>
    <div class="motshow-card-body">
        <div class="motshow-feature-box">
            <div class="motshow-info-grid">
                @if($violation->date_of_violation)
                <div>
                    <div class="motshow-label"><i class="ph ph-calendar-blank me-1"></i>Date</div>
                    <div class="motshow-value">{{ $violation->date_of_violation->format('M d, Y') }}</div>
                </div>
                @endif
                @if($violation->ticket_number)
                <div>
                    <div class="motshow-label"><i class="ph ph-ticket me-1"></i>Ticket #</div>
                    <div class="motshow-value" style="font-family:ui-monospace,monospace;font-size:.82rem;">{{ $violation->ticket_number }}</div>
                </div>
                @endif
                @if($violation->incident)
                <div>
                    <div class="motshow-label"><i class="ph ph-flag me-1"></i>Incident</div>
                    <div class="motshow-value">
                        <a href="{{ route('officer.incidents.show', $violation->incident) }}" style="color:#1d4ed8;text-decoration:none;">{{ $violation->incident->incident_number }}</a>
                    </div>
                </div>
                @endif
                @if($violation->location)
                <div class="motshow-info-full">
                    <div class="motshow-label"><i class="ph ph-map-pin me-1"></i>Location</div>
                    <div class="motshow-value motshow-value--soft">{{ $violation->location }}</div>
                </div>
                @endif
                @if($violation->recorder)
                <div class="motshow-info-full">
                    <div class="motshow-label"><i class="ph ph-user me-1"></i>Recorded By</div>
                    <div class="motshow-value motshow-value--soft">{{ $violation->recorder->name }}</div>
                </div>
                @endif
                @if($violation->notes)
                <div class="motshow-info-full">
                    <div class="motshow-label"><i class="ph ph-note me-1"></i>Notes</div>
                    <div class="motshow-value motshow-value--soft" style="font-weight:400;">{{ $violation->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Vehicle ── --}}
@if($hasVehicle)
<div class="motshow-section">Vehicle Involved</div>
<div class="motshow-item motshow-item--static" style="margin-bottom:.9rem;">
    <div class="motshow-item-icon motshow-item-icon--blue">
        <i class="ph-fill ph-car-profile"></i>
    </div>
    <div style="flex:1;min-width:0;">
        @if($plate)
        <div class="motshow-item-title">
            {{ $plate }}
            @if($color)
            <span class="motshow-tag motshow-tag--plate">{{ $color }}</span>
            @endif
        </div>
        @endif
        @if($make || $model)
        <div class="motshow-item-meta">{{ trim($make . ' ' . $model) }}</div>
        @endif
        @if($owner)
        <div class="mt-1">
            <span class="motshow-tag motshow-tag--owner">
                <i class="ph ph-user-circle"></i> {{ $owner }}
            </span>
        </div>
        @endif
        @if($orNo || $crNo)
        <div class="motshow-item-submeta" style="margin-top:.3rem;">
            @if($orNo) OR: {{ $orNo }}@endif
            @if($orNo && $crNo) &middot; @endif
            @if($crNo) CR: {{ $crNo }}@endif
        </div>
        @endif
        @if($chaNo)
        <div class="motshow-item-submeta">Chassis: {{ $chaNo }}</div>
        @endif
    </div>
</div>
@endif

{{-- ── Citation Ticket Photo ── --}}
@if($violation->citation_ticket_photo)
<div class="motshow-section">Citation Ticket</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    <div class="motshow-card-body" style="padding:1rem;">
        <img src="{{ uploaded_file_url($violation->citation_ticket_photo) }}"
             alt="Citation Ticket"
             class="mob-photo-thumb"
             data-full="{{ uploaded_file_url($violation->citation_ticket_photo) }}"
             data-caption="Citation Ticket — {{ $violation->violationType?->name ?? 'Violation' }}"
             style="width:100%;border-radius:14px;box-shadow:0 4px 16px rgba(15,23,42,.1);cursor:zoom-in;display:block;">
        <div style="display:flex;align-items:center;justify-content:center;gap:.35rem;margin-top:.6rem;font-size:.7rem;color:#94a3b8;">
            <i class="ph ph-magnifying-glass-plus"></i> Tap to enlarge
        </div>
    </div>
</div>
@endif

{{-- ── Vehicle Photos ── --}}
@if($violation->vehiclePhotos->isNotEmpty())
<div class="motshow-section">Vehicle Photos</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    <div class="motshow-card-body" style="padding:1rem;">
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;">
            @foreach($violation->vehiclePhotos as $photo)
            <img src="{{ uploaded_file_url($photo->photo) }}"
                 alt="Vehicle photo"
                 class="mob-photo-thumb"
                 data-full="{{ uploaded_file_url($photo->photo) }}"
                 data-caption="Vehicle Photo"
                 style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:12px;box-shadow:0 2px 8px rgba(15,23,42,.08);cursor:zoom-in;display:block;">
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Settlement Details ── --}}
@if($status === 'settled' || $violation->or_number || $violation->cashier_name || $violation->receipt_photo)
<div class="motshow-section">Settlement</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    <div class="motshow-card-head" style="display:flex;align-items:center;gap:.45rem;">
        <span style="width:24px;height:24px;border-radius:8px;background:linear-gradient(135deg,#059669,#047857);display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;color:#fff;flex-shrink:0;">
            <i class="ph-bold ph-check"></i>
        </span>
        Settlement Details
    </div>
    <div class="motshow-card-body">
        <div class="motshow-info-grid">
            @if($violation->settled_at)
            <div>
                <div class="motshow-label"><i class="ph ph-clock me-1"></i>Settled At</div>
                <div class="motshow-value">{{ $violation->settled_at->format('M d, Y') }}</div>
                <div style="font-size:.7rem;color:#64748b;">{{ $violation->settled_at->format('g:i A') }}</div>
            </div>
            @endif
            @if($violation->or_number)
            <div>
                <div class="motshow-label"><i class="ph ph-receipt me-1"></i>OR Number</div>
                <div class="motshow-value" style="font-family:ui-monospace,monospace;">{{ $violation->or_number }}</div>
            </div>
            @endif
            @if($violation->cashier_name)
            <div class="motshow-info-full">
                <div class="motshow-label"><i class="ph ph-user me-1"></i>Cashier</div>
                <div class="motshow-value motshow-value--soft">{{ $violation->cashier_name }}</div>
            </div>
            @endif
        </div>

        @if($violation->receipt_photo)
        <div style="margin-top:.9rem;">
            <div class="motshow-label" style="margin-bottom:.45rem;"><i class="ph ph-image me-1"></i>Receipt Photo</div>
            <img src="{{ uploaded_file_url($violation->receipt_photo) }}"
                 alt="Receipt Photo"
                 class="mob-photo-thumb"
                 data-full="{{ uploaded_file_url($violation->receipt_photo) }}"
                 data-caption="Settlement Receipt"
                 style="width:100%;border-radius:14px;box-shadow:0 4px 16px rgba(15,23,42,.1);cursor:zoom-in;display:block;">
            <div style="display:flex;align-items:center;justify-content:center;gap:.35rem;margin-top:.5rem;font-size:.7rem;color:#94a3b8;">
                <i class="ph ph-magnifying-glass-plus"></i> Tap to enlarge
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ── Edit FAB ── --}}
<a href="{{ route('officer.violations.edit', $violation) }}"
   style="position:fixed;bottom:1.5rem;right:1.25rem;z-index:999;
          width:52px;height:52px;border-radius:50%;
          background:linear-gradient(135deg,#dc2626,#b91c1c);
          color:#fff;display:flex;align-items:center;justify-content:center;
          box-shadow:0 4px 14px rgba(220,38,38,.45);text-decoration:none;">
    <i class="ph ph-pencil-simple" style="font-size:1.3rem;"></i>
</a>

@endsection
