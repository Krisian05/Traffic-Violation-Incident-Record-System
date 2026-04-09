@extends('layouts.mobile')
@section('title', 'Record Incident')
@section('back_url', route('officer.incidents.index'))

@section('content')
@php
    $blankMotorist = [
        'violator_id' => '',
        'motorist_name' => '',
        'motorist_license' => '',
        'license_type' => '',
        'license_restriction' => [],
        'license_expiry_date' => '',
        'motorist_contact' => '',
        'motorist_address' => '',
        'vehicle_id' => '',
        'vehicle_plate' => '',
        'vehicle_type_manual' => '',
        'vehicle_make' => '',
        'vehicle_model' => '',
        'vehicle_color' => '',
        'vehicle_or_number' => '',
        'vehicle_cr_number' => '',
        'vehicle_chassis' => '',
        'incident_charge_type_id' => '',
        'notes' => '',
    ];
    $oldMotorists = old('motorists', [$blankMotorist, $blankMotorist]);
    $violatorsForJs = $violators->map(fn($v) => [
        'id' => $v->id,
        'label' => $v->last_name . ', ' . $v->first_name . ($v->license_number ? ' (' . $v->license_number . ')' : ''),
        'name' => $v->full_name,
        'license' => $v->license_number,
        'type' => $v->license_type,
        'expiry' => $v->license_expiry_date?->format('Y-m-d'),
        'restrictions' => $v->license_restriction ? array_values(array_filter(array_map('trim', explode(',', $v->license_restriction)))) : [],
        'contact' => $v->contact_number,
        'address' => $v->temporary_address ?: $v->permanent_address,
    ])->values();
    $vehiclesForJs = $vehiclesByOwner->map(fn($group) => $group->values()->map(fn($vehicle) => [
        'id' => $vehicle->id,
        'plate' => $vehicle->plate_number,
        'type' => $vehicle->vehicle_type,
        'make' => $vehicle->make,
        'model' => $vehicle->model,
        'color' => $vehicle->color,
        'or' => $vehicle->or_number,
        'cr' => $vehicle->cr_number,
        'chassis' => $vehicle->chassis_number,
    ]));
    $chargeTypesForJs = $chargeTypes->map(fn($type) => ['id' => $type->id, 'name' => $type->name])->values();
@endphp

<style>
.incident-row{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:14px;padding:.9rem}
.incident-row+.incident-row{margin-top:.9rem}
.incident-row-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem}
.incident-row-badge{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:9px;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;font-size:.78rem;font-weight:800}
.incident-chips{display:flex;flex-wrap:wrap;gap:.35rem}
.incident-chip{display:inline-flex;align-items:center;gap:.32rem;padding:.28rem .58rem;border-radius:999px;border:1px solid #cbd5e1;background:#fff;font-size:.73rem;font-weight:700;color:#334155}
.incident-media-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.6rem;margin-top:.7rem}
.incident-media-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:.6rem}
.incident-media-card img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:10px;margin-bottom:.45rem}
</style>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.incidents.store') }}" enctype="multipart/form-data" id="incidentForm" data-offline-sync="true" data-offline-record-type="incident-create" data-offline-label="Incident">
            @csrf

            @if($errors->any())
            <div class="mob-alert mob-alert-danger">
                <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Incident Info</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-7">
                    <label class="mob-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_incident" value="{{ old('date_of_incident', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required class="form-control mob-input @error('date_of_incident') is-invalid @enderror">
                    @error('date_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-5">
                    <label class="mob-label">Time</label>
                    <input type="time" name="time_of_incident" value="{{ old('time_of_incident') }}" class="form-control mob-input @error('time_of_incident') is-invalid @enderror">
                    @error('time_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                @include('partials.location-selector', ['fieldName' => 'location', 'required' => true, 'label' => 'Location', 'inputSize' => ''])
            </div>

            <div class="mb-4">
                <label class="mob-label">Description</label>
                <textarea name="description" rows="3" class="form-control mob-input @error('description') is-invalid @enderror" style="min-height:auto;resize:none;" placeholder="Brief description of what happened...">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Motorists Involved</span>
                <span class="mob-form-divider-line"></span>
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-bottom:.8rem;">Minimum 2 motorists required. Officers can now link registered motorists and vehicles, plus record the same key details used by operators.</div>

            <div id="motorists-container"></div>

            <button type="button" id="add-motorist" style="display:flex;align-items:center;justify-content:center;gap:.4rem;width:100%;min-height:42px;border-radius:10px;border:1.5px dashed #93c5fd;background:transparent;color:#1d4ed8;font-weight:700;font-size:.85rem;cursor:pointer;margin:1rem 0 1.1rem;">
                <i class="ph ph-plus-circle"></i> Add Another Motorist
            </button>

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Scene Photos</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Incident Photos <span style="font-size:.68rem;color:#94a3b8;">(up to 6)</span></label>
                <div id="picker-incident-photos"></div>
                @error('incident_photos')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Additional Evidence</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-4">
                <label class="mob-label">Media / Documents</label>
                <input type="file" name="media[]" id="mediaInput" accept="image/jpg,image/jpeg,image/png,application/pdf" multiple class="form-control mob-input">
                <div style="font-size:.72rem;color:#94a3b8;margin-top:.4rem;">Add tickets, documents, or extra evidence. Each selected file gets its own type and caption.</div>
                <div id="media-preview-container" class="incident-media-grid"></div>
            </div>

            <button type="submit" class="mob-btn-primary mob-btn-danger mb-2" id="submitBtn">
                <i class="ph-bold ph-check"></i> Save Incident
            </button>
            <a href="{{ route('officer.incidents.index') }}" class="mob-btn-outline">
                <i class="ph ph-x-circle"></i> Cancel
            </a>
        </form>
    </div>
</div>

<div id="incident-page-data"
     data-old-motorists='@json($oldMotorists)'
     data-violators='@json($violatorsForJs)'
     data-vehicles='@json($vehiclesForJs)'
     data-charge-types='@json($chargeTypesForJs)'
     hidden></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initPhotoPicker('picker-incident-photos', 'incident_photos[]', { multiple: true });
});

