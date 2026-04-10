@extends('layouts.mobile')
@section('title', $violation->ticket_number ?? 'Violation Detail')
@section('back_url', $violation->violator ? route('officer.motorists.show', $violation->violator) : route('officer.motorists.index'))

@push('styles')
@include('partials.motshow-styles')
<style>
.vshow-hero { position:relative;overflow:hidden;background:linear-gradient(160deg,#7f1d1d 0%,#dc2626 55%,#b91c1c 100%);border-radius:24px;padding:1.15rem;margin-bottom:1rem;box-shadow:0 14px 36px rgba(127,29,29,.36); }
.vshow-hero::before { content:'';position:absolute;top:-78px;right:-46px;width:176px;height:176px;border-radius:50%;background:rgba(255,255,255,.08); }
.vshow-hero::after  { content:'';position:absolute;left:-22px;bottom:-62px;width:138px;height:138px;border-radius:50%;background:rgba(255,255,255,.05); }
.vshow-hero-inner { position:relative;z-index:1; }
.vshow-stat-grid { display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin-top:1rem; }
.vshow-stat { text-align:center;padding:.78rem .42rem;border-radius:15px;background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.16); }
.vshow-stat-num   { font-size:1.1rem;line-height:1;font-weight:800;color:#fff; }
.vshow-stat-label { margin-top:.22rem;font-size:.56rem;font-weight:800;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.08em; }
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
    $orNo  = $veh ? $veh->or_number  : $violation->vehicle_or_number;
    $crNo  = $veh ? $veh->cr_number  : $violation->vehicle_cr_number;
    $chaNo = $veh ? $veh->chassis_number : $violation->vehicle_chassis;
    $hasVehicle = $plate || $make || $model || $owner;

    $fineAmt = $violation->violationType?->fine_amount ?? 0;
@endphp

{{-- ── Hero ── --}}
<div class="vshow-hero">
    <div class="vshow-hero-inner">
        <div class="d-flex align-items-start gap-3">

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
                <span class="motshow-status motshow-status--{{ $status === 'settled' ? 'safe' : ($status === 'contested' ? 'info' : ($isOverdue ? 'danger' : 'warn')) }}">
                    <i class="ph-fill {{ $statusIcon }}"></i>
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        @if($fineAmt > 0)
        <div class="vshow-stat-grid">
            <div class="vshow-stat">
                <div class="vshow-stat-num">₱{{ number_format($fineAmt, 0) }}</div>
                <div class="vshow-stat-label">Fine Amount</div>
            </div>
            <div class="vshow-stat">
                <div class="vshow-stat-num">{{ $violation->date_of_violation?->format('Y') ?? '—' }}</div>
                <div class="vshow-stat-label">Year</div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ── Motorist card ── --}}
@php $motorist = $violation->violator; @endphp
@if($motorist)
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
        <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Motorist</div>
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
<div class="motshow-alert motshow-alert--danger">
    <div class="motshow-alert-icon"><i class="ph-fill ph-warning-octagon"></i></div>
    <div>
        <div class="motshow-alert-title">Overdue Violation</div>
        <div class="motshow-alert-text">This violation is past 72 hours and remains unsettled. Follow-up action may be required.</div>
    </div>
</div>
@endif

{{-- ── Violation Details ── --}}
<div class="motshow-section">Violation Details</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    @if($violation->ticket_number)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-ticket" style="font-size:.9rem;color:#dc2626;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Ticket Number</div>
            <div style="font-size:.88rem;font-weight:800;color:#0f172a;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:.04em;">{{ $violation->ticket_number }}</div>
        </div>
    </div>
    @endif

    @if($violation->date_of_violation)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-calendar-blank" style="font-size:.9rem;color:#2563eb;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Date of Violation</div>
            <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ $violation->date_of_violation->format('F d, Y') }}</div>
        </div>
    </div>
    @endif

    @if($violation->location)
    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
            <i class="ph-fill ph-map-pin" style="font-size:.9rem;color:#ea580c;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Location</div>
            <div style="font-size:.85rem;font-weight:600;color:#334155;line-height:1.4;">{{ $violation->location }}</div>
        </div>
    </div>
    @endif

    @if($violation->incident)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-flag" style="font-size:.9rem;color:#7c3aed;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Linked Incident</div>
            <a href="{{ route('officer.incidents.show', $violation->incident) }}" style="font-size:.88rem;font-weight:700;color:#1d4ed8;text-decoration:none;">{{ $violation->incident->incident_number }}</a>
        </div>
    </div>
    @endif

    @if($violation->recorder)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-user-circle-check" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Recorded By</div>
            <div style="font-size:.88rem;font-weight:600;color:#334155;">{{ $violation->recorder->name }}</div>
        </div>
    </div>
    @endif

    @if($violation->notes)
    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem 1rem;">
        <div style="width:32px;height:32px;border-radius:10px;background:#fafafa;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
            <i class="ph-fill ph-note" style="font-size:.9rem;color:#64748b;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Notes</div>
            <div style="font-size:.85rem;color:#334155;line-height:1.5;">{{ $violation->notes }}</div>
        </div>
    </div>
    @endif
