@extends('layouts.app')
@section('title', 'Add Vehicle')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('violators.index') }}" style="color:#dc2626;text-decoration:none;">Motorists</a></li>
    <li class="breadcrumb-item"><a href="{{ route('violators.show', $violator) }}" style="color:#dc2626;text-decoration:none;">{{ $violator->full_name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Vehicle</li>
@endsection

@section('content')

{{-- ── PAGE HEADER ── --}}
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:42px;height:42px;background:linear-gradient(135deg,#1d4ed8,#1e40af);flex-shrink:0;">
        <i class="bi bi-car-front-fill text-white" style="font-size:1rem;"></i>
    </div>
    <div>
        <h5 class="mb-0 fw-700" style="color:#1c1917;">Add Vehicle</h5>
        <div style="font-size:.8rem;color:#78716c;">Registering a new vehicle for <strong>{{ $violator->full_name }}</strong></div>
    </div>
</div>

<form method="POST" action="{{ route('vehicles.store', $violator) }}" enctype="multipart/form-data">
@csrf

{{-- ── Section: Registration ── --}}
<div class="vlt-section-card">
    <div class="vlt-card-header">
        <span class="vlt-section-icon" style="background:#dbeafe;">
            <i class="bi bi-car-front-fill" style="color:#1d4ed8;"></i>
        </span>
        <div>
            <div class="vlt-section-title">Vehicle Registration</div>
            <div class="vlt-section-sub">Plate number and vehicle type</div>
        </div>
        <span class="ms-auto badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;font-size:.7rem;font-weight:700;">Required</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Plate Number <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-grid-3x3" style="color:#1d4ed8;font-size:.8rem;"></i></span>
                    <input type="text" name="plate_number"
                        class="form-control @error('plate_number') is-invalid @enderror"
                        value="{{ old('plate_number') }}" required placeholder="e.g. ABC 1234"
                        style="text-transform:uppercase;">
                    @error('plate_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag-fill" style="color:#d97706;font-size:.8rem;"></i></span>
                    <select name="vehicle_type"
                        class="form-select @error('vehicle_type') is-invalid @enderror" required>
                        <option value="">— Select a vehicle type —</option>
                        <option value="MV" {{ old('vehicle_type') == 'MV' ? 'selected' : '' }}>MV — Motor Vehicle</option>
                        <option value="MC" {{ old('vehicle_type') == 'MC' ? 'selected' : '' }}>MC — Motorcycle</option>
                    </select>
                    @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Registered Owner Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge-fill" style="color:#1d4ed8;font-size:.8rem;"></i></span>
                    <input type="text" name="owner_name"
                        class="form-control @error('owner_name') is-invalid @enderror"
                        value="{{ old('owner_name') }}" placeholder="Name as it appears on OR/CR">
                    @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-text" style="font-size:.72rem;">Leave blank if the same as the motorist above.</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Section: Vehicle Details ── --}}
<div class="vlt-section-card">
    <div class="vlt-card-header">
        <span class="vlt-section-icon" style="background:#fef3c7;">
            <i class="bi bi-info-circle-fill" style="color:#d97706;"></i>
        </span>
        <div>
            <div class="vlt-section-title">Vehicle Details</div>
            <div class="vlt-section-sub">Make, model, color and year</div>
        </div>
        <span class="ms-auto badge" style="background:#f8fafc;color:#475569;border:1px solid #cbd5e1;font-size:.7rem;font-weight:700;">Optional</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Make / Brand</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-building" style="color:#57534e;font-size:.8rem;"></i></span>
                    <input type="text" name="make"
                        class="form-control @error('make') is-invalid @enderror"
                        value="{{ old('make') }}" placeholder="e.g. Toyota">
                    @error('make')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Model</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-card-list" style="color:#0369a1;font-size:.8rem;"></i></span>
                    <input type="text" name="model"
                        class="form-control @error('model') is-invalid @enderror"
                        value="{{ old('model') }}" placeholder="e.g. Vios">
                    @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Color</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-palette-fill" style="color:#7c3aed;font-size:.8rem;"></i></span>
                    <input type="text" name="color"
                        class="form-control @error('color') is-invalid @enderror"
                        value="{{ old('color') }}" placeholder="e.g. White">
                    @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar3" style="color:#d97706;font-size:.8rem;"></i></span>
                    <input type="number" name="year"
                        class="form-control @error('year') is-invalid @enderror"
                        value="{{ old('year') }}" min="1900" max="{{ date('Y') + 1 }}"
                        placeholder="{{ date('Y') }}">
                    @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Section: Document Numbers ── --}}
<div class="vlt-section-card">
    <div class="vlt-card-header">
        <span class="vlt-section-icon" style="background:#dcfce7;">
            <i class="bi bi-file-earmark-text-fill" style="color:#15803d;"></i>
        </span>
        <div>
            <div class="vlt-section-title">Document Numbers</div>
            <div class="vlt-section-sub">Official Receipt, Certificate of Registration, and Chassis</div>
        </div>
        <span class="ms-auto badge" style="background:#f8fafc;color:#475569;border:1px solid #cbd5e1;font-size:.7rem;font-weight:700;">Optional</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">OR Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-receipt" style="color:#15803d;font-size:.8rem;"></i></span>
                    <input type="text" name="or_number"
                        class="form-control @error('or_number') is-invalid @enderror"
                        value="{{ old('or_number') }}" placeholder="Official Receipt No.">
                    @error('or_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">CR Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-file-earmark-check-fill" style="color:#0369a1;font-size:.8rem;"></i></span>
                    <input type="text" name="cr_number"
                        class="form-control @error('cr_number') is-invalid @enderror"
                        value="{{ old('cr_number') }}" placeholder="Certificate of Registration No.">
                    @error('cr_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Chassis Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-upc-scan" style="color:#57534e;font-size:.8rem;"></i></span>
                    <input type="text" name="chassis_number"
                        class="form-control @error('chassis_number') is-invalid @enderror"
                        value="{{ old('chassis_number') }}" placeholder="e.g. 1HGCM82633A004352">
                    @error('chassis_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Section: Photos ── --}}
<div class="vlt-section-card">
    <div class="vlt-card-header">
        <span class="vlt-section-icon" style="background:#ede9fe;">
            <i class="bi bi-camera-fill" style="color:#6d28d9;"></i>
        </span>
        <div>
            <div class="vlt-section-title">Vehicle Photos</div>
            <div class="vlt-section-sub">Up to 4 photos, max 10 MB each</div>
        </div>
        <span class="ms-auto badge" style="background:#f8fafc;color:#475569;border:1px solid #cbd5e1;font-size:.7rem;font-weight:700;">Optional</span>
    </div>
    <div class="card-body">
        <div class="veh-upload-zone" id="uploadZone" onclick="document.getElementById('vehiclePhotos').click()">
            <i class="bi bi-cloud-arrow-up-fill" style="font-size:2rem;color:#a8a29e;display:block;margin-bottom:.5rem;"></i>
            <div style="font-size:.88rem;font-weight:700;color:#44403c;" id="uploadText">Click to choose photos</div>
            <div style="font-size:.72rem;color:#a8a29e;margin-top:.2rem;">JPG, PNG, WEBP — up to 4 files</div>
            <input type="file" name="photos[]" id="vehiclePhotos" accept="image/*" multiple
                class="d-none @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror"
                onchange="previewVehiclePhotos(event)">
        </div>
        @error('photos')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        @error('photos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        <div class="mt-3 d-flex flex-wrap gap-2" id="photoPreviewContainer"></div>
    </div>
</div>

{{-- ── Actions ── --}}
<div class="d-flex gap-2 mb-4">
    <button type="submit" class="vlt-form-submit" style="background:linear-gradient(135deg,#1d4ed8,#1e40af);box-shadow:0 3px 10px rgba(29,78,216,.3);">
        <i class="bi bi-check-lg me-1"></i> Save Vehicle
    </button>
    <a href="{{ route('violators.show', $violator) }}"
       class="btn d-inline-flex align-items-center gap-2 rounded-pill"
       style="border:1.5px solid #d6d3d1;color:#78716c;background:#fff;font-weight:500;">
        <i class="bi bi-x-circle" style="font-size:.85rem;"></i> Cancel
    </a>
</div>

</form>

<style>
.vlt-section-card {
    background: #fff;
    border: 1px solid #f0ebe3;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden;
    margin-bottom: 1.5rem;
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
.vlt-section-sub   { font-size: .72rem; color: #a8a29e; margin-top: .05rem; }
.vlt-section-card .card-body { padding: 1.25rem; }
.vlt-form-submit {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1.6rem;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .875rem; font-weight: 600;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
}
.vlt-form-submit:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(29,78,216,.4) !important;
}

/* Upload zone */
.veh-upload-zone {
    border: 2px dashed #c8b99a;
    border-radius: 10px;
    background: #fdf8f0;
    padding: 1.75rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
}
.veh-upload-zone:hover { border-color: #1d4ed8; background: #eff6ff; }
</style>

<script>
function previewVehiclePhotos(event) {
    const container = document.getElementById('photoPreviewContainer');
    const zone = document.getElementById('uploadZone');
    const uploadText = document.getElementById('uploadText');
    container.innerHTML = '';
    const files = Array.from(event.target.files).slice(0, 4);
    files.forEach(file => {
        const wrap = document.createElement('div');
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.cssText = 'height:110px;width:150px;object-fit:cover;border-radius:8px;border:2px solid #bfdbfe;';
        const label = document.createElement('div');
        label.style.cssText = 'font-size:.65rem;color:#78716c;margin-top:.25rem;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;';
        label.textContent = file.name;
        wrap.appendChild(img);
        wrap.appendChild(label);
        container.appendChild(wrap);
    });
    if (files.length > 0) {
        zone.style.borderColor = '#1d4ed8';
        zone.style.background = '#eff6ff';
        uploadText.textContent = files.length + ' photo' + (files.length > 1 ? 's' : '') + ' selected';
    }
}
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor='#1d4ed8'; zone.style.background='#eff6ff'; });
zone.addEventListener('dragleave', () => { zone.style.borderColor=''; zone.style.background=''; });
zone.addEventListener('drop', e => {
    e.preventDefault();
    const input = document.getElementById('vehiclePhotos');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).slice(0,4).forEach(f => dt.items.add(f));
    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
});
</script>

@endsection