(function () {
    const pageData = document.getElementById('incident-page-data');
    const oldMotorists = JSON.parse(pageData.dataset.oldMotorists || '[]');
    const violators = JSON.parse(pageData.dataset.violators || '[]');
    const vehiclesByOwner = JSON.parse(pageData.dataset.vehicles || '{}');
    const chargeTypes = JSON.parse(pageData.dataset.chargeTypes || '[]');
    const restrictions = ['A','A1','B','B1','B2','C','D','BE','CE'];
    const container = document.getElementById('motorists-container');
    let motoristIndex = 0;

    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function optionHtml(list, selected, formatter) {
        return list.map((item) => formatter(item, String(selected) === String(item.id))).join('');
    }

    function restrictionHtml(index, selectedValues) {
        const selected = new Set((selectedValues || []).map(String));
        return restrictions.map((code) =>
            '<label class="incident-chip"><input type="checkbox" name="motorists[' + index + '][license_restriction][]" value="' + code + '"' + (selected.has(code) ? ' checked' : '') + '>' + code + '</label>'
        ).join('');
    }

    function vehicleSelectHtml(index, violatorId, selectedVehicleId) {
        const vehicles = vehiclesByOwner[String(violatorId)] || [];
        const options = optionHtml(vehicles, selectedVehicleId, (vehicle, isSelected) =>
            '<option value="' + escapeHtml(vehicle.id) + '"' +
                ' data-plate="' + escapeHtml(vehicle.plate) + '"' +
                ' data-type="' + escapeHtml(vehicle.type) + '"' +
                ' data-make="' + escapeHtml(vehicle.make) + '"' +
                ' data-model="' + escapeHtml(vehicle.model) + '"' +
                ' data-color="' + escapeHtml(vehicle.color) + '"' +
                ' data-or="' + escapeHtml(vehicle.or) + '"' +
                ' data-cr="' + escapeHtml(vehicle.cr) + '"' +
                ' data-chassis="' + escapeHtml(vehicle.chassis) + '"' +
                (isSelected ? ' selected' : '') + '>' +
                escapeHtml(vehicle.plate || 'Unknown plate') +
                (vehicle.type ? ' (' + escapeHtml(vehicle.type) + ')' : '') +
                ((vehicle.make || vehicle.model) ? ' — ' + escapeHtml(((vehicle.make || '') + ' ' + (vehicle.model || '')).trim()) : '') +
            '</option>'
        );
        return '<option value="">— Manual vehicle details below —</option>' + options;
    }

    function buildMotoristRow(index, values = {}) {
        const rowNumber = container.querySelectorAll('.incident-row').length + 1;
        const violatorOptions = '<option value="">— Unregistered / enter details below —</option>' + optionHtml(violators, values.violator_id, (violator, isSelected) =>
            '<option value="' + escapeHtml(violator.id) + '"' +
                ' data-name="' + escapeHtml(violator.name) + '"' +
                ' data-license="' + escapeHtml(violator.license) + '"' +
                ' data-type="' + escapeHtml(violator.type) + '"' +
                ' data-expiry="' + escapeHtml(violator.expiry) + '"' +
                ' data-contact="' + escapeHtml(violator.contact) + '"' +
                ' data-address="' + escapeHtml(violator.address) + '"' +
                ' data-restrictions="' + escapeHtml(JSON.stringify(violator.restrictions || [])) + '"' +
                (isSelected ? ' selected' : '') + '>' + escapeHtml(violator.label) + '</option>'
        );
        const chargeOptions = '<option value="">— None —</option>' + optionHtml(chargeTypes, values.incident_charge_type_id, (type, isSelected) =>
            '<option value="' + escapeHtml(type.id) + '"' + (isSelected ? ' selected' : '') + '>' + escapeHtml(type.name) + '</option>'
        );

        return `
            <div class="incident-row" data-index="${index}">
                <div class="incident-row-head">
                    <div style="display:flex;align-items:center;gap:.65rem;">
                        <span class="incident-row-badge">${rowNumber}</span>
                        <div class="incident-row-title" style="font-size:.76rem;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:.06em;">Motorist #${rowNumber}</div>
                    </div>
                    <button type="button" class="remove-motorist" style="border:none;background:none;color:#dc2626;font-size:.76rem;font-weight:700;${rowNumber <= 2 ? 'display:none;' : ''}">
                        <i class="ph ph-x-circle me-1"></i>Remove
                    </button>
                </div>

                <div class="mb-3">
                    <label class="mob-label">Registered Motorist</label>
                    <select name="motorists[${index}][violator_id]" class="form-select mob-select violator-select">${violatorOptions}</select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-12"><label class="mob-label">Full Name</label><input type="text" name="motorists[${index}][motorist_name]" value="${escapeHtml(values.motorist_name || '')}" class="form-control mob-input motorist-name-field" placeholder="Enter full name if unregistered"></div>
                    <div class="col-7"><label class="mob-label">License Number</label><input type="text" name="motorists[${index}][motorist_license]" value="${escapeHtml(values.motorist_license || '')}" class="form-control mob-input motorist-license-field" placeholder="e.g. N01-23-456789"></div>
                    <div class="col-5"><label class="mob-label">License Type</label><select name="motorists[${index}][license_type]" class="form-select mob-select motorist-license-type-field"><option value="">— Select —</option><option value="Professional"${values.license_type === 'Professional' ? ' selected' : ''}>Professional</option><option value="Non-Professional"${values.license_type === 'Non-Professional' ? ' selected' : ''}>Non-Professional</option><option value="Student Permit"${values.license_type === 'Student Permit' ? ' selected' : ''}>Student Permit</option></select></div>
                    <div class="col-6"><label class="mob-label">License Expiry</label><input type="date" name="motorists[${index}][license_expiry_date]" value="${escapeHtml(values.license_expiry_date || '')}" class="form-control mob-input motorist-expiry-field"></div>
                    <div class="col-6"><label class="mob-label">Contact Number</label><input type="text" name="motorists[${index}][motorist_contact]" value="${escapeHtml(values.motorist_contact || '')}" class="form-control mob-input motorist-contact-field" placeholder="09XX-XXX-XXXX"></div>
                    <div class="col-12"><label class="mob-label">Address</label><input type="text" name="motorists[${index}][motorist_address]" value="${escapeHtml(values.motorist_address || '')}" class="form-control mob-input motorist-address-field" placeholder="Current address"></div>
                </div>

                <div class="mb-3">
                    <label class="mob-label d-block mb-2">Restriction Codes</label>
                    <div class="incident-chips">${restrictionHtml(index, values.license_restriction || [])}</div>
                </div>

                <div class="mob-form-divider"><span class="mob-form-divider-text">Vehicle</span><span class="mob-form-divider-line"></span></div>
                <div class="mb-3">
                    <label class="mob-label">Registered Vehicle</label>
                    <select name="motorists[${index}][vehicle_id]" class="form-select mob-select vehicle-select">${vehicleSelectHtml(index, values.violator_id || '', values.vehicle_id || '')}</select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6"><label class="mob-label">Plate No.</label><input type="text" name="motorists[${index}][vehicle_plate]" value="${escapeHtml(values.vehicle_plate || '')}" class="form-control mob-input vehicle-plate-field" placeholder="e.g. ABC 1234"></div>
                    <div class="col-6"><label class="mob-label">Vehicle Type</label><select name="motorists[${index}][vehicle_type_manual]" class="form-select mob-select vehicle-type-field"><option value="">— Select —</option><option value="MV"${values.vehicle_type_manual === 'MV' ? ' selected' : ''}>MV</option><option value="MC"${values.vehicle_type_manual === 'MC' ? ' selected' : ''}>MC</option></select></div>
                    <div class="col-6"><label class="mob-label">Make</label><input type="text" name="motorists[${index}][vehicle_make]" value="${escapeHtml(values.vehicle_make || '')}" class="form-control mob-input vehicle-make-field" placeholder="e.g. Honda"></div>
                    <div class="col-6"><label class="mob-label">Model</label><input type="text" name="motorists[${index}][vehicle_model]" value="${escapeHtml(values.vehicle_model || '')}" class="form-control mob-input vehicle-model-field" placeholder="e.g. Click 125"></div>
                    <div class="col-6"><label class="mob-label">Color</label><input type="text" name="motorists[${index}][vehicle_color]" value="${escapeHtml(values.vehicle_color || '')}" class="form-control mob-input vehicle-color-field" placeholder="e.g. Red"></div>
                    <div class="col-6"><label class="mob-label">OR Number</label><input type="text" name="motorists[${index}][vehicle_or_number]" value="${escapeHtml(values.vehicle_or_number || '')}" class="form-control mob-input vehicle-or-field" placeholder="Official Receipt #"></div>
                    <div class="col-6"><label class="mob-label">CR Number</label><input type="text" name="motorists[${index}][vehicle_cr_number]" value="${escapeHtml(values.vehicle_cr_number || '')}" class="form-control mob-input vehicle-cr-field" placeholder="Certificate of Reg. #"></div>
                    <div class="col-6"><label class="mob-label">Chassis No.</label><input type="text" name="motorists[${index}][vehicle_chassis]" value="${escapeHtml(values.vehicle_chassis || '')}" class="form-control mob-input vehicle-chassis-field" placeholder="Frame / chassis number"></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="mob-label">ID / License Photo</label>
                        <div id="picker-id-${index}" class="picker-placeholder" data-name="motorist_id_photos[${index}]" data-multiple="false"></div>
                    </div>
                    <div class="col-6">
                        <label class="mob-label">Vehicle Photos</label>
                        <div id="picker-vp-${index}" class="picker-placeholder" data-name="motorist_photos[${index}][]" data-multiple="true"></div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6"><label class="mob-label">Charge / Offense</label><select name="motorists[${index}][incident_charge_type_id]" class="form-select mob-select">${chargeOptions}</select></div>
                    <div class="col-6"><label class="mob-label">Notes</label><input type="text" name="motorists[${index}][notes]" value="${escapeHtml(values.notes || '')}" class="form-control mob-input" placeholder="Optional remarks"></div>
                </div>
            </div>`;
    }

    function renumberRows() {
        container.querySelectorAll('.incident-row').forEach((row, index) => {
            row.querySelector('.incident-row-badge').textContent = index + 1;
            row.querySelector('.incident-row-title').textContent = 'Motorist #' + (index + 1);
            const btn = row.querySelector('.remove-motorist');
            btn.style.display = index < 2 ? 'none' : '';
        });
    }

    function applyViolatorData(row, option) {
        if (!option || !option.value) {
            row.querySelector('.vehicle-select').innerHTML = vehicleSelectHtml(row.dataset.index, '', '');
            return;
        }
        if (!row.querySelector('.motorist-name-field').value.trim()) row.querySelector('.motorist-name-field').value = option.dataset.name || '';
        if (!row.querySelector('.motorist-license-field').value.trim()) row.querySelector('.motorist-license-field').value = option.dataset.license || '';
        if (!row.querySelector('.motorist-license-type-field').value) row.querySelector('.motorist-license-type-field').value = option.dataset.type || '';
        if (!row.querySelector('.motorist-expiry-field').value) row.querySelector('.motorist-expiry-field').value = option.dataset.expiry || '';
        if (!row.querySelector('.motorist-contact-field').value.trim()) row.querySelector('.motorist-contact-field').value = option.dataset.contact || '';
        if (!row.querySelector('.motorist-address-field').value.trim()) row.querySelector('.motorist-address-field').value = option.dataset.address || '';
        if (row.querySelectorAll('input[type=\"checkbox\"]:checked').length === 0 && option.dataset.restrictions) {
            try {
                const wanted = new Set(JSON.parse(option.dataset.restrictions).map(String));
                row.querySelectorAll('input[type=\"checkbox\"]').forEach((input) => input.checked = wanted.has(String(input.value)));
            } catch (e) {}
        }
        row.querySelector('.vehicle-select').innerHTML = vehicleSelectHtml(row.dataset.index, option.value, row.querySelector('.vehicle-select').value);
    }

    function applyVehicleData(row, option) {
        if (!option || !option.value) return;
        const mappings = [
            ['.vehicle-plate-field', 'plate'],
            ['.vehicle-type-field', 'type'],
            ['.vehicle-make-field', 'make'],
            ['.vehicle-model-field', 'model'],
            ['.vehicle-color-field', 'color'],
            ['.vehicle-or-field', 'or'],
            ['.vehicle-cr-field', 'cr'],
            ['.vehicle-chassis-field', 'chassis'],
        ];
        mappings.forEach(([selector, key]) => {
            const field = row.querySelector(selector);
            if (field && !field.value.trim()) field.value = option.dataset[key] || '';
        });
    }

    function attachRow(row) {
        row.querySelector('.violator-select').addEventListener('change', function () {
            applyViolatorData(row, this.options[this.selectedIndex]);
        });
        row.querySelector('.vehicle-select').addEventListener('change', function () {
            applyVehicleData(row, this.options[this.selectedIndex]);
        });
        const selectedViolator = row.querySelector('.violator-select');
        if (selectedViolator.value) applyViolatorData(row, selectedViolator.options[selectedViolator.selectedIndex]);
        const selectedVehicle = row.querySelector('.vehicle-select');
        if (selectedVehicle.value) applyVehicleData(row, selectedVehicle.options[selectedVehicle.selectedIndex]);
    }

    function addRow(values = {}) {
        const wrapper = document.createElement('div');
        const idx = motoristIndex++;
        wrapper.innerHTML = buildMotoristRow(idx, values);
        const row = wrapper.firstElementChild;
        container.appendChild(row);
        attachRow(row);
        renumberRows();
        // Init photo pickers for this row
        initPhotoPicker('picker-id-' + idx, 'motorist_id_photos[' + idx + ']',   { multiple: false });
        initPhotoPicker('picker-vp-' + idx, 'motorist_photos[' + idx + '][]',     { multiple: true  });
    }

    oldMotorists.forEach((motorist) => addRow(motorist));

    document.getElementById('add-motorist').addEventListener('click', () => addRow({}));

    container.addEventListener('click', function (event) {
        const btn = event.target.closest('.remove-motorist');
        if (!btn) return;
        if (container.querySelectorAll('.incident-row').length <= 2) {
            alert('An incident must have at least 2 motorists.');
            return;
        }
        btn.closest('.incident-row').remove();
        renumberRows();
    });

    document.getElementById('mediaInput').addEventListener('change', function () {
        const preview = document.getElementById('media-preview-container');
        preview.innerHTML = '';
        Array.from(this.files).forEach((file) => {
            const isPdf = file.name.toLowerCase().endsWith('.pdf');
            const card = document.createElement('div');
            card.className = 'incident-media-card';
            card.innerHTML = (isPdf
                ? '<div style="aspect-ratio:4/3;border-radius:10px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;margin-bottom:.45rem;"><i class="ph-fill ph-file-pdf" style="font-size:2rem;color:#dc2626;"></i></div>'
                : '<img src="' + URL.createObjectURL(file) + '" alt="' + escapeHtml(file.name) + '">') +
                '<div style="font-size:.72rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:.35rem;">' + escapeHtml(file.name) + '</div>' +
                '<select name="media_types[]" class="form-select mob-select" style="margin-bottom:.35rem;"><option value="scene">Scene</option><option value="ticket">Ticket</option><option value="document">Document</option><option value="other">Other</option></select>' +
                '<input type="text" name="captions[]" class="form-control mob-input" placeholder="Caption (optional)">';
            preview.appendChild(card);
        });
    });

    document.getElementById('incidentForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-hourglass"></i> Saving…';
    });
})();
</script>
@endpush
