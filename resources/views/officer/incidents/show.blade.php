@extends('layouts.mobile')
@section('title', $incident->incident_number)
@section('back_url', route('officer.incidents.index'))

@section('content')

@php
    $sc = ['open'=>'mob-badge-open','under_review'=>'mob-badge-review','closed'=>'mob-badge-closed'][$incident->status] ?? 'mob-badge-closed';
    $restrDesc = [
        'A'=>'Motorcycle','A1'=>'MC w/ Sidecar','B'=>'Light Vehicle',
        'B1'=>'Light Vehicle (Prof.)','B2'=>'Light Vehicle w/ Trailer',
        'C'=>'Medium/Heavy Truck','D'=>'Bus','BE'=>'Light + Heavy Trailer','CE'=>'Large Truck + Trailer'
    ];
    $mediaLabels = ['scene'=>'Scene Photo','ticket'=>'Citation Ticket','document'=>'Document','other'=>'Other'];
@endphp

<style>
.inc-media-badge { display:inline-block;font-size:.62rem;font-weight:700;padding:.15rem .45rem;border-radius:8px; }
.inc-media-scene    { background:#eff6ff;color:#3b82f6; }
.inc-media-ticket   { background:#fffbeb;color:#f59e0b; }
.inc-media-document { background:#f5f3ff;color:#8b5cf6; }
.inc-media-other    { background:#f9fafb;color:#6b7280; }
.inc-tag-reg   { display:inline-flex;align-items:center;gap:.2rem;background:#dcfce7;color:#15803d;border-radius:10px;font-size:.62rem;font-weight:700;padding:.12rem .45rem; }
.inc-tag-unreg { display:inline-flex;align-items:center;gap:.2rem;background:#fef9c3;color:#92400e;border-radius:10px;font-size:.62rem;font-weight:700;padding:.12rem .45rem; }
</style>

{{-- ── Incident Header ── --}}
<div class="mob-card" style="border-left:4px solid #dc2626;">
    <div class="mob-card-body">
        <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
                <div style="font-size:.62rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;">Incident Report</div>
                <div style="font-size:1.05rem;font-weight:800;color:#0f172a;margin-top:.1rem;">{{ $incident->incident_number }}</div>
                <div style="font-size:.68rem;color:#94a3b8;margin-top:.2rem;">
                    Recorded on {{ $incident->created_at->format('M d, Y · g:i A') }}
                </div>
            </div>
            <span class="mob-badge {{ $sc }}" style="flex-shrink:0;">{{ ucfirst(str_replace('_',' ',$incident->status)) }}</span>
        </div>

        <div class="mob-info-grid">
            <div>
                <div class="mob-info-label">Date</div>
                <div class="mob-info-value">{{ $incident->date_of_incident ? \Carbon\Carbon::parse($incident->date_of_incident)->format('M d, Y') : '—' }}</div>
            </div>
            @if($incident->time_of_incident)
            <div>
                <div class="mob-info-label">Time</div>
                <div class="mob-info-value">{{ \Carbon\Carbon::parse($incident->time_of_incident)->format('g:i A') }}</div>
            </div>
            @endif
            <div class="mob-info-grid-full">
                <div class="mob-info-label"><i class="ph ph-map-pin me-1"></i>Location</div>
                <div class="mob-info-value">{{ $incident->location }}</div>
            </div>
            @if($incident->description)
            <div class="mob-info-grid-full">
                <div class="mob-info-label">Description</div>
                <div class="mob-info-value" style="font-weight:400;color:#334155;">{{ $incident->description }}</div>
            </div>
            @endif
            @if($incident->recorder)
            <div class="mob-info-grid-full">
                <div class="mob-info-label"><i class="ph ph-user me-1"></i>Recorded by</div>
                <div class="mob-info-value">{{ $incident->recorder->name }}</div>
            </div>
            @endif
        </div>

        @can('update', $incident)
        <div class="mt-3">
            <a href="{{ route('officer.incidents.edit', $incident) }}"
               class="btn btn-outline-primary w-100" style="border-radius:12px;font-weight:700;">
                <i class="ph ph-pencil-simple me-2"></i>Edit Incident
            </a>
        </div>
        @endcan
    </div>
</div>

{{-- ── Motorists Involved ── --}}
<div class="mob-card">
    <div class="mob-section-title">Motorists Involved ({{ $incident->motorists->count() }})</div>

    @forelse($incident->motorists as $idx => $m)
    @php
        $restrRaw   = $m->violator ? $m->violator->license_restriction : ($m->license_restriction ?? null);
        $restrCodes = $restrRaw ? array_filter(array_map('trim', explode(',', $restrRaw))) : [];
        $expDate    = $m->violator ? $m->violator->license_expiry_date : ($m->license_expiry_date ?? null);
        $licType    = $m->violator ? ($m->violator->license_type ?? null) : ($m->license_type ?? null);
        $licNum     = $m->violator ? ($m->violator->license_number ?? null) : ($m->motorist_license ?? null);
        $vPlate = $m->vehicle ? $m->vehicle->plate_number : $m->vehicle_plate;
        $vType  = $m->vehicle ? $m->vehicle->vehicle_type : ($m->vehicle_type_manual ?? null);
        $vMake  = $m->vehicle ? $m->vehicle->make         : $m->vehicle_make;
        $vModel = $m->vehicle ? $m->vehicle->model        : $m->vehicle_model;
        $vColor = $m->vehicle ? $m->vehicle->color        : $m->vehicle_color;
        $vOR    = $m->vehicle ? $m->vehicle->or_number    : ($m->vehicle_or_number ?? null);
        $vCR    = $m->vehicle ? $m->vehicle->cr_number    : ($m->vehicle_cr_number ?? null);
        $vCha   = $m->vehicle ? $m->vehicle->chassis_number : ($m->vehicle_chassis ?? null);
    @endphp
    <div class="mob-list-item" style="cursor:default;align-items:flex-start;padding-top:.9rem;padding-bottom:.9rem;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
        {{-- Avatar --}}
        @if($m->violator?->photo)
            <img src="{{ uploaded_file_url($m->violator->photo) }}"
                 class="mob-photo-single" data-full="{{ uploaded_file_url($m->violator->photo) }}"
                 style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0;margin-right:.875rem;margin-top:.05rem;cursor:zoom-in;" alt="">
        @elseif($m->motorist_photo)
            <img src="{{ uploaded_file_url($m->motorist_photo) }}"
                 class="mob-photo-single" data-full="{{ uploaded_file_url($m->motorist_photo) }}"
                 style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0;margin-right:.875rem;margin-top:.05rem;cursor:zoom-in;" alt="ID photo">
        @else
            <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:.875rem;margin-top:.05rem;">
                <i class="ph-fill ph-user" style="color:#1d4ed8;font-size:.9rem;"></i>
            </div>
        @endif

        <div style="flex:1;min-width:0;">
            {{-- Name + tags --}}
            <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-bottom:.2rem;">
                <span style="font-size:.875rem;font-weight:700;color:#0f172a;">
                    @if($m->violator)
                        <a href="{{ route('officer.motorists.show', $m->violator) }}" style="color:#1d4ed8;text-decoration:none;">
                            {{ $m->violator->last_name }}, {{ $m->violator->first_name }}
                        </a>
                    @else
                        {{ $m->motorist_name ?? '—' }}
                    @endif
                </span>
                @if($m->violator)
                    <span class="inc-tag-reg"><i class="ph-fill ph-seal-check"></i>Registered</span>
                @else
                    <span class="inc-tag-unreg"><i class="ph ph-warning"></i>Unregistered</span>
                @endif
                {{-- Motorist number badge --}}
                <span style="background:linear-gradient(135deg,#d97706,#b45309);color:#fff;border-radius:6px;font-size:.62rem;font-weight:800;padding:.1rem .35rem;">#{{ $idx + 1 }}</span>
            </div>

            {{-- Contact info for unregistered --}}
            @if(!$m->violator && ($m->motorist_contact ?? null))
            <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;">
                <i class="ph ph-phone me-1"></i>{{ $m->motorist_contact }}
            </div>
            @endif

            {{-- Charge badge --}}
            @if($m->chargeType)
            <div style="margin-bottom:.3rem;">
                <span style="display:inline-flex;align-items:center;gap:.25rem;background:#f3e8ff;color:#6d28d9;border:1px solid #e9d5ff;border-radius:20px;font-size:.65rem;font-weight:700;padding:.15rem .55rem;">
                    <i class="ph-fill ph-shield-warning"></i>{{ $m->chargeType->name }}
                </span>
            </div>
            @endif

            {{-- License details --}}
            @if($licNum || $licType || $restrCodes || $expDate)
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.45rem .6rem;margin-bottom:.35rem;font-size:.72rem;">
                @if($licNum)
                <div style="display:flex;gap:.4rem;margin-bottom:.15rem;">
                    <span style="color:#94a3b8;min-width:54px;">License:</span>
                    <span style="font-family:ui-monospace,monospace;font-weight:600;color:#0f172a;">{{ $licNum }}</span>
                </div>
                @endif
                @if($licType)
                <div style="display:flex;gap:.4rem;margin-bottom:.15rem;">
                    <span style="color:#94a3b8;min-width:54px;">Type:</span>
                    <span style="color:#334155;">{{ $licType }}</span>
                </div>
                @endif
                @if($expDate)
                @php $exp = \Carbon\Carbon::parse($expDate); @endphp
                <div style="display:flex;gap:.4rem;align-items:center;margin-bottom:.15rem;">
                    <span style="color:#94a3b8;min-width:54px;">Expiry:</span>
                    <span style="{{ $exp->isPast() ? 'color:#dc2626;' : 'color:#334155;' }}">{{ $exp->format('M d, Y') }}</span>
                    @if($exp->isPast())
                        <span style="background:#fee2e2;color:#dc2626;border-radius:6px;padding:.05rem .3rem;font-size:.6rem;font-weight:700;">Expired</span>
                    @endif
                </div>
                @endif
                @if($restrCodes)
                <div style="display:flex;gap:.4rem;align-items:flex-start;">
                    <span style="color:#94a3b8;min-width:54px;padding-top:.1rem;">Restr.:</span>
                    <div>
                        @foreach($restrCodes as $code)
                            <span title="{{ $restrDesc[$code] ?? '' }}"
                                  style="display:inline-block;background:#fef3c7;color:#92400e;border-radius:6px;padding:.05rem .35rem;font-size:.65rem;font-weight:700;margin:.05rem .1rem 0 0;">{{ $code }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Vehicle --}}
            @if($vPlate || $vMake)
            <div style="display:flex;align-items:flex-start;gap:.35rem;padding:.4rem .6rem;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;font-size:.72rem;color:#334155;margin-bottom:.25rem;">
                <i class="ph-fill ph-truck" style="color:#94a3b8;flex-shrink:0;margin-top:.1rem;"></i>
                <div style="flex:1;min-width:0;">
                    <div>
                        <span style="font-weight:700;font-family:ui-monospace,monospace;">{{ $vPlate ?? '—' }}</span>
                        @if($vType)<span style="display:inline-block;background:#dbeafe;color:#1e40af;border-radius:4px;padding:0 .3rem;font-size:.62rem;font-weight:700;margin-left:.25rem;">{{ $vType }}</span>@endif
                    </div>
                    @if($vMake || $vModel || $vColor)
                    <div style="color:#94a3b8;margin-top:.1rem;">{{ implode(' · ', array_filter([$vMake, $vModel, $vColor])) }}</div>
                    @endif
                    @if($vOR || $vCR)
                    <div style="margin-top:.1rem;color:#64748b;">
                        @if($vOR)<span>OR: <span style="font-family:ui-monospace,monospace;">{{ $vOR }}</span></span>@endif
                        @if($vOR && $vCR)<span style="margin:0 .3rem;color:#cbd5e1;">·</span>@endif
                        @if($vCR)<span>CR: <span style="font-family:ui-monospace,monospace;">{{ $vCR }}</span></span>@endif
                    </div>
                    @endif
                    @if($vCha)
                    <div style="margin-top:.1rem;color:#64748b;">Chassis: <span style="font-family:ui-monospace,monospace;">{{ $vCha }}</span></div>
                    @endif
                </div>
            </div>
            @endif

            @if($m->notes)
            <div style="font-size:.72rem;color:#64748b;font-style:italic;">{{ $m->notes }}</div>
            @endif
        </div>
    </div>
    @empty
    <div class="mob-empty">
        <i class="ph ph-users mob-empty-icon"></i>
        <div class="mob-empty-text">No motorists linked</div>
    </div>
    @endforelse
</div>

{{-- ── Evidence & Media ── --}}
@if($incident->media->isNotEmpty())
<div class="mob-card">
    <div class="mob-section-title">Evidence &amp; Media ({{ $incident->media->count() }})</div>
    <div class="mob-card-body pt-1">
        <div class="row g-2">
            @foreach($incident->media as $med)
            <div class="{{ $med->isImage() ? 'col-6' : 'col-12' }}">
                @if($med->isImage())
                <div style="position:relative;">
                    <img src="{{ uploaded_file_url($med->file_path) }}"
                         alt="{{ $med->caption ?? 'Media' }}"
                         class="mob-photo-thumb"
                         data-full="{{ uploaded_file_url($med->file_path) }}"
                         style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);cursor:zoom-in;">
                    <div style="position:absolute;bottom:.4rem;left:.4rem;">
                        <span class="inc-media-badge inc-media-{{ $med->media_type ?? 'other' }}">
                            {{ $mediaLabels[$med->media_type ?? 'other'] ?? ($med->media_type ?? 'Other') }}
                        </span>
                    </div>
                </div>
                @if($med->caption)
                <div style="font-size:.68rem;color:#94a3b8;margin-top:.25rem;padding:0 .1rem;">{{ $med->caption }}</div>
                @endif
                @else
                <a href="{{ uploaded_file_url($med->file_path) }}" target="_blank"
                   style="display:flex;align-items:center;gap:.65rem;padding:.65rem .75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#334155;">
                    <i class="ph-fill ph-file-pdf" style="font-size:1.6rem;color:#dc2626;flex-shrink:0;"></i>
                    <div>
                        <div style="font-size:.8rem;font-weight:700;">
                            {{ $mediaLabels[$med->media_type ?? 'other'] ?? 'Document' }}
                        </div>
                        @if($med->caption)
                        <div style="font-size:.68rem;color:#94a3b8;">{{ $med->caption }}</div>
                        @endif
                    </div>
                    <i class="ph ph-arrow-square-out" style="margin-left:auto;color:#94a3b8;flex-shrink:0;"></i>
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection
