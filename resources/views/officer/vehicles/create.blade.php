@extends('layouts.mobile')
@section('title', 'Add Vehicle')
@section('back_url', route('officer.motorists.show', $violator))

@section('content')

<style>
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
.veh-photo-slot img {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
.veh-photo-slot .veh-remove-btn {
    position: absolute; top: .3rem; right: .3rem;
    width: 22px; height: 22px;
    background: rgba(220,38,38,.85);
    border: none; border-radius: 50%;
    color: #fff; font-size: .65rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; z-index: 2;
}
.veh-add-slot {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: .25rem;
    color: #94a3b8;
    font-size: .68rem; font-weight: 600;
}
</style>

{{-- Motorist context --}}
<div class="mob-card" style="border-left:4px solid #1d4ed8;">
    <div class="mob-card-body d-flex align-items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#1d4ed8,#1e40af);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.95rem;font-weight:800;color:#fff;">
            {{ strtoupper(substr($violator->first_name, 0, 1)) }}
        </div>
        <div>
            <div style="font-size:.62rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Adding vehicle for</div>
            <div style="font-size:.95rem;font-weight:800;color:#0f172a;">{{ $violator->last_name }}, {{ $violator->first_name }}</div>
        </div>
    </div>
</div>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.motorists.vehicles.store', $violator) }}"
              enctype="multipart/form-data" id="vehicleForm">
            @csrf

            @if($errors->any())
            <div class="mob-alert mob-alert-danger">
                <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            {{-- Photos ── --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Vehicle Photos <span style="font-weight:400;color:#94a3b8;">(up to 4)</span></span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="veh-photo-grid mb-4" id="photoGrid">
                {{-- slots filled by JS --}}
            </div>
            <input type="file" id="photoInput" accept="image/jpeg,image/png,image/webp"
                   capture="environment" class="d-none" multiple>
            <span class="mob-hint d-block text-center mb-3" style="margin-top:-.5rem;">
                JPG / PNG · max 10 MB each · up to 4 photos
            </span>

            {{-- Vehicle Identity --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Vehicle Identity</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Plate Number <span class="text-danger">*</span></label>
                <input type="text" name="plate_number" value="{{ old('plate_number') }}" required
                       class="form-control mob-input @error('plate_number') is-invalid @enderror"
                       placeholder="e.g. ABC 1234"
                       style="text-transform:uppercase;font-weight:700;letter-spacing:.05em;">
                @error('plate_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Vehicle Type <span class="text-danger">*</span></label>
                <select name="vehicle_type" class="form-select mob-select @error('vehicle_type') is-invalid @enderror">
                    <option value="">— Select type —</option>
                    <option value="MV" {{ old('vehicle_type') === 'MV' ? 'selected' : '' }}>MV — Motor Vehicle</option>
                    <option value="MC" {{ old('vehicle_type') === 'MC' ? 'selected' : '' }}>MC — Motorcycle</option>
                </select>
                @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-2 mb-3">
                <div class="col-4">
                    <label class="mob-label">Make</label>
                    <input type="text" name="make" value="{{ old('make') }}"
                           class="form-control mob-input @error('make') is-invalid @enderror"
                           placeholder="e.g. Honda">
                    @error('make')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-4">
                    <label class="mob-label">Model</label>
                    <input type="text" name="model" value="{{ old('model') }}"
                           class="form-control mob-input @error('model') is-invalid @enderror"
                           placeholder="e.g. Click 125">
                    @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-4">
                    <label class="mob-label">Year</label>
                    <input type="number" name="year" value="{{ old('year') }}"
                           min="1900" max="{{ date('Y') + 1 }}"
                           class="form-control mob-input @error('year') is-invalid @enderror"
                           placeholder="{{ date('Y') }}">
                    @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Color</label>
                <input type="text" name="color" value="{{ old('color') }}"
                       class="form-control mob-input @error('color') is-invalid @enderror"
                       placeholder="e.g. Red">
                @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Registration --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Registration</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">OR Number</label>
                    <input type="text" name="or_number" value="{{ old('or_number') }}"
                           class="form-control mob-input @error('or_number') is-invalid @enderror"
                           placeholder="Official Receipt #">
                    @error('or_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="mob-label">CR Number</label>
                    <input type="text" name="cr_number" value="{{ old('cr_number') }}"
                           class="form-control mob-input @error('cr_number') is-invalid @enderror"
                           placeholder="Certificate of Reg. #">
                    @error('cr_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Chassis Number</label>
                <input type="text" name="chassis_number" value="{{ old('chassis_number') }}"
                       class="form-control mob-input @error('chassis_number') is-invalid @enderror"
                       placeholder="e.g. MRHGD6160FP..."
                       style="text-transform:uppercase;">
                @error('chassis_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Owner --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Registered Owner</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-4">
                <label class="mob-label">
                    Owner Name
                    <span style="font-size:.65rem;font-weight:500;color:#94a3b8;margin-left:.3rem;">(if different from motorist)</span>
                </label>
                <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                       class="form-control mob-input @error('owner_name') is-invalid @enderror"
                       placeholder="Full name as on CR / OR"
                       autocomplete="off">
                <span class="mob-hint">Leave blank if the motorist is the registered owner.</span>
                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="mob-btn-primary mb-2" id="submitBtn">
                <i class="ph-bold ph-check"></i> Save Vehicle
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
(function () {
    const MAX = 4;
    const grid      = document.getElementById('photoGrid');
    const fileInput = document.getElementById('photoInput');
    let   files     = [];  // array of File objects

    function renderGrid() {
        grid.innerHTML = '';

        // Filled slots
        files.forEach(function (f, i) {
            var url  = URL.createObjectURL(f);
            var slot = document.createElement('div');
            slot.className = 'veh-photo-slot';
            slot.innerHTML =
                '<img src="' + url + '" alt="photo ' + (i + 1) + '">' +
                '<button type="button" class="veh-remove-btn" data-idx="' + i + '" title="Remove">' +
                '<i class="ph-bold ph-x"></i></button>';
            grid.appendChild(slot);
        });

        // Add-more slot (only if < MAX)
        if (files.length < MAX) {
            var add = document.createElement('div');
            add.className = 'veh-photo-slot';
            add.innerHTML =
                '<div class="veh-add-slot">' +
                '<i class="ph ph-camera-plus" style="font-size:1.5rem;color:#93c5fd;"></i>' +
                '<span>Add Photo</span>' +
                '<span style="font-size:.6rem;color:#c0cad8;">' + files.length + ' / ' + MAX + '</span>' +
                '</div>';
            add.addEventListener('click', function () { fileInput.click(); });
            grid.appendChild(add);
        }

        // Sync hidden file inputs
        syncInputs();
    }

    function syncInputs() {
        // Remove old hidden inputs
        document.querySelectorAll('.veh-hidden-input').forEach(function (el) { el.remove(); });

        // Create a DataTransfer to build a FileList
        var dt = new DataTransfer();
        files.forEach(function (f) { dt.items.add(f); });

        // Use a named input for form submission
        var inp = document.createElement('input');
        inp.type = 'file';
        inp.name = 'photos[]';
        inp.multiple = true;
        inp.className = 'veh-hidden-input d-none';
        inp.files = dt.files;
        document.getElementById('vehicleForm').appendChild(inp);
    }

    // Remove button
    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('.veh-remove-btn');
        if (!btn) return;
        var idx = parseInt(btn.dataset.idx, 10);
        files.splice(idx, 1);
        renderGrid();
    });

    // File picker change
    fileInput.addEventListener('change', function () {
        var newFiles = Array.from(this.files);
        newFiles.forEach(function (f) {
            if (files.length >= MAX) return;
            if (f.size > 10 * 1024 * 1024) {
                alert(f.name + ' exceeds 10 MB and was skipped.');
                return;
            }
            files.push(f);
        });
        this.value = '';
        renderGrid();
    });

    // Double-submit protection
    document.getElementById('vehicleForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving…';
        }
    });

    // Boot
    renderGrid();
})();
</script>
@endpush
