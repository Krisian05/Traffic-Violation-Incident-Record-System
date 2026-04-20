@extends('layouts.app')
@section('title', $violator->full_name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('violators.index') }}" style="color:#78716c;">Motorists</a></li>
    <li class="breadcrumb-item active" aria-current="page" style="color:#44403c;">{{ $violator->full_name }}</li>
@endsection

@section('content')

<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- Profile Card --}}
        @php $vc = $violator->violations->count(); @endphp
        <div class="vlt-profile-card mb-4">
            <div class="vlt-profile-banner">
                @if(Auth::user()->isOperator())
                <div class="vlt-banner-actions">
                    <a href="{{ route('violators.edit', $violator) }}"
                       class="vlt-banner-btn"
                       data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Edit Profile">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    @php
                        $deleteConfirmMsg = $vc > 0
                            ? "This motorist has {$vc} violation record(s). Deleting will permanently remove the motorist AND all their violation records. This cannot be undone."
                            : "Delete this motorist? This cannot be undone.";
                    @endphp
                    <form method="POST" action="{{ route('violators.destroy', $violator) }}"
                          data-confirm="{{ $deleteConfirmMsg }}" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="vlt-banner-btn vlt-banner-btn--del"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Delete Motorist">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
                @endif
            </div>
            <div class="vlt-profile-body text-center">
                <div class="vlt-photo-wrap mx-auto">
                    @if($violator->photo)
                        @php $photoJson = json_encode([['src' => uploaded_file_url($violator->photo), 'caption' => $violator->full_name . ' — Profile Photo']]); @endphp
                        <img src="{{ uploaded_file_url($violator->photo) }}"
                             data-photos="{{ $photoJson }}"
                             onclick="openGallery(this, 0)"
                             style="width:100%;height:100%;object-fit:cover;cursor:zoom-in;"
                             title="Click to enlarge">
                    @else
                        <i class="bi bi-person-fill" style="font-size:3rem;color:#93c5fd;"></i>
                    @endif
                </div>
                <h5 class="fw-700 mb-1 mt-3" style="font-size:1.05rem;color:#1c1917;">{{ $violator->full_name }}</h5>
                @if($violator->license_number)
                    <div style="font-size:.78rem;color:#a8a29e;font-family:ui-monospace,monospace;margin-bottom:.6rem;">
                        {{ $violator->license_number }}
                    </div>
                @endif
                @if($vc >= 3)
                    <span class="vlt-status-badge" style="background:#fef2f2;color:#b91c1c;border-color:#fca5a5;box-shadow:0 2px 8px rgba(185,28,28,.15);">
                        <i class="bi bi-fire me-1"></i>Recidivist
                    </span>
                @elseif($vc == 2)
                    <span class="vlt-status-badge" style="background:#fffbeb;color:#92400e;border-color:#fcd34d;box-shadow:0 2px 8px rgba(146,64,14,.12);">
                        <i class="bi bi-shield-exclamation me-1"></i>Repeat Offender
                    </span>
                @elseif($vc == 1)
                    <span class="vlt-status-badge" style="background:#f0f9ff;color:#0369a1;border-color:#7dd3fc;box-shadow:0 2px 8px rgba(3,105,161,.12);">
                        <i class="bi bi-record-circle-fill me-1"></i>1 Violation
                    </span>
                @else
                    <span class="vlt-status-badge" style="background:#f0fdf4;color:#15803d;border-color:#86efac;box-shadow:0 2px 8px rgba(21,128,61,.12);">
                        <i class="bi bi-shield-fill-check me-1"></i>No Violations
                    </span>
                @endif

                <div class="vlt-stat-row mt-3">
                    <a href="{{ route('violations.index', ['search' => $violator->full_name]) }}"
                       class="vlt-stat-item text-decoration-none" title="View all violations">
                        <div class="vlt-stat-num" style="color:#dc2626;">{{ $vc }}</div>
                        <div class="vlt-stat-lbl">Violations</div>
                    </a>
                    <div class="vlt-stat-divider"></div>
                    <div class="vlt-stat-item">
                        <div class="vlt-stat-num" style="color:#1d4ed8;">{{ $violator->vehicles->count() }}</div>
                        <div class="vlt-stat-lbl">Vehicles</div>
                    </div>
                    <div class="vlt-stat-divider"></div>
                    <a href="{{ route('violations.index', ['search' => $violator->full_name, 'status' => 'settled']) }}"
                       class="vlt-stat-item text-decoration-none" title="View settled violations">
                        <div class="vlt-stat-num" style="color:#15803d;">
                            {{ $violator->violations->where('status','settled')->count() }}
                        </div>
                        <div class="vlt-stat-lbl">Settled</div>
                    </a>
                </div>

                {{-- Print Record button --}}
                <div style="padding: .9rem 1.25rem 1rem; border-top: 1px solid #f0ebe3; margin-top: .85rem;">
                    <a href="{{ route('violators.print', $violator) }}"
                       onclick="window.open(this.href,'print_popup','width=1000,height=750,scrollbars=yes,resizable=yes'); return false;"
                       class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2 fw-600">
                        <i class="bi bi-printer-fill" style="font-size:.85rem;"></i>
                        Print Full Record
                    </a>
                </div>
            </div>
        </div>

        {{-- Personal Info --}}
        <div class="vlt-info-card mb-4">
            <div class="vlt-card-header">
                <span class="vlt-section-icon" style="background:#dbeafe;">
                    <i class="bi bi-person-lines-fill" style="color:#1d4ed8;"></i>
                </span>
                <div>
                    <div class="vlt-section-title">Personal Information</div>
                    <div class="vlt-section-sub">Identity &amp; contact details</div>
                </div>
            </div>
            <div class="vlt-info-list">
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Date of Birth</span>
                    <span class="vlt-info-value">{{ $violator->date_of_birth?->format('M d, Y') ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Place of Birth</span>
                    <span class="vlt-info-value">{{ $violator->place_of_birth ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Sex</span>
                    <span class="vlt-info-value">{{ $violator->gender ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Civil Status</span>
                    <span class="vlt-info-value">{{ $violator->civil_status ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Blood Type</span>
                    <span class="vlt-info-value">{{ $violator->blood_type ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Height</span>
                    <span class="vlt-info-value">{{ $violator->height ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Weight</span>
                    <span class="vlt-info-value">{{ $violator->weight ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Valid ID</span>
                    <span class="vlt-info-value">{{ $violator->valid_id ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Contact</span>
                    <span class="vlt-info-value">{{ $violator->contact_number ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Email</span>
                    <span class="vlt-info-value" style="word-break:break-all;">{{ $violator->email ?? '—' }}</span>
                </div>
                @if($violator->temporary_address)
                <div class="vlt-info-row vlt-info-row--block">
                    <span class="vlt-info-label">Temporary Address</span>
                    <div class="vlt-info-value mt-1">{{ $violator->temporary_address }}</div>
                </div>
                @endif
                @if($violator->permanent_address)
                <div class="vlt-info-row vlt-info-row--block" style="border-bottom:none;">
                    <span class="vlt-info-label">Permanent Address</span>
                    <div class="vlt-info-value mt-1">{{ $violator->permanent_address }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- License Information --}}
        <div class="vlt-info-card mb-4">
            <div class="vlt-card-header">
                <span class="vlt-section-icon" style="background:#fef9c3;">
                    <i class="bi bi-credit-card-2-front-fill" style="color:#ca8a04;"></i>
                </span>
                <div>
                    <div class="vlt-section-title">License Information</div>
                    <div class="vlt-section-sub">Driver's license details</div>
                </div>
            </div>
            <div class="vlt-info-list">
                <div class="vlt-info-row">
                    <span class="vlt-info-label">License No.</span>
                    <span class="vlt-info-value">
                        @if($violator->license_number)
                            <span style="font-family:ui-monospace,monospace;font-size:.8rem;background:#fef9c3;padding:.15rem .45rem;border-radius:4px;border:1px solid #fde68a;">{{ $violator->license_number }}</span>
                        @else —
                        @endif
                    </span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">License Type</span>
                    <span class="vlt-info-value">{{ $violator->license_type ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Restriction Code</span>
                    <span class="vlt-info-value">{{ $violator->license_restriction ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Date Issued</span>
                    <span class="vlt-info-value">{{ $violator->license_issued_date?->format('M d, Y') ?? '—' }}</span>
                </div>
                <div class="vlt-info-row">
                    <span class="vlt-info-label">Expiry Date</span>
                    <span class="vlt-info-value">
                        @if($violator->license_expiry_date)
                            @php $expired = $violator->license_expiry_date->isPast(); @endphp
                            <span class="{{ $expired ? 'expiry-expired' : 'expiry-valid' }}">
                                {{ $violator->license_expiry_date->format('M d, Y') }}
                                @if($expired) <i class="bi bi-exclamation-circle ms-1"></i> @endif
                            </span>
                        @else —
                        @endif
                    </span>
                </div>
                <div class="vlt-info-row" style="border-bottom:none;">
                    <span class="vlt-info-label">Conditions</span>
                    <span class="vlt-info-value">{{ $violator->license_conditions ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Violations by Type --}}
        @if($violationsByType->isNotEmpty())
        <div class="vlt-info-card mb-4">
            <div class="vlt-card-header">
                <span class="vlt-section-icon" style="background:#fff1f2;">
                    <i class="bi bi-bar-chart-fill" style="color:#dc2626;"></i>
                </span>
                <div>
                    <div class="vlt-section-title">Violations by Type</div>
                    <div class="vlt-section-sub">Breakdown of offense categories</div>
                </div>
            </div>
            <div class="vlt-info-list">
                @foreach($violationsByType as $row)
                <div class="vlt-info-row">
                    <a href="{{ route('violations.index', ['search' => $violator->full_name, 'type' => $row['type']->id]) }}"
                       class="vlt-info-label text-decoration-none" style="text-transform:none;letter-spacing:0;font-size:.82rem;color:#57534e;" title="Filter violations by this type">
                        {{ $row['type']->name }}
                    </a>
                    <a href="{{ route('violations.index', ['search' => $violator->full_name, 'type' => $row['type']->id]) }}"
                       class="vlt-vio-badge {{ $row['count'] > 1 ? 'vlt-recidivist' : 'vlt-once' }} text-decoration-none"
                       style="font-size:.68rem;padding:.18rem .55rem;"
                       title="View {{ $row['count'] }} violation{{ $row['count'] !== 1 ? 's' : '' }}">
                        {{ $row['count'] }}
                    </a>
                </div>
                @endforeach
                <div class="vlt-info-row" style="border-bottom:none;background:#fdf8f0;">
                    <span class="vlt-info-label" style="text-transform:none;letter-spacing:0;font-size:.82rem;color:#1c1917;font-weight:700;">Total</span>
                    <span class="vlt-vio-badge vlt-recidivist" style="font-size:.68rem;padding:.18rem .55rem;">
                        {{ $vc }}
                    </span>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- Vehicles --}}
        <div class="vlt-info-card mb-4">
            <div class="vlt-card-header">
                <span class="vlt-section-icon" style="background:#eff6ff;">
                    <i class="bi bi-car-front-fill" style="color:#1d4ed8;"></i>
                </span>
                <div>
                    <div class="vlt-section-title">Vehicles</div>
                    <div class="vlt-section-sub">Registered vehicles of this motorist</div>
                </div>
                @if(Auth::user()->isOperator())
                    <a href="{{ route('vehicles.create', $violator) }}" class="vlt-add-inline-btn ms-auto">
                        <i class="bi bi-plus-lg"></i> Add Vehicle
                    </a>
                @endif
            </div>
            @if($violator->vehicles->isEmpty())
                <div class="vlt-empty-inline">
                    <i class="bi bi-car-front" style="font-size:1.6rem;color:#c4b8a8;"></i>
                    <span>No vehicles registered.</span>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="vehicles-table">
                        <thead>
                            <tr>
                                <th style="padding-left:1.25rem;">Photos</th>
                                <th>Plate</th>
                                <th>Type</th>
                                <th>Make / Model</th>
                                <th>Color / Year</th>
                                @if(Auth::user()->isOperator())<th class="text-center">Actions</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($violator->vehicles as $v)
                            <tr class="vlt-tbl-row">
                                <td style="padding-left:1.25rem;min-width:80px;">
                                    @php
                                        if ($v->photos->isNotEmpty()) {
                                            $galleryPhotos = $v->photos->map(fn($p) => [
                                                'src'     => uploaded_file_url($p->photo),
                                                'caption' => $v->plate_number,
                                            ])->values();
                                        } elseif ($v->allViolationPhotos->isNotEmpty()) {
                                            $galleryPhotos = $v->allViolationPhotos->map(fn($p) => [
                                                'src'     => uploaded_file_url($p->photo),
                                                'caption' => $v->plate_number,
                                            ])->values();
                                        } else {
                                            $galleryPhotos = collect([]);
                                        }
                                        $thumbSrc  = $galleryPhotos->first()['src'] ?? null;
                                        $allPhotos = $galleryPhotos->toJson();
                                        $extra     = max(0, $galleryPhotos->count() - 1);
                                    @endphp
                                    @if($thumbSrc)
                                        <div class="vlt-gallery-thumb"
                                             data-photos="{{ $allPhotos }}"
                                             onclick="openGallery(this, 0)">
                                            <img src="{{ $thumbSrc }}"
                                                 style="width:64px;height:48px;object-fit:cover;border-radius:8px;border:2px solid #bfdbfe;"
                                                 alt="vehicle photo">
                                            @if($extra > 0)
                                                <div class="vlt-gallery-more">+{{ $extra }}</div>
                                            @endif
                                            <div class="vlt-gallery-overlay"><i class="bi bi-images"></i></div>
                                        </div>
                                    @else
                                        <div class="d-inline-flex align-items-center justify-content-center"
                                            style="width:64px;height:48px;background:#f5f0e8;border-radius:8px;border:1.5px solid #ddd0be;">
                                            <i class="bi bi-car-front" style="color:#c4b8a8;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="vlt-plate-chip">{{ $v->plate_number }}</span>
                                </td>
                                <td>
                                    <span class="vlt-type-chip">{{ $v->vehicle_type }}</span>
                                </td>
                                <td style="font-size:.85rem;color:#57534e;">
                                    {{ implode(' ', array_filter([$v->make, $v->model])) ?: '—' }}
                                </td>
                                <td style="font-size:.85rem;color:#57534e;">
                                    {{ implode(' / ', array_filter([$v->color, $v->year])) ?: '—' }}
                                </td>
                                @if(Auth::user()->isOperator())
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('vehicles.edit', $v) }}" class="vlt-act-btn vlt-act-edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form method="POST" action="{{ route('vehicles.destroy', $v) }}" class="d-inline"
                                              data-confirm="Remove this vehicle from the system?">
                                            @csrf @method('DELETE')
                                            <button class="vlt-act-btn vlt-act-del">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Violation History --}}
        <div class="vlt-info-card mb-4">
            <div class="vlt-card-header">
                <span class="vlt-section-icon" style="background:#fff1f2;">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;"></i>
                </span>
                <div>
                    <div class="vlt-section-title">
                        Violation History
                        <span class="vlt-count-badge ms-1">{{ $vc }}</span>
                    </div>
                    <div class="vlt-section-sub">Complete record of offenses</div>
                </div>
                @if(Auth::user()->isOperator())
                    <a href="{{ route('violations.create', $violator) }}" class="vlt-add-inline-btn vlt-add-danger ms-auto">
                        <i class="bi bi-plus-lg"></i> Add Violation
                    </a>
                @endif
            </div>
            @if($violator->violations->isEmpty())
                <div class="vlt-empty-inline">
                    <i class="bi bi-clipboard-check" style="font-size:1.6rem;color:#c4b8a8;"></i>
                    <span>No violations recorded.</span>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="history-table">
                        <thead>
                            <tr>
                                <th style="padding-left:1.25rem;">
                                    <span class="vlt-th-inner"><i class="bi bi-calendar-event-fill me-1"></i>Date</span>
                                </th>
                                <th>
                                    <span class="vlt-th-inner"><i class="bi bi-tag-fill me-1"></i>Violation</span>
                                </th>
                                <th>
                                    <span class="vlt-th-inner"><i class="bi bi-car-front-fill me-1"></i>Vehicle</span>
                                </th>
                                <th class="text-center">
                                    <span class="vlt-th-inner"><i class="bi bi-activity me-1"></i>Status</span>
                                </th>
                                <th class="text-center">
                                    <span class="vlt-th-inner"><i class="bi bi-lightning-charge-fill me-1"></i>Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($violator->violations->sortByDesc('date_of_violation') as $viol)
                            <tr class="vlt-tbl-row vlt-history-row" data-href="{{ route('violations.show', $viol) }}">
                                <td style="padding-left:1.25rem;">
                                    <span style="font-size:.84rem;color:#57534e;">
                                        <i class="bi bi-calendar-check me-1" style="color:#a8a29e;font-size:.72rem;"></i>
                                        {{ $viol->date_of_violation->format('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="vlt-vtype-pill">
                                        <i class="bi bi-exclamation-octagon-fill me-1" style="font-size:.6rem;"></i>
                                        {{ $viol->violationType?->name ?? '—' }}
                                    </span>
                                </td>
                                <td style="font-size:.84rem;color:#57534e;">
                                    @if($viol->vehicle?->plate_number)
                                        <span class="vlt-plate-chip">{{ $viol->vehicle->plate_number }}</span>
                                    @else
                                        <span style="color:#c4b8a8;font-weight:600;">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $isOverdue = $viol->status === 'pending' && $viol->created_at <= now()->subHours(72);
                                        $displayStatus = $isOverdue ? 'overdue' : $viol->status;
                                        $sc = match($displayStatus) {
                                            'overdue'   => ['cls'=>'vlt-status-overdue',  'icon'=>'bi-exclamation-triangle-fill'],
                                            'pending'   => ['cls'=>'vlt-status-pending',  'icon'=>'bi-hourglass-split'],
                                            'settled'   => ['cls'=>'vlt-status-settled',  'icon'=>'bi-check2-circle'],
                                            'contested' => ['cls'=>'vlt-status-contested','icon'=>'bi-shield-slash'],
                                            default     => ['cls'=>'vlt-status-default',  'icon'=>'bi-circle'],
                                        };
                                    @endphp
                                    <span class="vlt-status-badge {{ $sc['cls'] }}"
                                        @if($displayStatus === 'overdue') data-bs-toggle="tooltip" data-bs-title="Pending payment for more than 72 hours" @endif>
                                        <i class="bi {{ $sc['icon'] }}"></i> {{ ucfirst($displayStatus) }}
                                    </span>
                                </td>
                                <td class="text-center vlt-history-act">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('violations.show', $viol) }}" class="vlt-act-btn vlt-act-view">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        @if(Auth::user()->isOperator())
                                        <a href="{{ route('violations.edit', $viol) }}" class="vlt-act-btn vlt-act-edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        @if($viol->status === 'pending')
                                        <button type="button" class="vlt-act-btn vlt-act-settle"
                                            data-id="{{ $viol->id }}"
                                            data-type="{{ $viol->violationType?->name ?? '' }}"
                                            data-date="{{ $viol->date_of_violation->format('M d, Y') }}"
                                            onclick="openSettleModal(this)">
                                            <i class="bi bi-receipt"></i> Settle
                                        </button>
                                        @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Incident Involvement --}}
        @php $incidents = $violator->incidentMotorists->sortByDesc(fn($m) => $m->incident?->date_of_incident); @endphp
        <div class="vlt-info-card mb-4">
            <div class="vlt-card-header">
                <span class="vlt-section-icon" style="background:#fef3c7;">
                    <i class="bi bi-flag-fill" style="color:#d97706;"></i>
                </span>
                <div>
                    <div class="vlt-section-title">
                        Incident Involvement
                        @if($incidents->isNotEmpty())
                            <span class="vlt-count-badge ms-1" style="background:#d97706;">{{ $incidents->count() }}</span>
                        @endif
                    </div>
                    <div class="vlt-section-sub">Traffic incidents this motorist was involved in</div>
                </div>
            </div>
            @if($incidents->isEmpty())
                <div class="vlt-empty-inline">
                    <i class="bi bi-flag" style="font-size:1.6rem;color:#c4b8a8;"></i>
                    <span>No incident involvement recorded.</span>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="padding-left:1.25rem;"><span class="vlt-th-inner"><i class="bi bi-hash me-1"></i>Incident</span></th>
                                <th><span class="vlt-th-inner"><i class="bi bi-calendar-event-fill me-1"></i>Date</span></th>
                                <th><span class="vlt-th-inner"><i class="bi bi-geo-alt-fill me-1"></i>Location</span></th>
                                <th><span class="vlt-th-inner"><i class="bi bi-tag-fill me-1"></i>Charge</span></th>
                                <th class="text-center"><span class="vlt-th-inner"><i class="bi bi-activity me-1"></i>Status</span></th>
                                <th class="text-center"><span class="vlt-th-inner">View</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($incidents as $im)
                            @if($im->incident)
                            <tr class="vlt-tbl-row vlt-inc-row" data-href="{{ route('incidents.show', $im->incident) }}">
                                <td style="padding-left:1.25rem;">
                                    <span class="font-monospace fw-600" style="font-size:.82rem;color:#1d4ed8;">{{ $im->incident->incident_number }}</span>
                                </td>
                                <td style="font-size:.84rem;color:#57534e;">
                                    <i class="bi bi-calendar-check me-1" style="color:#a8a29e;font-size:.72rem;"></i>
                                    {{ $im->incident->date_of_incident->format('M d, Y') }}
                                </td>
                                <td style="font-size:.82rem;color:#57534e;max-width:160px;">{{ $im->incident->location }}</td>
                                <td>
                                    @if($im->chargeType)
                                        <span class="badge rounded-pill" style="background:#f3e8ff;color:#6d28d9;font-size:.74rem;">{{ $im->chargeType->name }}</span>
                                    @else
                                        <span style="color:#c4b8a8;">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $iscLabel = match($im->incident->status) {
                                            'under_investigation' => 'Under Investigation',
                                            'cleared'             => 'Cleared',
                                            'solved'              => 'Solved',
                                            default               => ucfirst($im->incident->status),
                                        };
                                        $iscClass = in_array($im->incident->status, ['under_investigation','cleared','solved'])
                                            ? 'inc-status-' . $im->incident->status
                                            : 'inc-status-default';
                                    @endphp
                                    <span class="inc-status-badge {{ $iscClass }}">{{ $iscLabel }}</span>
                                </td>
                                <td class="text-center vlt-inc-act">
                                    <a href="{{ route('incidents.show', $im->incident) }}" class="vlt-act-btn vlt-act-view">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Photo Gallery Modal --}}
<div class="modal fade" id="photoLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="background:#111;">
            <div class="modal-header border-0 py-2 px-3" style="background:#1a1a1a;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-images text-white" style="font-size:.85rem;"></i>
                    <span class="text-white" style="font-size:.82rem;" id="lightboxCaption"></span>
                    <span class="text-white ms-1" style="font-size:.75rem;opacity:.5;" id="lightboxCounter"></span>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center position-relative" style="background:#111;">
                <button id="galleryPrev" class="vlt-gallery-nav vlt-gallery-nav--prev" onclick="galleryNav(-1)">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <img id="lightboxImg" src="" alt=""
                    style="max-width:100%;max-height:78vh;object-fit:contain;display:block;margin:0 auto;">
                <button id="galleryNext" class="vlt-gallery-nav vlt-gallery-nav--next" onclick="galleryNav(1)">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div id="galleryDots" class="d-flex justify-content-center gap-2 py-2" style="background:#1a1a1a;min-height:28px;"></div>
        </div>
    </div>
</div>

<style>
/* ─── BANNER ACTION BUTTONS ─── */
.vlt-banner-actions {
    position: absolute;
    top: 10px; right: 10px;
    display: flex;
    gap: .4rem;
}
.vlt-banner-btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.18);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,.4);
    font-size: .8rem;
    text-decoration: none;
    cursor: pointer;
    transition: all .18s;
    backdrop-filter: blur(4px);
}
.vlt-banner-btn:hover {
    background: rgba(255,255,255,.95);
    color: #b45309;
    border-color: #fff;
    transform: scale(1.1);
    box-shadow: 0 3px 10px rgba(0,0,0,.2);
}
.vlt-banner-btn--del:hover {
    color: #dc2626;
}

/* ─── PROFILE CARD ─── */
.vlt-profile-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #f0ebe3;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden;
}
.vlt-profile-banner {
    height: 72px;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    position: relative;
}
.vlt-profile-body { padding: 0 1.5rem 1.5rem; position: relative; z-index: 1; }
.vlt-photo-wrap {
    width: 90px; height: 108px;
    border-radius: 10px;
    border: 3px solid #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    margin-top: -45px;
    background: #eff6ff;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
}
.vlt-status-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .28rem .75rem;
    border-radius: 20px;
    border: 1.5px solid;
    font-size: .72rem; font-weight: 700;
    letter-spacing: .03em;
}
.vlt-status-overdue   { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
.vlt-status-pending   { background:#fff7ed;color:#c2410c;border-color:#fed7aa; }
.vlt-status-settled   { background:#f0fdf4;color:#15803d;border-color:#86efac; }
.vlt-status-contested { background:#f8fafc;color:#475569;border-color:#cbd5e1; }
.vlt-status-default   { background:#f8fafc;color:#475569;border-color:#e2e8f0; }
.vlt-stat-row {
    display: flex;
    justify-content: center;
    gap: 0;
    border-top: 1px solid #f0ebe3;
    padding-top: .85rem;
}
.vlt-stat-item { flex: 1; text-align: center; }
.vlt-stat-num { font-size: 1.3rem; font-weight: 700; line-height: 1; }
.vlt-stat-lbl { font-size: .7rem; color: #a8a29e; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-top: .2rem; }
.vlt-stat-divider { width: 1px; background: #f0ebe3; }

/* ─── INFO CARD ─── */
.vlt-info-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #f0ebe3;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden;
}
.vlt-card-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .9rem 1.25rem;
    background: linear-gradient(135deg, #fdf8f0 0%, #fff 100%);
    border-bottom: 1px solid #f0ebe3;
}
.vlt-section-icon {
    width: 36px; height: 36px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
}
.vlt-section-title { font-size: .88rem; font-weight: 700; color: #1c1917; }
.vlt-section-sub { font-size: .72rem; color: #a8a29e; margin-top: .05rem; }
.vlt-count-badge {
    display: inline-flex; align-items: center; justify-content: center;
    background: #dc2626; color: #fff;
    font-size: .65rem; font-weight: 700;
    padding: .1rem .4rem;
    border-radius: 10px;
    vertical-align: middle;
}

/* ─── INFO LIST ─── */
.vlt-info-row {
    display: grid;
    grid-template-columns: 42% 1fr;
    align-items: center;
    padding: .6rem 1.25rem;
    border-bottom: 1px solid #f5f0ea;
    gap: .5rem;
}
.vlt-info-row--block {
    grid-template-columns: 1fr;
}
.vlt-info-list .vlt-info-row:last-child { border-bottom: none; }
.vlt-info-label {
    font-size: .7rem;
    font-weight: 700;
    color: #a8a29e;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.vlt-info-value {
    font-size: .84rem;
    color: #1c1917;
    font-weight: 500;
    text-align: right;
}
/* Right-align any non-label child in the grid (e.g. badges, chips) */
.vlt-info-row > *:last-child:not(.vlt-info-label) {
    justify-self: end;
}
.vlt-info-row--block .vlt-info-value {
    text-align: left;
    color: #57534e;
}
.vlt-info-row--block > *:last-child {
    justify-self: start;
}

/* ─── INLINE ADD BUTTON ─── */
.vlt-add-inline-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .28rem .8rem;
    border-radius: 8px;
    font-size: .76rem; font-weight: 600;
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    color: #fff;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(29,78,216,.25);
    transition: all .15s;
}
.vlt-add-inline-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(29,78,216,.35); }
.vlt-add-danger { background: linear-gradient(135deg, #dc2626, #b91c1c); box-shadow: 0 2px 8px rgba(220,38,38,.25); }
.vlt-add-danger:hover { box-shadow: 0 4px 12px rgba(220,38,38,.35); }

/* ─── EMPTY INLINE ─── */
.vlt-empty-inline {
    display: flex; align-items: center; gap: .6rem;
    padding: 1.25rem;
    color: #a8a29e;
    font-size: .85rem;
}

/* ─── TABLE STYLES ─── */
#vehicles-table thead tr, #history-table thead tr {
    background: linear-gradient(135deg, #fdf8f0 0%, #faf5ee 100%);
}
#vehicles-table thead th, #history-table thead th {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #78716c;
    border-bottom: 2px solid #ece5da;
    padding-top: .85rem;
    padding-bottom: .85rem;
}
.vlt-th-inner { display: inline-flex; align-items: center; }
.vlt-tbl-row { transition: background .15s; }
.vlt-tbl-row:hover { background: #fffbf8 !important; }
.vlt-history-row[data-href], .vlt-inc-row[data-href] { cursor: pointer; }
.vlt-history-row[data-href]:hover td:not(.vlt-history-act):not(.vlt-inc-act) { position: relative; }
.vlt-history-row[data-href]:hover td:first-child::before,
.vlt-inc-row[data-href]:hover td:first-child::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: #dc2626;
    border-radius: 0 2px 2px 0;
}
a.vlt-stat-item { color: inherit; }
a.vlt-stat-item:hover .vlt-stat-num { text-decoration: underline; text-underline-offset: 2px; }
.vlt-tbl-row td {
    padding-top: .85rem;
    padding-bottom: .85rem;
    border-color: #f5f0ea;
    vertical-align: middle;
}

/* ─── CHIPS & PILLS ─── */
.vlt-plate-chip {
    display: inline-flex; align-items: center;
    background: #f5f0e8; color: #57534e;
    font-size: .74rem; font-weight: 700;
    padding: .22rem .6rem;
    border-radius: 6px;
    border: 1px solid #ddd0be;
    font-family: ui-monospace, 'Cascadia Code', monospace;
    letter-spacing: .04em;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.vlt-type-chip {
    display: inline-flex; align-items: center;
    background: #eff6ff; color: #1d4ed8;
    font-size: .72rem; font-weight: 600;
    padding: .22rem .55rem;
    border-radius: 6px;
    border: 1px solid #bfdbfe;
}
.vlt-vtype-pill {
    display: inline-flex; align-items: center;
    background: #fff1f2; color: #be123c;
    font-size: .71rem; font-weight: 700;
    padding: .25rem .65rem;
    border-radius: 20px;
    border: 1px solid #fecdd3;
    box-shadow: 0 1px 4px rgba(190,18,60,.1);
}
.vlt-vio-badge {
    display: inline-flex; align-items: center;
    font-size: .72rem; font-weight: 700;
    padding: .25rem .6rem;
    border-radius: 20px;
    border: 1.5px solid;
}
.vlt-recidivist { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
.vlt-once       { background:#f0f9ff;color:#0369a1;border-color:#7dd3fc; }

/* ─── ACTION BUTTONS (table) ─── */
.vlt-act-btn {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .28rem .6rem;
    border-radius: 7px;
    font-size: .76rem; font-weight: 700;
    text-decoration: none;
    border: 1.5px solid transparent;
    cursor: pointer;
    background: none;
    transition: all .18s;
}
.vlt-act-view { background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe; }
.vlt-act-view:hover { background:#1d4ed8;color:#fff;border-color:#1d4ed8;transform:translateY(-2px);box-shadow:0 4px 12px rgba(29,78,216,.3); }
.vlt-act-edit { background:#fdf8f0;color:#b45309;border-color:#fde68a; }
.vlt-act-edit:hover { background:#d97706;color:#fff;border-color:#d97706;transform:translateY(-2px);box-shadow:0 4px 12px rgba(217,119,6,.3); }
.vlt-act-del { background:#fff1f2;color:#b91c1c;border-color:#fca5a5; }
.vlt-act-del:hover { background:#dc2626;color:#fff;border-color:#dc2626;transform:translateY(-2px);box-shadow:0 4px 12px rgba(220,38,38,.3); }
.vlt-act-settle { background:#f0fdf4;color:#15803d;border-color:#86efac; }
.vlt-act-settle:hover { background:#15803d;color:#fff;border-color:#15803d;transform:translateY(-2px);box-shadow:0 4px 12px rgba(21,128,61,.3); }

.fw-700 { font-weight: 700; }
.expiry-expired { color: #dc2626; font-weight: 700; }
.expiry-valid   { color: #15803d; font-weight: 600; }

/* ─── PRINT BUTTON ─── */

/* ─── GALLERY THUMBNAIL ─── */
.vlt-gallery-thumb {
    position: relative;
    display: inline-block;
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
}
.vlt-gallery-thumb img {
    display: block;
    transition: transform .2s, opacity .2s;
}
.vlt-gallery-thumb:hover img {
    transform: scale(1.06);
    opacity: .88;
}
.vlt-gallery-more {
    position: absolute;
    bottom: 5px; right: 5px;
    background: rgba(0,0,0,.65);
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    padding: .1rem .38rem;
    border-radius: 5px;
    pointer-events: none;
    backdrop-filter: blur(2px);
}
.vlt-gallery-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    opacity: 0;
    transition: opacity .18s, background .18s;
    pointer-events: none;
}
.vlt-gallery-thumb:hover .vlt-gallery-overlay {
    opacity: 1;
    background: rgba(0,0,0,.28);
}

/* ─── GALLERY MODAL NAV ─── */
.vlt-gallery-nav {
    position: absolute;
    top: 50%; transform: translateY(-50%);
    width: 40px; height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,.15);
    color: #fff;
    font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background .18s;
    z-index: 10;
    backdrop-filter: blur(4px);
}
.vlt-gallery-nav:hover { background: rgba(255,255,255,.3); }
.vlt-gallery-nav--prev { left: 12px; }
.vlt-gallery-nav--next { right: 12px; }
/* ─── INCIDENT STATUS BADGES ─── */
.inc-status-badge {
    font-size:.72rem; font-weight:700; padding:.25rem .65rem;
    border-radius:20px; white-space:nowrap; display:inline-block;
}
.inc-status-under_investigation { background:#eff6ff; color:#1d4ed8; }
.inc-status-cleared             { background:#fffbeb; color:#92400e; }
.inc-status-solved              { background:#f0fdf4; color:#15803d; }
.inc-status-default             { background:#f8fafc; color:#475569; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
});

// ── Gallery state ──
let _galleryPhotos = [];
let _galleryIndex  = 0;

function openGallery(el, startIndex) {
    _galleryPhotos = JSON.parse(el.dataset.photos);
    _galleryIndex  = startIndex;
    renderGallery();
    new bootstrap.Modal(document.getElementById('photoLightbox')).show();
}

function galleryNav(dir) {
    _galleryIndex = (_galleryIndex + dir + _galleryPhotos.length) % _galleryPhotos.length;
    renderGallery();
}

function renderGallery() {
    const photo   = _galleryPhotos[_galleryIndex];
    const total   = _galleryPhotos.length;
    const hasMany = total > 1;

    document.getElementById('lightboxImg').src       = photo.src;
    document.getElementById('lightboxCaption').textContent = photo.caption;
    document.getElementById('lightboxCounter').textContent = hasMany ? `${_galleryIndex + 1} / ${total}` : '';
    document.getElementById('galleryPrev').style.display = hasMany ? 'flex' : 'none';
    document.getElementById('galleryNext').style.display = hasMany ? 'flex' : 'none';

    // Dots
    const dotsEl = document.getElementById('galleryDots');
    dotsEl.innerHTML = '';
    if (hasMany) {
        _galleryPhotos.forEach((_, i) => {
            const dot = document.createElement('span');
            dot.style.cssText = `width:7px;height:7px;border-radius:50%;background:${i===_galleryIndex?'#fff':'rgba(255,255,255,.3)'};cursor:pointer;transition:background .15s;display:inline-block;`;
            dot.onclick = () => { _galleryIndex = i; renderGallery(); };
            dotsEl.appendChild(dot);
        });
    }
}

// Keyboard navigation
document.addEventListener('keydown', function (e) {
    if (!document.getElementById('photoLightbox').classList.contains('show')) return;
    if (e.key === 'ArrowLeft')  galleryNav(-1);
    if (e.key === 'ArrowRight') galleryNav(1);
});

// ── Settle modal ──
function openSettleModal(btn) {
    var id   = btn.dataset.id;
    var type = btn.dataset.type;
    var date = btn.dataset.date;

    document.getElementById('settleForm').action = '/violations/' + id + '/settle';
    document.getElementById('settleViolationType').textContent = type;
    document.getElementById('settleViolationDate').textContent = date;

    // Reset form fields
    document.getElementById('settleForm').reset();
    document.getElementById('receiptPreview').src = '';
    document.getElementById('receiptPreviewWrap').classList.add('d-none');

    new bootstrap.Modal(document.getElementById('settleModal')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    var receiptInput = document.getElementById('receipt_photo');
    if (receiptInput) {
        receiptInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('receiptPreview').src = e.target.result;
                document.getElementById('receiptPreviewWrap').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
});

// ── Clickable violation history rows ──
document.querySelectorAll('.vlt-history-row[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.vlt-history-act')) return;
        if (e.target.closest('a'))                return;
        if (e.target.closest('button'))           return;
        if (e.target.closest('form'))             return;
        window.location.href = row.dataset.href;
    });
});

// ── Clickable incident involvement rows ──
document.querySelectorAll('.vlt-inc-row[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
        if (e.target.closest('.vlt-inc-act')) return;
        if (e.target.closest('a'))            return;
        if (e.target.closest('button'))       return;
        if (e.target.closest('form'))         return;
        window.location.href = row.dataset.href;
    });
});
</script>

{{-- ── Settle Violation Modal ── --}}
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

            {{-- Violation info strip --}}
            <div style="background:#f0fdf4;padding:.65rem 1.25rem;border-bottom:1px solid #bbf7d0;font-size:.8rem;">
                <span class="fw-700" style="color:#15803d;" id="settleViolationType">—</span>
                <span style="color:#a8a29e;margin:0 .4rem;">·</span>
                <span style="color:#57534e;" id="settleViolationDate">—</span>
            </div>

            <form id="settleForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body" style="padding:1.25rem;">

                    {{-- OR Number --}}
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.82rem;">OR Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash" style="color:#15803d;font-size:.8rem;"></i></span>
                            <input type="text" name="or_number" class="form-control" placeholder="e.g. 1234567"
                                style="font-family:ui-monospace,monospace;" required maxlength="50">
                        </div>
                        <small style="font-size:.72rem;color:#a8a29e;">Official Receipt number from the cashier.</small>
                    </div>

                    {{-- Cashier Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.82rem;">Cashier Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge-fill" style="color:#15803d;font-size:.8rem;"></i></span>
                            <input type="text" name="cashier_name" class="form-control" placeholder="Full name of cashier" required maxlength="150">
                        </div>
                    </div>

                    {{-- Receipt Photo --}}
                    <div class="mb-2">
                        <label class="form-label fw-700" style="font-size:.82rem;">Receipt Photo <span style="color:#a8a29e;font-weight:400;">(optional)</span></label>
                        <input type="file" name="receipt_photo" id="receipt_photo"
                            class="form-control" accept="image/jpg,image/jpeg,image/png">
                        <small style="font-size:.72rem;color:#a8a29e;">JPG/PNG, max 5 MB.</small>
                        <div id="receiptPreviewWrap" class="d-none mt-2 text-center">
                            <img id="receiptPreview" src="" alt="Receipt"
                                style="max-width:100%;max-height:180px;border-radius:8px;border:1px solid #bbf7d0;object-fit:contain;">
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0" style="padding:.75rem 1.25rem;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size:.82rem;">Cancel</button>
                    <button type="submit" class="btn" style="background:linear-gradient(135deg,#15803d,#166534);color:#fff;font-size:.82rem;font-weight:700;padding:.45rem 1.2rem;border-radius:8px;">
                        <i class="bi bi-check2-circle me-1"></i> Mark as Settled
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
