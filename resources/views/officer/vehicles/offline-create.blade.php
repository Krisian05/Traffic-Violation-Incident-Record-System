@extends('layouts.mobile')
@section('title', 'Offline Vehicle')
@section('back_url', route('officer.motorists.index'))

@push('styles')
<style>
.offline-violator-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .24rem .62rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: .66rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.offline-link-note {
    display: flex;
    gap: .7rem;
    align-items: flex-start;
    padding: .88rem .95rem;
    margin-bottom: .85rem;
    border-radius: 16px;
    background: #fff7ed;
    border: 1px solid #fdba74;
    color: #9a3412;
    font-size: .76rem;
    line-height: 1.5;
}
.veh-photo-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .5rem;
}
.veh-photo-slot {
    position: relative;
    aspect-ratio: 4/3;
    border-radius: 10px;
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
    transition: border-color .15s;
}
.veh-photo-slot:hover { border-color: #93c5fd; }
.veh-photo-slot img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
.veh-photo-slot .veh-remove-btn {
    position: absolute; top: .3rem; right: .3rem;
    width: 22px; height: 22px;
    background: rgba(220,38,38,.85); border: none; border-radius: 50%;
    color: #fff; font-size: .65rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; z-index: 2;
}
.veh-add-slot {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: .25rem; color: #94a3b8; font-size: .68rem; font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="mob-card" style="border-left:4px solid #1d4ed8;">
    <div class="mob-card-body d-flex align-items-center gap-3">
        <div id="offlineMotoristAvatar" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#1d4ed8,#1e40af);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.96rem;font-weight:800;color:#fff;">
            OF
        </div>
        <div style="min-width:0;">
            <div class="offline-violator-chip">
                <i class="ph-fill ph-wifi-slash"></i>
                Queued Motorist
            </div>
            <div id="offlineMotoristName" style="font-size:.96rem;font-weight:800;color:#0f172a;margin-top:.42rem;">Loading motorist...</div>
            <div id="offlineMotoristMeta" style="font-size:.73rem;color:#64748b;margin-top:.12rem;">Checking the local offline queue on this device.</div>
        </div>
    </div>
</div>

<div class="offline-link-note">
    <i class="ph-fill ph-link"></i>
    <div>
        This vehicle will stay in the offline queue until the linked motorist record syncs. Once that motorist reaches the server, this vehicle publishes automatically after it.
    </div>
</div>

<div id="offlineMotoristMissing" class="mob-alert mob-alert-danger" style="display:none;">
    <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
    <div>The linked offline motorist could not be found on this device anymore.</div>
</div>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.offline.vehicles.create') }}" enctype="multipart/form-data" id="offlineVehicleForm" data-offline-sync="true" data-offline-record-type="offline-vehicle-create" data-offline-label="Vehicle">
            @csrf
            <input type="hidden" name="offline_motorist_key" id="offline_motorist_key" value="">

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Vehicle Identity</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Plate Number <span class="text-danger">*</span></label>
                <input type="text" name="plate_number" required class="form-control mob-input" placeholder="e.g. ABC 1234" style="text-transform:uppercase;font-weight:700;letter-spacing:.05em;">
            </div>

            <div class="mb-3">
                <label class="mob-label">Vehicle Type <span class="text-danger">*</span></label>
                <select name="vehicle_type" class="form-select mob-select" required>
                    <option value="">Select type</option>
                    <option value="MV">MV — Motor Vehicle</option>
                    <option value="MC">MC — Motorcycle</option>
                </select>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Make</label>
                    <input type="text" name="make" class="form-control mob-input" placeholder="e.g. Honda">
                </div>
                <div class="col-6">
                    <label class="mob-label">Model</label>
                    <input type="text" name="model" class="form-control mob-input" placeholder="e.g. Click 125">
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Color</label>
                <input type="text" name="color" class="form-control mob-input" placeholder="e.g. Red">
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Year</label>
                    <input type="number" name="year" min="1900" max="{{ date('Y') + 1 }}" class="form-control mob-input" placeholder="{{ date('Y') }}">
                </div>
                <div class="col-6">
                    <label class="mob-label">OR Number</label>
                    <input type="text" name="or_number" class="form-control mob-input" placeholder="Official Receipt #">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">CR Number</label>
                    <input type="text" name="cr_number" class="form-control mob-input" placeholder="Certificate of Reg. #">
                </div>
                <div class="col-6">
                    <label class="mob-label">Chassis Number</label>
                    <input type="text" name="chassis_number" class="form-control mob-input" placeholder="MRHGD6160FP..." style="text-transform:uppercase;">
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">
                    Owner Name <span style="font-size:.68rem;color:#94a3b8;">(if different from motorist)</span>
                </label>
                <input type="text" name="owner_name" class="form-control mob-input" placeholder="Full name as on CR/OR">
            </div>

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Photos</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-4">
                <label class="mob-label">Vehicle Photos <span style="font-size:.68rem;color:#94a3b8;">(up to 4)</span></label>
                <div class="veh-photo-grid" id="photoGrid"></div>
                <input type="file" id="photoInput" accept="image/*" class="d-none" multiple>
            </div>

            <button type="submit" class="mob-btn-primary mob-btn-danger mb-2" id="submitBtn" disabled>
                <i class="ph-bold ph-cloud-arrow-up"></i> Queue Vehicle
            </button>
            <a href="{{ route('officer.motorists.index') }}" class="mob-btn-outline">
                <i class="ph ph-arrow-left"></i> Back to Motorists
            </a>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('offlineVehicleForm');
    const keyInput = document.getElementById('offline_motorist_key');
    const nameEl = document.getElementById('offlineMotoristName');
    const metaEl = document.getElementById('offlineMotoristMeta');
    const avatarEl = document.getElementById('offlineMotoristAvatar');
    const missingAlert = document.getElementById('offlineMotoristMissing');
    const submitBtn = document.getElementById('submitBtn');

    function hashMotoristKey() {
        var hash = String(window.location.hash || '').replace(/^#/, '');
        var params = new URLSearchParams(hash);
        return params.get('motorist') || '';
    }

    function setMotoristState(motorist) {
        keyInput.value = motorist.offlineMotoristKey;
        form.dataset.offlineParentKey = motorist.offlineMotoristKey;
        form.dataset.offlineLabel = 'Vehicle for ' + motorist.summary.displayName;
        nameEl.textContent = motorist.summary.displayName;
        var metaParts = [];
        if (motorist.summary.licenseNumber) metaParts.push('License ' + motorist.summary.licenseNumber);
        metaEl.textContent = metaParts.join(' • ') || 'Saved only on this device until internet returns.';
        avatarEl.textContent = motorist.summary.initials || 'OF';
        missingAlert.style.display = 'none';
        submitBtn.disabled = false;
        keyInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setMissingState(message) {
        nameEl.textContent = 'Offline motorist not available';
        metaEl.textContent = 'Go back to the motorists list and open a queued motorist from this device.';
        avatarEl.textContent = '!';
        missingAlert.style.display = '';
        missingAlert.querySelector('div').textContent = message;
        submitBtn.disabled = true;
    }

    var motorKey = hashMotoristKey();
    if (!motorKey) {
        setMissingState('No offline motorist was selected for this vehicle.');
        return;
    }

    if (!window.TvirsOffline || typeof window.TvirsOffline.getOfflineMotoristByKey !== 'function') {
        setMissingState('Offline tools did not load correctly on this page.');
        return;
    }

    window.TvirsOffline.getOfflineMotoristByKey(motorKey).then(function (motorist) {
        if (motorist) {
            setMotoristState(motorist);
            return;
        }

        var syncedId = typeof window.TvirsOffline.getSyncedMotoristId === 'function' ? window.TvirsOffline.getSyncedMotoristId(motorKey) : '';
        if (syncedId) {
            window.location.href = '{{ route("officer.motorists.vehicles.create", ":id") }}'.replace(':id', syncedId);
            return;
        }

        setMissingState('The linked offline motorist is no longer queued on this device.');
    }).catch(function () {
        setMissingState('Unable to read the offline motorist queue on this device.');
    });

    // Photo grid JS from vehicles/create.blade.php
    const MAX_PHOTOS = 4;
    const photoGrid = document.getElementById('photoGrid');
    const photoInput = document.getElementById('photoInput');
    let vehiclePhotos = [];

    function renderPhotoGrid() {
        photoGrid.innerHTML = '';
        vehiclePhotos.forEach(function (f, i) {
            var url = URL.createObjectURL(f);
            var slot = document.createElement('div');
            slot.className = 'veh-photo-slot';
            slot.innerHTML = '<img src="' + url + '" alt="photo ' + (i + 1) + '">' +
                '<button type="button" class="veh-remove-btn" data-idx="' + i + '" title="Remove"><i class="ph-bold ph-x"></i></button>';
            photoGrid.appendChild(slot);
        });
        if (vehiclePhotos.length < MAX_PHOTOS) {
            var addSlot = document.createElement('div');
            addSlot.className = 'veh-photo-slot';
            addSlot.innerHTML = '<div class="veh-add-slot">' +
                '<i class="ph ph-camera-plus" style="font-size:1.5rem;color:#93c5fd;"></i>' +
                '<span>Add Photo</span>' +
                '<span style="font-size:.6rem;color:#c0cad8;">' + vehiclePhotos.length + ' / ' + MAX_PHOTOS + '</span>' +
                '</div>';
            addSlot.onclick = function () { photoInput.click(); };
            photoGrid.appendChild(addSlot);
        }
        syncPhotoInputs();
    }

    function syncPhotoInputs() {
        document.querySelectorAll('.veh-hidden-photo').forEach(el => el.remove());
        var dt = new DataTransfer();
        vehiclePhotos.forEach(f => dt.items.add(f));
        var inp = document.createElement('input');
        inp.type = 'file'; inp.name = 'photos[]'; inp.multiple = true;
        inp.className = 'veh-hidden-photo d-none'; inp.files = dt.files;
        form.appendChild(inp);
    }

    photoGrid.onclick = function (e) {
        var btn = e.target.closest('.veh-remove-btn');
        if (btn) {
            var idx = parseInt(btn.dataset.idx);
            vehiclePhotos.splice(idx, 1);
            renderPhotoGrid();
        }
    };

    photoInput.onchange = function () {
        Array.from(this.files).forEach(f => {
            if (vehiclePhotos.length < MAX_PHOTOS && f.size <= 10*1024*1024) {
                vehiclePhotos.push(f);
            }
        });
        this.value = '';
        renderPhotoGrid();
    };

    renderPhotoGrid();
});
</script>
@endpush
