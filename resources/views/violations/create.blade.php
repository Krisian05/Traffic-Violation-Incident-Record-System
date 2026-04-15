@extends('layouts.app')
@section('title', 'Record Violation')
@section('topbar-sub', 'For: ' . $violator->full_name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('violators.index') }}" style="color:#78716c;">Motorists</a></li>
    <li class="breadcrumb-item"><a href="{{ route('violators.show', $violator) }}" style="color:#78716c;">{{ $violator->full_name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page" style="color:#44403c;">Record Violation</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper .ts-control { background:#fffdf9; border-color:#c8b99a; color:#44403c; font-size:.9rem; }
    .ts-wrapper.focus .ts-control { border-color:#d97706; box-shadow:0 0 0 .2rem rgba(217,119,6,.15); }
    .vt-select-empty { color: #9ca3af; }
    .ts-dropdown { border-color:#c8b99a; background:#fffdf9; }
    .ts-dropdown .option { color:#44403c; font-size:.88rem; }
    .ts-dropdown .option:hover, .ts-dropdown .option.active { background:#fef3c7; color:#292524; }
    .ts-dropdown .optgroup-header { color:#78716c; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; background:#fdf8f0; }
    .ts-wrapper .ts-control .item { color:#292524; }
    .ts-wrapper .clear-button { color:#78716c; }

/* ── Flatpickr red calendar theme (incidents/create style) ── */
.incident-flatpickr-theme.flatpickr-calendar { width:284px !important; border-radius:14px !important; box-shadow:0 8px 32px rgba(0,0,0,.14),0 2px 8px rgba(0,0,0,.08) !important; border:1px solid #f0ebe3 !important; font-family:inherit !important; overflow:hidden; }
.incident-flatpickr-theme .flatpickr-rContainer,.incident-flatpickr-theme .flatpickr-days { width:100% !important; }
.incident-flatpickr-theme .dayContainer { width:100% !important; min-width:100% !important; max-width:100% !important; padding:.35rem; }
.incident-flatpickr-theme .flatpickr-months { background:linear-gradient(135deg,#dc2626,#b91c1c) !important; border-radius:14px 14px 0 0; align-items:center; }
.incident-flatpickr-theme .flatpickr-months .flatpickr-month { background:transparent !important; color:#fff !important; min-height:38px; }
.incident-flatpickr-theme .flatpickr-months .flatpickr-prev-month,.incident-flatpickr-theme .flatpickr-months .flatpickr-next-month { color:#fff !important; fill:#fff !important; min-height:38px; }
.incident-flatpickr-theme .flatpickr-prev-month:hover svg,.incident-flatpickr-theme .flatpickr-next-month:hover svg { fill:#fde68a !important; }
.incident-flatpickr-theme .flatpickr-current-month { color:#fff !important; font-size:.92rem !important; font-weight:700; }
.incident-flatpickr-theme .flatpickr-current-month .cur-month,.incident-flatpickr-theme .flatpickr-current-month .flatpickr-monthDropdown-months { color:#fff !important; background:transparent !important; font-weight:700; }
.incident-flatpickr-theme .flatpickr-current-month .numInputWrapper { width:4.2ch; }
.incident-flatpickr-theme .flatpickr-current-month input.cur-year { color:#fff !important; font-weight:700; }
.incident-flatpickr-theme .flatpickr-weekdays { background:#fff7f7 !important; border-bottom:1px solid #fecaca; }
.incident-flatpickr-theme span.flatpickr-weekday { background:#fff7f7 !important; color:#b91c1c !important; font-weight:700; font-size:.72rem; }
.incident-flatpickr-theme .flatpickr-day { border-radius:8px !important; font-size:.82rem; font-weight:500; color:#1c1917; transition:all .12s; }
.incident-flatpickr-theme .flatpickr-day:hover { background:#fff1f2 !important; border-color:#fecaca !important; color:#dc2626 !important; }
.incident-flatpickr-theme .flatpickr-day.selected,.incident-flatpickr-theme .flatpickr-day.selected:hover { background:linear-gradient(135deg,#dc2626,#b91c1c) !important; border-color:#b91c1c !important; color:#fff !important; box-shadow:0 2px 8px rgba(185,28,28,.35) !important; font-weight:700; }
.incident-flatpickr-theme .flatpickr-day.today { border-color:#fca5a5 !important; color:#dc2626 !important; font-weight:700; }
.incident-flatpickr-theme .flatpickr-day.today:hover { background:#fff1f2 !important; }
.incident-flatpickr-theme .flatpickr-day.flatpickr-disabled,.incident-flatpickr-theme .flatpickr-day.flatpickr-disabled:hover { color:#d1d5db !important; background:transparent !important; }
</style>
@endpush

@section('content')


{{-- Page Header --}}
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:42px;height:42px;background:linear-gradient(135deg,#dc2626,#b91c1c);flex-shrink:0;">
        <i class="bi bi-exclamation-triangle-fill text-white" style="font-size:1.1rem;"></i>
    </div>
    <div>
        <h5 class="mb-0 fw-700" style="color:#1c1917;">Record Violation</h5>
        <div style="font-size:.8rem;color:#78716c;">Filing a new violation record for <strong>{{ $violator->full_name }}</strong></div>
    </div>
</div>

<form method="POST" action="{{ route('violations.store', $violator) }}" enctype="multipart/form-data" id="violationCreateForm">
@csrf

<div class="row g-4">

    {{-- LEFT COLUMN --}}
    <div class="col-lg-8">

        {{-- Card 1: Violation Details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fee2e2;">
                    <i class="bi bi-shield-exclamation text-danger" style="font-size:.85rem;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Violation Details</span>
                <span class="ms-auto badge" style="background:#fee2e2;color:#991b1b;font-size:.7rem;">Required</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label">
                            Violation Type <span class="text-danger">*</span>
                        </label>
                        <select name="violation_type_id"
                                class="form-select @error('violation_type_id') is-invalid @enderror"
                                required>
                            <option value="">— Select a violation type —</option>
                            @foreach($violationTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('violation_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}{{ $type->fine_amount ? ' — ₱' . number_format($type->fine_amount, 2) : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('violation_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Date of Violation <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-event" style="color:#d97706;font-size:.85rem;"></i></span>
                            <input type="text" name="date_of_violation" id="dp-violation-date"
                                class="form-control @error('date_of_violation') is-invalid @enderror"
                                value="{{ old('date_of_violation', date('Y-m-d')) }}"
                                placeholder="YYYY-MM-DD" required>
                            @error('date_of_violation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required>
                            <option value="pending" {{ old('status','pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="settled" {{ old('status') == 'settled'              ? 'selected' : '' }}>Settled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Card 2: Location & Vehicle --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dbeafe;">
                    <i class="bi bi-geo-alt-fill" style="font-size:.85rem;color:#1d4ed8;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Location &amp; Vehicle</span>
                <span class="ms-auto badge" style="background:#f5f0e8;color:#78716c;font-size:.7rem;">Optional</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">

                    <div class="col-12">
                        @include('partials.location-selector', [
                            'fieldName' => 'location',
                            'required'  => false,
                            'label'     => 'Location of Violation',
                        ])
                    </div>

                    {{-- Vehicle mode toggle --}}
                    @php $initManual = old('vehicle_plate') ? true : false; @endphp
                    <div class="col-12">
                        <label class="form-label">Vehicle Involved</label>
                        <div class="d-flex flex-wrap gap-2 mb-2" role="group">
                            <button type="button" id="btn-from-system"
                                    onclick="setVehicleMode('system')"
                                    class="btn btn-sm {{ $initManual ? 'btn-outline-secondary' : 'btn-primary' }}">
                                <i class="bi bi-search me-1"></i>Pick from system
                            </button>
                            <button type="button" id="btn-manual"
                                    onclick="setVehicleMode('manual')"
                                    class="btn btn-sm {{ $initManual ? 'btn-warning' : 'btn-outline-secondary' }}">
                                <i class="bi bi-pencil-square me-1"></i>Not in system — enter manually
                            </button>
                        </div>

                        {{-- From-system panel --}}
                        <div id="panel-system" {{ $initManual ? 'style=display:none' : '' }}>
                            <select name="vehicle_id" id="vehicle_id"
                                    class="form-select @error('vehicle_id') is-invalid @enderror">
                                    <option value="" data-owner="">— None / Not applicable —</option>
                                    @php
                                        $driverVehicles = $allVehicles->where('violator_id', $violator->id);
                                        $otherVehicles  = $allVehicles->where('violator_id', '!=', $violator->id);
                                    @endphp
                                    @if($driverVehicles->isNotEmpty())
                                    <optgroup label="Driver's own vehicles">
                                        @foreach($driverVehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}"
                                                    data-owner="{{ $violator->full_name }}"
                                                    {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->plate_number }}{{ $vehicle->vehicle_type ? ' ('.$vehicle->vehicle_type.')' : '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                    @if($otherVehicles->isNotEmpty())
                                    <optgroup label="Borrowed / Other vehicles">
                                        @foreach($otherVehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}"
                                                    data-owner="{{ $vehicle->violator?->full_name ?? '' }}"
                                                    {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->plate_number }}{{ $vehicle->vehicle_type ? ' ('.$vehicle->vehicle_type.')' : '' }}
                                                — {{ $vehicle->violator?->full_name ?? 'Unknown Owner' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                            </select>
                            @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Type to search by plate number or owner name.</div>
                        </div>

                        {{-- Manual panel --}}
                        <div id="panel-manual" {{ $initManual ? '' : 'style=display:none' }}>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:.8rem;">Plate Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-card-text" style="color:#b45309;"></i></span>
                                        <input type="text" name="vehicle_plate" id="vehicle_plate"
                                               class="form-control @error('vehicle_plate') is-invalid @enderror"
                                               value="{{ old('vehicle_plate') }}"
                                               placeholder="e.g. ABC 1234"
                                               style="text-transform:uppercase;">
                                        @error('vehicle_plate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" style="font-size:.8rem;">Type</label>
                                    <select name="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror {{ old('vehicle_type') ? '' : 'vt-select-empty' }}"
                                            onchange="this.classList.toggle('vt-select-empty',!this.value)">
                                        <option value="">Select</option>
                                        <option value="MV" {{ old('vehicle_type') === 'MV' ? 'selected' : '' }}>MV</option>
                                        <option value="MC" {{ old('vehicle_type') === 'MC' ? 'selected' : '' }}>MC</option>
                                    </select>
                                    @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:.8rem;">Brand / Make</label>
                                    <input type="text" name="vehicle_make"
                                           class="form-control @error('vehicle_make') is-invalid @enderror"
                                           value="{{ old('vehicle_make') }}"
                                           placeholder="e.g. Honda, Yamaha">
                                    @error('vehicle_make')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:.8rem;">Model</label>
                                    <input type="text" name="vehicle_model"
                                           class="form-control @error('vehicle_model') is-invalid @enderror"
                                           value="{{ old('vehicle_model') }}"
                                           placeholder="e.g. Click 125i, Mio">
                                    @error('vehicle_model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:.8rem;">Color</label>
                                    <input type="text" name="vehicle_color"
                                           class="form-control @error('vehicle_color') is-invalid @enderror"
                                           value="{{ old('vehicle_color') }}"
                                           placeholder="e.g. Red, Black">
                                    @error('vehicle_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:.8rem;">OR Number</label>
                                    <input type="text" name="vehicle_or_number"
                                           class="form-control @error('vehicle_or_number') is-invalid @enderror font-monospace"
                                           value="{{ old('vehicle_or_number') }}"
                                           placeholder="Official Receipt No.">
                                    @error('vehicle_or_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:.8rem;">CR Number</label>
                                    <input type="text" name="vehicle_cr_number"
                                           class="form-control @error('vehicle_cr_number') is-invalid @enderror font-monospace"
                                           value="{{ old('vehicle_cr_number') }}"
                                           placeholder="Certificate of Registration No.">
                                    @error('vehicle_cr_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-size:.8rem;">Chassis Number</label>
                                    <input type="text" name="vehicle_chassis"
                                           class="form-control @error('vehicle_chassis') is-invalid @enderror font-monospace"
                                           value="{{ old('vehicle_chassis') }}"
                                           placeholder="Chassis / Frame No.">
                                    @error('vehicle_chassis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-size:.8rem;">Vehicle Photos</label>
                                    <input type="file" name="photos[]" accept="image/jpeg,image/png" multiple
                                           class="form-control @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror"
                                           onchange="previewVehiclePhotos(event)">
                                    @error('photos')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @error('photos.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    <div class="form-text">Optional. Up to 4 photos, max 50 MB each. JPG/PNG.</div>
                                    <div class="mt-2 d-flex flex-wrap gap-2" id="photoPreviewContainer"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-flex align-items-center gap-2">
                            Registered Owner
                            <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.68rem;font-weight:600;">
                                Auto-filled · editable
                            </span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-vcard-fill" style="color:#7c3aed;"></i></span>
                            <input type="text" name="vehicle_owner_name" id="vehicle_owner_name"
                                   class="form-control @error('vehicle_owner_name') is-invalid @enderror"
                                   value="{{ old('vehicle_owner_name') }}"
                                   placeholder="Name of the registered vehicle owner (if different from driver)">
                            @error('vehicle_owner_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">
                            Leave blank if the driver is the owner. Fill in for borrowed or unregistered vehicles.
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Card 3: Administrative Notes --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#f0fdf4;">
                    <i class="bi bi-file-text-fill" style="font-size:.85rem;color:#15803d;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Administrative Information</span>
                <span class="ms-auto badge" style="background:#f5f0e8;color:#78716c;font-size:.7rem;">Optional</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Ticket / Citation Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                            <input type="text" name="ticket_number"
                                class="form-control @error('ticket_number') is-invalid @enderror"
                                value="{{ old('ticket_number') }}"
                                placeholder="e.g. TCK-2024-00123">
                            @error('ticket_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Citation Ticket Photo</label>
                        <input type="file" name="citation_ticket_photo" id="citation_ticket_photo"
                            accept="image/jpeg,image/png"
                            class="form-control @error('citation_ticket_photo') is-invalid @enderror"
                            onchange="previewCitationPhoto(event)">
                        @error('citation_ticket_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Optional. Photo of the physical citation ticket. JPG/PNG, max 10 MB.</div>
                        <div class="mt-2" id="citationPhotoPreview" style="display:none;">
                            <img id="citationPhotoImg" src="" alt="Citation ticket"
                                 style="max-width:100%;max-height:220px;border-radius:8px;border:2px solid #fcd34d;object-fit:contain;">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes / Remarks</label>
                        <textarea name="notes"
                            class="form-control @error('notes') is-invalid @enderror"
                            rows="4"
                            placeholder="Additional details, officer remarks, or any relevant information…">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maximum 1,000 characters.</div>
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- /LEFT COLUMN --}}

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-4">

        {{-- Violator Summary Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#ede9fe;">
                    <i class="bi bi-person-fill" style="font-size:.85rem;color:#6d28d9;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Violator</span>
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-700 text-white"
                         style="width:46px;height:46px;flex-shrink:0;font-size:1.1rem;
                                background:linear-gradient(135deg,#6d28d9,#4c1d95);">
                        {{ strtoupper(substr($violator->first_name, 0, 1)) }}{{ strtoupper(substr($violator->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-600" style="color:#1c1917;font-size:.9rem;">{{ $violator->full_name }}</div>
                        @if($violator->license_number)
                            <div style="font-size:.75rem;color:#78716c;">
                                <i class="bi bi-card-text me-1"></i>{{ $violator->license_number }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="border-top pt-3" style="border-color:#ede8df!important;">
                    <div class="row g-2" style="font-size:.78rem;">
                        @if($violator->address)
                        <div class="col-12">
                            <div style="color:#78716c;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Address</div>
                            <div style="color:#44403c;">{{ $violator->address }}</div>
                        </div>
                        @endif
                        @if($violator->contact_number)
                        <div class="col-12">
                            <div style="color:#78716c;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Contact</div>
                            <div style="color:#44403c;">{{ $violator->contact_number }}</div>
                        </div>
                        @endif
                        <div class="col-12">
                            <div style="color:#78716c;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Registered Vehicles</div>
                            <div style="color:#44403c;">{{ $violator->vehicles->count() }} {{ Str::plural('vehicle', $violator->vehicles->count()) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Guide Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-left:3px solid #d97706!important;">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fef3c7;">
                    <i class="bi bi-lightbulb-fill" style="font-size:.85rem;color:#d97706;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Quick Guide</span>
            </div>
            <div class="card-body p-3">
                <ul class="mb-0 ps-3" style="font-size:.8rem;color:#57534e;line-height:1.8;">
                    <li>Fields marked <span class="text-danger fw-600">*</span> are required.</li>
                    <li>Select the correct <strong>Violation Type</strong> — the fine amount is shown for reference only.</li>
                    <li>Set <strong>Status</strong> to <em>Pending</em> if not yet settled.</li>
                    <li>Ticket number can be filled in later if not yet issued.</li>
                </ul>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fee2e2;">
                    <i class="bi bi-save-fill" style="font-size:.85rem;color:#dc2626;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Actions</span>
            </div>
            <div class="card-body p-3 d-flex flex-column gap-2">
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-check-lg me-1"></i> Save Violation
                </button>
                <a href="{{ route('violators.show', $violator) }}" class="btn d-inline-flex align-items-center justify-content-center gap-2 rounded-pill w-100" style="border:1.5px solid #d6d3d1;color:#78716c;background:#fff;font-weight:500;">
                    <i class="bi bi-x-circle" style="font-size:.85rem;"></i> Cancel
                </a>
            </div>
        </div>

    </div>{{-- /RIGHT COLUMN --}}

</div>{{-- /row --}}
</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    function attachIncidentFlatpickrTheme(inst) {
        if (inst && inst.calendarContainer) inst.calendarContainer.classList.add('incident-flatpickr-theme');
    }
    function initIncidentDatePicker(target, opts) {
        if (!target || !window.flatpickr) return null;
        const extra = opts || {}, origReady = extra.onReady;
        return flatpickr(target, Object.assign({ dateFormat:'Y-m-d', allowInput:true, appendTo:document.body, monthSelectorType:'static',
            onReady: function(d,s,inst) { attachIncidentFlatpickrTheme(inst); if (typeof origReady==='function') origReady.call(this,d,s,inst); }
        }, extra));
    }
    initIncidentDatePicker('#dp-violation-date', { maxDate:'today', defaultDate: document.getElementById('dp-violation-date').value || 'today' });
</script>
<script>

    // Searchable vehicle dropdown
    let tsVehicle = new TomSelect('#vehicle_id', {
        allowEmptyOption: true,
        placeholder: '— None / Not applicable —',
        maxOptions: null,
        onChange: function(value) {
            let owner = '';
            if (value) {
                const opt = document.querySelector('#vehicle_id option[value="' + value + '"]');
                owner = opt ? (opt.dataset.owner || '') : '';
            }
            document.getElementById('vehicle_owner_name').value = owner;
        }
    });

    function previewVehiclePhotos(event) {
        const container = document.getElementById('photoPreviewContainer');
        container.innerHTML = '';
        Array.from(event.target.files).forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.cssText = 'height:100px;width:140px;object-fit:cover;border-radius:6px;border:2px dashed #fcd34d;';
            container.appendChild(img);
        });
    }

    function previewCitationPhoto(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('citationPhotoImg').src = e.target.result;
            document.getElementById('citationPhotoPreview').style.display = '';
        };
        reader.readAsDataURL(file);
    }

    function setVehicleMode(mode) {
        const panelSystem  = document.getElementById('panel-system');
        const panelManual  = document.getElementById('panel-manual');
        const btnSystem    = document.getElementById('btn-from-system');
        const btnManual    = document.getElementById('btn-manual');
        const vehiclePlate = document.getElementById('vehicle_plate');

        if (mode === 'system') {
            panelSystem.style.display = '';
            panelManual.style.display = 'none';
            btnSystem.className = 'btn btn-sm btn-primary';
            btnManual.className = 'btn btn-sm btn-outline-secondary';
            vehiclePlate.value = '';
        } else {
            panelSystem.style.display = 'none';
            panelManual.style.display = '';
            btnSystem.className = 'btn btn-sm btn-outline-secondary';
            btnManual.className = 'btn btn-sm btn-warning';
            tsVehicle.clear(true);
            document.getElementById('vehicle_owner_name').value = '';
        }
    }

    // Double-submit protection
    document.getElementById('violationCreateForm').addEventListener('submit', function () {
        var btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Saving…';
    });
</script>
@endpush
