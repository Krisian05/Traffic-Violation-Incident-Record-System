@extends('layouts.mobile')
@section('title', 'Offline Incident')
@section('back_url', route('officer.incidents.index'))

@push('styles')
<style>
.offline-inc-note {
    display: flex; gap: .7rem; align-items: flex-start;
    padding: .88rem .95rem; margin-bottom: .85rem;
    border-radius: 16px; background: #fff7ed;
    border: 1px solid #fdba74; color: #9a3412;
    font-size: .76rem; line-height: 1.5;
}
.offline-inc-note i { font-size: 1rem; flex-shrink: 0; margin-top: .08rem; }
.offline-loc-preview {
    display: none; margin-top: .55rem; padding: .58rem .75rem;
    border-radius: 12px; background: #f0fdf4;
    border: 1px solid #bbf7d0; color: #166534;
    font-size: .74rem; line-height: 1.45; font-weight: 700;
}
.offline-loc-preview i { margin-right: .28rem; }
.inc-off-motorist-row {
    border: 1.5px solid #e2e8f0; border-radius: 12px;
    padding: .85rem .95rem; margin-bottom: .85rem;
    background: #f8fafc; position: relative;
}
.inc-off-motorist-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: .75rem;
}
.inc-off-motorist-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 8px;
    background: linear-gradient(135deg,#d97706,#b45309);
    color: #fff; font-size: .76rem; font-weight: 800;
}
</style>
@endpush

@section('content')

<div class="mob-card" style="border-left:4px solid #ea580c;">
    <div class="mob-card-body d-flex align-items-center gap-3">
        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#ea580c,#c2410c);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-wifi-slash" style="font-size:1.1rem;color:#fff;"></i>
        </div>
        <div>
            <div style="font-size:.62rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Offline Mode</div>
            <div style="font-size:.95rem;font-weight:800;color:#0f172a;">Record Incident Offline</div>
            <div style="font-size:.72rem;color:#64748b;margin-top:.1rem;">Saved on this device — syncs automatically when internet returns</div>
        </div>
    </div>
</div>

<div class="offline-inc-note">
    <i class="ph-fill ph-cloud-arrow-up"></i>
    <div>
        This incident will be saved in the offline queue on this device and published automatically once the device reconnects to the internet.
    </div>
