@extends('layouts.app')
@section('title', 'Edit Violation #' . $violation->id)
@section('topbar-sub', 'Editing record for: ' . $violation->violator->full_name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('violations.index') }}" style="color:#78716c;">Violations</a></li>
    <li class="breadcrumb-item"><a href="{{ route('violations.show', $violation) }}" style="color:#78716c;">Violation #{{ $violation->id }}</a></li>
    <li class="breadcrumb-item active" aria-current="page" style="color:#44403c;">Edit</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper .ts-control { background:#fffdf9; border-color:#c8b99a; color:#44403c; font-size:.9rem; }
    .ts-wrapper.focus .ts-control { border-color:#d97706; box-shadow:0 0 0 .2rem rgba(217,119,6,.15); }
    .ts-dropdown { border-color:#c8b99a; background:#fffdf9; }
    .ts-dropdown .option { color:#44403c; font-size:.88rem; }
    .ts-dropdown .option:hover, .ts-dropdown .option.active { background:#fef3c7; color:#292524; }
    .ts-dropdown .optgroup-header { color:#78716c; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; background:#fdf8f0; }
    .ts-wrapper .ts-control .item { color:#292524; }
    .ts-wrapper .clear-button { color:#78716c; }

    /* Vehicle photo thumbnails: responsive on mobile */
    @media (max-width: 767px) {
        .d-flex.flex-wrap img[style*="height:100px"] {
            width: calc(50% - .25rem) !important;
            height: auto !important;
            aspect-ratio: 4/3;
            object-fit: cover;
        }
    }
</style>
@endpush

@section('content')

{{-- ── EXISTING VEHICLE PHOTOS — outside any <form>, standalone delete forms ── --}}
@if($violation->vehiclePhotos->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex align-items-center gap-2 py-3">
        <span class="rounded d-flex align-items-center justify-content-center"
              style="width:28px;height:28px;background:#e0f2fe;">
            <i class="bi bi-images" style="font-size:.85rem;color:#0369a1;"></i>
        </span>
        <span class="fw-600" style="font-size:.925rem;color:#292524;">Current Vehicle Photos</span>
        <span class="badge bg-secondary ms-1">{{ $violation->vehiclePhotos->count() }} / 4</span>
        <span class="ms-auto" style="font-size:.78rem;color:#78716c;">Click a photo to enlarge · click × to delete</span>
    </div>
    <div class="card-body p-4">
        <div class="d-flex flex-wrap gap-2">
            @foreach($violation->vehiclePhotos as $photo)
            <div class="position-relative">
                <img src="{{ uploaded_file_url($photo->photo) }}"
                     data-lightbox="{{ uploaded_file_url($photo->photo) }}"
                     data-caption="Violation #{{ $violation->id }}"
                     style="height:100px;width:140px;object-fit:cover;border-radius:6px;border:2px solid #fde68a;cursor:pointer;"
                     alt="vehicle photo">
                <form method="POST" action="{{ route('violation-vehicle-photos.destroy', $photo) }}"
                      data-confirm="Delete this photo? This cannot be undone.">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center position-absolute top-0 end-0"
                        style="width:22px;height:22px;border-radius:50%;font-size:11px;margin:3px;">
                        <i class="bi bi-x"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── PAGE HEADER ── --}}
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:42px;height:42px;background:linear-gradient(135deg,#d97706,#b45309);flex-shrink:0;">
        <i class="bi bi-pencil-fill text-white" style="font-size:1rem;"></i>
    </div>
    <div>
        <h5 class="mb-0 fw-700" style="color:#1c1917;">Edit Violation #{{ $violation->id }}</h5>
        <div style="font-size:.8rem;color:#78716c;">Modifying violation record for <strong>{{ $violation->violator->full_name }}</strong></div>
    </div>
</div>

<form method="POST" action="{{ route('violations.update', $violation) }}" enctype="multipart/form-data" id="violationEditForm">
@csrf @method('PUT')

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
                                    {{ old('violation_type_id', $violation->violation_type_id) == $type->id ? 'selected' : '' }}>
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
                                value="{{ old('date_of_violation', $violation->date_of_violation->format('Y-m-d')) }}"
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
                            @foreach(['pending','settled'] as $s)
                                <option value="{{ $s }}" {{ old('status', $violation->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
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
                        <label class="form-label">Location of Violation</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-pin-map-fill"></i></span>
                            <input type="text" name="location"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location', $violation->location) }}"
                                placeholder="e.g. Rizal Ave. corner Mabini St.">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Vehicle mode toggle --}}
                    @php $initManual = old('vehicle_plate', $violation->vehicle_plate) ? true : false; @endphp
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
                                    $driverVehicles = $allVehicles->where('violator_id', $violation->violator_id);
                                    $otherVehicles  = $allVehicles->where('violator_id', '!=', $violation->violator_id);
                                @endphp
                                @if($driverVehicles->isNotEmpty())
                                <optgroup label="Driver's own vehicles">
                                    @foreach($driverVehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}"
                                                data-owner="{{ $violation->violator->full_name }}"
                                                {{ old('vehicle_id', $violation->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
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
                                                {{ old('vehicle_id', $violation->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
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
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size:.8rem;">Plate Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-card-text" style="color:#b45309;"></i></span>
                                        <input type="text" name="vehicle_plate" id="vehicle_plate"
                                               class="form-control @error('vehicle_plate') is-invalid @enderror"
                                               value="{{ old('vehicle_plate', $violation->vehicle_plate) }}"
                                               placeholder="e.g. ABC 1234"
                                               style="text-transform:uppercase;">
                                        @error('vehicle_plate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:.8rem;">Brand / Make</label>
                                    <input type="text" name="vehicle_make"
                                           class="form-control @error('vehicle_make') is-invalid @enderror"
                                           value="{{ old('vehicle_make', $violation->vehicle_make) }}"
                                           placeholder="e.g. Honda, Yamaha">
                                    @error('vehicle_make')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:.8rem;">Model</label>
                                    <input type="text" name="vehicle_model"
                                           class="form-control @error('vehicle_model') is-invalid @enderror"
                                           value="{{ old('vehicle_model', $violation->vehicle_model) }}"
                                           placeholder="e.g. Click 125i, Mio">
                                    @error('vehicle_model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size:.8rem;">Color</label>
                                    <input type="text" name="vehicle_color"
                                           class="form-control @error('vehicle_color') is-invalid @enderror"
                                           value="{{ old('vehicle_color', $violation->vehicle_color) }}"
                                           placeholder="e.g. Red, Black">
                                    @error('vehicle_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:.8rem;">OR Number</label>
                                    <input type="text" name="vehicle_or_number"
                                           class="form-control @error('vehicle_or_number') is-invalid @enderror font-monospace"
                                           value="{{ old('vehicle_or_number', $violation->vehicle_or_number) }}"
                                           placeholder="Official Receipt No.">
                                    @error('vehicle_or_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size:.8rem;">CR Number</label>
                                    <input type="text" name="vehicle_cr_number"
                                           class="form-control @error('vehicle_cr_number') is-invalid @enderror font-monospace"
                                           value="{{ old('vehicle_cr_number', $violation->vehicle_cr_number) }}"
                                           placeholder="Certificate of Registration No.">
                                    @error('vehicle_cr_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-size:.8rem;">Chassis Number</label>
                                    <input type="text" name="vehicle_chassis"
                                           class="form-control @error('vehicle_chassis') is-invalid @enderror font-monospace"
                                           value="{{ old('vehicle_chassis', $violation->vehicle_chassis) }}"
                                           placeholder="Chassis / Frame No.">
                                    @error('vehicle_chassis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    @php $remainingSlots = 4 - $violation->vehiclePhotos->count(); @endphp
                                    <label class="form-label" style="font-size:.8rem;">
                                        Add Vehicle Photos
                                        <span class="text-muted fw-normal" style="font-size:.8rem;">
                                            ({{ $violation->vehiclePhotos->count() }}/4 used
                                            @if($remainingSlots > 0) — {{ $remainingSlots }} slot{{ $remainingSlots != 1 ? 's' : '' }} remaining @else — limit reached @endif)
                                        </span>
                                    </label>
                                    @if($remainingSlots > 0)
                                        <input type="file" name="photos[]" accept="image/jpeg,image/png" multiple
                                               class="form-control @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror"
                                               onchange="previewVehiclePhotos(event)">
                                        @error('photos')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        @error('photos.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        <div class="form-text">Max {{ $remainingSlots }} more photo{{ $remainingSlots != 1 ? 's' : '' }}. Each must be under 50 MB. JPG/PNG.</div>
                                        <div class="mt-2 d-flex flex-wrap gap-2" id="photoPreviewContainer"></div>
                                    @else
                                        <div class="alert alert-warning py-2 mb-0" style="font-size:.875rem;">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Maximum 4 photos reached. Delete an existing photo above to add a new one.
                                        </div>
                                    @endif
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
                                   value="{{ old('vehicle_owner_name', $violation->vehicle_owner_name) }}"
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

        {{-- Card 3: Administrative Information --}}
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
                                value="{{ old('ticket_number', $violation->ticket_number) }}"
                                placeholder="e.g. TCK-2024-00123">
                            @error('ticket_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Citation Ticket Photo</label>
                        @if($violation->citation_ticket_photo)
                            <div class="mb-2" id="currentCitationWrap">
                                <img src="{{ uploaded_file_url($violation->citation_ticket_photo) }}"
                                     alt="Current citation ticket"
                                     style="max-width:100%;max-height:160px;object-fit:contain;border-radius:8px;border:2px solid #fcd34d;">
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span style="font-size:.72rem;color:#a8a29e;">Current photo — upload new to replace</span>
                                    <label class="d-flex align-items-center gap-1 ms-auto" style="font-size:.75rem;color:#dc2626;cursor:pointer;">
                                        <input type="checkbox" name="remove_citation_photo" value="1" id="removeCitationPhoto"
                                               onchange="toggleCitationInput(this)">
                                        Remove
                                    </label>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="citation_ticket_photo" id="citation_ticket_photo"
                            accept="image/jpeg,image/png"
                            class="form-control @error('citation_ticket_photo') is-invalid @enderror"
                            onchange="previewCitationPhoto(event)">
                        @error('citation_ticket_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Optional. JPG/PNG, max 10 MB.</div>
                        <div class="mt-2" id="citationPhotoPreview" style="display:none;">
                            <img id="citationPhotoImg" src="" alt="Citation ticket preview"
                                 style="max-width:100%;max-height:160px;border-radius:8px;border:2px dashed #fcd34d;object-fit:contain;">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes / Remarks</label>
                        <textarea name="notes"
                            class="form-control @error('notes') is-invalid @enderror"
                            rows="4"
                            placeholder="Additional details, officer remarks, or any relevant information…">{{ old('notes', $violation->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maximum 1,000 characters.</div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Card 4: Settlement Details (shown when status = settled) --}}
        <div class="card border-0 shadow-sm mb-4 {{ old('status', $violation->status) === 'settled' ? '' : 'd-none' }}" id="settlementCard">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#dcfce7;">
                    <i class="bi bi-receipt" style="font-size:.85rem;color:#15803d;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Settlement Details</span>
                <span class="ms-auto badge" style="background:#dcfce7;color:#15803d;font-size:.7rem;">Required when settled</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">OR Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash" style="color:#15803d;"></i></span>
                            <input type="text" name="or_number"
                                class="form-control font-monospace @error('or_number') is-invalid @enderror"
                                value="{{ old('or_number', $violation->or_number) }}"
                                placeholder="Official Receipt No.">
                            @error('or_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cashier Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge-fill" style="color:#15803d;"></i></span>
                            <input type="text" name="cashier_name"
                                class="form-control @error('cashier_name') is-invalid @enderror"
                                value="{{ old('cashier_name', $violation->cashier_name) }}"
                                placeholder="Full name of cashier">
                            @error('cashier_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    @if($violation->settled_at ?? $violation->status === 'settled')
                    <div class="col-12">
                        <div style="font-size:.78rem;color:#78716c;">
                            <i class="bi bi-clock-history me-1" style="color:#15803d;"></i>
                            Date Settled: <strong style="color:#15803d;">{{ ($violation->settled_at ?? $violation->updated_at)->format('F d, Y  g:i A') }}</strong>
                        </div>
                    </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label">Receipt Photo</label>
                        @if($violation->receipt_photo)
                            <div class="mb-2" id="currentReceiptWrap">
                                <img src="{{ uploaded_file_url($violation->receipt_photo) }}"
                                     alt="Current receipt"
                                     style="max-width:100%;max-height:160px;object-fit:contain;border-radius:8px;border:2px solid #bbf7d0;">
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span style="font-size:.72rem;color:#a8a29e;">Current receipt — upload new to replace</span>
                                    <label class="d-flex align-items-center gap-1 ms-auto" style="font-size:.75rem;color:#dc2626;cursor:pointer;">
                                        <input type="checkbox" name="remove_receipt_photo" value="1" id="removeReceiptPhoto"
                                               onchange="toggleReceiptInput(this)">
                                        Remove
                                    </label>
                                </div>
                            </div>
                        @endif
                        <input type="file" name="receipt_photo" id="receipt_photo_edit"
                            accept="image/jpeg,image/png"
                            class="form-control @error('receipt_photo') is-invalid @enderror"
                            onchange="previewReceiptPhoto(event)">
                        @error('receipt_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Optional. JPG/PNG, max 5 MB.</div>
                        <div class="mt-2" id="receiptPhotoPreview" style="display:none;">
                            <img id="receiptPhotoImg" src="" alt="Receipt preview"
                                 style="max-width:100%;max-height:160px;border-radius:8px;border:2px dashed #bbf7d0;object-fit:contain;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /LEFT COLUMN --}}

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- Violator Summary Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#ede9fe;">
                    <i class="bi bi-person-fill" style="font-size:.85rem;color:#6d28d9;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Violator</span>
                <a href="{{ route('violators.show', $violation->violator) }}"
                   class="ms-auto" style="font-size:.75rem;color:#6d28d9;text-decoration:none;">
                    View profile <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;"></i>
                </a>
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-700 text-white"
                         style="width:46px;height:46px;flex-shrink:0;font-size:1.1rem;
                                background:linear-gradient(135deg,#6d28d9,#4c1d95);">
                        {{ strtoupper(substr($violation->violator->first_name, 0, 1)) }}{{ strtoupper(substr($violation->violator->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-600" style="color:#1c1917;font-size:.9rem;">{{ $violation->violator->full_name }}</div>
                        @if($violation->violator->license_number)
                            <div style="font-size:.75rem;color:#78716c;">
                                <i class="bi bi-card-text me-1"></i>{{ $violation->violator->license_number }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="border-top pt-3" style="border-color:#ede8df!important;">
                    <div class="row g-2" style="font-size:.78rem;">
                        @if($violation->violator->address)
                        <div class="col-12">
                            <div style="color:#78716c;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Address</div>
                            <div style="color:#44403c;">{{ $violation->violator->address }}</div>
                        </div>
                        @endif
                        @if($violation->violator->contact_number)
                        <div class="col-12">
                            <div style="color:#78716c;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Contact</div>
                            <div style="color:#44403c;">{{ $violation->violator->contact_number }}</div>
                        </div>
                        @endif
                        <div class="col-12">
                            <div style="color:#78716c;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Total Violations</div>
                            <div style="color:#44403c;">{{ $violation->violator->violations->count() }} {{ Str::plural('violation', $violation->violator->violations->count()) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Record Meta Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-left:3px solid #d97706!important;">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fef3c7;">
                    <i class="bi bi-info-circle-fill" style="font-size:.85rem;color:#d97706;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Record Info</span>
            </div>
            <div class="card-body p-3">
                <ul class="mb-0 list-unstyled" style="font-size:.8rem;color:#57534e;line-height:2;">
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
                        <span>{{ $violation->created_at->format('M d, Y g:i A') }}</span>
                    </li>
                    <li class="border-top pt-2 mt-1" style="border-color:#ede8df!important;">
                        <span style="color:#a8a29e;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;display:block;">Last Updated</span>
                        <span>{{ $violation->updated_at->format('M d, Y g:i A') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <span class="rounded d-flex align-items-center justify-content-center"
                      style="width:28px;height:28px;background:#fef3c7;">
                    <i class="bi bi-save-fill" style="font-size:.85rem;color:#d97706;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#292524;">Actions</span>
            </div>
            <div class="card-body p-3 d-flex flex-column gap-2">
                <button type="submit" class="btn btn-warning w-100 fw-600">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('violations.show', $violation) }}"
                   class="btn d-inline-flex align-items-center justify-content-center gap-2 rounded-pill w-100"
                   style="border:1.5px solid #d6d3d1;color:#78716c;background:#fff;font-weight:500;">
                    <i class="bi bi-x-circle" style="font-size:.85rem;"></i> Cancel
                </a>
            </div>
        </div>

    </div>{{-- /RIGHT COLUMN --}}

</div>{{-- /row --}}
</form>

{{-- Lightbox Modal --}}
<div class="modal fade" id="photoLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 py-2">
                <span class="text-white small" id="lightboxCaption"></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="lightboxImg" src="" alt=""
                    style="max-width:100%;max-height:80vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    // Show/hide settlement card based on status
    const statusSelect = document.querySelector('select[name="status"]');
    const settlementCard = document.getElementById('settlementCard');
    statusSelect.addEventListener('change', function () {
        settlementCard.classList.toggle('d-none', this.value !== 'settled');
    });

    flatpickr('#dp-violation-date', {
        dateFormat: 'Y-m-d',
        maxDate: 'today',
        defaultDate: document.getElementById('dp-violation-date').value || null,
        allowInput: true,
        disableMobile: true,
    });
    applyDateMask(document.getElementById('dp-violation-date'));

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

    document.addEventListener('click', function (e) {
        const img = e.target.closest('[data-lightbox]');
        if (!img) return;
        document.getElementById('lightboxImg').src = img.dataset.lightbox;
        document.getElementById('lightboxCaption').textContent = img.dataset.caption ?? '';
        new bootstrap.Modal(document.getElementById('photoLightbox')).show();
    });

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

    function toggleCitationInput(checkbox) {
        const input = document.getElementById('citation_ticket_photo');
        input.disabled = checkbox.checked;
        if (checkbox.checked) {
            input.value = '';
            document.getElementById('citationPhotoPreview').style.display = 'none';
        }
    }

    function previewReceiptPhoto(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('receiptPhotoImg').src = e.target.result;
            document.getElementById('receiptPhotoPreview').style.display = '';
        };
        reader.readAsDataURL(file);
    }

    function toggleReceiptInput(checkbox) {
        const input = document.getElementById('receipt_photo_edit');
        input.disabled = checkbox.checked;
        if (checkbox.checked) {
            input.value = '';
            document.getElementById('receiptPhotoPreview').style.display = 'none';
        }
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
    document.getElementById('violationEditForm').addEventListener('submit', function () {
        var btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Saving…';
    });
</script>
@endpush
