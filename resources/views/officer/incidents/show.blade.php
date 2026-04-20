@extends('layouts.mobile')
@section('title', $incident->incident_number)
@section('back_url', route('officer.incidents.index'))

@push('styles')
@include('partials.motshow-styles')
<style>
.ishow-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #7f1d1d 0%, #dc2626 55%, #b91c1c 100%);
    border-radius: 24px;
    padding: 1.15rem;
    margin-bottom: 1rem;
    box-shadow: 0 14px 36px rgba(127,29,29,.36);
}
.ishow-hero::before {
    content: '';
    position: absolute;
    top: -78px; right: -46px;
    width: 176px; height: 176px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}
.ishow-hero::after {
    content: '';
    position: absolute;
    left: -22px; bottom: -62px;
    width: 138px; height: 138px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}
.ishow-hero-inner { position: relative; z-index: 1; }
.ishow-stat-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: .55rem; margin-top: 1rem; }
.ishow-stat { text-align: center; padding: .78rem .42rem; border-radius: 15px; background: rgba(255,255,255,.11); border: 1px solid rgba(255,255,255,.16); }
.ishow-stat-num   { font-size: 1.1rem; line-height: 1; font-weight: 800; color: #fff; }
.ishow-stat-label { margin-top: .22rem; font-size: .56rem; font-weight: 800; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .08em; }
.ishow-tag-reg   { display:inline-flex;align-items:center;gap:.2rem;background:#dcfce7;color:#15803d;border-radius:10px;font-size:.62rem;font-weight:700;padding:.12rem .45rem; }
.ishow-tag-unreg { display:inline-flex;align-items:center;gap:.2rem;background:#fef9c3;color:#92400e;border-radius:10px;font-size:.62rem;font-weight:700;padding:.12rem .45rem; }
.ishow-exp-expired { color: #dc2626; }
.ishow-exp-valid   { color: #334155; }
.ishow-media-badge { display:inline-block;font-size:.62rem;font-weight:700;padding:.15rem .45rem;border-radius:8px; }
.ishow-media-scene    { background:#eff6ff;color:#3b82f6; }
.ishow-media-ticket   { background:#fffbeb;color:#f59e0b; }
.ishow-media-document { background:#f5f3ff;color:#8b5cf6; }
.ishow-media-other    { background:#f9fafb;color:#6b7280; }
.ishow-mot-item { padding: .9rem 1rem; }
.ishow-mot-sep  { border-bottom: 1px solid #f1f5f9; }
</style>
@endpush

@section('content')

@php
    $sc = match($incident->status) {
        'under_investigation' => ['label' => 'Under Investigation', 'class' => 'motshow-status--danger', 'icon' => 'ph-flag'],
        'cleared'             => ['label' => 'Cleared',             'class' => 'motshow-status--info',   'icon' => 'ph-shield-check'],
        'solved'              => ['label' => 'Solved',              'class' => 'motshow-status--safe',   'icon' => 'ph-check-circle'],
        default               => ['label' => 'Under Investigation', 'class' => 'motshow-status--danger', 'icon' => 'ph-flag'],
    };
    $mediaLabels = ['scene' => 'Scene Photo', 'ticket' => 'Citation Ticket', 'document' => 'Document', 'other' => 'Other'];
    $restrDesc = [
        'A'  => 'Motorcycle',         'A1' => 'MC w/ Sidecar',
        'B'  => 'Light Vehicle',      'B1' => 'Light Vehicle (Prof.)',
        'B2' => 'Light Vehicle w/ Trailer',
        'C'  => 'Medium/Heavy Truck', 'D'  => 'Bus',
        'BE' => 'Light + Heavy Trailer', 'CE' => 'Large Truck + Trailer',
    ];
    $motoristCount = $incident->motorists->count();
    $mediaCount    = $incident->media->count();
@endphp

{{-- ── Hero ── --}}
<div class="ishow-hero">
    <div class="ishow-hero-inner">
        <div class="d-flex align-items-start gap-3">
            <div style="width:72px;height:72px;border-radius:20px;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 10px 22px rgba(0,0,0,.18);">
                <i class="ph-fill ph-flag" style="font-size:2rem;color:#fff;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="motshow-chip">
                    <i class="ph-fill ph-flag"></i>
                    Incident Report
                </div>
                <div class="motshow-name" style="font-size:1rem;">
                    {{ $incident->incident_number }}
                </div>
                <div class="motshow-subtitle">Recorded {{ $incident->created_at->format('M d, Y · g:i A') }}</div>
                <div class="motshow-meta-row">
                    @if($incident->date_of_incident)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-calendar-blank"></i>
                        {{ \Carbon\Carbon::parse($incident->date_of_incident)->format('M d, Y') }}
                    </span>
                    @endif
                    @if($incident->time_of_incident)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-clock"></i>
                        {{ \Carbon\Carbon::parse($incident->time_of_incident)->format('g:i A') }}
                    </span>
                    @endif
                    @if($incident->location)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-map-pin"></i>
                        {{ \Illuminate\Support\Str::limit($incident->location, 24) }}
                    </span>
                    @endif
                </div>
                <span class="motshow-status {{ $sc['class'] }}">
                    <i class="ph-fill {{ $sc['icon'] }}"></i>
                    {{ $sc['label'] }}
                </span>
            </div>
        </div>

        <div class="ishow-stat-grid">
            <div class="ishow-stat">
                <div class="ishow-stat-num">{{ $motoristCount }}</div>
                <div class="ishow-stat-label">Motorists</div>
            </div>
            <div class="ishow-stat">
                <div class="ishow-stat-num">{{ $mediaCount }}</div>
                <div class="ishow-stat-label">Media</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Actions ── --}}
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.55rem;margin-bottom:1rem;">
    <a href="{{ route('officer.incidents.edit', $incident) }}" class="motshow-action motshow-action--blue">
        <i class="ph-bold ph-pencil-simple"></i>
        Edit Incident
    </a>
    <a href="{{ route('officer.incidents.index') }}" class="motshow-action motshow-action--ghost">
        <i class="ph ph-list-bullets"></i>
        All Incidents
    </a>
</div>

{{-- ── Incident Details ── --}}
<div class="motshow-section">Incident Details</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    @if($incident->date_of_incident)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-calendar-blank" style="font-size:.9rem;color:#2563eb;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Date</div>
            <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ \Carbon\Carbon::parse($incident->date_of_incident)->format('F d, Y') }}</div>
        </div>
    </div>
    @endif

    @if($incident->time_of_incident)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-clock" style="font-size:.9rem;color:#7c3aed;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Time</div>
            <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ \Carbon\Carbon::parse($incident->time_of_incident)->format('g:i A') }}</div>
        </div>
    </div>
    @endif

    @if($incident->location)
    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
            <i class="ph-fill ph-map-pin" style="font-size:.9rem;color:#ea580c;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Location</div>
            <div style="font-size:.85rem;font-weight:600;color:#334155;line-height:1.4;">{{ $incident->location }}</div>
        </div>
    </div>
    @endif

    @if($incident->description)
    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fafafa;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
            <i class="ph-fill ph-note" style="font-size:.9rem;color:#64748b;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Description</div>
            <div style="font-size:.85rem;color:#334155;line-height:1.5;">{{ $incident->description }}</div>
        </div>
    </div>
    @endif

    @if($incident->recorder)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-user-circle-check" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Recorded By</div>
            <div style="font-size:.88rem;font-weight:600;color:#334155;">{{ $incident->recorder->name }}</div>
        </div>
    </div>
    @endif
</div>

{{-- ── Motorists Involved ── --}}
<div class="motshow-section">Motorists Involved ({{ $motoristCount }})</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    @forelse($incident->motorists as $idx => $m)
    @php
        $restrRaw   = $m->violator ? $m->violator->license_restriction : ($m->license_restriction ?? null);
        $restrCodes = $restrRaw ? array_filter(array_map('trim', explode(',', $restrRaw))) : [];
        $expDate    = $m->violator ? $m->violator->license_expiry_date : ($m->license_expiry_date ?? null);
        $licType    = $m->violator ? ($m->violator->license_type ?? null)   : ($m->license_type ?? null);
        $licNum     = $m->violator ? ($m->violator->license_number ?? null) : ($m->motorist_license ?? null);
        $vPlate = $m->vehicle ? $m->vehicle->plate_number  : $m->vehicle_plate;
        $vType  = $m->vehicle ? $m->vehicle->vehicle_type  : ($m->vehicle_type_manual ?? null);
        $vMake  = $m->vehicle ? $m->vehicle->make          : $m->vehicle_make;
        $vModel = $m->vehicle ? $m->vehicle->model         : $m->vehicle_model;
        $vColor = $m->vehicle ? $m->vehicle->color         : $m->vehicle_color;
        $vOR    = $m->vehicle ? $m->vehicle->or_number     : ($m->vehicle_or_number ?? null);
        $vCR    = $m->vehicle ? $m->vehicle->cr_number     : ($m->vehicle_cr_number ?? null);
        $vCha   = $m->vehicle ? $m->vehicle->chassis_number : ($m->vehicle_chassis ?? null);
    @endphp
    <div class="ishow-mot-item {{ !$loop->last ? 'ishow-mot-sep' : '' }}">
        <div style="display:flex;align-items:flex-start;gap:.75rem;">
            {{-- Avatar --}}
            @if($m->violator?->photo)
                <img src="{{ uploaded_file_url($m->violator->photo) }}"
                     class="mob-photo-single" data-full="{{ uploaded_file_url($m->violator->photo) }}"
                     style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0;cursor:zoom-in;" alt="">
            @elseif($m->motorist_photo)
                <img src="{{ uploaded_file_url($m->motorist_photo) }}"
                     class="mob-photo-single" data-full="{{ uploaded_file_url($m->motorist_photo) }}"
                     style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0;cursor:zoom-in;" alt="ID photo">
            @else
                <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ph-fill ph-user" style="color:#1d4ed8;font-size:1rem;"></i>
                </div>
            @endif

            <div style="flex:1;min-width:0;">
                {{-- Name + tags --}}
                <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-bottom:.25rem;">
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
                        <span class="ishow-tag-reg"><i class="ph-fill ph-seal-check"></i>Registered</span>
                    @else
                        <span class="ishow-tag-unreg"><i class="ph ph-warning"></i>Unregistered</span>
                    @endif
                    <span style="background:linear-gradient(135deg,#d97706,#b45309);color:#fff;border-radius:6px;font-size:.62rem;font-weight:800;padding:.1rem .35rem;">#{{ $idx + 1 }}</span>
                </div>

                {{-- Contact for unregistered --}}
                @if(!$m->violator && (($m->motorist_contact ?? null) || ($m->motorist_address ?? null)))
                <div style="font-size:.72rem;color:#64748b;margin-bottom:.2rem;display:flex;flex-wrap:wrap;gap:.6rem;">
                    @if($m->motorist_contact)<span><i class="ph ph-phone me-1"></i>{{ $m->motorist_contact }}</span>@endif
                    @if($m->motorist_address)<span><i class="ph ph-map-pin me-1"></i>{{ $m->motorist_address }}</span>@endif
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
                        <span class="{{ $exp->isPast() ? 'ishow-exp-expired' : 'ishow-exp-valid' }}">{{ $exp->format('M d, Y') }}</span>
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

                @if($m->vehicle_photo && count($m->vehicle_photo))
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.45rem;margin:.45rem 0 .25rem;">
                    @foreach($m->vehicle_photo as $vp)
                    <img src="{{ uploaded_file_url($vp) }}"
                         class="mob-photo-thumb"
                         data-full="{{ uploaded_file_url($vp) }}"
                         data-caption="Vehicle photo — {{ $m->violator?->full_name ?? ($m->motorist_name ?? 'Motorist') }}"
                         style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);cursor:zoom-in;"
                         alt="Vehicle photo">
                    @endforeach
                </div>
                @endif

                @if($m->notes)
                <div style="font-size:.72rem;color:#64748b;font-style:italic;">{{ $m->notes }}</div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div style="padding:1.5rem 1rem;text-align:center;">
        <i class="ph ph-users" style="font-size:2rem;color:#cbd5e1;display:block;margin-bottom:.5rem;"></i>
        <div style="font-size:.82rem;color:#94a3b8;font-weight:600;">No motorists linked</div>
    </div>
    @endforelse
</div>

{{-- ── Evidence & Media ── --}}
@if($incident->media->isNotEmpty())
<div class="motshow-section">Evidence &amp; Media ({{ $mediaCount }})</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    <div style="padding:1rem;">
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
                        <span class="ishow-media-badge ishow-media-{{ $med->media_type ?? 'other' }}">
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
                        <div style="font-size:.8rem;font-weight:700;">{{ $mediaLabels[$med->media_type ?? 'other'] ?? 'Document' }}</div>
                        @if($med->caption)<div style="font-size:.68rem;color:#94a3b8;">{{ $med->caption }}</div>@endif
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