</div>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST"
              action="{{ route('officer.incidents.store') }}"
              enctype="multipart/form-data"
              id="offlineIncidentForm"
              data-offline-sync="true"
              data-offline-label="Incident"
              data-offline-record-type="offline-incident-create">
            @csrf

            {{-- Incident Info --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Incident Info</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-7">
                    <label class="mob-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_incident"
                           value="{{ now()->format('Y-m-d') }}"
                           max="{{ now()->format('Y-m-d') }}"
                           required class="form-control mob-input">
                </div>
                <div class="col-5">
                    <label class="mob-label">Time</label>
                    <input type="time" name="time_of_incident" class="form-control mob-input">
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Location <span class="text-danger">*</span></label>
                <input type="hidden" name="location" id="offline_location_value" value="">
                <div class="row g-2">
                    <div class="col-12">
                        <input type="text" id="offline_loc_street" class="form-control mob-input"
                               placeholder="Street / specific spot (optional)">
                    </div>
                    <div class="col-6">
                        <input type="text" id="offline_loc_barangay" class="form-control mob-input"
                               placeholder="Barangay" autocomplete="off">
                    </div>
                    <div class="col-6">
                        <input type="text" id="offline_loc_municipality" class="form-control mob-input"
                               placeholder="Municipality" value="Balamban, Cebu" autocomplete="off">
                    </div>
                </div>
                <div id="offline_location_preview" class="offline-loc-preview">
                    <i class="ph-fill ph-check-circle"></i>
                    <span id="offline_location_preview_text"></span>
                </div>
            </div>

            <div class="mb-4">
                <label class="mob-label">Description</label>
                <textarea name="description" rows="3" class="form-control mob-input"
                          style="min-height:auto;resize:none;"
                          placeholder="Brief description of what happened..."></textarea>
            </div>

            {{-- Motorists --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Motorists Involved</span>
                <span class="mob-form-divider-line"></span>
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-bottom:.8rem;">At least 1 motorist required. Enter details manually — you can link to registered motorists when back online.</div>

            <div id="offline-motorists-container"></div>

            <button type="button" id="add-offline-motorist"
                    style="display:flex;align-items:center;justify-content:center;gap:.4rem;width:100%;min-height:42px;border-radius:10px;border:1.5px dashed #93c5fd;background:transparent;color:#1d4ed8;font-weight:700;font-size:.85rem;cursor:pointer;margin:.5rem 0 1.1rem;">
                <i class="ph ph-plus-circle"></i> Add Another Motorist
            </button>

            {{-- Other Involved Parties --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-line"></span>
                <span class="mob-form-divider-text">Other Involved Parties</span>
                <span class="mob-form-divider-line"></span>
            </div>
            <div class="mb-3" style="font-size:.78rem;color:#94a3b8;">Pedestrians, cyclists, pedicabs, bystanders, etc.</div>
            <div id="other-parties-list"></div>
            <button type="button" class="mob-btn-outline w-100 mb-4" onclick="addOtherParty()" style="border-color:#fed7aa;color:#ea580c;">
                <i class="ph-bold ph-plus"></i> Add Other Involved Party
            </button>

            {{-- Scene Photos --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Scene Photos</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-4">
                <label class="mob-label">Incident Photos <span style="font-size:.68rem;color:#94a3b8;">(up to 6)</span></label>
                <div id="picker-incident-photos"></div>
            </div>

            <button type="submit" class="mob-btn-primary mob-btn-danger mb-2" id="offlineIncidentSubmitBtn">
                <i class="ph-bold ph-cloud-arrow-up"></i> Queue Incident
            </button>
            <a href="{{ route('officer.incidents.index') }}" class="mob-btn-outline">
                <i class="ph ph-arrow-left"></i> Back to Incidents
            </a>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initPhotoPicker('picker-incident-photos', 'incident_photos[]', { multiple: true });

    /* ── Location composer ── */
    var locStreet       = document.getElementById('offline_loc_street');
    var locBarangay     = document.getElementById('offline_loc_barangay');
    var locMunicipality = document.getElementById('offline_loc_municipality');
    var locValue        = document.getElementById('offline_location_value');
    var locPreview      = document.getElementById('offline_location_preview');
    var locPreviewText  = document.getElementById('offline_location_preview_text');

    function syncLocation() {
        var street   = (locStreet.value || '').trim();
        var barangay = (locBarangay.value || '').trim();
        var muni     = (locMunicipality.value || '').trim();
        var parts    = [];
        if (street)   parts.push(street);
        if (barangay) parts.push('Brgy. ' + barangay);
        if (muni)     parts.push(muni);
        var composed = parts.join(', ');
        locValue.value = composed;
        if (composed) {
            locPreviewText.textContent = composed;
            locPreview.style.display = 'block';
        } else {
            locPreview.style.display = 'none';
        }
    }

    [locStreet, locBarangay, locMunicipality].forEach(function (el) {
        el.addEventListener('input', syncLocation);
    });
    syncLocation();

    /* ── Motorist rows ── */
    var motoristContainer = document.getElementById('offline-motorists-container');
    var motoristIndex = 0;

    function buildMotoristRow(index) {
        var rowNumber = motoristContainer.querySelectorAll('.inc-off-motorist-row').length + 1;
        var div = document.createElement('div');
        div.className = 'inc-off-motorist-row';
        div.dataset.index = index;
        div.innerHTML =
            '<div class="inc-off-motorist-head">' +
                '<div style="display:flex;align-items:center;gap:.6rem;">' +
                    '<span class="inc-off-motorist-badge">' + rowNumber + '</span>' +
                    '<div style="font-size:.76rem;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:.05em;">Motorist #' + rowNumber + '</div>' +
                '</div>' +
                (rowNumber > 1
                    ? '<button type="button" class="remove-off-motorist" style="border:none;background:none;color:#dc2626;font-size:.76rem;font-weight:700;"><i class="ph ph-trash me-1"></i>Remove</button>'
                    : '') +
            '</div>' +
            '<div class="row g-2 mb-3">' +
                '<div class="col-12"><label class="mob-label">Full Name <span class="text-danger">*</span></label><input type="text" name="motorists[' + index + '][motorist_name]" class="form-control mob-input" placeholder="Enter full name" required></div>' +
                '<div class="col-7"><label class="mob-label">License Number</label><input type="text" name="motorists[' + index + '][motorist_license]" class="form-control mob-input" placeholder="e.g. N01-23-456789"></div>' +
                '<div class="col-5"><label class="mob-label">Contact</label><input type="text" name="motorists[' + index + '][motorist_contact]" class="form-control mob-input" placeholder="09XX-XXX-XXXX"></div>' +
                '<div class="col-12"><label class="mob-label">Address</label><input type="text" name="motorists[' + index + '][motorist_address]" class="form-control mob-input" placeholder="Current address"></div>' +
            '</div>' +
            '<div class="mob-form-divider" style="margin:.5rem 0;"><span class="mob-form-divider-text" style="font-size:.65rem;">Vehicle</span><span class="mob-form-divider-line"></span></div>' +
            '<div class="row g-2 mb-2">' +
                '<div class="col-6"><label class="mob-label">Plate No.</label><input type="text" name="motorists[' + index + '][vehicle_plate]" class="form-control mob-input" placeholder="e.g. ABC 1234"></div>' +
                '<div class="col-6"><label class="mob-label">Type</label><select name="motorists[' + index + '][vehicle_type_manual]" class="form-select mob-select"><option value="">— Select —</option><option value="MV">MV</option><option value="MC">MC</option></select></div>' +
                '<div class="col-6"><label class="mob-label">Make</label><input type="text" name="motorists[' + index + '][vehicle_make]" class="form-control mob-input" placeholder="e.g. Honda"></div>' +
                '<div class="col-6"><label class="mob-label">Model</label><input type="text" name="motorists[' + index + '][vehicle_model]" class="form-control mob-input" placeholder="e.g. Click 125"></div>' +
                '<div class="col-6"><label class="mob-label">Color</label><input type="text" name="motorists[' + index + '][vehicle_color]" class="form-control mob-input" placeholder="e.g. Red"></div>' +
                '<div class="col-6"><label class="mob-label">OR Number</label><input type="text" name="motorists[' + index + '][vehicle_or_number]" class="form-control mob-input" placeholder="Official Receipt #"></div>' +
            '</div>' +
            '<div class="mb-1"><label class="mob-label">Notes / Remarks</label><textarea name="motorists[' + index + '][notes]" rows="2" class="form-control mob-input" style="min-height:auto;resize:none;" placeholder="Injuries, condition, remarks..."></textarea></div>';
        return div;
    }

    function renumberMotoristRows() {
        motoristContainer.querySelectorAll('.inc-off-motorist-row').forEach(function (row, i) {
            row.querySelector('.inc-off-motorist-badge').textContent = i + 1;
            var titleEl = row.querySelector('.inc-off-motorist-head div div');
            if (titleEl) titleEl.textContent = 'Motorist #' + (i + 1);
        });
    }

    function addMotoristRow() {
        var idx = motoristIndex++;
        var row = buildMotoristRow(idx);
        motoristContainer.appendChild(row);
        row.querySelector('.remove-off-motorist') && row.querySelector('.remove-off-motorist').addEventListener('click', function () {
            if (motoristContainer.querySelectorAll('.inc-off-motorist-row').length <= 1) {
                alert('At least 1 motorist is required.');
                return;
            }
            row.remove();
            renumberMotoristRows();
        });
    }

    addMotoristRow(); // start with 1 row

    document.getElementById('add-offline-motorist').addEventListener('click', addMotoristRow);

    /* ── Form submit guard ── */
    document.getElementById('offlineIncidentForm').addEventListener('submit', function (event) {
        if (!locValue.value.trim()) {
            event.preventDefault();
            alert('Please enter a location.');
            locBarangay.focus();
            return;
        }
    });
});

/* ── Other Involved Parties ── */
let otherPartyCount = 0;
const OTHER_TYPES = ['Pedestrian', 'Bicycle', 'Pedicab', 'Tricycle', 'Animal-drawn', 'Bystander', 'Other'];

function addOtherParty(data) {
    const i = otherPartyCount++;
    const list = document.getElementById('other-parties-list');
    const typeOptions = OTHER_TYPES.map(t =>
        `<option value="${t}"${data && data.type === t ? ' selected' : ''}>${t}</option>`
    ).join('');
    const div = document.createElement('div');
    div.id = `other-party-${i}`;
    div.style.cssText = 'border:1.5px solid #fed7aa;border-radius:10px;padding:.85rem 1rem;margin-bottom:.75rem;background:#fffbf5;position:relative;';
    div.innerHTML = `
        <button type="button" onclick="removeOtherParty(${i})"
            style="position:absolute;top:.5rem;right:.5rem;background:#fee2e2;border:none;border-radius:6px;color:#dc2626;font-size:.75rem;padding:.2rem .5rem;cursor:pointer;font-weight:700;">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="mb-2">
            <label class="mob-label">Type <span style="color:#dc2626;">*</span></label>
            <select name="other_involved[${i}][type]" class="form-select mob-select" required>
                <option value="">Select type...</option>
                ${typeOptions}
            </select>
        </div>
        <div class="mb-2">
            <label class="mob-label">Name</label>
            <input type="text" name="other_involved[${i}][name]" class="form-control mob-input" placeholder="Full name (optional)" value="${data ? (data.name || '') : ''}">
        </div>
        <div class="mb-2">
            <label class="mob-label">Contact / Address</label>
            <input type="text" name="other_involved[${i}][contact]" class="form-control mob-input" placeholder="Contact or address" value="${data ? (data.contact || '') : ''}">
        </div>
        <div>
            <label class="mob-label">Notes</label>
            <input type="text" name="other_involved[${i}][notes]" class="form-control mob-input" placeholder="Injuries, condition, remarks..." value="${data ? (data.notes || '') : ''}">
        </div>`;
    list.appendChild(div);
}

function removeOtherParty(i) {
    const el = document.getElementById(`other-party-${i}`);
    if (el) el.remove();
}
</script>
@endpush