</div>

{{-- ── Vehicle ── --}}
@if($hasVehicle || $violation->vehiclePhotos->isNotEmpty())
@php
    $vPhotos  = $violation->vehiclePhotos;
    $vCaption = $plate ? 'Vehicle — ' . $plate : 'Vehicle Photo';
    $vGallery = $vPhotos->map(fn($p) => uploaded_file_url($p->photo))->values()->toJson();
    $vExtra   = $vPhotos->count() - 1;
@endphp
<div class="motshow-section">Vehicle Involved</div>
<div style="display:flex;align-items:flex-start;gap:.85rem;background:#fff;border-radius:16px;border:1px solid rgba(15,23,42,.06);box-shadow:0 2px 10px rgba(15,23,42,.04);margin-bottom:.9rem;overflow:hidden;position:relative;">
    <div style="width:4px;background:linear-gradient(180deg,#dc2626,#b91c1c);align-self:stretch;flex-shrink:0;"></div>
    <div style="display:flex;align-items:flex-start;gap:.75rem;flex:1;min-width:0;padding:.85rem .85rem .85rem 0;">

        {{-- Icon / first photo --}}
        @if($vPhotos->isNotEmpty())
        <div style="position:relative;width:40px;height:40px;border-radius:12px;overflow:hidden;flex-shrink:0;box-shadow:0 4px 12px rgba(29,78,216,.25);cursor:zoom-in;">
            <img src="{{ uploaded_file_url($vPhotos->first()->photo) }}"
                 alt="Vehicle photo"
                 class="mob-photo-thumb"
                 data-full="{{ uploaded_file_url($vPhotos->first()->photo) }}"
                 data-gallery="{{ e($vGallery) }}"
                 data-caption="{{ $vCaption }}"
                 style="width:40px;height:40px;object-fit:cover;display:block;">
            @if($vExtra > 0)
            <div style="position:absolute;inset:0;background:rgba(0,0,0,.48);display:flex;align-items:center;justify-content:center;pointer-events:none;">
                <span style="color:#fff;font-size:.52rem;font-weight:800;line-height:1;">+{{ $vExtra }}</span>
            </div>
            @endif
        </div>
        @else
        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#dc2626,#b91c1c);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(220,38,38,.3);">
            <i class="ph-fill ph-car-profile" style="font-size:1.1rem;color:#fff;"></i>
        </div>
        @endif

        {{-- Vehicle info --}}
        <div style="flex:1;min-width:0;">
            @if($plate)
            <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;margin-bottom:.18rem;">
                <span style="font-size:.9rem;font-weight:800;color:#0f172a;">{{ $plate }}</span>
                @if($color)
                <span style="background:#eff6ff;color:#1e40af;border-radius:6px;font-size:.62rem;font-weight:800;padding:.08rem .38rem;">{{ $color }}</span>
                @endif
            </div>
            @endif
            @if($make || $model)
            <div style="font-size:.75rem;color:#64748b;margin-bottom:.14rem;">{{ trim($make . ' ' . $model) }}</div>
            @endif
            @if($orNo || $crNo)
            <div style="font-size:.68rem;color:#94a3b8;">
                @if($orNo)OR: {{ $orNo }}@endif
                @if($orNo && $crNo) &middot; @endif
                @if($crNo)CR: {{ $crNo }}@endif
            </div>
            @endif
            @if($chaNo)
            <div style="font-size:.68rem;color:#94a3b8;">Chassis: {{ $chaNo }}</div>
            @endif
            @if($owner)
            <div style="margin-top:.3rem;">
                <span style="display:inline-flex;align-items:center;gap:.22rem;background:#fef9c3;color:#92400e;border-radius:8px;font-size:.63rem;font-weight:800;padding:.1rem .4rem;">
                    <i class="ph ph-user-circle"></i> {{ $owner }}
                </span>
            </div>
            @endif
            @if(!$plate && !$make && !$model && !$owner)
            <div style="font-size:.75rem;color:#94a3b8;display:flex;align-items:center;gap:.35rem;">
                <i class="ph ph-car-profile"></i> No details on file
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Vehicle Photos ── --}}
@if(!empty($vPhotos) && $vPhotos->isNotEmpty())
@php
    $vPhotoGallery = $vPhotos->map(fn($p) => uploaded_file_url($p->photo))->values()->toJson();
    $vPhotoCaption = $plate ? 'Vehicle — ' . $plate : 'Vehicle Photo';
    $vPhotoUrls    = $vPhotos->map(fn($p) => uploaded_file_url($p->photo))->values()->all();
