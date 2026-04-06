@extends('layouts.mobile')
@section('title', 'Edit Violation')
@section('back_url', route('officer.violations.show', $violation))

@section('content')

@php
    $ownVehicles = $allVehicles->where('violator_id', $violation->violator_id);
    $otherVehicles = $allVehicles->where('violator_id', '!=', $violation->violator_id);
    $manualVehicle = old('vehicle_plate', $violation->vehicle_plate) || (!old('vehicle_id', $violation->vehicle_id) && !$violation->vehicle);
@endphp

<div class="mob-card" style="border-left:4px solid #dc2626;">
    <div class="mob-card-body d-flex align-items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#1d4ed8,#1e40af);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.95rem;font-weight:800;color:#fff;">
            {{ strtoupper(substr($violation->violator->first_name, 0, 1)) }}
        </div>
        <div>
            <div style="font-size:.62rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Editing violation for</div>
            <div style="font-size:.95rem;font-weight:800;color:#0f172a;">{{ $violation->violator->last_name }}, {{ $violation->violator->first_name }}</div>
        </div>
    </div>
</div>

@if($violation->vehiclePhotos->isNotEmpty())
<div class="mob-card">
    <div class="mob-section-title">Current Vehicle Photos</div>
    <div class="mob-card-body pt-1">
        <div class="row g-2">
            @foreach($violation->vehiclePhotos as $photo)
            <div class="col-6">
                <img src="{{ uploaded_file_url($photo->photo) }}"
                     class="mob-photo-thumb"
                     data-full="{{ uploaded_file_url($photo->photo) }}"
                     data-caption="Violation vehicle photo"
                     style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);cursor:zoom-in;"
                     alt="Vehicle photo">
            </div>
            @endforeach
        </div>
        <div style="font-size:.7rem;color:#94a3b8;margin-top:.45rem;">Existing photos stay linked to this violation. You can add more below until the 4-photo limit is reached.</div>
    </div>
