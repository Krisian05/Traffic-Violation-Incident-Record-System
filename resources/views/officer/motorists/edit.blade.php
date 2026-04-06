@extends('layouts.mobile')
@section('title', 'Edit Motorist')
@section('back_url', route('officer.motorists.show', $violator))

@push('styles')
<style>
    /* ── Edit-page overrides ── */
    .edit-section {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.04);
        border: 1px solid rgba(0,0,0,.04);
        overflow: hidden;
        margin-bottom: .875rem;
    }

    .edit-section-header {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .8rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .edit-section-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .edit-section-icon--blue  { background: linear-gradient(135deg,#1d4ed8,#1e40af); color:#fff; box-shadow:0 2px 8px rgba(29,78,216,.28); }
    .edit-section-icon--green { background: linear-gradient(135deg,#059669,#047857); color:#fff; box-shadow:0 2px 8px rgba(5,150,105,.28); }
    .edit-section-icon--amber { background: linear-gradient(135deg,#d97706,#b45309); color:#fff; box-shadow:0 2px 8px rgba(217,119,6,.28); }
    .edit-section-icon--violet{ background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; box-shadow:0 2px 8px rgba(124,58,237,.28); }

    .edit-section-title {
        font-size: .72rem;
        font-weight: 800;
        color: var(--text-dark);
        text-transform: uppercase;
        letter-spacing: .07em;
    }

    .edit-section-body {
        padding: 1rem;
    }

    /* Input with leading icon */
    .field-group {
        margin-bottom: .875rem;
    }

    .field-group:last-child { margin-bottom: 0; }

    .field-wrap {
        position: relative;
    }

    .field-icon {
        position: absolute;
        left: .85rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        color: var(--text-muted);
        pointer-events: none;
        z-index: 2;
    }

    .field-icon--top {
        top: .78rem;
        transform: none;
    }

    .has-field-icon .mob-input,
    .has-field-icon .mob-select {
        padding-left: 2.45rem !important;
    }

    /* Restriction chip checkboxes */
    .restriction-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .32rem .72rem;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        background: #f8fafc;
        font-size: .78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s;
        user-select: none;
    }

    .restriction-chip input[type=checkbox] { display: none; }

    .restriction-chip.checked {
        border-color: #1d4ed8;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .restriction-chip i { font-size: .85rem; }

    /* Photo preview */
    .photo-preview-card {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .85rem 1rem;
        background: linear-gradient(135deg,#f0f9ff,#e0f2fe);
        border-radius: 12px;
        border: 1px solid #bae6fd;
        margin-bottom: .875rem;
    }

    .photo-preview-img {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
        flex-shrink: 0;
    }

    .photo-preview-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .65rem;
        font-weight: 700;
        color: #0284c7;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    /* Submit strip */
    .submit-strip {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.04);
        border: 1px solid rgba(0,0,0,.04);
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: .6rem;
    }
</style>
@endpush

@section('content')

{{-- Header identity strip --}}
<div style="display:flex;align-items:center;gap:.75rem;padding:.9rem 1rem;background:linear-gradient(135deg,#1d4ed8,#1e3a8a);border-radius:16px;margin-bottom:.875rem;box-shadow:0 6px 24px rgba(29,78,216,.32);">
    <div style="width:46px;height:46px;border-radius:14px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;flex-shrink:0;">
        <i class="ph-fill ph-pencil-simple"></i>
    </div>
    <div style="min-width:0;">
        <div style="font-size:.62rem;font-weight:700;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.07em;">Editing Motorist Record</div>
        <div style="font-size:.97rem;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            {{ $violator->first_name }} {{ $violator->last_name }}
        </div>
    </div>
</div>

<form method="POST" action="{{ route('officer.motorists.update', $violator) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @if($errors->any())
    <div class="mob-alert mob-alert-danger mb-3">
        <i class="ph-fill ph-warning-circle" style="font-size:1.15rem;flex-shrink:0;"></i>
        <div>{{ $errors->first() }}</div>
    </div>
    @endif

    {{-- ── PERSONAL INFO ── --}}
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon edit-section-icon--blue">
                <i class="ph-bold ph-user"></i>
            </div>
            <span class="edit-section-title">Personal Information</span>
        </div>
        <div class="edit-section-body">

            <div class="field-group">
                <label class="mob-label">First Name <span class="text-danger">*</span></label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-text-aa field-icon"></i>
                    <input type="text" name="first_name" value="{{ old('first_name', $violator->first_name) }}" required
                           class="form-control mob-input @error('first_name') is-invalid @enderror"
                           placeholder="e.g. Juan">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Middle Name <span style="font-weight:400;color:var(--text-muted);text-transform:none;letter-spacing:0;">(optional)</span></label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-text-aa field-icon"></i>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $violator->middle_name) }}"
                           class="form-control mob-input @error('middle_name') is-invalid @enderror"
                           placeholder="e.g. Dela">
                    @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Last Name <span class="text-danger">*</span></label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-text-aa field-icon"></i>
                    <input type="text" name="last_name" value="{{ old('last_name', $violator->last_name) }}" required
                           class="form-control mob-input @error('last_name') is-invalid @enderror"
                           placeholder="e.g. Cruz">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-2 mb-0">
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Date of Birth</label>
                        <div class="field-wrap has-field-icon">
                            <i class="ph ph-calendar-blank field-icon"></i>
                            <input type="date" name="date_of_birth"
                                   value="{{ old('date_of_birth', $violator->date_of_birth?->format('Y-m-d')) }}"
                                   class="form-control mob-input @error('date_of_birth') is-invalid @enderror">
                            @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Gender</label>
                        <div class="field-wrap has-field-icon">
                            <i class="ph ph-gender-intersex field-icon"></i>
                            <select name="gender" class="form-select mob-select has-field-icon @error('gender') is-invalid @enderror">
                                <option value="">— Select —</option>
                                <option value="Male"   {{ old('gender', $violator->gender) === 'Male'   ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $violator->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-0">
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Civil Status</label>
                        <div class="field-wrap has-field-icon">
                            <i class="ph ph-ring field-icon"></i>
                            <select name="civil_status" class="form-select mob-select @error('civil_status') is-invalid @enderror">
                                <option value="">— Select —</option>
                                <option value="Single"    {{ old('civil_status', $violator->civil_status) === 'Single'    ? 'selected' : '' }}>Single</option>
                                <option value="Married"   {{ old('civil_status', $violator->civil_status) === 'Married'   ? 'selected' : '' }}>Married</option>
                                <option value="Widowed"   {{ old('civil_status', $violator->civil_status) === 'Widowed'   ? 'selected' : '' }}>Widowed</option>
                                <option value="Separated" {{ old('civil_status', $violator->civil_status) === 'Separated' ? 'selected' : '' }}>Separated</option>
                            </select>
                            @error('civil_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Blood Type</label>
                        <div class="field-wrap has-field-icon">
                            <i class="ph ph-drop field-icon"></i>
                            <select name="blood_type" class="form-select mob-select @error('blood_type') is-invalid @enderror">
                                <option value="">— Select —</option>
                                @foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $bt)
                                <option value="{{ $bt }}" {{ old('blood_type', $violator->blood_type) === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                            @error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Place of Birth</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-map-pin field-icon"></i>
                    <input type="text" name="place_of_birth"
                           value="{{ old('place_of_birth', $violator->place_of_birth) }}"
                           class="form-control mob-input @error('place_of_birth') is-invalid @enderror"
                           placeholder="City / Municipality">
                    @error('place_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-2 mb-0">
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Height (cm)</label>
                        <div class="field-wrap has-field-icon">
                            <i class="ph ph-ruler field-icon"></i>
                            <input type="number" name="height" value="{{ old('height', $violator->height) }}" min="50" max="250" step="0.1"
                                   class="form-control mob-input @error('height') is-invalid @enderror"
                                   placeholder="165">
                            @error('height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Weight (kg)</label>
                        <div class="field-wrap has-field-icon">
                            <i class="ph ph-scales field-icon"></i>
                            <input type="number" name="weight" value="{{ old('weight', $violator->weight) }}" min="10" max="300" step="0.1"
                                   class="form-control mob-input @error('weight') is-invalid @enderror"
                                   placeholder="60">
                            @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Valid ID Presented</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-identification-card field-icon"></i>
                    <input type="text" name="valid_id" value="{{ old('valid_id', $violator->valid_id) }}"
                           class="form-control mob-input @error('valid_id') is-invalid @enderror"
                           placeholder="PhilSys ID / Passport / UMID">
                    @error('valid_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Email Address</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-envelope field-icon"></i>
                    <input type="email" name="email" value="{{ old('email', $violator->email) }}"
                           class="form-control mob-input @error('email') is-invalid @enderror"
                           placeholder="juan@email.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>
    </div>

    {{-- ── LICENSE ── --}}
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon edit-section-icon--green">
                <i class="ph-bold ph-card"></i>
            </div>
            <span class="edit-section-title">Driver's License</span>
        </div>
        <div class="edit-section-body">

            <div class="field-group">
                <label class="mob-label">License Number</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-hash field-icon"></i>
                    <input type="text" name="license_number" value="{{ old('license_number', $violator->license_number) }}"
                           class="form-control mob-input @error('license_number') is-invalid @enderror"
                           placeholder="N01-01-123456">
                    @error('license_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-2 mb-0">
                <div class="col-7">
                    <div class="field-group">
                        <label class="mob-label">License Type</label>
                        <div class="field-wrap has-field-icon">
                            <i class="ph ph-certificate field-icon"></i>
                            <select name="license_type" class="form-select mob-select @error('license_type') is-invalid @enderror">
                                <option value="">— Select —</option>
                                <option value="Non-Professional" {{ old('license_type', $violator->license_type) === 'Non-Professional' ? 'selected' : '' }}>Non-Professional</option>
                                <option value="Professional"     {{ old('license_type', $violator->license_type) === 'Professional'     ? 'selected' : '' }}>Professional</option>
                            </select>
                            @error('license_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-5">
                    <div class="field-group">
                        <label class="mob-label">Expiry Date</label>
                        <div class="field-wrap has-field-icon">
                            <i class="ph ph-calendar-x field-icon"></i>
                            <input type="date" name="license_expiry_date"
                                   value="{{ old('license_expiry_date', $violator->license_expiry_date?->format('Y-m-d')) }}"
                                   class="form-control mob-input @error('license_expiry_date') is-invalid @enderror">
                            @error('license_expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Date Issued</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-calendar-check field-icon"></i>
                    <input type="date" name="license_issued_date"
                           value="{{ old('license_issued_date', $violator->license_issued_date?->format('Y-m-d')) }}"
                           class="form-control mob-input @error('license_issued_date') is-invalid @enderror">
                    @error('license_issued_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label" style="margin-bottom:.55rem;">Restriction Codes</label>
                @php $currentRestrictions = array_filter(explode(',', $violator->license_restriction ?? '')); @endphp
                <div class="d-flex flex-wrap gap-2" id="restriction-wrap">
                    @foreach(['A','A1','B','B1','B2','C','D','BE','CE'] as $rc)
                    @php $isChecked = is_array(old('license_restriction')) ? in_array($rc, old('license_restriction')) : in_array($rc, $currentRestrictions); @endphp
                    <label class="restriction-chip {{ $isChecked ? 'checked' : '' }}">
                        <input type="checkbox" name="license_restriction[]" value="{{ $rc }}" {{ $isChecked ? 'checked' : '' }}>
                        <i class="ph {{ $isChecked ? 'ph-fill ph-check-circle' : 'ph ph-circle' }}"></i>
                        {{ $rc }}
                    </label>
                    @endforeach
                </div>
                @error('license_restriction')<div style="font-size:.72rem;color:#dc2626;margin-top:.3rem;">{{ $message }}</div>@enderror
            </div>

            <div class="field-group">
                <label class="mob-label">Conditions / Remarks</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-note-pencil field-icon field-icon--top"></i>
                    <textarea name="license_conditions" rows="2"
                              class="form-control mob-input @error('license_conditions') is-invalid @enderror"
                              placeholder="e.g. Must wear corrective lenses"
                              style="min-height:auto;resize:none;">{{ old('license_conditions', $violator->license_conditions) }}</textarea>
                    @error('license_conditions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>
    </div>

    {{-- ── CONTACT ── --}}
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon edit-section-icon--amber">
                <i class="ph-bold ph-phone"></i>
            </div>
            <span class="edit-section-title">Contact Details</span>
        </div>
        <div class="edit-section-body">

            <div class="field-group">
                <label class="mob-label">Contact Number</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-device-mobile field-icon"></i>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $violator->contact_number) }}"
                           class="form-control mob-input @error('contact_number') is-invalid @enderror"
                           placeholder="09XX-XXX-XXXX">
                    @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Temporary / Current Address</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-house field-icon field-icon--top"></i>
                    <textarea name="address" rows="2"
                              class="form-control mob-input @error('address') is-invalid @enderror"
                              placeholder="Barangay / Municipality / Province"
                              style="min-height:auto;resize:none;">{{ old('address', $violator->temporary_address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Permanent Address</label>
                <div class="field-wrap has-field-icon">
                    <i class="ph ph-map-trifold field-icon field-icon--top"></i>
                    <textarea name="permanent_address" rows="2"
                              class="form-control mob-input @error('permanent_address') is-invalid @enderror"
                              placeholder="Street, Barangay, City / Province"
                              style="min-height:auto;resize:none;">{{ old('permanent_address', $violator->permanent_address) }}</textarea>
                    @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div style="display:flex;align-items:center;gap:.35rem;margin-top:.35rem;">
                    <i class="ph ph-info" style="font-size:.8rem;color:var(--text-muted);"></i>
                    <span style="font-size:.68rem;color:var(--text-muted);">Leave blank if same as temporary address.</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── PHOTO ── --}}
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon edit-section-icon--violet">
                <i class="ph-bold ph-camera"></i>
            </div>
            <span class="edit-section-title">Motorist Photo</span>
        </div>
        <div class="edit-section-body">

            @if($violator->photo)
            <div class="photo-preview-card">
                <img src="{{ uploaded_file_url($violator->photo) }}" alt="Current photo" class="photo-preview-img">
                <div>
                    <div class="photo-preview-badge">
                        <i class="ph-fill ph-check-circle"></i> Current Photo on File
                    </div>
                    <div style="font-size:.72rem;color:#334155;margin-top:.25rem;">Upload a new image below to replace it.</div>
                </div>
            </div>
            @else
            <div style="display:flex;align-items:center;gap:.55rem;padding:.7rem .9rem;background:#fefce8;border:1px solid #fde68a;border-radius:10px;margin-bottom:.875rem;">
                <i class="ph ph-image" style="font-size:1.1rem;color:#92400e;flex-shrink:0;"></i>
                <span style="font-size:.75rem;color:#92400e;">No photo on file. Upload one below.</span>
            </div>
            @endif

            <div id="picker-photo"></div>
            @error('photo')<div style="font-size:.72rem;color:#dc2626;margin-top:.3rem;">{{ $message }}</div>@enderror

        </div>
    </div>

    {{-- ── ACTION BUTTONS ── --}}
    <div class="submit-strip">
        <button type="submit" class="mob-btn-primary">
            <i class="ph-bold ph-floppy-disk" style="font-size:1.05rem;"></i>
            Save Changes
        </button>
    </div>

</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Photo picker
    initPhotoPicker('picker-photo', 'photo', { multiple: false });

    // Interactive restriction chips
    document.querySelectorAll('.restriction-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            const cb  = chip.querySelector('input[type=checkbox]');
            const ico = chip.querySelector('i');
            const on  = cb.checked;
            chip.classList.toggle('checked', on);
            if (on) {
                ico.className = 'ph-fill ph-check-circle';
            } else {
                ico.className = 'ph ph-circle';
            }
        });
    });
});
</script>
@endpush

@endsection