@endphp
<div class="motshow-section">Vehicle Photos</div>
<div class="motshow-card" style="margin-bottom:.9rem;overflow:hidden;">
    {{-- Carousel --}}
    <div id="vphCarousel" style="position:relative;user-select:none;touch-action:pan-y;">
        {{-- Slides --}}
        <div id="vphTrack" style="display:flex;transition:transform .32s cubic-bezier(.4,0,.2,1);will-change:transform;">
            @foreach($vPhotoUrls as $idx => $url)
            <div style="flex:0 0 100%;min-width:0;padding:.85rem .85rem 0;">
                <img src="{{ $url }}"
                     alt="Vehicle photo {{ $idx + 1 }}"
                     class="mob-photo-thumb"
                     data-full="{{ $url }}"
                     data-gallery="{{ e($vPhotoGallery) }}"
                     data-gallery-index="{{ $idx }}"
                     data-caption="{{ $vPhotoCaption }}"
                     style="width:100%;height:220px;object-fit:cover;border-radius:14px;display:block;box-shadow:0 4px 16px rgba(15,23,42,.1);cursor:zoom-in;">
            </div>
            @endforeach
        </div>
        {{-- Dot indicators + counter --}}
        <div style="display:flex;align-items:center;justify-content:center;gap:.45rem;padding:.65rem 1rem .85rem;flex-wrap:wrap;">
            @if(count($vPhotoUrls) > 1)
            @foreach($vPhotoUrls as $idx => $url)
            <span class="vph-dot" data-idx="{{ $idx }}"
                  style="width:{{ $idx === 0 ? '18px' : '7px' }};height:7px;border-radius:99px;background:{{ $idx === 0 ? '#1d4ed8' : '#cbd5e1' }};transition:all .25s;display:inline-block;cursor:pointer;"></span>
            @endforeach
            @else
            <span style="font-size:.7rem;color:#94a3b8;display:flex;align-items:center;gap:.3rem;">
                <i class="ph ph-magnifying-glass-plus"></i> Tap to enlarge
            </span>
            @endif
        </div>
    </div>
</div>
<script>
(function () {
    var track  = document.getElementById('vphTrack');
    var dots   = document.querySelectorAll('.vph-dot');
    var total  = {{ count($vPhotoUrls) }};
    var cur    = 0;
    var startX = 0, startY = 0, dragging = false, moved = false;

    function goTo(n) {
        cur = (n + total) % total;
        track.style.transform = 'translateX(-' + (cur * 100) + '%)';
        dots.forEach(function (d, i) {
            d.style.width      = i === cur ? '18px' : '7px';
            d.style.background = i === cur ? '#1d4ed8' : '#cbd5e1';
        });
    }

    dots.forEach(function (d) {
        d.addEventListener('click', function () { goTo(parseInt(d.dataset.idx)); });
    });

    /* Touch swipe */
    track.addEventListener('touchstart', function (e) {
        startX  = e.touches[0].clientX;
        startY  = e.touches[0].clientY;
        dragged = false;
        moved   = false;
    }, { passive: true });

    track.addEventListener('touchmove', function (e) {
        var dx = e.touches[0].clientX - startX;
        var dy = e.touches[0].clientY - startY;
        if (!moved && Math.abs(dx) > Math.abs(dy) + 5) { moved = true; }
    }, { passive: true });

    track.addEventListener('touchend', function (e) {
        if (!moved) return;
        var dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) {
            goTo(dx < 0 ? cur + 1 : cur - 1);
            /* prevent lightbox opening on swipe */
            e.stopPropagation();
        }
    });
})();
</script>
@endif

{{-- ── Citation Ticket Photo ── --}}
@if($violation->citation_ticket_photo)
<div class="motshow-section">Citation Ticket</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    <div style="padding:1rem;">
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

{{-- ── Settlement Details ── --}}
@if($status === 'settled' || $violation->or_number || $violation->cashier_name || $violation->receipt_photo)
<div class="motshow-section">Settlement</div>
<div class="motshow-card" style="margin-bottom:.9rem;">
    <div style="display:flex;align-items:center;gap:.65rem;padding:.75rem 1rem .5rem;">
        <div style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,#059669,#047857);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-bold ph-check" style="font-size:.85rem;color:#fff;"></i>
        </div>
        <div style="font-size:.62rem;font-weight:800;color:#059669;text-transform:uppercase;letter-spacing:.08em;">Settled</div>
    </div>

    @if($violation->settled_at)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-clock" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Settled At</div>
            <div style="font-size:.88rem;font-weight:700;color:#0f172a;">{{ $violation->settled_at->format('M d, Y') }}</div>
            <div style="font-size:.72rem;color:#64748b;">{{ $violation->settled_at->format('g:i A') }}</div>
        </div>
    </div>
    @endif

    @if($violation->or_number)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-receipt" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">OR Number</div>
            <div style="font-size:.88rem;font-weight:800;color:#0f172a;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;">{{ $violation->or_number }}</div>
        </div>
    </div>
    @endif

    @if($violation->cashier_name)
    @if($violation->receipt_photo)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
    @else
    <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1rem;">
    @endif
        <div style="width:32px;height:32px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-user" style="font-size:.9rem;color:#16a34a;"></i>
        </div>
        <div>
            <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Cashier</div>
            <div style="font-size:.88rem;font-weight:600;color:#334155;">{{ $violation->cashier_name }}</div>
        </div>
    </div>
    @endif

    @if($violation->receipt_photo)
    <div style="padding:1rem;">
        <div style="font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Receipt Photo</div>
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
@endif

@endsection
