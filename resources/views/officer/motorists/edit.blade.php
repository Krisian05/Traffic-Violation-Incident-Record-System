@extends('layouts.mobile')
@section('title', 'Edit Motorist')
@section('back_url', route('officer.motorists.show', $violator))

@section('content')

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.motorists.update', $violator) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if($errors->any())
            <div class="mob-alert mob-alert-danger">
                <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            {{-- Personal Info --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Personal Info</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name', $violator->first_name) }}" required
                       class="form-control mob-input @error('first_name') is-invalid @enderror">
                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Middle Name</label>
                <input type="text" name="middle_name" value="{{ old('middle_name', $violator->middle_name) }}"
                       class="form-control mob-input @error('middle_name') is-invalid @enderror"
                       placeholder="Optional">
                @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" value="{{ old('last_name', $violator->last_name) }}" required
                       class="form-control mob-input @error('last_name') is-invalid @enderror">
                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Date of Birth</label>
                    <input type="date" name="date_of_birth"
                           value="{{ old('date_of_birth', $violator->date_of_birth?->format('Y-m-d')) }}"
                           class="form-control mob-input @error('date_of_birth') is-invalid @enderror">
                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="mob-label">Gender</label>
                    <select name="gender" class="form-select mob-select @error('gender') is-invalid @enderror">
                        <option value="">— Select —</option>
                        <option value="Male"   {{ old('gender', $violator->gender) === 'Male'   ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $violator->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Civil Status</label>
                    <select name="civil_status" class="form-select mob-select @error('civil_status') is-invalid @enderror">
                        <option value="">— Select —</option>
                        <option value="Single"    {{ old('civil_status', $violator->civil_status) === 'Single'    ? 'selected' : '' }}>Single</option>
                        <option value="Married"   {{ old('civil_status', $violator->civil_status) === 'Married'   ? 'selected' : '' }}>Married</option>
                        <option value="Widowed"   {{ old('civil_status', $violator->civil_status) === 'Widowed'   ? 'selected' : '' }}>Widowed</option>
                        <option value="Separated" {{ old('civil_status', $violator->civil_status) === 'Separated' ? 'selected' : '' }}>Separated</option>
                    </select>
                    @error('civil_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="mob-label">Blood Type</label>
                    <select name="blood_type" class="form-select mob-select @error('blood_type') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $bt)
                        <option value="{{ $bt }}" {{ old('blood_type', $violator->blood_type) === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                    @error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Place of Birth</label>
                <input type="text" name="place_of_birth"
                       value="{{ old('place_of_birth', $violator->place_of_birth) }}"
                       class="form-control mob-input @error('place_of_birth') is-invalid @enderror"
                       placeholder="City / Municipality of birth">
                @error('place_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Height (cm)</label>
                    <input type="number" name="height" value="{{ old('height', $violator->height) }}" min="50" max="250" step="0.1"
                           class="form-control mob-input @error('height') is-invalid @enderror"
                           placeholder="e.g. 165">
                    @error('height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="mob-label">Weight (kg)</label>
                    <input type="number" name="weight" value="{{ old('weight', $violator->weight) }}" min="10" max="300" step="0.1"
                           class="form-control mob-input @error('weight') is-invalid @enderror"
                           placeholder="e.g. 60">
                    @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Valid ID Presented</label>
                <input type="text" name="valid_id" value="{{ old('valid_id', $violator->valid_id) }}"
                       class="form-control mob-input @error('valid_id') is-invalid @enderror"
                       placeholder="e.g. PhilSys ID, Passport, UMID">
                @error('valid_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $violator->email) }}"
                       class="form-control mob-input @error('email') is-invalid @enderror"
                       placeholder="e.g. juan@email.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- License --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">License</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">License Number</label>
                <input type="text" name="license_number" value="{{ old('license_number', $violator->license_number) }}"
                       class="form-control mob-input @error('license_number') is-invalid @enderror"
                       placeholder="e.g. N01-01-123456">
                @error('license_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-2 mb-3">
                <div class="col-7">
                    <label class="mob-label">License Type</label>
                    <select name="license_type" class="form-select mob-select @error('license_type') is-invalid @enderror">
                        <option value="">— Select —</option>
                        <option value="Non-Professional" {{ old('license_type', $violator->license_type) === 'Non-Professional' ? 'selected' : '' }}>Non-Professional</option>
                        <option value="Professional"     {{ old('license_type', $violator->license_type) === 'Professional'     ? 'selected' : '' }}>Professional</option>
                    </select>
                    @error('license_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-5">
                    <label class="mob-label">Expiry Date</label>
                    <input type="date" name="license_expiry_date"
                           value="{{ old('license_expiry_date', $violator->license_expiry_date?->format('Y-m-d')) }}"
                           class="form-control mob-input @error('license_expiry_date') is-invalid @enderror">
                    @error('license_expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Date Issued</label>
                <input type="date" name="license_issued_date"
                       value="{{ old('license_issued_date', $violator->license_issued_date?->format('Y-m-d')) }}"
                       class="form-control mob-input @error('license_issued_date') is-invalid @enderror">
                @error('license_issued_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label d-block mb-2">Restriction Codes</label>
                @php $currentRestrictions = array_filter(explode(',', $violator->license_restriction ?? '')); @endphp
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['A','A1','B','B1','B2','C','D','BE','CE'] as $rc)
                    <label style="display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .7rem;border-radius:8px;border:1.5px solid #cbd5e1;background:#f8fafc;font-size:.8rem;font-weight:600;cursor:pointer;">
                        <input type="checkbox" name="license_restriction[]" value="{{ $rc }}"
                               {{ (is_array(old('license_restriction')) ? in_array($rc, old('license_restriction')) : in_array($rc, $currentRestrictions)) ? 'checked' : '' }}
                               style="accent-color:#2563eb;">
                        {{ $rc }}
                    </label>
                    @endforeach
                </div>
                @error('license_restriction')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Conditions / Remarks</label>
                <textarea name="license_conditions" rows="2"
                          class="form-control mob-input @error('license_conditions') is-invalid @enderror"
                          placeholder="e.g. Must wear corrective lenses"
                          style="min-height:auto;resize:none;">{{ old('license_conditions', $violator->license_conditions) }}</textarea>
                @error('license_conditions')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Contact --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Contact</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number', $violator->contact_number) }}"
                       class="form-control mob-input @error('contact_number') is-invalid @enderror"
                       placeholder="e.g. 09XX-XXX-XXXX">
                @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Temporary / Current Address</label>
                <textarea name="address" rows="2"
                          class="form-control mob-input @error('address') is-invalid @enderror"
                          placeholder="Barangay / Municipality / Province"
                          style="min-height:auto;resize:none;">{{ old('address', $violator->temporary_address) }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Permanent Address</label>
                <textarea name="permanent_address" rows="2"
                          class="form-control mob-input @error('permanent_address') is-invalid @enderror"
                          placeholder="Street, Barangay, City / Municipality, Province"
                          style="min-height:auto;resize:none;">{{ old('permanent_address', $violator->permanent_address) }}</textarea>
                @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <span class="mob-hint">Leave blank if same as temporary address above.</span>
            </div>

            {{-- Photo --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Photo</span>
                <span class="mob-form-divider-line"></span>
            </div>

            @if($violator->photo)
            <div class="d-flex align-items-center gap-3 mb-3 p-3" style="background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">
                <img src="{{ uploaded_file_url($violator->photo) }}" alt="Current photo"
                     style="width:52px;height:52px;border-radius:12px;object-fit:cover;flex-shrink:0;">
                <div>
                    <div style="font-size:.75rem;font-weight:600;color:#0f172a;">Current photo</div>
                    <div style="font-size:.68rem;color:#94a3b8;margin-top:.1rem;">Upload a new one to replace</div>
                </div>
            </div>
            @endif

            <div class="mb-4">
                <input type="file" name="photo" accept="image/*"
                       class="form-control mob-input @error('photo') is-invalid @enderror">
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="mob-btn-primary mb-2">
                <i class="ph-bold ph-check"></i> Save Changes
            </button>
            <a href="{{ route('officer.motorists.show', $violator) }}" class="mob-btn-outline">
                <i class="ph ph-x-circle"></i> Cancel
            </a>
        </form>
    </div>
</div>

@endsection
