@extends('layouts.app')
@section('title', 'Violation #' . $violation->id)
@section('topbar-sub', 'Record for: ' . ($violation->violator?->full_name ?? '(Deleted Motorist)'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('violations.index') }}" style="color:#78716c;">Violations</a></li>
    @if($violation->violator)
    <li class="breadcrumb-item"><a href="{{ route('violators.show', $violation->violator) }}" style="color:#78716c;">{{ $violation->violator->full_name }}</a></li>
    @endif
    <li class="breadcrumb-item active" aria-current="page" style="color:#44403c;">Violation #{{ $violation->id }}</li>
@endsection

@push('styles')
<style>
/* ── Violation status pills ── */
.viol-status-pill { display:inline-flex;align-items:center;gap:8px;padding:.25rem .75rem;border-radius:9999px;font-weight:600; }
.viol-status-overdue   { background:#fef2f2;color:#b91c1c;border:1.5px solid #fca5a5; }
.viol-status-pending   { background:#fef3c7;color:#92400e;border:1.5px solid #fcd34d; }
.viol-status-settled   { background:#f0fdf4;color:#15803d;border:1.5px solid #86efac; }
.viol-status-contested { background:#f8fafc;color:#475569;border:1.5px solid #cbd5e1; }
.viol-status-dot { border-radius:50%;display:inline-block;flex-shrink:0; }
.viol-status-overdue   .viol-status-dot { background:#dc2626; }
.viol-status-pending   .viol-status-dot { background:#f59e0b; }
.viol-status-settled   .viol-status-dot { background:#22c55e; }
.viol-status-contested .viol-status-dot { background:#94a3b8; }

/* ── Mobile ── */
@media (max-width: 767px) {

    /* Page header */
    .viol-page-header { margin-bottom: .75rem !important; gap: .5rem !important; }
    .viol-page-header h5 { font-size: .875rem !important; }
    .viol-page-header .topbar-sub-text { font-size: .72rem !important; }
    .viol-page-header .viol-icon-circle {
        width: 34px !important; height: 34px !important;
    }
    .viol-page-header .viol-icon-circle i { font-size: .8rem !important; }

    /* Row gutter — tighter on mobile */
    .row.g-4 {
        --bs-gutter-x: .5rem;
        --bs-gutter-y: .5rem;
    }

    /* Card header — compact */
    .card-header { padding: .5rem .75rem !important; }
    .card-header span[style*="width:28px"] {
        width: 24px !important; height: 24px !important;
    }

    /* DL rows: stack label above value, tighter padding */
    dl .d-flex.align-items-start {
        flex-direction: column !important;
        gap: .15rem !important;
        padding: .55rem .75rem !important;
    }
    dl .d-flex.align-items-start > div[style*="width:120px"] {
        width: auto !important;
        flex-shrink: unset !important;
    }
    dl .d-flex.align-items-start img[style*="max-width:260px"] {
        max-width: 100% !important;
        width: 100%;
        border-radius: 6px !important;
    }

    /* Vehicle photo grid — 2-up on mobile */
    .veh-photo-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr);
        gap: .4rem;
    }
    .veh-photo-grid img {
        width: 100% !important;
        height: 90px !important;
        object-fit: cover;
    }

    /* Actions card — touch-friendly buttons */
    .card-body .btn {
        min-height: 44px;
        font-size: .875rem !important;
    }

    /* Violator card — compact avatar */
    .viol-avatar-circle {
        width: 40px !important; height: 40px !important;
        font-size: .95rem !important;
    }

    /* Settle modal — full-width on mobile */
    #settleModal .modal-dialog {
        margin: .4rem;
        max-width: calc(100vw - .8rem);
    }
    #settleModal .modal-header { padding: .75rem 1rem !important; }
    #settleModal .modal-body { padding: .875rem 1rem !important; }
    #settleModal .modal-footer { padding: .6rem 1rem !important; }
    #settleModal .mb-3 { margin-bottom: .75rem !important; }
    #settleModal .form-label { font-size: .8rem !important; }
    #settleModal .form-control, #settleModal .input-group-text {
        font-size: .875rem !important;
    }
    #settleModal .modal-footer .btn {
        flex: 1;
        min-height: 42px;
    }

    /* Lightbox — trim horizontal padding on small screens */
    #lightboxImg { padding: .5rem .75rem !important; }
    #lbPrev { left: 6px !important; width: 32px !important; height: 32px !important; }
    #lbNext { right: 6px !important; width: 32px !important; height: 32px !important; }

    /* Settlement & Record info cards — compact list */
    .card-body ul[style*="line-height:2.2"] {
        line-height: 1.9 !important;
    }
}

@media (max-width: 400px) {
    dl .d-flex.align-items-start { padding: .45rem .6rem !important; }
    .card-header { padding: .4rem .6rem !important; }
}
</style>
@endpush

@section('content')

@php
    $isOverdue = $violation->isOverdue();
    $displayStatus = $isOverdue ? 'overdue' : $violation->status;
    $statusLabel = ['overdue' => 'Overdue', 'pending' => 'Pending', 'settled' => 'Settled', 'contested' => 'Contested'];
    $label = $statusLabel[$displayStatus] ?? ucfirst($displayStatus);
@endphp

{{-- ── PAGE HEADER ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 viol-page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center viol-icon-circle"
             style="width:42px;height:42px;background:linear-gradient(135deg,#dc2626,#b91c1c);flex-shrink:0;">
            <i class="bi bi-exclamation-triangle-fill text-white" style="font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-700" style="color:#1c1917;">Violation Record #{{ $violation->id }}</h5>
            <div class="topbar-sub-text" style="font-size:.8rem;color:#78716c;">
                Filed on {{ $violation->created_at->format('F d, Y') }} · {{ $violation->violator?->full_name ?? '(Deleted Motorist)' }}
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- Card 1: Violation Details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fee2e2;">
                    <i class="bi bi-shield-exclamation text-danger" style="font-size:.85rem;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Violation Details</span>
            </div>
            <div class="card-body p-0">
                <dl class="mb-0">
                    {{-- Violation Type --}}
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Type</div>
                        <div class="fw-700" style="color:#1c1917;font-size:.95rem;">{{ $violation->violationType?->name ?? '—' }}</div>
                    </div>

                    {{-- Fine Amount --}}
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Fine Amount</div>
                        <div>
                            @if($violation->violationType?->fine_amount)
                                <span class="fw-700" style="color:#1c1917;font-size:.95rem;">
                                    ₱{{ number_format($violation->violationType->fine_amount, 2) }}
                                </span>
                            @else
                                <span style="color:#a8a29e;font-style:italic;">No fine set</span>
                            @endif
                        </div>
                    </div>

                    {{-- Date --}}
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Date</div>
                        <div style="color:#292524;">
                            <i class="bi bi-calendar-event me-1" style="color:#d97706;font-size:.8rem;"></i>
                            {{ $violation->date_of_violation?->format('F d, Y') ?? '—' }}
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Location</div>
                        <div style="color:#292524;">
                            @if($violation->location)
                                <i class="bi bi-pin-map-fill me-1" style="color:#1d4ed8;font-size:.8rem;"></i>
                                {{ $violation->location }}
                            @else
                                <span style="color:#a8a29e;font-style:italic;">—</span>
                            @endif
                        </div>
                    </div>

                    {{-- Ticket Number --}}
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Ticket No.</div>
                        <div>
                            @if($violation->ticket_number)
                                <span class="font-monospace fw-600" style="color:#1c1917;">{{ $violation->ticket_number }}</span>
                            @else
                                <span style="color:#a8a29e;font-style:italic;">Not issued</span>
                            @endif
                        </div>
                    </div>

                    {{-- Citation Ticket Photo --}}
                    @if($violation->citation_ticket_photo)
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Citation Ticket</div>
                        <div>
                            <img src="{{ uploaded_file_url($violation->citation_ticket_photo) }}"
                                 alt="Citation ticket"
                                 data-lightbox="{{ uploaded_file_url($violation->citation_ticket_photo) }}"
                                 data-caption="Citation Ticket — Violation #{{ $violation->id }}"
                                 style="max-width:260px;max-height:200px;object-fit:contain;border-radius:8px;border:2px solid #fcd34d;cursor:zoom-in;transition:transform .15s;"
                                 onmouseover="this.style.transform='scale(1.02)'"
                                 onmouseout="this.style.transform='scale(1)'">
                            <div style="font-size:.72rem;color:#a8a29e;margin-top:4px;"><i class="bi bi-zoom-in me-1"></i>Click to enlarge</div>
                        </div>
                    </div>
                    @endif

                    {{-- Status --}}
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Status</div>
                        <div>
                            <span class="viol-status-pill viol-status-{{ $displayStatus }}" style="font-size:.8rem;"
                                @if($displayStatus === 'overdue') data-bs-toggle="tooltip" data-bs-title="Pending settlement for more than 72 hours" @endif>
                                <span class="viol-status-dot" style="width:7px;height:7px;"></span>
                                {{ $label }}
                            </span>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="d-flex align-items-start gap-3 px-4 py-3">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Notes</div>
                        <div style="color:#57534e;font-size:.875rem;line-height:1.6;">
                            {{ $violation->notes ?? '—' }}
                        </div>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Card 2: Vehicle Information --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dbeafe;">
                    <i class="bi bi-truck-front-fill" style="font-size:.85rem;color:#1d4ed8;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Vehicle Involved</span>
                @if($violation->vehicle_plate && !$violation->vehicle_id)
                    <span class="ms-auto badge" style="background:#fef3c7;color:#92400e;font-size:.7rem;">Manual entry</span>
                @elseif($violation->vehicle_id)
                    <span class="ms-auto badge" style="background:#dbeafe;color:#1e40af;font-size:.7rem;">From system</span>
                @endif
            </div>
            <div class="card-body p-0">
                <dl class="mb-0">

                    {{-- Vehicle (plate / system) --}}
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Plate No.</div>
                        <div>
                            @if($violation->vehicle)
                                <span class="font-monospace fw-700" style="color:#1c1917;font-size:1rem;letter-spacing:.05em;">
                                    {{ $violation->vehicle->plate_number }}
                                </span>
                                @if($violation->vehicle->vehicle_type)
                                <span class="badge ms-2" style="background:#dbeafe;color:#1e40af;font-size:.72em;">
                                    {{ $violation->vehicle->vehicle_type }}
                                </span>
                                @endif
                                @php $vMake = trim(($violation->vehicle->make ?? '') . ' ' . ($violation->vehicle->model ?? '')); @endphp
                                @if($vMake || $violation->vehicle->color)
                                <div style="font-size:.8rem;color:#57534e;margin-top:3px;">
                                    {{ implode(' · ', array_filter([$vMake ?: null, $violation->vehicle->color])) }}
                                </div>
                                @endif
                                <div style="font-size:.78rem;color:#78716c;margin-top:2px;">
                                    <i class="bi bi-box-arrow-up-right me-1" style="font-size:.65rem;"></i>
                                    @if($violation->vehicle->violator)
                                    <a href="{{ route('violators.show', $violation->vehicle->violator) }}"
                                       style="color:#6d28d9;text-decoration:none;font-size:.78rem;">
                                        Registered to {{ $violation->vehicle->violator->full_name }}
                                    </a>
                                    @else
                                    <span style="font-size:.78rem;color:#a8a29e;">Registered owner not on file</span>
                                    @endif
                                </div>
                            @elseif($violation->vehicle_plate)
                                <span class="font-monospace fw-700" style="color:#1c1917;font-size:1rem;letter-spacing:.05em;">
                                    {{ $violation->vehicle_plate }}
                                </span>
                                <span class="badge ms-2" style="background:#fef3c7;color:#92400e;font-size:.72em;">not in system</span>
                            @else
                                <span style="color:#a8a29e;font-style:italic;">Not recorded</span>
                            @endif
                        </div>
                    </div>

                    {{-- Registered Owner --}}
                    @if($violation->vehicle_owner_name)
                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Reg. Owner</div>
                        <div>
                            <span class="badge" style="background:#f3e8ff;color:#6b21a8;font-weight:600;font-size:.85em;padding:.35em .75em;">
                                <i class="bi bi-person-vcard-fill me-1"></i>{{ $violation->vehicle_owner_name }}
                            </span>
                            <span class="ms-1" style="font-size:.78rem;color:#78716c;">(borrowed vehicle)</span>
                        </div>
                    </div>
                    @endif

                    {{-- Manual vehicle details --}}
                    @if($violation->vehicle_plate && !$violation->vehicle_id)

                        @if($violation->vehicle_make || $violation->vehicle_model || $violation->vehicle_color)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Make / Model</div>
                            <div style="color:#292524;">
                                {{ implode(' · ', array_filter([$violation->vehicle_make, $violation->vehicle_model, $violation->vehicle_color])) ?: '—' }}
                            </div>
                        </div>
                        @endif

                        @if($violation->vehicle_or_number || $violation->vehicle_cr_number)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">OR / CR No.</div>
                            <div class="font-monospace" style="color:#292524;font-size:.88rem;">
                                {{ $violation->vehicle_or_number ?? '—' }}&nbsp;/&nbsp;{{ $violation->vehicle_cr_number ?? '—' }}
                            </div>
                        </div>
                        @endif

                        @if($violation->vehicle_chassis)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Chassis No.</div>
                            <div class="font-monospace" style="color:#292524;font-size:.88rem;">{{ $violation->vehicle_chassis }}</div>
                        </div>
                        @endif

                    @else
                        {{-- System vehicle: show OR/CR and chassis if present --}}
                        @if($violation->vehicle)
                            @if($violation->vehicle->or_number || $violation->vehicle->cr_number)
                            <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                                <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">OR / CR No.</div>
                                <div class="font-monospace" style="color:#292524;font-size:.88rem;">
                                    {{ $violation->vehicle->or_number ?? '—' }}&nbsp;/&nbsp;{{ $violation->vehicle->cr_number ?? '—' }}
                                </div>
                            </div>
                            @endif
                            @if($violation->vehicle->chassis_number)
                            <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                                <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Chassis No.</div>
                                <div class="font-monospace" style="color:#292524;font-size:.88rem;">{{ $violation->vehicle->chassis_number }}</div>
                            </div>
                            @endif
                        @elseif($violation->vehiclePhotos->isEmpty() && !$violation->vehicle_plate)
                        <div class="d-flex align-items-start gap-3 px-4 py-3">
                            <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Details</div>
                            <div style="color:#a8a29e;font-style:italic;font-size:.875rem;">No vehicle recorded for this violation.</div>
                        </div>
                        @endif
                    @endif

                        {{-- Vehicle photos — always shown regardless of vehicle source --}}
                        @if($violation->vehiclePhotos->isNotEmpty())
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-top:1px solid #f5f0e8;">
                            <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Photos</div>
                            <div>
                                <div class="d-flex flex-wrap gap-2 veh-photo-grid">
                                    @foreach($violation->vehiclePhotos as $photo)
                                    <img src="{{ uploaded_file_url($photo->photo) }}"
                                         alt="Vehicle photo"
                                         data-lightbox="{{ uploaded_file_url($photo->photo) }}"
                                         data-caption="Violation #{{ $violation->id }} — Vehicle Photo"
                                         style="height:100px;width:140px;object-fit:cover;border-radius:8px;border:2px solid #fde68a;cursor:pointer;transition:transform .15s;"
                                         onmouseover="this.style.transform='scale(1.03)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                    @endforeach
                                </div>
                                <div style="font-size:.75rem;color:#a8a29e;margin-top:6px;">
                                    <i class="bi bi-zoom-in me-1"></i>Click a photo to enlarge
                                </div>
                            </div>
                        </div>
                        @endif

                </dl>
            </div>
        </div>

    </div>{{-- /LEFT COLUMN --}}

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- Violator Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#ede9fe;">
                    <i class="bi bi-person-fill" style="font-size:.85rem;color:#6d28d9;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Violator</span>
                @if($violation->violator)
                <a href="{{ route('violators.show', $violation->violator) }}"
                   class="ms-auto" style="font-size:.75rem;color:#6d28d9;text-decoration:none;">
                    View profile <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;"></i>
                </a>
                @endif
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-700 text-white viol-avatar-circle"
                         style="width:48px;height:48px;flex-shrink:0;font-size:1.1rem;
                                background:linear-gradient(135deg,#6d28d9,#4c1d95);">
                        {{ strtoupper(substr($violation->violator?->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($violation->violator?->last_name ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-700" style="color:#1c1917;font-size:.9rem;">{{ $violation->violator?->full_name ?? '(Deleted Motorist)' }}</div>
                        @if($violation->violator?->license_number)
                            <div class="font-monospace" style="font-size:.72rem;color:#a8a29e;">
                                {{ $violation->violator->license_number }}
                            </div>
                        @endif
                        @php $totalVc = $violation->violator?->violations->count() ?? 0; @endphp
                        <div style="font-size:.75rem;margin-top:2px;">
                            @if($totalVc >= 3)
                                <span style="color:#b91c1c;font-weight:700;"><i class="bi bi-fire me-1"></i>Recidivist</span>
                            @elseif($totalVc >= 2)
                                <span style="color:#92400e;font-weight:700;"><i class="bi bi-shield-exclamation me-1"></i>Repeat Offender</span>
                            @elseif($totalVc === 1)
                                <span style="color:#0369a1;font-weight:700;"><i class="bi bi-record-circle me-1"></i>1st Violation</span>
                            @else
                                <span style="color:#15803d;font-weight:700;"><i class="bi bi-shield-check me-1"></i>No Prior</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($violation->violator?->contact_number || $violation->violator?->address)
                <div class="border-top pt-2" style="border-color:#ede8df!important;">
                    <ul class="mb-0 list-unstyled" style="font-size:.78rem;color:#57534e;line-height:1.9;">
                        @if($violation->violator->contact_number)
                        <li><i class="bi bi-telephone-fill me-2" style="color:#6d28d9;font-size:.7rem;"></i>{{ $violation->violator->contact_number }}</li>
                        @endif
                        @if($violation->violator->address)
                        <li><i class="bi bi-geo-alt-fill me-2" style="color:#6d28d9;font-size:.7rem;"></i>{{ $violation->violator->address }}</li>
                        @endif
                    </ul>
                </div>
                @endif
            </div>
        </div>

        {{-- Settlement Details (only when settled) --}}
        @if($violation->status === 'settled')
        <div class="card border-0 shadow-sm mb-4" style="border-left:3px solid #15803d!important;">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dcfce7;">
                    <i class="bi bi-receipt" style="font-size:.85rem;color:#15803d;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Settlement Details</span>
            </div>
            <div class="card-body p-3">
                <ul class="mb-0 list-unstyled" style="font-size:.8rem;color:#57534e;line-height:2.2;">
                    <li>
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Date Settled</span>
                        <span class="fw-600" style="color:#15803d;">{{ ($violation->settled_at ?? $violation->updated_at)->format('F d, Y  g:i A') }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">OR Number</span>
                        <span class="fw-600 font-monospace" style="color:#15803d;">{{ $violation->or_number ?? '—' }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Cashier</span>
                        <span>{{ $violation->cashier_name ?? '—' }}</span>
                    </li>
                    @if($violation->receipt_photo)
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Receipt Photo</span>
                        <img src="{{ uploaded_file_url($violation->receipt_photo) }}" alt="Receipt"
                             data-lightbox="{{ uploaded_file_url($violation->receipt_photo) }}"
                             data-caption="Receipt — {{ $violation->or_number }}"
                             style="max-width:100%;max-height:160px;object-fit:contain;border-radius:8px;border:1px solid #bbf7d0;cursor:pointer;margin-top:4px;display:block;">
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        @endif

        {{-- Record Meta --}}
        <div class="card border-0 shadow-sm mb-4" style="border-left:3px solid #d97706!important;">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fef3c7;">
                    <i class="bi bi-info-circle-fill" style="font-size:.85rem;color:#d97706;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Record Info</span>
            </div>
            <div class="card-body p-3">
                <ul class="mb-0 list-unstyled" style="font-size:.8rem;color:#57534e;line-height:2.2;">
                    <li>
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Record ID</span>
                        <span class="fw-600">#{{ $violation->id }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Recorded By</span>
                        <span>{{ $violation->recorder?->name ?? '(Deleted User)' }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Recorded On</span>
                        <span>{{ $violation->created_at->format('M d, Y  g:i A') }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Last Updated</span>
                        <span>{{ $violation->updated_at->format('M d, Y  g:i A') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fee2e2;">
                    <i class="bi bi-gear-fill" style="font-size:.85rem;color:#dc2626;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Actions</span>
            </div>
            <div class="card-body p-3 d-flex flex-column gap-2">
                {{-- Print — visible to all roles --}}
                <a href="{{ route('violations.print', $violation) }}" target="_blank"
                   class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2 fw-600">
                    <i class="bi bi-printer-fill" style="font-size:.85rem;"></i> Print
                </a>
                @if(Auth::user()->isOperator())
                @if($violation->status === 'pending')
                <button type="button" class="btn btn-success w-100 fw-600 d-inline-flex align-items-center justify-content-center gap-2"
                    data-id="{{ $violation->id }}"
                    data-type="{{ $violation->violationType?->name ?? '' }}"
                    data-date="{{ $violation->date_of_violation->format('M d, Y') }}"
                    onclick="openSettleModal(this)">
                    <i class="bi bi-receipt" style="font-size:.85rem;"></i> Settle Violation
                </button>
                @endif
                <a href="{{ route('violations.edit', $violation) }}"
                   class="btn btn-warning w-100 fw-600 d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-pencil-fill" style="font-size:.85rem;"></i> Edit Violation
                </a>
                <form method="POST" action="{{ route('violations.destroy', $violation) }}"
                      data-confirm="Permanently delete this violation record? This cannot be undone.">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-trash" style="font-size:.85rem;"></i> Delete Record
                    </button>
                </form>
                @endif
            </div>
        </div>

    </div>{{-- /RIGHT COLUMN --}}

</div>{{-- /row --}}

{{-- Lightbox Modal --}}
<div class="modal fade" id="photoLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:min(92vw,940px);">
        <div class="modal-content border-0" style="background:#111;border-radius:14px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.6);">
            <div class="modal-header border-0 px-4 py-2" style="background:#1a1a1a;">
                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <i class="bi bi-images" style="color:#fcd34d;font-size:.9rem;flex-shrink:0;"></i>
                    <span id="lightboxCaption" style="color:#e5e7eb;font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:400px;"></span>
                    <span id="lightboxCounter" style="color:#6b7280;font-size:.75rem;flex-shrink:0;margin-left:.25rem;"></span>
                </div>
                <a id="lightboxDownload" href="#" download title="Download image"
                   style="color:#9ca3af;font-size:.85rem;text-decoration:none;margin-right:1rem;flex-shrink:0;display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .55rem;border-radius:6px;transition:all .15s;"
                   onmouseover="this.style.color='#fcd34d';this.style.background='rgba(252,211,77,.1)'"
                   onmouseout="this.style.color='#9ca3af';this.style.background='transparent'">
                    <i class="bi bi-download"></i>
                </a>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center position-relative" style="background:#111;min-height:120px;">
                <button id="lbPrev" onclick="_lbNav(-1)"
                        style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.2);color:#fff;font-size:.9rem;cursor:pointer;z-index:2;display:none;align-items:center;justify-content:center;transition:all .15s;backdrop-filter:blur(4px);"
                        onmouseover="this.style.background='rgba(255,255,255,.28)'"
                        onmouseout="this.style.background='rgba(255,255,255,.12)'">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <img id="lightboxImg" src="" alt=""
                     style="max-width:100%;max-height:82vh;object-fit:contain;display:block;margin:0 auto;padding:.75rem 3rem;">
                <button id="lbNext" onclick="_lbNav(1)"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.2);color:#fff;font-size:.9rem;cursor:pointer;z-index:2;display:none;align-items:center;justify-content:center;transition:all .15s;backdrop-filter:blur(4px);"
                        onmouseover="this.style.background='rgba(255,255,255,.28)'"
                        onmouseout="this.style.background='rgba(255,255,255,.12)'">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div id="lbDots" class="d-flex justify-content-center gap-2 py-2" style="background:#1a1a1a;min-height:26px;"></div>
        </div>
    </div>
</div>

@endsection

{{-- ── Settle Modal ── --}}
<div class="modal fade" id="settleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#15803d,#166534);padding:1rem 1.25rem;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-white" style="font-size:1.1rem;"></i>
                    <h6 class="modal-title text-white fw-700 mb-0">Settle Violation</h6>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div style="background:#f0fdf4;padding:.65rem 1.25rem;border-bottom:1px solid #bbf7d0;font-size:.8rem;">
                <span class="fw-700" style="color:#15803d;" id="settleViolationType">—</span>
                <span style="color:#a8a29e;margin:0 .4rem;">·</span>
                <span style="color:#57534e;" id="settleViolationDate">—</span>
            </div>
            <form id="settleForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="modal-body" style="padding:1.25rem;">
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.82rem;">OR Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash" style="color:#15803d;font-size:.8rem;"></i></span>
                            <input type="text" name="or_number" class="form-control" placeholder="e.g. 1234567"
                                style="font-family:ui-monospace,monospace;" required maxlength="50">
                        </div>
                        <small style="font-size:.72rem;color:#a8a29e;">Official Receipt number from the cashier.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.82rem;">Cashier Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge-fill" style="color:#15803d;font-size:.8rem;"></i></span>
                            <input type="text" name="cashier_name" class="form-control" placeholder="Full name of cashier" required maxlength="150">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-700" style="font-size:.82rem;">Receipt Photo <span style="color:#a8a29e;font-weight:400;">(optional)</span></label>
                        <input type="file" name="receipt_photo" id="receipt_photo" class="form-control" accept="image/jpg,image/jpeg,image/png">
                        <small style="font-size:.72rem;color:#a8a29e;">JPG/PNG, max 5 MB.</small>
                        <div id="receiptPreviewWrap" class="d-none mt-2 text-center">
                            <img id="receiptPreview" src="" alt="Receipt"
                                style="max-width:100%;max-height:180px;border-radius:8px;border:1px solid #bbf7d0;object-fit:contain;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 gap-2" style="padding:.75rem 1.25rem;">
                    <button type="button" class="btn btn-light fw-600" data-bs-dismiss="modal" style="font-size:.82rem;">Cancel</button>
                    <button type="submit" class="btn fw-700" style="background:linear-gradient(135deg,#15803d,#166534);color:#fff;font-size:.82rem;padding:.45rem 1.2rem;border-radius:8px;">
                        <i class="bi bi-check2-circle me-1"></i> Mark as Settled
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let _lbImgs = [], _lbIdx = 0;
document.addEventListener('click', function (e) {
    const trigger = e.target.closest('[data-lightbox]');
    if (!trigger) return;
    e.preventDefault();
    const all = [...document.querySelectorAll('[data-lightbox]')];
    _lbImgs = all.map(el => ({ src: el.dataset.lightbox, caption: el.dataset.caption ?? '' }));
    _lbIdx  = all.indexOf(trigger);
    _lbShow(_lbIdx);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('photoLightbox')).show();
});
function _lbShow(idx) {
    _lbIdx = ((idx % _lbImgs.length) + _lbImgs.length) % _lbImgs.length;
    const item  = _lbImgs[_lbIdx], total = _lbImgs.length;
    document.getElementById('lightboxImg').src = item.src;
    document.getElementById('lightboxCaption').textContent = item.caption;
    document.getElementById('lightboxCounter').textContent = total > 1 ? `${_lbIdx + 1} / ${total}` : '';
    const dl = document.getElementById('lightboxDownload');
    if (dl) dl.href = item.src;
    const prev = document.getElementById('lbPrev'), next = document.getElementById('lbNext');
    if (prev) prev.style.display = total > 1 ? 'flex' : 'none';
    if (next) next.style.display = total > 1 ? 'flex' : 'none';
    const dots = document.getElementById('lbDots');
    if (dots) dots.innerHTML = total > 1
        ? _lbImgs.map((_,i) => `<span onclick="_lbShow(${i})" style="width:7px;height:7px;border-radius:50%;background:${i===_lbIdx?'#fcd34d':'rgba(255,255,255,.3)'};cursor:pointer;display:inline-block;transition:background .15s;"></span>`).join('')
        : '';
}
function _lbNav(dir) { _lbShow(_lbIdx + dir); }
document.addEventListener('keydown', function (e) {
    const modal = document.getElementById('photoLightbox');
    if (!modal || !modal.classList.contains('show') || !_lbImgs.length) return;
    if (e.key === 'ArrowRight') _lbShow(_lbIdx + 1);
    if (e.key === 'ArrowLeft')  _lbShow(_lbIdx - 1);
});

function openSettleModal(btn) {
    document.getElementById('settleForm').action = '/violations/' + btn.dataset.id + '/settle';
    document.getElementById('settleViolationType').textContent = btn.dataset.type;
    document.getElementById('settleViolationDate').textContent = btn.dataset.date;
    document.getElementById('settleForm').reset();
    document.getElementById('receiptPreview').src = '';
    document.getElementById('receiptPreviewWrap').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('settleModal')).show();
}

document.getElementById('receipt_photo').addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('receiptPreview').src = e.target.result;
        document.getElementById('receiptPreviewWrap').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