</div>
@endif

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.violations.update', $violation) }}" enctype="multipart/form-data" id="officerViolationEditForm">
            @csrf
            @method('PUT')

            @if($errors->any())
            <div class="mob-alert mob-alert-danger">
                <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Violation</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Violation Type <span class="text-danger">*</span></label>
                <select name="violation_type_id" class="form-select mob-select @error('violation_type_id') is-invalid @enderror" required>
                    <option value="">— Select violation —</option>
                    @foreach($violationTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('violation_type_id', $violation->violation_type_id) == $type->id)>
                        {{ $type->name }}@if($type->fine_amount > 0) — ₱{{ number_format($type->fine_amount, 2) }}@endif
                    </option>
                    @endforeach
                </select>
                @error('violation_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_violation" required max="{{ date('Y-m-d') }}"
                           value="{{ old('date_of_violation', $violation->date_of_violation?->format('Y-m-d')) }}"
                           class="form-control mob-input @error('date_of_violation') is-invalid @enderror">
                    @error('date_of_violation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="mob-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select mob-select @error('status') is-invalid @enderror" required>
                        <option value="pending" {{ old('status', $violation->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="settled" {{ old('status', $violation->status) === 'settled' ? 'selected' : '' }}>Settled</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($relatedIncidents->isNotEmpty() || $violation->incident_id)
            <div class="mb-3">
                <label class="mob-label">Linked Incident</label>
                <select name="incident_id" class="form-select mob-select @error('incident_id') is-invalid @enderror">
                    <option value="">— None / not linked —</option>
                    @foreach($relatedIncidents as $incident)
                    <option value="{{ $incident->id }}" @selected(old('incident_id', $violation->incident_id) == $incident->id)>
                        {{ $incident->incident_number }} — {{ optional($incident->date_of_incident)->format('M d, Y') ?? 'No date' }}
                    </option>
                    @endforeach
                </select>
                @error('incident_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endif

            <div class="mb-3">
                <label class="mob-label">Location</label>
                <input type="text" name="location" value="{{ old('location', $violation->location) }}"
                       class="form-control mob-input @error('location') is-invalid @enderror"
                       placeholder="Street / Barangay / Municipality">
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Ticket Number</label>
                <input type="text" name="ticket_number" value="{{ old('ticket_number', $violation->ticket_number) }}"
                       class="form-control mob-input @error('ticket_number') is-invalid @enderror">
                @error('ticket_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Vehicle Involved</span>
                <span class="mob-form-divider-line"></span>
            </div>

            @if($allVehicles->isNotEmpty())
            <div class="mb-3">
                <label class="mob-label">Registered Vehicle</label>
                <select name="vehicle_id" id="vehicle_id" class="form-select mob-select @error('vehicle_id') is-invalid @enderror">
                    <option value="">— Manual entry below —</option>
                    @if($ownVehicles->isNotEmpty())
                    <optgroup label="Driver's own vehicles">
                        @foreach($ownVehicles as $veh)
                        <option value="{{ $veh->id }}"
                                data-owner="{{ $veh->owner_name ?: $violation->violator->full_name }}"
                                @selected(old('vehicle_id', $violation->vehicle_id) == $veh->id)>
                            {{ $veh->plate_number }}
                            @if($veh->make || $veh->model) — {{ trim($veh->make . ' ' . $veh->model) }} @endif
                            @if($veh->vehicle_type) ({{ $veh->vehicle_type }}) @endif
                        </option>
                        @endforeach
                    </optgroup>
                    @endif
                    @if($otherVehicles->isNotEmpty())
                    <optgroup label="Borrowed / other vehicles">
                        @foreach($otherVehicles as $veh)
                        <option value="{{ $veh->id }}"
                                data-owner="{{ $veh->owner_name ?: ($veh->violator?->full_name ?? '') }}"
                                @selected(old('vehicle_id', $violation->vehicle_id) == $veh->id)>
                            {{ $veh->plate_number }}
                            @if($veh->make || $veh->model) — {{ trim($veh->make . ' ' . $veh->model) }} @endif
                            @if($veh->vehicle_type) ({{ $veh->vehicle_type }}) @endif
                            @if($veh->violator) — {{ $veh->violator->full_name }} @endif
                        </option>
                        @endforeach
                    </optgroup>
                    @endif
                </select>
                @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endif

            <div id="vehicle-manual" @if(!$manualVehicle) style="display:none;" @endif>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="mob-label">Plate No.</label>
                        <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate', $violation->vehicle_plate) }}"
                               class="form-control mob-input" placeholder="e.g. ABC 1234">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Color</label>
                        <input type="text" name="vehicle_color" value="{{ old('vehicle_color', $violation->vehicle_color) }}"
                               class="form-control mob-input" placeholder="e.g. Red">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Make</label>
                        <input type="text" name="vehicle_make" value="{{ old('vehicle_make', $violation->vehicle_make) }}"
                               class="form-control mob-input" placeholder="e.g. Honda">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Model</label>
                        <input type="text" name="vehicle_model" value="{{ old('vehicle_model', $violation->vehicle_model) }}"
                               class="form-control mob-input" placeholder="e.g. Click 125">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">OR Number</label>
                        <input type="text" name="vehicle_or_number" value="{{ old('vehicle_or_number', $violation->vehicle_or_number) }}"
                               class="form-control mob-input" placeholder="Official Receipt #">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">CR Number</label>
                        <input type="text" name="vehicle_cr_number" value="{{ old('vehicle_cr_number', $violation->vehicle_cr_number) }}"
                               class="form-control mob-input" placeholder="Certificate of Reg. #">
                    </div>
                    <div class="col-12">
                        <label class="mob-label">Chassis Number</label>
                        <input type="text" name="vehicle_chassis" value="{{ old('vehicle_chassis', $violation->vehicle_chassis) }}"
                               class="form-control mob-input" placeholder="Frame / chassis number">
                    </div>
                    <div class="col-12">
                        <label class="mob-label">Add Vehicle Photos</label>
                        <div id="picker-veh-photos"></div>
                        @error('photos')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
                        <span class="mob-hint">You can add more photos until the 4-photo limit is reached.</span>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Registered Owner Name</label>
                <input type="text" name="vehicle_owner_name" id="vehicle_owner_name"
                       value="{{ old('vehicle_owner_name', $violation->vehicle_owner_name) }}"
                       class="form-control mob-input @error('vehicle_owner_name') is-invalid @enderror"
                       placeholder="Leave blank if same as driver">
                @error('vehicle_owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Documentation</span>
                <span class="mob-form-divider-line"></span>
            </div>

            @if($violation->citation_ticket_photo)
            <div class="mb-3">
                <label class="mob-label">Current Citation Ticket</label>
                <img src="{{ uploaded_file_url($violation->citation_ticket_photo) }}"
                     style="max-width:100%;max-height:180px;border-radius:12px;object-fit:contain;border:1px solid #e2e8f0;"
                     alt="Citation Ticket">
                <label style="display:flex;align-items:center;gap:.45rem;font-size:.76rem;color:#dc2626;margin-top:.45rem;">
                    <input type="checkbox" name="remove_citation_photo" value="1"> Remove current citation photo
                </label>
            </div>
            @endif

            <div class="mb-3">
                <label class="mob-label">{{ $violation->citation_ticket_photo ? 'Replace' : 'Upload' }} Citation Ticket Photo</label>
                <div id="picker-citation"></div>
                @error('citation_ticket_photo')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>

            <div id="settlement-fields" @if(old('status', $violation->status) !== 'settled') style="display:none;" @endif>
                <div class="mob-form-divider">
                    <span class="mob-form-divider-text">Settlement</span>
                    <span class="mob-form-divider-line"></span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="mob-label">OR Number</label>
                        <input type="text" name="or_number" value="{{ old('or_number', $violation->or_number) }}"
                               class="form-control mob-input @error('or_number') is-invalid @enderror">
                        @error('or_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Cashier Name</label>
                        <input type="text" name="cashier_name" value="{{ old('cashier_name', $violation->cashier_name) }}"
                               class="form-control mob-input @error('cashier_name') is-invalid @enderror">
                        @error('cashier_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if($violation->receipt_photo)
                <div class="mb-3">
                    <label class="mob-label">Current Receipt</label>
                    <img src="{{ uploaded_file_url($violation->receipt_photo) }}"
                         style="max-width:100%;max-height:180px;border-radius:12px;object-fit:contain;border:1px solid #e2e8f0;"
                         alt="Receipt Photo">
                    <label style="display:flex;align-items:center;gap:.45rem;font-size:.76rem;color:#dc2626;margin-top:.45rem;">
                        <input type="checkbox" name="remove_receipt_photo" value="1"> Remove current receipt photo
                    </label>
                </div>
                @endif

                <div class="mb-3">
                    <label class="mob-label">{{ $violation->receipt_photo ? 'Replace' : 'Upload' }} Receipt Photo</label>
                    <div id="picker-receipt"></div>
                    @error('receipt_photo')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="mob-label">Notes</label>
                <textarea name="notes" rows="3" class="form-control mob-input @error('notes') is-invalid @enderror"
                          style="min-height:auto;resize:none;"
                          placeholder="Optional remarks...">{{ old('notes', $violation->notes) }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="mob-btn-primary mob-btn-danger mb-2" id="submitBtn">
                <i class="ph-bold ph-check"></i> Save Changes
            </button>
            <a href="{{ route('officer.violations.show', $violation) }}" class="mob-btn-outline">
                <i class="ph ph-x-circle"></i> Cancel
            </a>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initPhotoPicker('picker-veh-photos', 'photos',                { multiple: true  });
    initPhotoPicker('picker-citation',   'citation_ticket_photo', { multiple: false });
    initPhotoPicker('picker-receipt',    'receipt_photo',         { multiple: false });
});

(function () {
    const vehicleSelect = document.getElementById('vehicle_id');
    const vehicleManual = document.getElementById('vehicle-manual');
    const vehicleOwner = document.getElementById('vehicle_owner_name');
    const statusSelect = document.getElementById('status');
    const settlementFields = document.getElementById('settlement-fields');

    function syncVehicleMode() {
        if (!vehicleSelect || !vehicleManual) return;
        vehicleManual.style.display = vehicleSelect.value ? 'none' : '';

        if (vehicleOwner && vehicleSelect.value && !vehicleOwner.value.trim()) {
            const opt = vehicleSelect.options[vehicleSelect.selectedIndex];
            if (opt && opt.dataset.owner) {
                vehicleOwner.value = opt.dataset.owner;
            }
        }
    }

    function syncSettlementFields() {
        if (!statusSelect || !settlementFields) return;
        settlementFields.style.display = statusSelect.value === 'settled' ? '' : 'none';
    }

    if (vehicleSelect) {
        vehicleSelect.addEventListener('change', syncVehicleMode);
        syncVehicleMode();
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', syncSettlementFields);
        syncSettlementFields();
    }

    document.getElementById('officerViolationEditForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-hourglass"></i> Saving…';
    });
})();
</script>
@endpush
