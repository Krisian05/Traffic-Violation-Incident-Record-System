@extends('layouts.mobile')
@section('title', $vehicle->plate_number ?? 'Vehicle Detail')
@section('back_url', $vehicle->violator ? route('officer.motorists.show', $vehicle->violator) : route('officer.motorists.index'))

@push('styles')
@include('partials.motshow-styles')
<style>
.vehshow-hero { position:relative;overflow:hidden;background:linear-gradient(160deg,#1e3a5f 0%,#1d4ed8 55%,#1e40af 100%);border-radius:24px;padding:1.15rem;margin-bottom:1rem;box-shadow:0 14px 36px rgba(29,78,216,.36); }
.vehshow-hero::before { content:'';position:absolute;top:-78px;right:-46px;width:176px;height:176px;border-radius:50%;background:rgba(255,255,255,.08); }
.vehshow-hero::after  { content:'';position:absolute;left:-22px;bottom:-62px;width:138px;height:138px;border-radius:50%;background:rgba(255,255,255,.05); }
.vehshow-hero-inner { position:relative;z-index:1; }
.vehshow-stat-grid { display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin-top:1rem; }
.vehshow-stat { text-align:center;padding:.78rem .42rem;border-radius:15px;background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.16); }
.vehshow-stat-num   { font-size:1.1rem;line-height:1;font-weight:800;color:#fff; }
.vehshow-stat-label { margin-top:.22rem;font-size:.56rem;font-weight:800;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.08em; }
</style>
@endpush

@section('content')

@php
    $plate = $vehicle->plate_number;
    $type  = $vehicle->vehicle_type; // MV or MC
    $make  = $vehicle->make;
    $model = $vehicle->model;
    $color = $vehicle->color;
    $year  = $vehicle->year;
    $orNo  = $vehicle->or_number;
    $crNo  = $vehicle->cr_number;
    $chaNo = $vehicle->chassis_number;
    $owner = $vehicle->owner_name ?: $vehicle->violator?->full_name;

    $typeIcon  = $type === 'MC' ? 'ph-motorcycle' : 'ph-car-profile';
    $typeLabel = $type === 'MC' ? 'Motorcycle' : 'Motor Vehicle';

    $gallery = $allPhotos->isNotEmpty()
        ? $allPhotos->map(fn($p) => uploaded_file_url($p->photo))->values()->implode('|')
        : '';
    $caption = $plate ? 'Vehicle — ' . $plate : 'Vehicle Photo';
@endphp

{{-- ── Hero ── --}}
<div class="vehshow-hero">
    <div class="vehshow-hero-inner">
        <div class="d-flex align-items-start gap-3">

            {{-- Thumbnail / icon --}}
            @if($allPhotos->isNotEmpty())
            <div style="width:72px;height:72px;border-radius:20px;overflow:hidden;flex-shrink:0;box-shadow:0 10px 22px rgba(0,0,0,.25);cursor:zoom-in;"
                 onclick="mobVphOpen(this.querySelector('img'))">
                <img src="{{ uploaded_file_url($allPhotos->first()->photo) }}"
                     data-gallery="{{ $gallery }}"
                     data-caption="{{ $caption }}"
                     style="width:100%;height:100%;object-fit:cover;pointer-events:none;"
                     alt="Vehicle photo">
            </div>
            @else
            <div style="width:72px;height:72px;border-radius:20px;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 10px 22px rgba(0,0,0,.18);">
                <i class="ph-fill {{ $typeIcon }}" style="font-size:2rem;color:#fff;"></i>
            </div>
            @endif

            <div style="flex:1;min-width:0;">
                <div class="motshow-chip">
                    <i class="ph-fill {{ $typeIcon }}"></i>
                    {{ $typeLabel }}
                </div>
                <div class="motshow-name" style="font-size:1.05rem;">
                    {{ $plate ?? 'No Plate' }}
                </div>
                @if($make || $model)
                <div class="motshow-subtitle">{{ trim($make . ' ' . $model) }}</div>
                @endif
                <div class="motshow-meta-row">
                    @if($color)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-paint-brush"></i>
                        {{ $color }}
                    </span>
                    @endif
                    @if($year)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-calendar-blank"></i>
                        {{ $year }}
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="vehshow-stat-grid">
            <div class="vehshow-stat">
                <div class="vehshow-stat-num">{{ $vehicle->violations_count }}</div>
                <div class="vehshow-stat-label">Violations</div>
            </div>
            <div class="vehshow-stat">
                <div class="vehshow-stat-num">{{ $allPhotos->count() }}</div>
                <div class="vehshow-stat-label">Photos</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Owner / Motorist card ── --}}
