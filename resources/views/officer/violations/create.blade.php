@extends('layouts.mobile')
@section('title', 'Record Violation')
@section('back_url', route('officer.motorists.show', $violator))

@push('styles')
<style>.vt-select-empty { color: #9ca3af; }</style>
@endpush

@section('content')

{{-- Motorist context --}}
<div class="mob-card" style="border-left:4px solid #dc2626;">
    <div class="mob-card-body d-flex align-items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#1d4ed8,#1e40af);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.95rem;font-weight:800;color:#fff;">
            {{ strtoupper(substr($violator->first_name, 0, 1)) }}
        </div>
        <div>
            <div style="font-size:.62rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Recording for</div>
            <div style="font-size:.95rem;font-weight:800;color:#0f172a;">{{ $violator->last_name }}, {{ $violator->first_name }}</div>
        </div>
    </div>
</div>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.violations.store', $violator) }}" enctype="multipart/form-data">
            @csrf

            @if($errors->any())
            <div class="mob-alert mob-alert-danger">
                <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            {{-- Violation --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Violation</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Violation Type <span class="text-danger">*</span></label>
                <select name="violation_type_id" id="violation_type_id"
                        class="form-select mob-select @error('violation_type_id') is-invalid @enderror" required>
                    <option value="">— Select violation —</option>
                    @foreach($violationTypes as $vt)
                    <option value="{{ $vt->id }}" data-fine="{{ $vt->fine_amount }}"
                            {{ old('violation_type_id') == $vt->id ? 'selected' : '' }}>
                        {{ $vt->name }}
                    </option>
                    @endforeach
                </select>
                @error('violation_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div id="fine-preview" style="display:none;margin-top:.45rem;padding:.45rem .75rem;background:#fef2f2;border-radius:8px;font-size:.8rem;font-weight:700;color:#b91c1c;border:1px solid #fca5a5;">
                    <i class="ph-fill ph-money me-1"></i>Fine: ₱<span id="fine-amount">0.00</span>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-7">
                    <label class="mob-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_violation"
                           value="{{ old('date_of_violation', now()->format('Y-m-d')) }}"
                           max="{{ now()->format('Y-m-d') }}" required
                           class="form-control mob-input @error('date_of_violation') is-invalid @enderror">
                    @error('date_of_violation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-5">
                    <label class="mob-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select mob-select @error('status') is-invalid @enderror" required>
                        <option value="pending" {{ old('status','pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="settled" {{ old('status') === 'settled'           ? 'selected' : '' }}>Settled</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Location</label>
                <input type="text" name="location" value="{{ old('location') }}"
                       class="form-control mob-input @error('location') is-invalid @enderror"
                       placeholder="Street / Barangay / Municipality">
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Ticket Number</label>
                <input type="text" name="ticket_number" value="{{ old('ticket_number') }}"
                       class="form-control mob-input @error('ticket_number') is-invalid @enderror"
                       placeholder="e.g. TMR-2026-001">
                @error('ticket_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if($relatedIncidents->isNotEmpty())
            <div class="mb-3">
                <label class="mob-label">Linked Incident</label>
                <select name="incident_id" class="form-select mob-select @error('incident_id') is-invalid @enderror">
                    <option value="">— None / not linked —</option>
                    @foreach($relatedIncidents as $incident)
                    <option value="{{ $incident->id }}" {{ old('incident_id') == $incident->id ? 'selected' : '' }}>
                        {{ $incident->incident_number }} — {{ optional($incident->date_of_incident)->format('M d, Y') ?? 'No date' }}
                    </option>
                    @endforeach
                </select>
                @error('incident_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <span class="mob-hint">Link this violation to an existing incident involving this motorist.</span>
            </div>
            @endif

            {{-- Vehicle --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Vehicle Involved</span>
                <span class="mob-form-divider-line"></span>
            </div>

            @php
                $ownVehicles = $allVehicles->where('violator_id', $violator->id);
                $otherVehicles = $allVehicles->where('violator_id', '!=', $violator->id);
            @endphp
            @if($allVehicles->isNotEmpty())
            <div class="mb-2">
                <label class="mob-label">Registered Vehicle</label>
                <select name="vehicle_id" id="vehicle_id" class="form-select mob-select">
                    <option value="">— Manual entry below —</option>
                    @if($ownVehicles->isNotEmpty())
                    <optgroup label="Driver's own vehicles">
                        @foreach($ownVehicles as $veh)
                        <option value="{{ $veh->id }}"
                                data-owner="{{ $veh->owner_name ?: $violator->full_name }}"
                                {{ old('vehicle_id') == $veh->id ? 'selected' : '' }}>
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
                                {{ old('vehicle_id') == $veh->id ? 'selected' : '' }}>
                            {{ $veh->plate_number }}
                            @if($veh->make || $veh->model) — {{ trim($veh->make . ' ' . $veh->model) }} @endif
                            @if($veh->vehicle_type) ({{ $veh->vehicle_type }}) @endif
                            @if($veh->violator) — {{ $veh->violator->full_name }} @endif
                        </option>
                        @endforeach
                    </optgroup>
                    @endif
                </select>
                <span class="mob-hint">Pick from the system if the vehicle is already registered, even if it belongs to another owner.</span>
            </div>
            @else
            <input type="hidden" name="vehicle_id" value="">
            @endif

            <div id="vehicle-manual"@if(old('vehicle_id')) style="display:none;"@endif>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="mob-label">Plate No.</label>
                        <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}"
                               class="form-control mob-input" placeholder="e.g. ABC 1234">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Type</label>
                        <select name="vehicle_type" class="form-select mob-select {{ old('vehicle_type') ? '' : 'vt-select-empty' }}"
                                onchange="this.classList.toggle('vt-select-empty',!this.value)">
                            <option value="">— Select —</option>
                            <option value="MV" {{ old('vehicle_type') === 'MV' ? 'selected' : '' }}>MV</option>
                            <option value="MC" {{ old('vehicle_type') === 'MC' ? 'selected' : '' }}>MC</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Color</label>
                        <input type="text" name="vehicle_color" value="{{ old('vehicle_color') }}"
                               class="form-control mob-input" placeholder="e.g. Red">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Make</label>
                        <input type="text" name="vehicle_make" value="{{ old('vehicle_make') }}"
                               class="form-control mob-input" placeholder="e.g. Honda">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Model</label>
                        <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}"
                               class="form-control mob-input" placeholder="e.g. Click 125">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">OR Number</label>
                        <input type="text" name="vehicle_or_number" value="{{ old('vehicle_or_number') }}"
                               class="form-control mob-input" placeholder="Official Receipt #">
                    </div>
                    <div class="col-6">
                        <label class="mob-label">CR Number</label>
                        <input type="text" name="vehicle_cr_number" value="{{ old('vehicle_cr_number') }}"
                               class="form-control mob-input" placeholder="Certificate of Reg. #">
                    </div>
                    <div class="col-12">
                        <label class="mob-label">Chassis Number</label>
                        <input type="text" name="vehicle_chassis" value="{{ old('vehicle_chassis') }}"
                               class="form-control mob-input" placeholder="Frame / chassis number">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Registered Owner Name</label>
                <input type="text" name="vehicle_owner_name" id="vehicle_owner_name" value="{{ old('vehicle_owner_name') }}"
                       class="form-control mob-input @error('vehicle_owner_name') is-invalid @enderror"
                       placeholder="Leave blank if same as driver">
                @error('vehicle_owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <span class="mob-hint">Useful for borrowed or company-owned vehicles so the operator side can show the owner correctly.</span>
            </div>

            {{-- Documentation --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Documentation</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Citation Ticket Photo</label>
                <div id="picker-citation"></div>
                @error('citation_ticket_photo')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Vehicle Photos <span style="font-size:.68rem;color:#94a3b8;">(up to 4)</span></label>
                <div id="picker-veh-photos"></div>
                @error('photos')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="mob-label">Notes</label>
                <textarea name="notes" rows="2"
                          class="form-control mob-input @error('notes') is-invalid @enderror"
                          style="min-height:auto;resize:none;"
                          placeholder="Optional remarks...">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="mob-btn-primary mob-btn-danger mb-2" id="violationSubmitBtn">
                <i class="ph-bold ph-check"></i> Save Violation
            </button>
            <a href="{{ route('officer.motorists.show', $violator) }}" class="mob-btn-outline">
                <i class="ph ph-x-circle"></i> Cancel
            </a>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initPhotoPicker('picker-citation',   'citation_ticket_photo', { multiple: false });
    initPhotoPicker('picker-veh-photos', 'photos',                { multiple: true  });
});

document.getElementById('violation_type_id').addEventListener('change', function () {
    var opt = this.options[this.selectedIndex];
    var fine = opt.dataset.fine;
    var preview = document.getElementById('fine-preview');
    if (fine && parseFloat(fine) > 0) {
        document.getElementById('fine-amount').textContent = parseFloat(fine).toLocaleString('en-PH', {minimumFractionDigits: 2});
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});
document.getElementById('violation_type_id').dispatchEvent(new Event('change'));

var vehicleSelect = document.getElementById('vehicle_id');
var vehicleManual = document.getElementById('vehicle-manual');
var vehicleOwner = document.getElementById('vehicle_owner_name');
if (vehicleSelect && vehicleManual) {
    vehicleSelect.addEventListener('change', function() {
        vehicleManual.style.display = this.value ? 'none' : '';
        if (vehicleOwner && this.value) {
            var opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.owner && !vehicleOwner.value.trim()) {
                vehicleOwner.value = opt.dataset.owner;
            }
        }
    });
}
if (vehicleSelect) {
    vehicleSelect.dispatchEvent(new Event('change'));
}

// Double-submit protection
document.getElementById('violationSubmitBtn').closest('form').addEventListener('submit', function () {
    var btn = document.getElementById('violationSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-hourglass"></i> Saving…';
});
</script>
@endpush
