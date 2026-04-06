@extends('layouts.mobile')
@section('title', 'New Motorist')
@section('back_url', route('officer.motorists.index'))

@section('content')

<style>
.mob-hint {
    display: block;
    font-size: .68rem;
    color: #94a3b8;
    margin-top: .28rem;
    line-height: 1.4;
}
.mob-hint.hint-ok   { color: #16a34a; }
.mob-hint.hint-warn { color: #dc2626; }
.mob-input.field-ok   { border-color: #16a34a !important; box-shadow: 0 0 0 3px rgba(22,163,74,.1) !important; }
.mob-input.field-warn { border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220,38,38,.1) !important; }
.mob-select.field-ok  { border-color: #16a34a !important; box-shadow: 0 0 0 3px rgba(22,163,74,.1) !important; }
.mob-select.field-warn{ border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220,38,38,.1) !important; }
</style>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.motorists.store') }}" enctype="multipart/form-data" id="motoristForm">
            @csrf

            @if($errors->any())
            <div class="mob-alert mob-alert-danger">
                <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            {{-- ── Photo ── --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Profile Photo</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-4 text-center">
                <div id="photoPreview"
                     style="width:96px;height:96px;border-radius:50%;background:#eff6ff;border:2.5px dashed #93c5fd;display:flex;align-items:center;justify-content:center;margin:0 auto .65rem;overflow:hidden;transition:border-color .15s;">
                    <i class="ph-fill ph-user" style="font-size:2.4rem;color:#93c5fd;"></i>
                </div>
                <div id="picker-photo" class="justify-content-center"></div>
                <span class="mob-hint d-block text-center mt-1">JPG or PNG, max 5 MB. Clear front-facing photo.</span>
                @error('photo')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>

            {{-- ── Personal Info ── --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Personal Info</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                       class="form-control mob-input @error('first_name') is-invalid @enderror"
                       placeholder="e.g. Juan" autocomplete="off">
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <span class="mob-hint" id="hint-first-name">Letters and spaces only. Will be auto-capitalized.</span>
                @enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name') }}"
                       class="form-control mob-input @error('middle_name') is-invalid @enderror"
                       placeholder="Optional" autocomplete="off">
                @error('middle_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <span class="mob-hint" id="hint-middle-name">Optional. Leave blank if none.</span>
                @enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                       class="form-control mob-input @error('last_name') is-invalid @enderror"
                       placeholder="e.g. dela Cruz" autocomplete="off">
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <span class="mob-hint" id="hint-last-name">Letters and spaces only. Will be auto-capitalized.</span>
                @enderror
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                           class="form-control mob-input @error('date_of_birth') is-invalid @enderror">
                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="mob-label">Gender</label>
                    <select name="gender" class="form-select mob-select @error('gender') is-invalid @enderror">
                        <option value="">— Select —</option>
                        <option value="Male"   {{ old('gender') === 'Male'   ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other"  {{ old('gender') === 'Other'  ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Civil Status</label>
                    <select name="civil_status" class="form-select mob-select @error('civil_status') is-invalid @enderror">
                        <option value="">— Select —</option>
                        <option value="Single"    {{ old('civil_status') === 'Single'    ? 'selected' : '' }}>Single</option>
                        <option value="Married"   {{ old('civil_status') === 'Married'   ? 'selected' : '' }}>Married</option>
                        <option value="Widowed"   {{ old('civil_status') === 'Widowed'   ? 'selected' : '' }}>Widowed</option>
                        <option value="Separated" {{ old('civil_status') === 'Separated' ? 'selected' : '' }}>Separated</option>
                    </select>
                    @error('civil_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="mob-label">Blood Type</label>
                    <select name="blood_type" class="form-select mob-select @error('blood_type') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $bt)
                        <option value="{{ $bt }}" {{ old('blood_type') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                    @error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Place of Birth</label>
                <input type="text" name="place_of_birth" value="{{ old('place_of_birth') }}"
                       class="form-control mob-input @error('place_of_birth') is-invalid @enderror"
                       placeholder="City / Municipality of birth">
                @error('place_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Height</label>
                    <input type="text" name="height" value="{{ old('height') }}"
                           class="form-control mob-input @error('height') is-invalid @enderror"
                           placeholder='e.g. 5&apos;8"'>
                    @error('height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="mob-label">Weight</label>
                    <input type="text" name="weight" value="{{ old('weight') }}"
                           class="form-control mob-input @error('weight') is-invalid @enderror"
                           placeholder="e.g. 65 kg">
                    @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Valid ID Presented</label>
                <input type="text" name="valid_id" value="{{ old('valid_id') }}"
                       class="form-control mob-input @error('valid_id') is-invalid @enderror"
                       placeholder="e.g. PhilSys ID, Passport, UMID">
                @error('valid_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-control mob-input @error('email') is-invalid @enderror"
                       placeholder="e.g. juan@email.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ── License ── --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">License</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">License Number</label>
                <input type="text" name="license_number" id="license_number" value="{{ old('license_number') }}"
                       class="form-control mob-input @error('license_number') is-invalid @enderror"
                       placeholder="e.g. N01-01-123456" style="text-transform:uppercase;" autocomplete="off">
                @error('license_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <span class="mob-hint" id="hint-license">LTO format: N01-23-456789. Auto-uppercased.</span>
                @enderror
            </div>

            <div class="row g-2 mb-3">
                <div class="col-7">
                    <label class="mob-label">License Type</label>
                    <select name="license_type" class="form-select mob-select @error('license_type') is-invalid @enderror">
                        <option value="">— Select —</option>
                        <option value="Non-Professional" {{ old('license_type') === 'Non-Professional' ? 'selected' : '' }}>Non-Professional</option>
                        <option value="Professional"     {{ old('license_type') === 'Professional'     ? 'selected' : '' }}>Professional</option>
                    </select>
                    @error('license_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-5">
                    <label class="mob-label">Expiry Date</label>
                    <input type="date" name="license_expiry_date" id="license_expiry_date"
                           value="{{ old('license_expiry_date') }}"
                           class="form-control mob-input @error('license_expiry_date') is-invalid @enderror">
                    @error('license_expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <span class="mob-hint" id="hint-expiry" style="margin-top:-.5rem;margin-bottom:.75rem;display:block;">Expiration date printed on the license card.</span>

            <div class="mb-3">
                <label class="mob-label">Date Issued</label>
                <input type="date" name="license_issued_date" value="{{ old('license_issued_date') }}"
                       class="form-control mob-input @error('license_issued_date') is-invalid @enderror">
                @error('license_issued_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label d-block mb-2">Restriction Codes</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['A','A1','B','B1','B2','C','D','BE','CE'] as $rc)
                    <label style="display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .7rem;border-radius:8px;border:1.5px solid #cbd5e1;background:#f8fafc;font-size:.8rem;font-weight:600;cursor:pointer;">
                        <input type="checkbox" name="license_restriction[]" value="{{ $rc }}"
                               {{ is_array(old('license_restriction')) && in_array($rc, old('license_restriction')) ? 'checked' : '' }}
                               style="accent-color:#2563eb;">
                        {{ $rc }}
                    </label>
                    @endforeach
                </div>
                @error('license_restriction')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
                <span class="mob-hint">Select all applicable restriction codes from the license card.</span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Conditions / Remarks</label>
                <textarea name="license_conditions" rows="2"
                          class="form-control mob-input @error('license_conditions') is-invalid @enderror"
                          placeholder="e.g. Must wear corrective lenses"
                          style="min-height:auto;resize:none;">{{ old('license_conditions') }}</textarea>
                @error('license_conditions')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ── Contact ── --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Contact</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Contact Number</label>
                <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}"
                       class="form-control mob-input @error('contact_number') is-invalid @enderror"
                       placeholder="e.g. 09XX-XXX-XXXX" maxlength="13">
                @error('contact_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <span class="mob-hint" id="hint-contact">PH mobile format: 09XX-XXX-XXXX (11 digits).</span>
                @enderror
            </div>

            {{-- ── Address (PSGC cascading selector) ── --}}
            <div class="mb-3">
                @include('partials.location-selector', [
                    'fieldName' => 'address',
                    'required'  => false,
                    'label'     => 'Temporary / Current Address',
                    'inputSize' => '',
                ])
            </div>

            <div class="mb-3">
                <label class="mob-label">Permanent Address</label>
                <textarea name="permanent_address" rows="2"
                          class="form-control mob-input @error('permanent_address') is-invalid @enderror"
                          placeholder="Street, Barangay, City / Municipality, Province"
                          style="min-height:auto;resize:none;">{{ old('permanent_address') }}</textarea>
                @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <span class="mob-hint">Leave blank if same as temporary address above.</span>
            </div>

            <button type="submit" class="mob-btn-primary mb-2" id="submitBtn">
                <i class="ph-bold ph-check"></i> Save Motorist
            </button>
            <a href="{{ route('officer.motorists.index') }}" class="mob-btn-outline">
                <i class="ph ph-x-circle"></i> Cancel
            </a>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initPhotoPicker('picker-photo', 'photo', { multiple: false });
});

(function () {

    /* ── Helpers ── */
    function setOk(input, hint, msg) {
        input.classList.remove('field-warn'); input.classList.add('field-ok');
        if (hint) { hint.className = 'mob-hint hint-ok'; hint.textContent = '✓ ' + msg; }
    }
    function setWarn(input, hint, msg) {
        input.classList.remove('field-ok'); input.classList.add('field-warn');
        if (hint) { hint.className = 'mob-hint hint-warn'; hint.textContent = '✕ ' + msg; }
    }
    function setNeutral(input, hint, msg) {
        input.classList.remove('field-ok', 'field-warn');
        if (hint) { hint.className = 'mob-hint'; hint.textContent = msg; }
    }

    /* ── Name fields: letters/spaces/hyphens only, auto-capitalize ── */
    [['first_name','hint-first-name','Letters and spaces only. Will be auto-capitalized.'],
     ['middle_name','hint-middle-name','Optional. Leave blank if none.'],
     ['last_name','hint-last-name','Letters and spaces only. Will be auto-capitalized.']
    ].forEach(function(cfg) {
        var el = document.getElementById(cfg[0]);
        var hint = document.getElementById(cfg[1]);
        if (!el) return;
        el.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = this.value.replace(/\b\w/g, function(c) { return c.toUpperCase(); });
            try { this.setSelectionRange(pos, pos); } catch(e) {}
            var v = this.value.trim();
            if (!v) { setNeutral(this, hint, cfg[2]); return; }
            if (/[^a-zA-ZÀ-ÿ\s\-\.\']/.test(v)) {
                this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-\.\']/g, '');
                setWarn(this, hint, 'Numbers and special characters are not allowed.');
            } else {
                setOk(this, hint, 'Looks good.');
            }
        });
    });

    /* ── License number: auto-uppercase + format check ── */
    var licenseEl   = document.getElementById('license_number');
    var licenseHint = document.getElementById('hint-license');
    if (licenseEl) {
        licenseEl.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            try { this.setSelectionRange(pos, pos); } catch(e) {}
            var v = this.value.trim();
            if (!v) { setNeutral(this, licenseHint, 'LTO format: N01-23-456789. Auto-uppercased.'); return; }
            if (/^[A-Z]\d{2}-\d{2}-\d{6}$/.test(v)) {
                setOk(this, licenseHint, 'Valid LTO license number format.');
            } else {
                setWarn(this, licenseHint, 'Expected format: N01-23-456789');
            }
        });
    }

    /* ── Expiry date: warn if expired ── */
    var expiryEl   = document.getElementById('license_expiry_date');
    var expiryHint = document.getElementById('hint-expiry');
    if (expiryEl) {
        expiryEl.addEventListener('change', function () {
            var v = this.value;
            if (!v) { setNeutral(this, expiryHint, 'Expiration date printed on the license card.'); return; }
            var chosen = new Date(v), today = new Date(); today.setHours(0,0,0,0);
            if (chosen < today) {
                setWarn(this, expiryHint, 'This license is already EXPIRED.');
            } else {
                setOk(this, expiryHint, 'License is still valid.');
            }
        });
    }

    /* ── Contact number: auto-format 09XX-XXX-XXXX ── */
    var contactEl   = document.getElementById('contact_number');
    var contactHint = document.getElementById('hint-contact');
    if (contactEl) {
        contactEl.addEventListener('input', function () {
            var digits = this.value.replace(/\D/g, '').slice(0, 11);
            var fmt = digits;
            if (digits.length > 7)      fmt = digits.slice(0,4) + '-' + digits.slice(4,7) + '-' + digits.slice(7);
            else if (digits.length > 4) fmt = digits.slice(0,4) + '-' + digits.slice(4);
            this.value = fmt;
            if (!digits) { setNeutral(this, contactHint, 'PH mobile format: 09XX-XXX-XXXX (11 digits).'); return; }
            if (digits.length === 11 && /^09\d{9}$/.test(digits)) {
                setOk(this, contactHint, 'Valid PH mobile number.');
            } else if (digits.length === 11) {
                setWarn(this, contactHint, 'PH numbers must start with 09.');
            } else {
                setWarn(this, contactHint, digits.length + '/11 digits. Keep typing…');
            }
        });
    }

    /* ── Photo preview — wire to both picker inputs after init ── */
    document.addEventListener('change', function (e) {
        if (!e.target.matches('.photo-picker-input[name="photo"]')) return;
        var preview = document.getElementById('photoPreview');
        if (e.target.files && e.target.files[0]) {
            if (e.target.files[0].size > 5 * 1024 * 1024) {
                alert('Photo exceeds 5 MB. Please choose a smaller file.');
                e.target.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function (ev) {
                preview.innerHTML = '<img src="' + ev.target.result + '" style="width:96px;height:96px;object-fit:cover;border-radius:50%;">';
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    /* ── Double-submit protection ── */
    document.getElementById('motoristForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving…';
        }
    });

})();
</script>
@endpush