@if($vehicle->violator)
@php $motorist = $vehicle->violator; @endphp
<a href="{{ route('officer.motorists.show', $motorist) }}"
   style="display:flex;align-items:center;gap:.85rem;padding:.9rem 1rem;background:#fff;border-radius:18px;border:1px solid rgba(15,23,42,.05);box-shadow:0 3px 16px rgba(15,23,42,.06);margin-bottom:.9rem;text-decoration:none;color:inherit;">
    <div style="width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#1d4ed8,#1e40af);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;box-shadow:0 4px 12px rgba(29,78,216,.3);">
        @if($motorist->photo)
            <img src="{{ uploaded_file_url($motorist->photo) }}" style="width:100%;height:100%;object-fit:cover;" alt="Motorist">
        @else
            <span style="font-size:.88rem;font-weight:800;color:#fff;">{{ strtoupper(substr($motorist->first_name,0,1).substr($motorist->last_name,0,1)) }}</span>
        @endif
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Registered Owner</div>
        <div style="font-size:.92rem;font-weight:800;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $motorist->full_name }}</div>
        @if($motorist->license_number)
        <div style="font-size:.72rem;color:#64748b;margin-top:.05rem;">License {{ $motorist->license_number }}</div>
        @endif
    </div>
    <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:1rem;flex-shrink:0;"></i>
</a>
@elseif($owner)
<div style="display:flex;align-items:center;gap:.85rem;padding:.9rem 1rem;background:#fff;border-radius:18px;border:1px solid rgba(15,23,42,.05);box-shadow:0 3px 16px rgba(15,23,42,.06);margin-bottom:.9rem;">
    <div style="width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#475569,#334155);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(71,85,105,.3);">
        <i class="ph-fill ph-user" style="font-size:1.2rem;color:#fff;"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Owner</div>
        <div style="font-size:.92rem;font-weight:800;color:#0f172a;">{{ $owner }}</div>
    </div>
</div>
@endif

{{-- ── Vehicle Details ── --}}
<div class="motshow-section">Vehicle Details</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    @if($plate)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-identification-badge" style="font-size:.9rem;color:#2563eb;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Plate Number</div>
            <div style="font-size:.92rem;font-weight:800;color:#0f172a;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:.06em;">{{ $plate }}</div>
        </div>
    </div>
    @endif

    @if($type)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill {{ $typeIcon }}" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Vehicle Type</div>
            <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ $typeLabel }}</div>
        </div>
    </div>
    @endif

    @if($make)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-factory" style="font-size:.9rem;color:#ea580c;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Make</div>
            <div style="font-size:.88rem;font-weight:600;color:#334155;">{{ $make }}</div>
        </div>
    </div>
    @endif

    @if($model)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fdf4ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-car" style="font-size:.9rem;color:#9333ea;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Model</div>
            <div style="font-size:.88rem;font-weight:600;color:#334155;">{{ $model }}</div>
        </div>
    </div>
    @endif

    @if($color)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fef9c3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-paint-bucket" style="font-size:.9rem;color:#ca8a04;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Color</div>
            <div style="font-size:.88rem;font-weight:600;color:#334155;">{{ $color }}</div>
        </div>
    </div>
    @endif

    @if($year)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-calendar" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Year</div>
            <div style="font-size:.88rem;font-weight:600;color:#334155;">{{ $year }}</div>
        </div>
    </div>
    @endif

    @if($orNo || $crNo)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-file-text" style="font-size:.9rem;color:#dc2626;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">OR / CR Number</div>
            <div style="font-size:.85rem;font-weight:600;color:#334155;">
                @if($orNo)OR: {{ $orNo }}@endif
                @if($orNo && $crNo) &nbsp;&middot;&nbsp; @endif
                @if($crNo)CR: {{ $crNo }}@endif
            </div>
        </div>
    </div>
    @endif

    @if($chaNo)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-barcode" style="font-size:.9rem;color:#64748b;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Chassis Number</div>
            <div style="font-size:.85rem;font-weight:600;color:#334155;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;">{{ $chaNo }}</div>
        </div>
    </div>
    @endif
