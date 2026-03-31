@extends('layouts.app')
@section('title', 'Vehicle ' . $vehicle->plate_number)

@section('topbar-sub', $vehicle->plate_number . ' — ' . ($vehicle->make ? $vehicle->make . ' ' . ($vehicle->model ?? '') : 'Vehicle Details'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}" style="color:#78716c;">Vehicles</a></li>
    <li class="breadcrumb-item active" aria-current="page" style="color:#44403c;">{{ $vehicle->plate_number }}</li>
@endsection

@push('styles')
<style>
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
.fw-600 { font-weight:600; }
.fw-700 { font-weight:700; }
.veh-user-card {
    display:flex;
    align-items:flex-start;
    gap:.9rem;
    padding:.95rem 1rem;
    border:1px solid #ede8df;
    border-radius:16px;
    background:linear-gradient(135deg,#fff,#fafaf9);
}
.veh-user-avatar {
    width:46px;
    height:46px;
    border-radius:14px;
    background:linear-gradient(135deg,#e0f2fe,#bae6fd);
    color:#0369a1;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    font-size:1rem;
    flex-shrink:0;
    overflow:hidden;
    border:1.5px solid #bae6fd;
}
.veh-user-avatar img {
    width:100%;
    height:100%;
    object-fit:cover;
}
.veh-user-badge {
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    border-radius:9999px;
    padding:.18rem .55rem;
    font-size:.68rem;
    font-weight:700;
    line-height:1;
}
.veh-user-badge--owner {
    background:#ede9fe;
    color:#6d28d9;
    border:1px solid #ddd6fe;
}
.veh-user-badge--registered {
    background:#ecfeff;
    color:#0f766e;
    border:1px solid #a5f3fc;
}
.veh-user-badge--manual {
    background:#fffbeb;
    color:#b45309;
    border:1px solid #fde68a;
}
.veh-user-stat {
    display:inline-flex;
    align-items:center;
    gap:.3rem;
    font-size:.7rem;
    font-weight:700;
    color:#57534e;
    background:#f5f5f4;
    border-radius:9999px;
    padding:.18rem .5rem;
}
</style>
@endpush

@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center"
             style="width:42px;height:42px;background:linear-gradient(135deg,#0284c7,#0369a1);flex-shrink:0;">
            <i class="bi bi-car-front-fill text-white" style="font-size:1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-700" style="color:#1c1917;font-family:ui-monospace,monospace;letter-spacing:.05em;">{{ $vehicle->plate_number }}</h5>
            <div style="font-size:.8rem;color:#78716c;">
                {{ $vehicle->vehicle_type === 'MV' ? 'Motor Vehicle' : 'Motorcycle' }}
                @if($vehicle->make || $vehicle->model)
                    · {{ trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? '')) }}
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- Card 1: Vehicle Details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dbeafe;">
                    <i class="bi bi-car-front-fill" style="font-size:.85rem;color:#1d4ed8;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Vehicle Details</span>
            </div>
            <div class="card-body p-0">
                <dl class="mb-0">

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Plate No.</div>
                        <div class="font-monospace fw-700" style="color:#1c1917;font-size:1.05rem;letter-spacing:.07em;">{{ $vehicle->plate_number }}</div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Type</div>
                        <div>
                            @if($vehicle->vehicle_type === 'MV')
                                <span style="display:inline-flex;align-items:center;gap:.4rem;background:#eff6ff;color:#1d4ed8;border:1.5px solid #bfdbfe;padding:.25rem .75rem;border-radius:20px;font-size:.78rem;font-weight:700;">
                                    <i class="bi bi-car-front-fill"></i> Motor Vehicle (MV)
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:.4rem;background:#fdf4ff;color:#7c3aed;border:1.5px solid #e9d5ff;padding:.25rem .75rem;border-radius:20px;font-size:.78rem;font-weight:700;">
                                    <i class="bi bi-bicycle"></i> Motorcycle (MC)
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Make</div>
                        <div style="color:#292524;">{{ $vehicle->make ?? '—' }}</div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Model</div>
                        <div style="color:#292524;">{{ $vehicle->model ?? '—' }}</div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Color</div>
                        <div style="color:#292524;">{{ $vehicle->color ?? '—' }}</div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Year</div>
                        <div style="color:#292524;">{{ $vehicle->year ?? '—' }}</div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">OR No.</div>
                        <div>
                            @if($vehicle->or_number)
                                <span class="font-monospace fw-600" style="color:#1c1917;">{{ $vehicle->or_number }}</span>
                            @else
                                <span style="color:#a8a29e;font-style:italic;">Not recorded</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3" style="border-bottom:1px solid #f5f0e8;">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">CR No.</div>
                        <div>
                            @if($vehicle->cr_number)
                                <span class="font-monospace fw-600" style="color:#1c1917;">{{ $vehicle->cr_number }}</span>
                            @else
                                <span style="color:#a8a29e;font-style:italic;">Not recorded</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 px-4 py-3">
                        <div style="width:120px;flex-shrink:0;font-size:.8rem;color:#a8a29e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;padding-top:2px;">Chassis No.</div>
                        <div>
                            @if($vehicle->chassis_number)
                                <span class="font-monospace fw-600" style="color:#1c1917;">{{ $vehicle->chassis_number }}</span>
                            @else
                                <span style="color:#a8a29e;font-style:italic;">Not recorded</span>
                            @endif
                        </div>
                    </div>

                </dl>
            </div>
        </div>

        {{-- Card 2: Photos --}}
        @if($vehicle->photos->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fef9c3;">
                    <i class="bi bi-images" style="font-size:.85rem;color:#d97706;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Vehicle Photos</span>
                <span class="ms-auto" style="font-size:.75rem;color:#a8a29e;">{{ $vehicle->photos->count() }} / 4</span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3">
                    @foreach($vehicle->photos as $photo)
                    <img src="{{ uploaded_file_url($photo->photo) }}"
                         alt="Vehicle photo"
                         data-lightbox="{{ uploaded_file_url($photo->photo) }}"
                         data-caption="{{ $vehicle->plate_number }} — Photo {{ $loop->iteration }}"
                         style="height:120px;width:160px;object-fit:cover;border-radius:10px;border:2px solid #bae6fd;cursor:pointer;transition:transform .15s,box-shadow .15s;"
                         onmouseover="this.style.transform='scale(1.04)';this.style.boxShadow='0 6px 20px rgba(2,132,199,.25)'"
                         onmouseout="this.style.transform='scale(1)';this.style.boxShadow='none'">
                    @endforeach
                </div>
                <div style="font-size:.75rem;color:#a8a29e;margin-top:.75rem;">
                    <i class="bi bi-zoom-in me-1"></i>Click a photo to enlarge
                </div>
            </div>
        </div>
        @endif

        {{-- Card 3: Violation History --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fee2e2;">
                    <i class="bi bi-shield-exclamation" style="font-size:.85rem;color:#dc2626;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Violation History</span>
                @if($vehicle->violations->count() > 0)
                    <span class="ms-auto badge" style="background:#fee2e2;color:#b91c1c;font-size:.75rem;">{{ $vehicle->violations->count() }} {{ Str::plural('record', $vehicle->violations->count()) }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($vehicle->violations as $v)
                @php
                    $isOverdue = $v->isOverdue();
                    $displayStatus = $isOverdue ? 'overdue' : $v->status;
                    $statusConf = match($displayStatus) {
                        'overdue'   => ['cls'=>'viol-status-overdue',   'dot'=>'#dc2626'],
                        'pending'   => ['cls'=>'viol-status-pending',   'dot'=>'#f59e0b'],
                        'settled'   => ['cls'=>'viol-status-settled',   'dot'=>'#22c55e'],
                        'contested' => ['cls'=>'viol-status-contested', 'dot'=>'#94a3b8'],
                        default     => ['cls'=>'',                      'dot'=>'#94a3b8'],
                    };
                @endphp
                <div class="d-flex align-items-start gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:#f5f0e8;">
                    <div style="width:38px;height:38px;border-radius:9px;background:#fff1f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-exclamation-octagon-fill" style="color:#be123c;font-size:.85rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <a href="{{ route('violations.show', $v) }}"
                                   class="fw-600 text-decoration-none" style="color:#1c1917;font-size:.88rem;">
                                    {{ $v->violationType?->name ?? '—' }}
                                </a>
                                <div style="font-size:.75rem;color:#a8a29e;margin-top:1px;">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    {{ $v->date_of_violation->format('M d, Y') }}
                                </div>
                            </div>
                            <span class="viol-status-pill {{ $statusConf['cls'] }}" style="font-size:.75rem;">
                                <span class="viol-status-dot" style="width:7px;height:7px;"></span>
                                {{ ucfirst($displayStatus) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#fff1f2,#fce7f3);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fca5a5;margin:0 auto .75rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <p class="fw-600 mb-0" style="color:#57534e;font-size:.9rem;">No violations on record</p>
                    <p class="mb-0" style="font-size:.8rem;color:#a8a29e;">This vehicle has a clean record.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>{{-- /LEFT COLUMN --}}

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- Owner Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#ede9fe;">
                    <i class="bi bi-person-fill" style="font-size:.85rem;color:#6d28d9;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Registered Owner</span>
                @if($vehicle->violator)
                <a href="{{ route('violators.show', $vehicle->violator) }}"
                   class="ms-auto" style="font-size:.75rem;color:#6d28d9;text-decoration:none;">
                    View profile <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;"></i>
                </a>
                @endif
            </div>
            <div class="card-body p-3">
                @if($vehicle->violator)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-700 text-white"
                         style="width:48px;height:48px;flex-shrink:0;font-size:1.1rem;
                                background:linear-gradient(135deg,#6d28d9,#4c1d95);">
                        {{ strtoupper(substr($vehicle->violator->first_name, 0, 1)) }}{{ strtoupper(substr($vehicle->violator->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-700" style="color:#1c1917;font-size:.9rem;">{{ $vehicle->violator->full_name }}</div>
                        @if($vehicle->violator->license_number)
                            <div class="font-monospace" style="font-size:.72rem;color:#a8a29e;">
                                {{ $vehicle->violator->license_number }}
                            </div>
                        @endif
                        @php $totalVio = $vehicle->violator->violations->count(); @endphp
                        <div style="font-size:.75rem;margin-top:2px;">
                            @if($totalVio >= 3)
                                <span style="color:#b91c1c;font-weight:700;"><i class="bi bi-fire me-1"></i>Recidivist</span>
                            @elseif($totalVio >= 2)
                                <span style="color:#92400e;font-weight:700;"><i class="bi bi-shield-exclamation me-1"></i>Repeat Offender</span>
                            @elseif($totalVio === 1)
                                <span style="color:#0369a1;font-weight:700;"><i class="bi bi-record-circle me-1"></i>1st Violation</span>
                            @else
                                <span style="color:#15803d;font-weight:700;"><i class="bi bi-shield-check me-1"></i>No Prior</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($vehicle->violator->contact_number || $vehicle->violator->temporary_address || $vehicle->violator->permanent_address)
                <div class="border-top pt-2" style="border-color:#ede8df!important;">
                    <ul class="mb-0 list-unstyled" style="font-size:.78rem;color:#57534e;line-height:1.9;">
                        @if($vehicle->violator->contact_number)
                        <li><i class="bi bi-telephone-fill me-2" style="color:#6d28d9;font-size:.7rem;"></i>{{ $vehicle->violator->contact_number }}</li>
                        @endif
                        @if($vehicle->violator->temporary_address || $vehicle->violator->permanent_address)
                        <li><i class="bi bi-geo-alt-fill me-2" style="color:#6d28d9;font-size:.7rem;"></i>{{ $vehicle->violator->temporary_address ?: $vehicle->violator->permanent_address }}</li>
                        @endif
                    </ul>
                </div>
                @endif
                @elseif($vehicle->owner_name)
                <div class="text-center py-2">
                    <div class="fw-700" style="color:#1c1917;font-size:.92rem;">{{ $vehicle->owner_name }}</div>
                    <div style="font-size:.78rem;color:#a8a29e;margin-top:.2rem;">Owner name recorded manually for this vehicle.</div>
                </div>
                @else
                <p class="mb-0 text-center py-2" style="font-size:.85rem;color:#a8a29e;font-style:italic;">No owner on record.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#e0f2fe;">
                    <i class="bi bi-people-fill" style="font-size:.85rem;color:#0284c7;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">People Using This Vehicle</span>
                <span class="ms-auto badge" style="background:#e0f2fe;color:#0369a1;font-size:.75rem;">
                    {{ $vehicleUsers->count() }} {{ Str::plural('person', $vehicleUsers->count()) }}
                </span>
            </div>
            <div class="card-body p-3">
                @forelse($vehicleUsers as $user)
                <div class="veh-user-card {{ !$loop->last ? 'mb-3' : '' }}">
                    <div class="veh-user-avatar">
                        @if(!empty($user['photo']))
                            <img src="{{ uploaded_file_url($user['photo']) }}" alt="{{ $user['name'] }}">
                        @else
                            {{ strtoupper(substr($user['name'], 0, 1)) }}
                        @endif
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                            <div style="min-width:0;">
                                @if($user['violator'])
                                <a href="{{ route('violators.show', $user['violator']) }}"
                                   class="fw-700 text-decoration-none" style="color:#1c1917;font-size:.9rem;">
                                    {{ $user['name'] }}
                                </a>
                                @else
                                <div class="fw-700" style="color:#1c1917;font-size:.9rem;">{{ $user['name'] }}</div>
                                @endif

                                @if($user['license_number'])
                                <div class="font-monospace" style="font-size:.72rem;color:#a8a29e;margin-top:2px;">
                                    {{ $user['license_number'] }}
                                </div>
                                @endif
                            </div>

                            <div class="d-flex gap-2 flex-wrap justify-content-end">
                                @if($user['is_owner'])
                                <span class="veh-user-badge veh-user-badge--owner">
                                    <i class="bi bi-key-fill" style="font-size:.62rem;"></i> Owner
                                </span>
                                @endif

                                @if($user['is_registered'])
                                <span class="veh-user-badge veh-user-badge--registered">
                                    <i class="bi bi-patch-check-fill" style="font-size:.62rem;"></i> Registered
                                </span>
                                @else
                                <span class="veh-user-badge veh-user-badge--manual">
                                    <i class="bi bi-person-fill-exclamation" style="font-size:.62rem;"></i> Manual Record
                                </span>
                                @endif
                            </div>
                        </div>

                        @if($user['contact_number'] || $user['address'])
                        <div style="font-size:.77rem;color:#57534e;line-height:1.7;margin-top:.35rem;">
                            @if($user['contact_number'])
                                <div><i class="bi bi-telephone-fill me-2" style="color:#0284c7;font-size:.7rem;"></i>{{ $user['contact_number'] }}</div>
                            @endif
                            @if($user['address'])
                                <div><i class="bi bi-geo-alt-fill me-2" style="color:#0284c7;font-size:.7rem;"></i>{{ $user['address'] }}</div>
                            @endif
                        </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="veh-user-stat">
                                <i class="bi bi-shield-exclamation" style="color:#dc2626;"></i>
                                {{ $user['violations_count'] }} {{ Str::plural('violation', $user['violations_count']) }}
                            </span>
                            <span class="veh-user-stat">
                                <i class="bi bi-flag-fill" style="color:#0369a1;"></i>
                                {{ $user['incidents_count'] }} {{ Str::plural('incident', $user['incidents_count']) }}
                            </span>
                        </div>

                        @if($user['last_activity_label'])
                        <div style="font-size:.72rem;color:#a8a29e;margin-top:.5rem;">
                            <i class="bi bi-clock-history me-1"></i>{{ $user['last_activity_label'] }}
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#f0f9ff,#e0f2fe);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#7dd3fc;margin:0 auto .75rem;">
                        <i class="bi bi-people"></i>
                    </div>
                    <p class="fw-600 mb-0" style="color:#57534e;font-size:.9rem;">No motorists linked yet</p>
                    <p class="mb-0" style="font-size:.8rem;color:#a8a29e;">People will appear here once this vehicle is used in violations or incidents.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="card border-0 shadow-sm mb-4" style="border-left:3px solid #0284c7!important;">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dbeafe;">
                    <i class="bi bi-bar-chart-fill" style="font-size:.85rem;color:#0284c7;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Quick Stats</span>
            </div>
            <div class="card-body p-3">
                <ul class="mb-0 list-unstyled" style="font-size:.8rem;color:#57534e;line-height:2.2;">
                    <li>
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Total Violations</span>
                        <span class="fw-700" style="color:#b91c1c;font-size:1.1rem;">{{ $vehicle->violations->count() }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Pending</span>
                        <span class="fw-600">{{ $vehicle->violations->where('status', 'pending')->count() }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Settled</span>
                        <span class="fw-600" style="color:#15803d;">{{ $vehicle->violations->where('status', 'settled')->count() }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Photos on File</span>
                        <span class="fw-600">{{ $vehicle->photos->count() }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dbeafe;">
                    <i class="bi bi-gear-fill" style="font-size:.85rem;color:#0284c7;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Actions</span>
            </div>
            <div class="card-body p-3 d-flex flex-column gap-2">
                @can('update', $vehicle)
                <a href="{{ route('vehicles.edit', $vehicle) }}"
                   class="btn btn-warning w-100 fw-600 d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-pencil-fill" style="font-size:.85rem;"></i> Edit Vehicle
                </a>
                @endcan
                @if($vehicle->violator)
                <a href="{{ route('violators.show', $vehicle->violator) }}"
                   class="btn btn-outline-secondary w-100 fw-600 d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-person-lines-fill" style="font-size:.85rem;"></i> View Owner Profile
                </a>
                @endif
                @can('delete', $vehicle)
                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                      data-confirm="Permanently delete this vehicle record? This cannot be undone.">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-trash" style="font-size:.85rem;"></i> Delete Vehicle
                    </button>
                </form>
                @endcan
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
                    <i class="bi bi-images" style="color:#7dd3fc;font-size:.9rem;flex-shrink:0;"></i>
                    <span id="lightboxCaption" style="color:#e5e7eb;font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:400px;"></span>
                    <span id="lightboxCounter" style="color:#6b7280;font-size:.75rem;flex-shrink:0;margin-left:.25rem;"></span>
                </div>
                <a id="lightboxDownload" href="#" download title="Download image"
                   style="color:#9ca3af;font-size:.85rem;text-decoration:none;margin-right:1rem;flex-shrink:0;display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .55rem;border-radius:6px;transition:all .15s;"
                   onmouseover="this.style.color='#7dd3fc';this.style.background='rgba(125,211,252,.1)'"
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
        ? _lbImgs.map((_,i) => `<span onclick="_lbShow(${i})" style="width:7px;height:7px;border-radius:50%;background:${i===_lbIdx?'#7dd3fc':'rgba(255,255,255,.3)'};cursor:pointer;display:inline-block;transition:background .15s;"></span>`).join('')
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
