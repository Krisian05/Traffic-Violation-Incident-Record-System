@extends('layouts.app')
@section('title', 'Edit Motorist')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('violators.index') }}" style="color:#78716c;">Motorists</a></li>
    <li class="breadcrumb-item"><a href="{{ route('violators.show', $violator) }}" style="color:#78716c;">{{ $violator->full_name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page" style="color:#44403c;">Edit</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:42px;height:42px;background:linear-gradient(135deg,#d97706,#b45309);flex-shrink:0;">
        <i class="bi bi-person-fill text-white" style="font-size:1rem;"></i>
    </div>
    <div>
        <h5 class="mb-0 fw-700" style="color:#1c1917;">Edit Motorist — {{ $violator->full_name }}</h5>
        <div style="font-size:.8rem;color:#78716c;">Update motorist profile and license details</div>
    </div>
</div>

<form method="POST" action="{{ route('violators.update', $violator) }}" enctype="multipart/form-data" id="editMotoristForm" novalidate>
@csrf
@method('PUT')

{{-- ── PERSONAL INFORMATION ── --}}
<div class="vlt-section-card">
    <div class="vlt-card-header">
        <span class="vlt-section-icon" style="background:#dbeafe;">
            <i class="bi bi-person-fill" style="color:#1d4ed8;"></i>
        </span>
        <div>
            <div class="vlt-section-title">Personal Information</div>
            <div class="vlt-section-sub">{{ $violator->full_name }}</div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">

            {{-- Photo --}}
            <div class="col-12 d-flex align-items-start gap-4 mb-2">
                <div>
                    <div id="photoPreview" class="rounded border d-flex align-items-center justify-content-center"
                         style="width:110px;height:130px;background:#eff6ff;border-color:#bfdbfe!important;overflow:hidden;">
                        @if($violator->photo)
                            <img src="{{ uploaded_file_url($violator->photo) }}"
                                 style="width:110px;height:130px;object-fit:cover;">
                        @else
                            <i class="bi bi-person-fill fs-1 text-muted"></i>
                        @endif
                    </div>
                </div>
                <div class="flex-fill">
                    <label class="form-label">Photograph</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-camera-fill" style="color:#1d4ed8;font-size:.8rem;"></i></span>
                        <input type="file" name="photo" id="photoInput" class="form-control @error('photo') is-invalid @enderror"
                            accept="image/jpeg,image/png">
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <small class="vlt-hint"><i class="bi bi-info-circle"></i> JPG or PNG only, max 20 MB. Leave blank to keep current photo.</small>
                </div>
            </div>

            {{-- Name --}}
            <div class="col-md-4">
                <label class="form-label">First Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge-fill" style="color:#1d4ed8;font-size:.8rem;"></i></span>
                    <input type="text" name="first_name" id="first_name"
                        class="form-control @error('first_name') is-invalid @enderror"
                        value="{{ old('first_name', $violator->first_name) }}" required
                        placeholder="e.g. Juan">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Letters and spaces only. No numbers.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Middle Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge" style="color:#78716c;font-size:.8rem;"></i></span>
                    <input type="text" name="middle_name" id="middle_name"
                        class="form-control @error('middle_name') is-invalid @enderror"
                        value="{{ old('middle_name', $violator->middle_name) }}"
                        placeholder="e.g. Santos (optional)">
                    @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Optional. Leave blank if none.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge-fill" style="color:#1d4ed8;font-size:.8rem;"></i></span>
                    <input type="text" name="last_name" id="last_name"
                        class="form-control @error('last_name') is-invalid @enderror"
                        value="{{ old('last_name', $violator->last_name) }}" required
                        placeholder="e.g. Dela Cruz">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Letters and spaces only. No numbers.</small>
            </div>

            {{-- DOB / Place of Birth --}}
            <div class="col-md-4">
                <label class="form-label">Date of Birth</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-event-fill" style="color:#d97706;font-size:.8rem;"></i></span>
                    <input type="text" name="date_of_birth" id="dp-dob"
                        class="form-control @error('date_of_birth') is-invalid @enderror"
                        value="{{ old('date_of_birth', $violator->date_of_birth?->format('Y-m-d')) }}"
                        placeholder="YYYY-MM-DD">
                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Click to pick date. Must be a past date.</small>
            </div>
            <div class="col-md-8">
                <label class="form-label">Place of Birth</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt-fill" style="color:#16a34a;font-size:.8rem;"></i></span>
                    <input type="text" name="place_of_birth"
                        class="form-control @error('place_of_birth') is-invalid @enderror"
                        value="{{ old('place_of_birth', $violator->place_of_birth) }}"
                        placeholder="e.g. Dagupan City, Pangasinan">
                    @error('place_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Format: City/Municipality, Province.</small>
            </div>

            {{-- Sex / Civil Status / Blood Type --}}
            <div class="col-md-4">
                <label class="form-label">Sex</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-gender-ambiguous" style="color:#7c3aed;font-size:.8rem;"></i></span>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach(['Male','Female','Other'] as $g)
                            <option value="{{ $g }}" {{ old('gender', $violator->gender) == $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Biological sex as stated in ID.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Civil Status</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-journal-bookmark-fill" style="color:#0369a1;font-size:.8rem;"></i></span>
                    <select name="civil_status" class="form-select @error('civil_status') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach(['Single','Married','Widowed','Separated'] as $cs)
                            <option value="{{ $cs }}" {{ old('civil_status', $violator->civil_status) == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                        @endforeach
                    </select>
                    @error('civil_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Current marital status.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Blood Type</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-droplet-fill" style="color:#dc2626;font-size:.8rem;"></i></span>
                    <select name="blood_type" class="form-select @error('blood_type') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                            <option value="{{ $bt }}" {{ old('blood_type', $violator->blood_type) == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                    @error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> As printed on valid ID or blood card.</small>
            </div>

            {{-- Height / Weight / Valid ID --}}
            <div class="col-md-3">
                <label class="form-label">Height (cm)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-arrow-up-short" style="color:#57534e;font-size:1rem;"></i></span>
                    <input type="number" name="height" min="50" max="250" step="0.1"
                        class="form-control @error('height') is-invalid @enderror"
                        value="{{ old('height', $violator->height) }}" placeholder="e.g. 165">
                    @error('height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Enter in centimeters</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Weight (kg)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-speedometer2" style="color:#0d9488;font-size:.8rem;"></i></span>
                    <input type="number" name="weight" min="10" max="300" step="0.1"
                        class="form-control @error('weight') is-invalid @enderror"
                        value="{{ old('weight', $violator->weight) }}" placeholder="e.g. 60">
                    @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Enter in kilograms</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Valid ID</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-card-text" style="color:#1d4ed8;font-size:.8rem;"></i></span>
                    <input type="text" name="valid_id"
                        class="form-control @error('valid_id') is-invalid @enderror"
                        value="{{ old('valid_id', $violator->valid_id) }}" placeholder="e.g. SSS, UMID, Passport">
                    @error('valid_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Type of government ID presented (e.g. SSS, PhilHealth, Passport).</small>
            </div>

            {{-- Contact / Email --}}
            <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone-fill" style="color:#16a34a;font-size:.8rem;"></i></span>
                    <input type="text" name="contact_number" id="contact_number"
                        class="form-control @error('contact_number') is-invalid @enderror"
                        value="{{ old('contact_number', $violator->contact_number) }}"
                        placeholder="09XX-XXX-XXXX" maxlength="13">
                    @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint" id="contact-hint"><i class="bi bi-info-circle"></i> PH mobile format: 09XX-XXX-XXXX (11 digits).</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope-fill" style="color:#1d4ed8;font-size:.8rem;"></i></span>
                    <input type="email" name="email" id="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $violator->email) }}" placeholder="example@email.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint" id="email-hint"><i class="bi bi-info-circle"></i> Must be a valid email address.</small>
            </div>

            {{-- Addresses --}}
            <div class="col-md-6">
                <label class="form-label">Temporary Address</label>
                <div class="input-group align-items-start">
                    <span class="input-group-text"><i class="bi bi-house-fill" style="color:#ea580c;font-size:.8rem;"></i></span>
                    <textarea name="temporary_address" class="form-control @error('temporary_address') is-invalid @enderror"
                        rows="2" placeholder="Blk/Lot, Street, Barangay, City/Municipality">{{ old('temporary_address', $violator->temporary_address) }}</textarea>
                    @error('temporary_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Current or temporary residence address.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Permanent Address</label>
                <div class="input-group align-items-start">
                    <span class="input-group-text"><i class="bi bi-house-door-fill" style="color:#1d4ed8;font-size:.8rem;"></i></span>
                    <textarea name="permanent_address" class="form-control @error('permanent_address') is-invalid @enderror"
                        rows="2" placeholder="Blk/Lot, Street, Barangay, City/Municipality">{{ old('permanent_address', $violator->permanent_address) }}</textarea>
                    @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Home address as stated in valid ID.</small>
            </div>

        </div>
    </div>
</div>

{{-- ── LICENSE INFORMATION ── --}}
<div class="vlt-section-card">
    <div class="vlt-card-header">
        <span class="vlt-section-icon" style="background:#fef9c3;">
            <i class="bi bi-credit-card-2-front-fill" style="color:#ca8a04;"></i>
        </span>
        <div>
            <div class="vlt-section-title">License Information</div>
            <div class="vlt-section-sub">Driver's license details</div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">

            {{-- License Number --}}
            <div class="col-md-6">
                <label class="form-label">License Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-upc-scan" style="color:#ca8a04;font-size:.8rem;"></i></span>
                    <input type="text" name="license_number" id="license_number"
                        class="form-control @error('license_number') is-invalid @enderror"
                        value="{{ old('license_number', $violator->license_number) }}"
                        placeholder="N01-23-456789" maxlength="20" style="text-transform:uppercase;">
                    @error('license_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint" id="license-hint"><i class="bi bi-info-circle"></i> LTO format: N01-23-456789. Auto-uppercased.</small>
            </div>

            {{-- License Type --}}
            <div class="col-md-6">
                <label class="form-label">License Type</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag-fill" style="color:#ca8a04;font-size:.8rem;"></i></span>
                    <select name="license_type" class="form-select @error('license_type') is-invalid @enderror">
                        <option value="">— Select Type —</option>
                        <option value="Non-Professional" {{ old('license_type', $violator->license_type) == 'Non-Professional' ? 'selected' : '' }}>Non-Professional</option>
                        <option value="Professional"     {{ old('license_type', $violator->license_type) == 'Professional'     ? 'selected' : '' }}>Professional</option>
                    </select>
                    @error('license_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Non-Professional: private use. Professional: for-hire/commercial.</small>
            </div>

            {{-- Restriction Code --}}
            <div class="col-md-4">
                <label class="form-label">Restriction Code</label>
                @php
                    $savedCodes = old('license_restriction')
                        ? (array) old('license_restriction')
                        : array_filter(explode(',', $violator->license_restriction ?? ''));
                @endphp
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock-fill" style="color:#ca8a04;font-size:.8rem;"></i></span>
                    <div class="restr-box form-control">
                    @foreach(['A'=>'Motorcycle','A1'=>'Motorcycle w/ Sidecar','B'=>'Light Vehicle','B1'=>'Light Vehicle (Professional)','B2'=>'Light Vehicle w/ Trailer','C'=>'Medium/Heavy Truck','D'=>'Bus','BE'=>'Light + Heavy Trailer','CE'=>'Large Truck + Trailer'] as $val => $desc)
                    <label class="restr-chip" title="{{ $val }} — {{ $desc }}">
                        <input type="checkbox" name="license_restriction[]" value="{{ $val }}"
                            {{ in_array($val, $savedCodes) ? 'checked' : '' }}>
                        <span>{{ $val }}</span>
                    </label>
                    @endforeach
                    </div>
                </div>
                @error('license_restriction')<div class="text-danger mt-1" style="font-size:.8rem;">{{ $message }}</div>@enderror
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Tap to select all codes printed on the license.</small>
            </div>

            {{-- Date Issued --}}
            <div class="col-md-4">
                <label class="form-label">Date Issued</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-check-fill" style="color:#16a34a;font-size:.8rem;"></i></span>
                    <input type="text" name="license_issued_date" id="dp-issued"
                        class="form-control @error('license_issued_date') is-invalid @enderror"
                        value="{{ old('license_issued_date', $violator->license_issued_date?->format('Y-m-d')) }}"
                        placeholder="YYYY-MM-DD">
                    @error('license_issued_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Date printed on the license card. Must be a past date.</small>
            </div>

            {{-- Expiry Date --}}
            <div class="col-md-4">
                <label class="form-label">Expiry Date</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-x-fill" style="color:#dc2626;font-size:.8rem;"></i></span>
                    <input type="text" name="license_expiry_date" id="dp-expiry"
                        class="form-control @error('license_expiry_date') is-invalid @enderror"
                        value="{{ old('license_expiry_date', $violator->license_expiry_date?->format('Y-m-d')) }}"
                        placeholder="YYYY-MM-DD">
                    @error('license_expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint" id="expiry-hint"><i class="bi bi-info-circle"></i> Expiration date on the license. Can be a future date.</small>
            </div>

            {{-- Conditions / Remarks --}}
            <div class="col-12">
                <label class="form-label">Conditions / Remarks</label>
                <div class="input-group align-items-start">
                    <span class="input-group-text"><i class="bi bi-chat-text-fill" style="color:#78716c;font-size:.8rem;"></i></span>
                    <textarea name="license_conditions" class="form-control @error('license_conditions') is-invalid @enderror"
                        rows="2" placeholder="e.g. Corrective lenses must be worn while driving">{{ old('license_conditions', $violator->license_conditions) }}</textarea>
                    @error('license_conditions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="vlt-hint"><i class="bi bi-info-circle"></i> Any special conditions or restrictions printed on the license. Leave blank if none.</small>
            </div>

        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <button type="submit" class="vlt-form-submit">
        <i class="bi bi-check-lg me-1"></i> Update Motorist
    </button>
    <a href="{{ route('violators.show', $violator) }}" class="btn d-inline-flex align-items-center gap-2 rounded-pill" style="border:1.5px solid #d6d3d1;color:#78716c;background:#fff;font-weight:500;"><i class="bi bi-x-circle" style="font-size:.85rem;"></i> Cancel</a>
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
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff; border: none; border-radius: 8px;
    font-size: .875rem; font-weight: 600; cursor: pointer;
    box-shadow: 0 3px 10px rgba(220,38,38,.3);
    transition: transform .15s, box-shadow .15s;
}
.vlt-form-submit:hover {
    color: #fff; transform: translateY(-1px);
    box-shadow: 0 5px 16px rgba(220,38,38,.4);
}
/* ── Restriction code chips ── */
.restr-box {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: .35rem;
    padding: 0 .5rem;
    height: calc(1.5em + .75rem + 2px);
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.restr-box::-webkit-scrollbar { display: none; }
.restr-chip { cursor: pointer; display: inline-block; }
.restr-chip input[type="checkbox"] { display: none; }
.restr-chip span {
    display: inline-block;
    padding: .22rem .6rem;
    border-radius: 20px;
    font-size: .73rem; font-weight: 700;
    background: #fff; color: #92400e;
    border: 1.5px solid #fde68a;
    transition: all .15s;
    user-select: none;
    letter-spacing: .02em;
}
.restr-chip span:hover { border-color: #ca8a04; background: #fef9c3; transform: translateY(-1px); }
.restr-chip input[type="checkbox"]:checked + span {
    background: #ca8a04; color: #fff; border-color: #ca8a04;
    box-shadow: 0 2px 6px rgba(202,138,4,.28);
}
.vlt-hint { display: block; margin-top: .3rem; font-size: .72rem; color: #a8a29e; line-height: 1.4; }
.vlt-hint.hint-ok   { color: #16a34a; }
.vlt-hint.hint-warn { color: #dc2626; }
.vlt-hint i { font-size: .7rem; margin-right: .2rem; }
.form-control.field-ok, .form-select.field-ok   { border-color: #16a34a !important; box-shadow: 0 0 0 3px rgba(22,163,74,.1) !important; }
.form-control.field-warn, .form-select.field-warn { border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220,38,38,.1) !important; }
.fw-600 { font-weight: 600; }

@media (max-width: 767px) {
    .col-12.d-flex.align-items-start.gap-4 { flex-direction: column; gap: .75rem !important; }
    #photoPreview { width: 90px !important; height: 107px !important; }
}
</style>

@endsection

@push('scripts')
<script>
(function () {
    function setOk(input, hintEl, msg) {
        input.classList.remove('field-warn'); input.classList.add('field-ok');
        if (hintEl) { hintEl.className = 'vlt-hint hint-ok'; hintEl.innerHTML = '<i class="bi bi-check-circle"></i> ' + msg; }
    }
    function setWarn(input, hintEl, msg) {
        input.classList.remove('field-ok'); input.classList.add('field-warn');
        if (hintEl) { hintEl.className = 'vlt-hint hint-warn'; hintEl.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' + msg; }
    }
    function setNeutral(input, hintEl, msg) {
        input.classList.remove('field-ok', 'field-warn');
        if (hintEl) { hintEl.className = 'vlt-hint'; hintEl.innerHTML = '<i class="bi bi-info-circle"></i> ' + msg; }
    }

    /* ── Name fields ── */
    ['first_name', 'middle_name', 'last_name'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = this.value.replace(/\b\w/g, function (c) { return c.toUpperCase(); });
            this.setSelectionRange(pos, pos);
            var v = this.value.trim();
            if (!v) { setNeutral(this, null, ''); return; }
            if (/[^a-zA-ZÀ-ÿ\s\-\.]/.test(v)) {
                setWarn(this, null, '');
                this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-\.]/g, '');
            } else {
                setOk(this, null, '');
            }
        });
    });

    /* ── Contact number ── */
    var contactEl   = document.getElementById('contact_number');
    var contactHint = document.getElementById('contact-hint');
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
                setWarn(this, contactHint, 'PH numbers must start with 09 (e.g. 09XX-XXX-XXXX).');
            } else {
                setWarn(this, contactHint, digits.length + '/11 digits entered. Keep typing…');
            }
        });
    }

    /* ── Email ── */
    var emailEl   = document.getElementById('email');
    var emailHint = document.getElementById('email-hint');
    if (emailEl) {
        emailEl.addEventListener('input', function () {
            var v = this.value.trim();
            if (!v) { setNeutral(this, emailHint, 'Must be a valid email address.'); return; }
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
                setOk(this, emailHint, 'Valid email format.');
            } else {
                setWarn(this, emailHint, 'Invalid email. Example: juan@email.com');
            }
        });
    }

    /* ── License number ── */
    var licenseEl   = document.getElementById('license_number');
    var licenseHint = document.getElementById('license-hint');
    if (licenseEl) {
        licenseEl.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
            var v = this.value.trim();
            if (!v) { setNeutral(this, licenseHint, 'LTO format: N01-23-456789. Auto-uppercased.'); return; }
            if (/^[A-Z]\d{2}-\d{2}-\d{6}$/.test(v)) {
                setOk(this, licenseHint, 'Valid LTO license number format.');
            } else {
                setWarn(this, licenseHint, 'Expected format: N01-23-456789');
            }
        });
    }

    /* ── Expiry date warning ── */
    var expiryHint = document.getElementById('expiry-hint');
    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'license_expiry_date') {
            var val = e.target.value;
            if (!val || !expiryHint) return;
            var chosen = new Date(val);
            var today  = new Date(); today.setHours(0,0,0,0);
            if (chosen < today) {
                setWarn(e.target, expiryHint, 'This license is already EXPIRED.');
            } else {
                setOk(e.target, expiryHint, 'License is still valid.');
            }
        }
    });

    /* ── Photo preview ── */
    document.getElementById('photoInput').addEventListener('change', function () {
        var preview = document.getElementById('photoPreview');
        if (this.files && this.files[0]) {
            var file = this.files[0];
            if (file.size > 20 * 1024 * 1024) {
                alert('Photo exceeds 20 MB. Please choose a smaller file.');
                this.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="width:110px;height:130px;object-fit:cover;">';
            };
            reader.readAsDataURL(file);
        }
    });

    /* ── Flatpickr ── */
    flatpickr('#dp-dob', {
        dateFormat: 'Y-m-d', maxDate: 'today',
        defaultDate: document.getElementById('dp-dob').value || null,
        allowInput: true, disableMobile: true,
    });
    applyDateMask(document.getElementById('dp-dob'));

    flatpickr('#dp-issued', {
        dateFormat: 'Y-m-d', maxDate: 'today',
        defaultDate: document.getElementById('dp-issued').value || null,
        allowInput: true, disableMobile: true,
    });
    applyDateMask(document.getElementById('dp-issued'));

    flatpickr('#dp-expiry', {
        dateFormat: 'Y-m-d',
        defaultDate: document.getElementById('dp-expiry').value || null,
        allowInput: true, disableMobile: true,
    });
    applyDateMask(document.getElementById('dp-expiry'));

    /* ── Trigger expiry check on load if value already set ── */
    var expiryInput = document.querySelector('[name="license_expiry_date"]');
    if (expiryInput && expiryInput.value && expiryHint) {
        var chosen = new Date(expiryInput.value);
        var today  = new Date(); today.setHours(0,0,0,0);
        if (chosen < today) {
            setWarn(expiryInput, expiryHint, 'This license is already EXPIRED.');
        } else {
            setOk(expiryInput, expiryHint, 'License is still valid.');
        }
    }

    /* ── Double-submit protection ── */
    document.getElementById('editMotoristForm').addEventListener('submit', function () {
        var btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving…';
        }
    });

})();
</script>
@endpush