</div>

{{-- ── Photos ── --}}
@if($allPhotos->isNotEmpty())
<div class="motshow-section">Photos</div>
<div style="display:flex;gap:.6rem;overflow-x:auto;padding-bottom:.4rem;margin-bottom:.9rem;scrollbar-width:none;">
    @foreach($allPhotos as $i => $photo)
    <div style="position:relative;width:100px;height:100px;border-radius:14px;overflow:hidden;flex-shrink:0;box-shadow:0 3px 10px rgba(15,23,42,.15);cursor:zoom-in;">
        <img src="{{ uploaded_file_url($photo->photo) }}"
             alt="Photo {{ $i + 1 }}"
             data-gallery="{{ $gallery }}"
             data-caption="{{ $caption }}"
             onclick="event.stopPropagation();mobVphOpen(this);"
             style="width:100%;height:100%;object-fit:cover;display:block;">
        @if($i === 0 && $allPhotos->count() > 1)
        <div style="position:absolute;bottom:4px;right:4px;background:rgba(0,0,0,.55);border-radius:6px;padding:.1rem .28rem;pointer-events:none;">
            <span style="color:#fff;font-size:.55rem;font-weight:800;line-height:1;">+{{ $allPhotos->count() - 1 }}</span>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ── Violations ── --}}
@if($vehicle->violations->isNotEmpty())
<div class="motshow-section">Violations ({{ $vehicle->violations->count() }})</div>
@foreach($vehicle->violations as $viol)
@php
    $isOvd = $viol->status === 'pending'
        && $viol->date_of_violation
        && $viol->date_of_violation <= now()->subHours(72);
    $vs = $viol->status ?? 'pending';
    $vLabel = match(true) {
        $vs === 'settled'   => 'Settled',
        $vs === 'contested' => 'Contested',
        $isOvd              => 'Overdue',
        default             => 'Pending',
    };
    $vColor = match(true) {
        $vs === 'settled'   => '#16a34a',
        $vs === 'contested' => '#2563eb',
        $isOvd              => '#dc2626',
        default             => '#d97706',
    };
    $vBg = match(true) {
        $vs === 'settled'   => '#f0fdf4',
        $vs === 'contested' => '#eff6ff',
        $isOvd              => '#fef2f2',
        default             => '#fffbeb',
    };
@endphp
<a href="{{ route('officer.violations.show', $viol) }}"
   style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;background:#fff;border-radius:16px;border:1px solid rgba(15,23,42,.05);box-shadow:0 2px 10px rgba(15,23,42,.04);margin-bottom:.55rem;text-decoration:none;color:inherit;">
    <div style="width:38px;height:38px;border-radius:12px;background:{{ $vBg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="ph-fill ph-warning-circle" style="font-size:1rem;color:{{ $vColor }};"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:.82rem;font-weight:800;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $viol->violationType?->name ?? 'Unknown Violation' }}</div>
        <div style="font-size:.7rem;color:#94a3b8;margin-top:.08rem;">
            {{ $viol->date_of_violation?->format('M d, Y') ?? '—' }}
            @if($viol->ticket_number) &middot; #{{ $viol->ticket_number }} @endif
        </div>
    </div>
    <span style="font-size:.6rem;font-weight:800;color:{{ $vColor }};background:{{ $vBg }};padding:.2rem .5rem;border-radius:6px;white-space:nowrap;">{{ $vLabel }}</span>
    <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.9rem;flex-shrink:0;"></i>
</a>
@endforeach
@endif

@endsection
