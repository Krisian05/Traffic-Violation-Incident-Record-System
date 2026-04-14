@extends('layouts.app')
@section('title', $incident->incident_number)
@section('topbar-sub', 'Traffic Incident Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('incidents.index') }}" style="color:#78716c;">Incidents</a></li>
    <li class="breadcrumb-item active" aria-current="page" style="color:#44403c;">{{ $incident->incident_number }}</li>
@endsection

@push('styles')
<style>
/* ── Incident status pills ── */
.inc-status-pill { display:inline-flex;align-items:center;gap:8px;padding:.25rem .75rem;border-radius:9999px;font-weight:600; }
.inc-status-open         { background:#eff6ff;color:#1d4ed8;border:1.5px solid #93c5fd; }
.inc-status-under_review { background:#fef3c7;color:#92400e;border:1.5px solid #fcd34d; }
.inc-status-closed       { background:#f0fdf4;color:#15803d;border:1.5px solid #86efac; }
.inc-status-default      { background:#f8fafc;color:#475569;border:1.5px solid #cbd5e1; }
.inc-status-dot { border-radius:50%;display:inline-block;flex-shrink:0; }
.inc-status-open         .inc-status-dot { background:#2563eb; }
.inc-status-under_review .inc-status-dot { background:#f59e0b; }
.inc-status-closed       .inc-status-dot { background:#22c55e; }
.inc-status-default      .inc-status-dot { background:#94a3b8; }
/* ── Motorist tag ── */
.mot-tag { font-size:.64rem;font-weight:700;padding:.15rem .5rem;border-radius:10px;letter-spacing:.03em; }
.mot-tag-reg   { background:#dcfce7;color:#15803d; }
.mot-tag-unreg { background:#fef9c3;color:#92400e; }
/* ── Restriction pill ── */
.restr-pill { display:inline-block;padding:.15rem .55rem;border-radius:20px;font-size:.68rem;font-weight:700;background:#fef3c7;color:#92400e;margin:.1rem .15rem 0 0; }
/* ── Charge badge ── */
.charge-badge { display:inline-flex;align-items:center;gap:.3rem;background:#f3e8ff;color:#6d28d9;border:1px solid #e9d5ff;font-size:.75rem;font-weight:700;padding:.3rem .75rem;border-radius:20px; }
/* ── Utility ── */
.mot-separator { border-bottom:2px solid #f5f0e8; }
.exp-date-expired { color:#dc2626; }
.exp-date-valid   { color:#292524; }
/* ── Media thumb ── */
.media-badge { font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:10px; }
.media-badge-scene    { background:#eff6ff;color:#3b82f6; }
.media-badge-ticket   { background:#fffbeb;color:#f59e0b; }
.media-badge-document { background:#f5f3ff;color:#8b5cf6; }
.media-badge-other    { background:#f9fafb;color:#6b7280; }
</style>
@endpush

@section('content')

@php
    $statusLabels = ['open' => 'Open', 'under_review' => 'Under Review', 'closed' => 'Closed'];
    $statusPill   = in_array($incident->status, ['open','under_review','closed'])
                        ? 'inc-status-' . $incident->status : 'inc-status-default';
    $restrDesc    = ['A'=>'Motorcycle','A1'=>'MC w/ Sidecar','B'=>'Light Vehicle','B1'=>'Light Vehicle (Prof.)','B2'=>'Light Vehicle w/ Trailer','C'=>'Medium/Heavy Truck','D'=>'Bus','BE'=>'Light + Heavy Trailer','CE'=>'Large Truck + Trailer'];
    $mediaLabels  = ['scene'=>'Scene Photo','ticket'=>'Citation Ticket','document'=>'Document','other'=>'Other'];
@endphp

{{-- ── PAGE HEADER ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center"
             style="width:42px;height:42px;background:linear-gradient(135deg,#1d4ed8,#1e40af);flex-shrink:0;">
            <i class="bi bi-flag-fill text-white" style="font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-700" style="color:#1c1917;">{{ $incident->incident_number }}</h5>
            <div style="font-size:.8rem;color:#78716c;">
                Recorded on {{ $incident->created_at->format('F d, Y') }}
                · {{ $incident->motorists->count() }} Motorist{{ $incident->motorists->count() != 1 ? 's' : '' }}
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- Card 1: Incident Details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dbeafe;">
                    <i class="bi bi-flag-fill" style="font-size:.85rem;color:#1d4ed8;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Incident Details</span>
            </div>
            <div class="card-body p-0">
                <dl class="mb-0">

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Incident No.</div>
                        <div class="fw-700 font-monospace" style="color:#1d4ed8;font-size:.95rem;">{{ $incident->incident_number }}</div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Status</div>
                        <div>
                            <span class="inc-status-pill {{ $statusPill }}" style="font-size:.8rem;">
                                <span class="inc-status-dot" style="width:7px;height:7px;"></span>
                                {{ $statusLabels[$incident->status] ?? ucfirst($incident->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Date</div>
                        <div style="color:#292524;">
                            <i class="bi bi-calendar-event me-1" style="color:#d97706;font-size:.8rem;"></i>
                            {{ $incident->date_of_incident->format('F d, Y') }}
                            @if($incident->time_of_incident)
                                <span class="ms-2" style="color:#78716c;">
                                    <i class="bi bi-clock me-1" style="font-size:.75rem;"></i>{{ \Carbon\Carbon::parse($incident->time_of_incident)->format('g:i A') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Location</div>
                        <div style="color:#292524;">
                            @if($incident->location)
                                <i class="bi bi-pin-map-fill me-1" style="color:#1d4ed8;font-size:.8rem;"></i>
                                {{ $incident->location }}
                            @else
                                <span style="color:#a8a29e;font-style:italic;">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3">
                        <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Description</div>
                        <div style="color:#57534e;font-size:.875rem;line-height:1.6;">
                            {{ $incident->description ?? '—' }}
                        </div>
                    </div>

                </dl>
            </div>
        </div>

        {{-- Card 2: Involved Motorists --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fef3c7;">
                    <i class="bi bi-people-fill" style="font-size:.85rem;color:#d97706;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Involved Motorists</span>
                <span class="ms-auto badge" style="background:#fef3c7;color:#92400e;font-size:.7rem;">
                    {{ $incident->motorists->count() }} motorist{{ $incident->motorists->count() != 1 ? 's' : '' }}
                </span>
            </div>
            <div class="card-body p-0">
                @foreach($incident->motorists as $idx => $m)
                @php
                    $restrRaw   = $m->violator ? $m->violator->license_restriction : $m->license_restriction;
                    $restrCodes = $restrRaw ? array_filter(explode(',', $restrRaw)) : [];
                    $expDate    = $m->violator ? $m->violator->license_expiry_date : $m->license_expiry_date;
                    $vPlate = $m->vehicle ? $m->vehicle->plate_number : $m->vehicle_plate;
                    $vType  = $m->vehicle ? $m->vehicle->vehicle_type : $m->vehicle_type_manual;
                    $vMake  = $m->vehicle ? $m->vehicle->make         : $m->vehicle_make;
                    $vModel = $m->vehicle ? $m->vehicle->model        : $m->vehicle_model;
                    $vColor = $m->vehicle ? $m->vehicle->color        : $m->vehicle_color;
                    $vOR    = $m->vehicle ? $m->vehicle->or_number    : $m->vehicle_or_number;
                    $vCR    = $m->vehicle ? $m->vehicle->cr_number    : $m->vehicle_cr_number;
                    $vCha   = $m->vehicle ? $m->vehicle->chassis_number : $m->vehicle_chassis;
                    $vOwner = $m->ownerViolator ? $m->ownerViolator->full_name : $m->vehicle_owner_name;
                    $vOwnerContact = $m->ownerViolator ? ($m->ownerViolator->contact_number ?? null) : $m->vehicle_owner_contact;
                @endphp
                <div class="{{ !$loop->last ? 'mot-separator' : '' }}">

                    {{-- Motorist header row --}}
                    <div class="d-flex align-items-center gap-3 px-4 py-3" style="background:#fafaf9;border-bottom:1px solid #f5f0e8;">
                        @if($m->violator?->photo)
                            <img src="{{ uploaded_file_url($m->violator->photo) }}"
                                 data-lightbox="{{ uploaded_file_url($m->violator->photo) }}"
                                 data-caption="{{ $m->violator->full_name }} — Profile Photo"
                                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0;cursor:zoom-in;" alt="">
                        @elseif($m->motorist_photo)
                            <img src="{{ uploaded_file_url($m->motorist_photo) }}"
                                 data-lightbox="{{ uploaded_file_url($m->motorist_photo) }}"
                                 data-caption="{{ $m->motorist_name ?? 'Motorist ' . ($idx + 1) }} — ID Photo"
                                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0;cursor:zoom-in;" alt="ID photo">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:40px;height:40px;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1d4ed8;font-size:1rem;flex-shrink:0;border:2px solid #bfdbfe;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if($m->violator)
                                    <a href="{{ route('violators.show', $m->violator) }}"
                                       class="fw-700 text-decoration-none" style="color:#1d4ed8;font-size:.9rem;">
                                        {{ $m->violator->full_name }}
                                    </a>
                                    <span class="mot-tag mot-tag-reg"><i class="bi bi-patch-check-fill me-1"></i>Registered</span>
                                @else
                                    <span class="fw-700" style="color:#1c1917;font-size:.9rem;">{{ $m->motorist_name ?? '—' }}</span>
                                    <span class="mot-tag mot-tag-unreg"><i class="bi bi-exclamation-circle me-1"></i>Unregistered</span>
                                    @if(Auth::user()->isOperator())
                                    <a href="{{ route('violators.create-from-incident', $m) }}"
                                       style="font-size:.7rem;padding:.18rem .5rem;background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:8px;text-decoration:none;font-weight:600;">
                                        <i class="bi bi-person-plus-fill me-1"></i>Register
                                    </a>
                                    @endif
                                @endif
                            </div>
                            @if(!$m->violator && ($m->motorist_contact || $m->motorist_address))
                            <div class="d-flex flex-wrap gap-3 mt-1" style="font-size:.75rem;color:#78716c;">
                                @if($m->motorist_contact)<span><i class="bi bi-telephone-fill me-1"></i>{{ $m->motorist_contact }}</span>@endif
                                @if($m->motorist_address)<span><i class="bi bi-geo-alt-fill me-1"></i>{{ $m->motorist_address }}</span>@endif
                            </div>
                            @endif
                        </div>
                        <div class="rounded d-flex align-items-center justify-content-center fw-800 text-white"
                             style="width:26px;height:26px;border-radius:7px;flex-shrink:0;font-size:.75rem;background:linear-gradient(135deg,#d97706,#b45309);">
                            {{ $idx + 1 }}
                        </div>
                    </div>

                    {{-- License details --}}
                    <dl class="mb-0">
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">License No.</div>
                            <div class="font-monospace fw-600" style="color:#292524;">
                                {{ $m->violator ? ($m->violator->license_number ?? '—') : ($m->motorist_license ?? '—') }}
                            </div>
                        </div>

                        @php $licType = $m->violator ? ($m->violator->license_type ?? null) : ($m->license_type ?? null); @endphp
                        @if($licType)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">License Type</div>
                            <div style="color:#292524;">{{ $licType }}</div>
                        </div>
                        @endif

                        @if($restrCodes)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Restriction</div>
                            <div>
                                @foreach($restrCodes as $code)
                                    <span class="restr-pill" title="{{ $restrDesc[trim($code)] ?? '' }}">{{ trim($code) }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($expDate)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">License Expiry</div>
                            <div class="{{ \Carbon\Carbon::parse($expDate)->isPast() ? 'exp-date-expired' : 'exp-date-valid' }}">
                                {{ \Carbon\Carbon::parse($expDate)->format('M j, Y') }}
                                @if(\Carbon\Carbon::parse($expDate)->isPast())
                                    <span style="font-size:.68rem;background:#fee2e2;color:#dc2626;padding:.1rem .4rem;border-radius:8px;margin-left:.3rem;">Expired</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Vehicle --}}
                        @if($vPlate || $m->vehicle)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Plate No.</div>
                            <div>
                                <span class="font-monospace fw-700" style="color:#1d4ed8;font-size:.95rem;letter-spacing:.05em;">{{ $vPlate ?? '—' }}</span>
                                @if($vType)<span class="badge ms-2" style="background:#dbeafe;color:#1e40af;font-size:.7rem;">{{ $vType }}</span>@endif
                            </div>
                        </div>
                        @if($vMake || $vModel || $vColor)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Make / Model</div>
                            <div style="color:#292524;">
                                {{ implode(' · ', array_filter([$vMake, $vModel, $vColor])) ?: '—' }}
                            </div>
                        </div>
                        @endif
                        @if($vOR || $vCR)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">OR / CR No.</div>
                            <div class="font-monospace" style="color:#292524;font-size:.88rem;">{{ $vOR ?? '—' }}&nbsp;/&nbsp;{{ $vCR ?? '—' }}</div>
                        </div>
                        @endif
                        @if($vCha)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Chassis No.</div>
                            <div class="font-monospace" style="color:#292524;font-size:.88rem;">{{ $vCha }}</div>
                        </div>
                        @endif
                        @if($vOwner)
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;background:#fff7ed;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#c2410c;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Vehicle Owner</div>
                            <div>
                                @if($m->ownerViolator)
                                    <a href="{{ route('violators.show', $m->ownerViolator) }}" class="fw-700" style="color:#c2410c;">{{ $vOwner }}</a>
                                    <span class="ms-1 badge" style="background:#fed7aa;color:#c2410c;font-size:.68rem;">Registered</span>
                                @else
                                    <span class="fw-700" style="color:#c2410c;">{{ $vOwner }}</span>
                                @endif
                                @if($vOwnerContact)
                                    <div style="font-size:.75rem;color:#78716c;margin-top:.15rem;"><i class="bi bi-telephone-fill me-1"></i>{{ $vOwnerContact }}</div>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($m->vehicle_photo && count($m->vehicle_photo))
                        <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Vehicle Photos</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($m->vehicle_photo as $vp)
                                    <img src="{{ uploaded_file_url($vp) }}"
                                         data-lightbox="{{ uploaded_file_url($vp) }}"
                                         data-caption="Vehicle — Motorist {{ $idx + 1 }}"
                                         style="height:80px;width:110px;object-fit:cover;border-radius:8px;border:2px solid #fde68a;cursor:pointer;transition:transform .15s;"
                                         onmouseover="this.style.transform='scale(1.03)'"
                                         onmouseout="this.style.transform='scale(1)'" alt="Vehicle photo">
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endif

                        {{-- Charge + Notes --}}
                        @if($m->chargeType || $m->notes)
                        <div class="d-flex align-items-start gap-3 px-4 py-3">
                            <div style="width:130px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Charge / Notes</div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                @if($m->chargeType)
                                    <span class="charge-badge"><i class="bi bi-shield-exclamation"></i>{{ $m->chargeType->name }}</span>
                                @endif
                                @if($m->notes)
                                    <span style="font-size:.82rem;color:#57534e;">{{ $m->notes }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </dl>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Card 3: Evidence & Media --}}
        @if($incident->media->count())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dcfce7;">
                    <i class="bi bi-images" style="font-size:.85rem;color:#16a34a;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Evidence &amp; Documents</span>
                <span class="ms-auto badge" style="background:#dcfce7;color:#15803d;font-size:.7rem;">
                    {{ $incident->media->count() }} file{{ $incident->media->count() != 1 ? 's' : '' }}
                </span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    @foreach($incident->media as $media)
                    <div class="col-md-4 col-sm-6">
                        <div style="border:1px solid #ede8df;border-radius:10px;overflow:hidden;">
                            @if($media->isImage())
                                <a href="{{ uploaded_file_url($media->file_path) }}"
                                   data-lightbox="{{ uploaded_file_url($media->file_path) }}"
                                   data-caption="{{ $media->caption }}">
                                    <img src="{{ uploaded_file_url($media->file_path) }}" alt="{{ $media->caption }}"
                                         style="width:100%;height:120px;object-fit:cover;display:block;cursor:pointer;"
                                         onmouseover="this.style.opacity='.85'"
                                         onmouseout="this.style.opacity='1'">
                                </a>
                            @else
                                <a href="{{ uploaded_file_url($media->file_path) }}" target="_blank" class="text-decoration-none"
                                   style="display:flex;align-items:center;justify-content:center;height:120px;background:#f8fafc;">
                                    <div class="text-center">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size:2.2rem;"></i>
                                        <div class="text-muted mt-1" style="font-size:.7rem;">PDF Document</div>
                                    </div>
                                </a>
                            @endif
                            <div class="px-2 py-2" style="font-size:.78rem;">
                                <span class="media-badge media-badge-{{ $media->media_type }}">
                                    {{ $mediaLabels[$media->media_type] ?? $media->media_type }}
                                </span>
                                @if($media->caption)
                                    <div class="text-muted mt-1" style="font-size:.74rem;">{{ $media->caption }}</div>
                                @endif
                                @if(Auth::user()->isOperator())
                                <form method="POST" action="{{ route('incident-media.destroy', $media) }}" class="mt-2"
                                    data-confirm="Remove this file? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:.71rem;">
                                        <i class="bi bi-trash-fill"></i> Remove
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /LEFT COLUMN --}}

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- Incident Details --}}
        <div class="card border-0 shadow-sm mb-4" style="border-left:3px solid #1d4ed8!important;">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dbeafe;">
                    <i class="bi bi-info-circle-fill" style="font-size:.85rem;color:#1d4ed8;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Incident Info</span>
            </div>
            <div class="card-body p-3">
                <ul class="mb-0 list-unstyled" style="font-size:.8rem;color:#57534e;line-height:2.2;">
                    <li>
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Incident No.</span>
                        <span class="fw-600 font-monospace" style="color:#1d4ed8;">{{ $incident->incident_number }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Status</span>
                        <span class="inc-status-pill {{ $statusPill }}" style="font-size:.75rem;">
                            <span class="inc-status-dot" style="width:6px;height:6px;"></span>
                            {{ $statusLabels[$incident->status] ?? ucfirst($incident->status) }}
                        </span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Date</span>
                        <span>{{ $incident->date_of_incident->format('M d, Y') }}</span>
                    </li>
                    @if($incident->time_of_incident)
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Time</span>
                        <span>{{ \Carbon\Carbon::parse($incident->time_of_incident)->format('g:i A') }}</span>
                    </li>
                    @endif
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Location</span>
                        <span>{{ $incident->location ?: '—' }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Motorists</span>
                        <span>{{ $incident->motorists->count() }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Media Files</span>
                        <span>{{ $incident->media->count() }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Record Meta --}}
        <div class="card border-0 shadow-sm mb-4" style="border-left:3px solid #d97706!important;">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fef3c7;">
                    <i class="bi bi-person-badge-fill" style="font-size:.85rem;color:#d97706;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Record Info</span>
            </div>
            <div class="card-body p-3">
                <ul class="mb-0 list-unstyled" style="font-size:.8rem;color:#57534e;line-height:2.2;">
                    <li>
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Recorded By</span>
                        <span>{{ $incident->recorder->name ?? '—' }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Recorded On</span>
                        <span>{{ $incident->created_at->format('M d, Y  g:i A') }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Last Updated</span>
                        <span>{{ $incident->updated_at->format('M d, Y  g:i A') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Actions --}}
        @if(Auth::user()->isOperator())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fee2e2;">
                    <i class="bi bi-gear-fill" style="font-size:.85rem;color:#dc2626;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Actions</span>
            </div>
            <div class="card-body p-3 d-flex flex-column gap-2">
                <a href="{{ route('incidents.print', $incident) }}" target="_blank"
                   class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2 fw-600">
                    <i class="bi bi-printer-fill" style="font-size:.85rem;"></i> Print Report
                </a>
                <a href="{{ route('incidents.edit', $incident) }}"
                   class="btn btn-warning w-100 fw-600 d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-pencil-fill" style="font-size:.85rem;"></i> Edit Incident
                </a>
                <form method="POST" action="{{ route('incidents.destroy', $incident) }}"
                      data-confirm="Permanently delete {{ $incident->incident_number }}? All motorist records and media files will also be deleted. This cannot be undone.">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-trash" style="font-size:.85rem;"></i> Delete Incident
                    </button>
                </form>
            </div>
        </div>
        @endif

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
</script>
@endpush
