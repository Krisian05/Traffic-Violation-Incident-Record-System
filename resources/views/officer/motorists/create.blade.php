@extends('layouts.mobile')
@section('title', 'New Motorist')
@section('back_url', route('officer.motorists.index'))

@push('styles')
<style>
    /* ── Edit-page overrides (shared with edit) ── */
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

    .edit-section-icon--blue   { background: linear-gradient(135deg,#1d4ed8,#1e40af); color:#fff; box-shadow:0 2px 8px rgba(29,78,216,.28); }
    .edit-section-icon--green  { background: linear-gradient(135deg,#059669,#047857); color:#fff; box-shadow:0 2px 8px rgba(5,150,105,.28); }
    .edit-section-icon--amber  { background: linear-gradient(135deg,#d97706,#b45309); color:#fff; box-shadow:0 2px 8px rgba(217,119,6,.28); }
    .edit-section-icon--violet { background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; box-shadow:0 2px 8px rgba(124,58,237,.28); }

    .edit-section-title {
        font-size: .72rem;
        font-weight: 800;
        color: var(--text-dark);
        text-transform: uppercase;
        letter-spacing: .07em;
    }

    .edit-section-body { padding: 1rem; }

    /* ── Field group ── */
    .field-group { margin-bottom: 1rem; }
    .field-group:last-child { margin-bottom: 0; }

    .mob-label {
        font-size: .65rem !important;
        font-weight: 700 !important;
        color: #475569 !important;
        text-transform: uppercase !important;
        letter-spacing: .06em !important;
        margin-bottom: .4rem !important;
        display: flex !important;
        align-items: center;
        gap: .3rem;
    }

    /* ── Input group ── */
    .field-wrap {
        display: flex;
        align-items: stretch;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        transition: border-color .2s, box-shadow .2s;
    }

    .field-wrap:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 3.5px rgba(37,99,235,.13);
    }

    .field-wrap.is-invalid-wrap {
        border-color: #ef4444;
        box-shadow: 0 0 0 3.5px rgba(239,68,68,.12);
    }

    .field-wrap.field-ok-wrap {
        border-color: #16a34a;
        box-shadow: 0 0 0 3.5px rgba(22,163,74,.1);
    }

    .field-adorn {
        width: 44px;
        min-height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border-right: 1.5px solid #e2e8f0;
        color: #94a3b8;
        font-size: 1.1rem;
        flex-shrink: 0;
        transition: background .2s, color .2s, border-color .2s;
    }

    .field-wrap:focus-within .field-adorn {
        background: #eff6ff;
        color: #2563eb;
        border-right-color: #bfdbfe;
    }

    .field-wrap.is-invalid-wrap .field-adorn {
        background: #fff1f2;
        color: #ef4444;
        border-right-color: #fecaca;
    }

    .field-wrap.field-ok-wrap .field-adorn {
        background: #f0fdf4;
        color: #16a34a;
        border-right-color: #bbf7d0;
    }

    .field-adorn--top {
        align-items: flex-start;
        padding-top: .8rem;
    }

    .field-wrap .mob-input,
    .field-wrap .mob-select {
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        outline: none !important;
        flex: 1;
        min-width: 0;
        background: #fff;
        padding-left: .8rem !important;
        font-size: .9rem !important;
        color: #0f172a !important;
        min-height: 46px;
    }

    .field-wrap .mob-input::placeholder { color: #cbd5e1; }

    .field-wrap .mob-input:focus,
    .field-wrap .mob-select:focus {
        box-shadow: none !important;
        border: none !important;
        outline: none !important;
        background: #fafcff;
    }

    /* hint below field */
    .field-hint {
        font-size: .68rem;
        color: #94a3b8;
        margin-top: .28rem;
        display: flex;
        align-items: center;
        gap: .28rem;
        line-height: 1.4;
    }
    .field-hint.hint-ok   { color: #16a34a; }
    .field-hint.hint-warn { color: #ef4444; }

    .field-error {
        font-size: .71rem;
        color: #ef4444;
        margin-top: .3rem;
        display: flex;
        align-items: center;
        gap: .28rem;
        font-weight: 500;
    }
    .field-error i { font-size: .8rem; }

    /* ── Restriction code chips ── */
    .rc-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .5rem;
    }

    .rc-chip {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .18rem;
        padding: .55rem .4rem;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        transition: all .18s;
        -webkit-tap-highlight-color: transparent;
    }

    .rc-chip input[type=checkbox] { display: none; }

    .rc-chip-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        color: #94a3b8;
        transition: all .18s;
    }

    .rc-chip-label {
        font-size: .72rem;
        font-weight: 800;
        color: #64748b;
        letter-spacing: .03em;
        transition: color .18s;
    }

    .rc-chip.checked {
        border-color: #2563eb;
        background: #eff6ff;
    }

    .rc-chip.checked .rc-chip-icon {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        box-shadow: 0 2px 6px rgba(37,99,235,.35);
    }

    .rc-chip.checked .rc-chip-label { color: #1d4ed8; }
    .rc-chip:active { transform: scale(.94); }

    /* ── Photo avatar ── */
    .photo-avatar-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .65rem;
        padding: 1rem 0 .25rem;
    }

    .photo-avatar-outer {
        position: relative;
        width: 88px;
        height: 88px;
        cursor: pointer;
    }

    .photo-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: #eff6ff;
        border: 2.5px dashed #93c5fd;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: border-color .2s;
    }

    .photo-avatar-outer:hover .photo-avatar { border-color: #2563eb; }

    .photo-avatar-edit {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg,#2563eb,#1d4ed8);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(37,99,235,.45);
        border: 2px solid #fff;
        z-index: 2;
    }

    /* ── Submit strip ── */
    .submit-strip {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.04);
        border: 1px solid rgba(0,0,0,.04);
        padding: 1rem;
    }
</style>
@endpush

@section('content')

{{-- Header strip --}}
<div style="display:flex;align-items:center;gap:.75rem;padding:.9rem 1rem;background:linear-gradient(135deg,#059669,#047857);border-radius:16px;margin-bottom:.875rem;box-shadow:0 6px 24px rgba(5,150,105,.32);">
    <div style="width:46px;height:46px;border-radius:14px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;flex-shrink:0;">
        <i class="ph-fill ph-user-plus"></i>
    </div>
    <div>
        <div style="font-size:.62rem;font-weight:700;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.07em;">New Record</div>
        <div style="font-size:.97rem;font-weight:800;color:#fff;">Add Motorist</div>
    </div>
</div>

<form method="POST" action="{{ route('officer.motorists.store') }}" enctype="multipart/form-data" id="motoristForm" data-offline-sync="true" data-offline-label="Motorist" data-offline-record-type="motorist-create">
    @csrf

    @if($errors->any())
    <div class="mob-alert mob-alert-danger mb-3">
        <i class="ph-fill ph-warning-circle" style="font-size:1.15rem;flex-shrink:0;"></i>
        <div>{{ $errors->first() }}</div>
    </div>
    @endif

    {{-- ── PHOTO ── --}}
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon edit-section-icon--violet">
                <i class="ph-bold ph-camera"></i>
            </div>
            <span class="edit-section-title">Motorist Photo</span>
        </div>
        <div class="edit-section-body">
            <div class="photo-avatar-wrap">
                <div class="photo-avatar-outer" id="photoPreviewOuter">
                    <div class="photo-avatar" id="photoPreview">
                        <i class="ph-fill ph-user" style="font-size:2.4rem;color:#93c5fd;"></i>
                    </div>
                    <span class="photo-avatar-edit">
                        <i class="ph-bold ph-pencil-simple" style="font-size:.65rem;color:#fff;"></i>
                    </span>
                </div>
                <span style="font-size:.7rem;color:#94a3b8;">Tap photo or use buttons below</span>
            </div>
            <div id="picker-photo"></div>
            @error('photo')<div class="field-error mt-2"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
        </div>
    </div>

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
                <div class="field-wrap @error('first_name') is-invalid-wrap @enderror" id="wrap-first-name">
                    <span class="field-adorn"><i class="ph ph-user"></i></span>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                           class="form-control mob-input" placeholder="e.g. Juan" autocomplete="off">
                </div>
                @error('first_name')
                    <div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>
                @else
                    <div class="field-hint" id="hint-first-name">Letters and spaces only. Will be auto-capitalized.</div>
                @enderror
            </div>

            <div class="field-group">
                <label class="mob-label">Middle Name <span style="font-weight:400;color:var(--text-muted);text-transform:none;letter-spacing:0;">(optional)</span></label>
                <div class="field-wrap @error('middle_name') is-invalid-wrap @enderror" id="wrap-middle-name">
                    <span class="field-adorn"><i class="ph ph-user"></i></span>
                    <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name') }}"
                           class="form-control mob-input" placeholder="e.g. Dela" autocomplete="off">
                </div>
                @error('middle_name')
                    <div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>
                @else
                    <div class="field-hint" id="hint-middle-name">Optional. Leave blank if none.</div>
                @enderror
            </div>

            <div class="field-group">
                <label class="mob-label">Last Name <span class="text-danger">*</span></label>
                <div class="field-wrap @error('last_name') is-invalid-wrap @enderror" id="wrap-last-name">
                    <span class="field-adorn"><i class="ph ph-user"></i></span>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                           class="form-control mob-input" placeholder="e.g. dela Cruz" autocomplete="off">
                </div>
                @error('last_name')
                    <div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>
                @else
                    <div class="field-hint" id="hint-last-name">Letters and spaces only. Will be auto-capitalized.</div>
                @enderror
            </div>

            <div class="row g-2 mb-0">
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Date of Birth</label>
                        <div class="field-wrap @error('date_of_birth') is-invalid-wrap @enderror">
                            <span class="field-adorn"><i class="ph ph-calendar-blank"></i></span>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   class="form-control mob-input">
                        </div>
                        @error('date_of_birth')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Gender</label>
                        <div class="field-wrap @error('gender') is-invalid-wrap @enderror">
                            <span class="field-adorn"><i class="ph ph-gender-intersex"></i></span>
                            <select name="gender" class="form-select mob-select">
                                <option value="">Select</option>
                                <option value="Male"   {{ old('gender') === 'Male'   ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other"  {{ old('gender') === 'Other'  ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        @error('gender')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-0">
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Civil Status</label>
                        <div class="field-wrap @error('civil_status') is-invalid-wrap @enderror">
                            <span class="field-adorn"><i class="ph ph-heart"></i></span>
                            <select name="civil_status" class="form-select mob-select">
                                <option value="">Select</option>
                                <option value="Single"    {{ old('civil_status') === 'Single'    ? 'selected' : '' }}>Single</option>
                                <option value="Married"   {{ old('civil_status') === 'Married'   ? 'selected' : '' }}>Married</option>
                                <option value="Widowed"   {{ old('civil_status') === 'Widowed'   ? 'selected' : '' }}>Widowed</option>
                                <option value="Separated" {{ old('civil_status') === 'Separated' ? 'selected' : '' }}>Separated</option>
                            </select>
                        </div>
                        @error('civil_status')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Blood Type</label>
                        <div class="field-wrap @error('blood_type') is-invalid-wrap @enderror">
                            <span class="field-adorn"><i class="ph ph-drop"></i></span>
                            <select name="blood_type" class="form-select mob-select">
                                <option value="">Select</option>
                                @foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $bt)
                                <option value="{{ $bt }}" {{ old('blood_type') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('blood_type')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Place of Birth</label>
                <div class="field-wrap @error('place_of_birth') is-invalid-wrap @enderror">
                    <span class="field-adorn"><i class="ph ph-map-pin"></i></span>
                    <input type="text" name="place_of_birth" value="{{ old('place_of_birth') }}"
                           class="form-control mob-input" placeholder="City / Municipality">
                </div>
                @error('place_of_birth')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
            </div>

            <div class="row g-2 mb-0">
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Height (cm)</label>
                        <div class="field-wrap @error('height') is-invalid-wrap @enderror">
                            <span class="field-adorn"><i class="ph ph-ruler"></i></span>
                            <input type="number" name="height" value="{{ old('height') }}" min="50" max="250" step="0.1"
                                   class="form-control mob-input" placeholder="165">
                        </div>
                        @error('height')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="field-group">
                        <label class="mob-label">Weight (kg)</label>
                        <div class="field-wrap @error('weight') is-invalid-wrap @enderror">
                            <span class="field-adorn"><i class="ph ph-scales"></i></span>
                            <input type="number" name="weight" value="{{ old('weight') }}" min="10" max="300" step="0.1"
                                   class="form-control mob-input" placeholder="60">
                        </div>
                        @error('weight')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label class="mob-label">Valid ID Presented</label>
                <div class="field-wrap @error('valid_id') is-invalid-wrap @enderror">
                    <span class="field-adorn"><i class="ph ph-identification-card"></i></span>
                    <input type="text" name="valid_id" value="{{ old('valid_id') }}"
                           class="form-control mob-input" placeholder="PhilSys ID / Passport / UMID">
                </div>
                @error('valid_id')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
            </div>

            <div class="field-group">
                <label class="mob-label">Email Address</label>
                <div class="field-wrap @error('email') is-invalid-wrap @enderror">
                    <span class="field-adorn"><i class="ph ph-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control mob-input" placeholder="juan@email.com">
                </div>
                @error('email')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    {{-- ── LICENSE ── --}}
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon edit-section-icon--green">
                <i class="ph-bold ph-identification-card"></i>
            </div>
            <span class="edit-section-title">Driver's License</span>
        </div>
        <div class="edit-section-body">

            <div class="field-group">
                <label class="mob-label">License Number</label>
                <div class="field-wrap @error('license_number') is-invalid-wrap @enderror" id="wrap-license-number">
                    <span class="field-adorn"><i class="ph ph-hash"></i></span>
                    <input type="text" name="license_number" id="license_number" value="{{ old('license_number') }}"
                           class="form-control mob-input" placeholder="N01-01-123456"
                           style="text-transform:uppercase;" autocomplete="off">
                </div>
                @error('license_number')
                    <div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>
                @else
                    <div class="field-hint" id="hint-license">LTO format: N01-23-456789. Auto-uppercased.</div>
                @enderror
            </div>

            <div class="row g-2 mb-0">
                <div class="col-7">
                    <div class="field-group">
                        <label class="mob-label">License Type</label>
                        <div class="field-wrap @error('license_type') is-invalid-wrap @enderror">
                            <span class="field-adorn"><i class="ph ph-certificate"></i></span>
                            <select name="license_type" class="form-select mob-select">
                                <option value="">Select</option>
                                <option value="Non-Professional" {{ old('license_type') === 'Non-Professional' ? 'selected' : '' }}>Non-Professional</option>
                                <option value="Professional"     {{ old('license_type') === 'Professional'     ? 'selected' : '' }}>Professional</option>
                            </select>
                        </div>
                        @error('license_type')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-5">
                    <div class="field-group">
                        <label class="mob-label">Expiry Date</label>
                        <div class="field-wrap @error('license_expiry_date') is-invalid-wrap @enderror" id="wrap-expiry">
                            <span class="field-adorn"><i class="ph ph-calendar-x"></i></span>
                            <input type="date" name="license_expiry_date" id="license_expiry_date"
                                   value="{{ old('license_expiry_date') }}"
                                   class="form-control mob-input">
                        </div>
                        @error('license_expiry_date')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="field-hint mb-2" id="hint-expiry" style="margin-top:-.25rem;">Expiration date printed on the license card.</div>

            <div class="field-group">
                <label class="mob-label">Date Issued</label>
                <div class="field-wrap @error('license_issued_date') is-invalid-wrap @enderror">
                    <span class="field-adorn"><i class="ph ph-calendar-check"></i></span>
                    <input type="date" name="license_issued_date" value="{{ old('license_issued_date') }}"
                           class="form-control mob-input">
                </div>
                @error('license_issued_date')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
            </div>

            <div class="field-group">
                <label class="mob-label" style="margin-bottom:.55rem;">Restriction Codes</label>
                <div class="rc-grid" id="restriction-wrap">
                    @foreach(['A','A1','B','B1','B2','C','D','BE','CE'] as $rc)
                    @php $isChecked = is_array(old('license_restriction')) && in_array($rc, old('license_restriction')); @endphp
                    <label class="rc-chip {{ $isChecked ? 'checked' : '' }}">
                        <input type="checkbox" name="license_restriction[]" value="{{ $rc }}" {{ $isChecked ? 'checked' : '' }}>
                        <span class="rc-chip-icon">
                            <i class="ph {{ $isChecked ? 'ph-fill ph-check' : 'ph ph-car' }}"></i>
                        </span>
                        <span class="rc-chip-label">{{ $rc }}</span>
                    </label>
                    @endforeach
                </div>
                @error('license_restriction')<div class="field-error mt-2"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                <div class="field-hint mt-2">Select all applicable restriction codes from the license card.</div>
            </div>

            <div class="field-group">
                <label class="mob-label">Conditions / Remarks</label>
                <div class="field-wrap @error('license_conditions') is-invalid-wrap @enderror" style="align-items:flex-start;">
                    <span class="field-adorn field-adorn--top"><i class="ph ph-note-pencil"></i></span>
                    <textarea name="license_conditions" rows="2"
                              class="form-control mob-input"
                              placeholder="e.g. Must wear corrective lenses"
                              style="min-height:auto;resize:none;">{{ old('license_conditions') }}</textarea>
                </div>
                @error('license_conditions')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
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
                <div class="field-wrap @error('contact_number') is-invalid-wrap @enderror" id="wrap-contact">
                    <span class="field-adorn"><i class="ph ph-device-mobile"></i></span>
                    <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}"
                           class="form-control mob-input" placeholder="09XX-XXX-XXXX" maxlength="13">
                </div>
                @error('contact_number')
                    <div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>
                @else
                    <div class="field-hint" id="hint-contact">PH mobile format: 09XX-XXX-XXXX (11 digits).</div>
                @enderror
            </div>

            <div class="field-group">
                @include('partials.location-selector', [
                    'fieldName' => 'address',
                    'required'  => false,
                    'label'     => 'Temporary / Current Address',
                    'inputSize' => '',
                ])
            </div>

            <div class="field-group">
                <label class="mob-label">Permanent Address</label>
                <div class="field-wrap @error('permanent_address') is-invalid-wrap @enderror" style="align-items:flex-start;">
                    <span class="field-adorn field-adorn--top"><i class="ph ph-map-trifold"></i></span>
                    <textarea name="permanent_address" rows="2"
                              class="form-control mob-input"
                              placeholder="Street, Barangay, City / Province"
                              style="min-height:auto;resize:none;">{{ old('permanent_address') }}</textarea>
                </div>
                @error('permanent_address')<div class="field-error"><i class="ph ph-warning-circle"></i>{{ $message }}</div>@enderror
                <div style="display:flex;align-items:center;gap:.35rem;margin-top:.35rem;">
                    <i class="ph ph-info" style="font-size:.8rem;color:var(--text-muted);"></i>
                    <span style="font-size:.68rem;color:var(--text-muted);">Leave blank if same as temporary address.</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── SUBMIT ── --}}
    <div class="submit-strip">
        <button type="submit" class="mob-btn-primary" id="submitBtn">
            <i class="ph-bold ph-floppy-disk" style="font-size:1.05rem;"></i>
            Save Motorist
        </button>
    </div>

    <div class="submit-strip" id="offlineMotoristActions" style="margin-top:.85rem;display:none;">
        <div id="offlineMotoristActionsTitle" style="font-size:.76rem;font-weight:800;color:#0f172a;">Offline Actions</div>
        <div id="offlineMotoristActionsText" style="font-size:.7rem;color:#64748b;line-height:1.5;margin-top:.2rem;">Save this motorist while offline first, then use the buttons below to add linked records on this device.</div>
        <a href="{{ route('officer.offline.violations.create') }}" id="offlineMotoristViolationLink" class="mob-btn-primary mob-btn-danger" style="margin-top:.8rem;text-decoration:none;pointer-events:none;opacity:.55;">
            <i class="ph-bold ph-file-plus"></i>
            Save Motorist Offline First
        </a>
        <a href="{{ route('officer.offline.vehicles.create') }}" id="offlineMotoristVehicleLink" class="mob-btn-outline" style="margin-top:.55rem;text-decoration:none;pointer-events:none;opacity:.55;">
            <i class="ph-fill ph-car-simple"></i>
            Save Motorist Offline First
        </a>
    </div>

</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initPhotoPicker('picker-photo', 'photo', { multiple: false });

    // Make avatar clickable → open gallery
    const avatarOuter = document.getElementById('photoPreviewOuter');
    if (avatarOuter) {
        avatarOuter.addEventListener('click', function () {
            const galBtn = document.querySelector('#picker-photo .mob-photo-picker-btn:not(.camera)');
            if (galBtn) galBtn.click();
        });
    }

    // Restriction chips
    document.querySelectorAll('.rc-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            const cb  = chip.querySelector('input[type=checkbox]');
            const ico = chip.querySelector('.rc-chip-icon i');
            const on  = cb.checked;
            chip.classList.toggle('checked', on);
            ico.className = on ? 'ph ph-fill ph-check' : 'ph ph-car';
        });
    });
});

(function () {

    /* ── Helpers ── */
    function setOk(wrap, hint, msg) {
        if (wrap) { wrap.classList.remove('is-invalid-wrap'); wrap.classList.add('field-ok-wrap'); }
        if (hint) { hint.className = 'field-hint hint-ok'; hint.innerHTML = '<i class="ph ph-check-circle"></i>' + msg; }
    }
    function setWarn(wrap, hint, msg) {
        if (wrap) { wrap.classList.remove('field-ok-wrap'); wrap.classList.add('is-invalid-wrap'); }
        if (hint) { hint.className = 'field-hint hint-warn'; hint.innerHTML = '<i class="ph ph-warning-circle"></i>' + msg; }
    }
    function setNeutral(wrap, hint, msg) {
        if (wrap) { wrap.classList.remove('field-ok-wrap', 'is-invalid-wrap'); }
        if (hint) { hint.className = 'field-hint'; hint.textContent = msg; }
    }

    /* ── Name fields ── */
    [['first_name','wrap-first-name','hint-first-name','Letters and spaces only. Will be auto-capitalized.'],
     ['middle_name','wrap-middle-name','hint-middle-name','Optional. Leave blank if none.'],
     ['last_name','wrap-last-name','hint-last-name','Letters and spaces only. Will be auto-capitalized.']
    ].forEach(function(cfg) {
        var el   = document.getElementById(cfg[0]);
        var wrap = document.getElementById(cfg[1]);
        var hint = document.getElementById(cfg[2]);
        if (!el) return;
        el.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = this.value.replace(/\b\w/g, function(c) { return c.toUpperCase(); });
            try { this.setSelectionRange(pos, pos); } catch(e) {}
            var v = this.value.trim();
            if (!v) { setNeutral(wrap, hint, cfg[3]); return; }
            if (/[^a-zA-ZÀ-ÿ\s\-\.\']/.test(v)) {
                this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-\.\']/g, '');
                setWarn(wrap, hint, 'Numbers and special characters are not allowed.');
            } else {
                setOk(wrap, hint, 'Looks good.');
            }
        });
    });

    /* ── License number ── */
    var licenseEl   = document.getElementById('license_number');
    var licenseWrap = document.getElementById('wrap-license-number');
    var licenseHint = document.getElementById('hint-license');
    if (licenseEl) {
        licenseEl.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            try { this.setSelectionRange(pos, pos); } catch(e) {}
            var v = this.value.trim();
            if (!v) { setNeutral(licenseWrap, licenseHint, 'LTO format: N01-23-456789. Auto-uppercased.'); return; }
            if (/^[A-Z]\d{2}-\d{2}-\d{6}$/.test(v)) {
                setOk(licenseWrap, licenseHint, 'Valid LTO license number format.');
            } else {
                setWarn(licenseWrap, licenseHint, 'Expected format: N01-23-456789');
            }
        });
    }

    /* ── Expiry date ── */
    var expiryEl   = document.getElementById('license_expiry_date');
    var expiryWrap = document.getElementById('wrap-expiry');
    var expiryHint = document.getElementById('hint-expiry');
    if (expiryEl) {
        expiryEl.addEventListener('change', function () {
            var v = this.value;
            if (!v) { setNeutral(expiryWrap, expiryHint, 'Expiration date printed on the license card.'); return; }
            var chosen = new Date(v), today = new Date(); today.setHours(0,0,0,0);
            if (chosen < today) {
                setWarn(expiryWrap, expiryHint, 'This license is already EXPIRED.');
            } else {
                setOk(expiryWrap, expiryHint, 'License is still valid.');
            }
        });
    }

    /* ── Contact number ── */
    var contactEl   = document.getElementById('contact_number');
    var contactWrap = document.getElementById('wrap-contact');
    var contactHint = document.getElementById('hint-contact');
    if (contactEl) {
        contactEl.addEventListener('input', function () {
            var digits = this.value.replace(/\D/g, '').slice(0, 11);
            var fmt = digits;
            if (digits.length > 7)      fmt = digits.slice(0,4) + '-' + digits.slice(4,7) + '-' + digits.slice(7);
            else if (digits.length > 4) fmt = digits.slice(0,4) + '-' + digits.slice(4);
            this.value = fmt;
            if (!digits) { setNeutral(contactWrap, contactHint, 'PH mobile format: 09XX-XXX-XXXX (11 digits).'); return; }
            if (digits.length === 11 && /^09\d{9}$/.test(digits)) {
                setOk(contactWrap, contactHint, 'Valid PH mobile number.');
            } else if (digits.length === 11) {
                setWarn(contactWrap, contactHint, 'PH numbers must start with 09.');
            } else {
                setWarn(contactWrap, contactHint, digits.length + '/11 digits. Keep typing…');
            }
        });
    }

    /* ── Photo avatar preview ── */
    var avatarEl  = document.getElementById('photoPreview');
    var pickerDiv = document.getElementById('picker-photo');
    if (pickerDiv && avatarEl) {
        var observer = new MutationObserver(function () {
            var syncInp = pickerDiv.querySelector('.picker-sync-input');
            if (syncInp && syncInp.files && syncInp.files[0]) {
                var reader = new FileReader();
                reader.onload = function (ev) {
                    avatarEl.innerHTML = '<img src="' + ev.target.result + '" style="width:88px;height:88px;object-fit:cover;border-radius:50%;">';
                };
                reader.readAsDataURL(syncInp.files[0]);
            } else {
                avatarEl.innerHTML = '<i class="ph-fill ph-user" style="font-size:2.4rem;color:#93c5fd;"></i>';
            }
        });
        observer.observe(pickerDiv, { childList: true, subtree: true });
    }

    /* ── Double-submit protection ── */
    document.getElementById('motoristForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving…';
        }
    });

    window.addEventListener('tvirs-offline-record-queued', function (event) {
        var record = event.detail && event.detail.record ? event.detail.record : null;
        if (!record || record.recordType !== 'motorist-create') {
            return;
        }

        activateOfflineMotoristLinks(record);
    });

    var actionsTitle = document.getElementById('offlineMotoristActionsTitle');
    var actionsText = document.getElementById('offlineMotoristActionsText');
    var violationLink = document.getElementById('offlineMotoristViolationLink');
    var vehicleLink = document.getElementById('offlineMotoristVehicleLink');
    var offlineActions = document.getElementById('offlineMotoristActions');
    var motoristForm = document.getElementById('motoristForm');
    var baseOfflineViolationHref = '{{ route("officer.offline.violations.create") }}';
    var baseOfflineVehicleHref = '{{ route("officer.offline.vehicles.create") }}';
    var defaultActionsTitle = actionsTitle ? actionsTitle.textContent : "Offline Actions";
    var defaultActionsText = actionsText ? actionsText.textContent : "Save this motorist while offline first, then use the buttons below to add linked records on this device.";
    var defaultViolationLabel = violationLink ? violationLink.innerHTML : '<i class="ph-bold ph-file-plus"></i> Save Motorist Offline First';
    var defaultVehicleLabel = vehicleLink ? vehicleLink.innerHTML : '<i class="ph-fill ph-car-simple"></i> Save Motorist Offline First';

    function syncOfflineMotoristActionVisibility() {
        if (!offlineActions) {
            return;
        }

        offlineActions.style.display = navigator.onLine ? 'none' : '';
    }

    function resetOfflineMotoristLinks() {
        if (!violationLink || !actionsText || !actionsTitle) {
            return;
        }

        actionsTitle.textContent = defaultActionsTitle;
        actionsText.textContent = defaultActionsText;
        violationLink.href = baseOfflineViolationHref;
        violationLink.style.pointerEvents = 'none';
        violationLink.style.opacity = '.55';
        violationLink.innerHTML = defaultViolationLabel;

        if (vehicleLink) {
            vehicleLink.href = baseOfflineVehicleHref;
            vehicleLink.style.pointerEvents = 'none';
            vehicleLink.style.opacity = '.55';
            vehicleLink.innerHTML = defaultVehicleLabel;
        }
    }

    function activateOfflineMotoristLinks(record) {
        if (!record || !violationLink || !actionsText || !actionsTitle) {
            return;
        }

        if (motoristForm && record.offlineMotoristKey) {
            motoristForm.dataset.offlineMotoristKey = record.offlineMotoristKey;
        }

        syncOfflineMotoristActionVisibility();
        actionsTitle.textContent = 'Motorist queued on this device';
        actionsText.textContent = 'You can record a violation or add a vehicle for this unsynced motorist right away. Each record will publish after the motorist syncs.';
        violationLink.href = baseOfflineViolationHref + '#motorist=' + encodeURIComponent(record.offlineMotoristKey || '');
        violationLink.style.pointerEvents = 'auto';
        violationLink.style.opacity = '1';
        violationLink.innerHTML = '<i class="ph-bold ph-file-plus"></i> Record Violation Now';

        if (vehicleLink) {
            vehicleLink.href = baseOfflineVehicleHref + '#motorist=' + encodeURIComponent(record.offlineMotoristKey || '');
            vehicleLink.style.pointerEvents = 'auto';
            vehicleLink.style.opacity = '1';
            vehicleLink.innerHTML = '<i class="ph-fill ph-car-simple"></i> Add Vehicle Now';
        }
    }

    function activateOfflineMotoristLinksFromForm() {
        if (!motoristForm) {
            return false;
        }

        var offlineKey = motoristForm.dataset.offlineMotoristKey || '';
        if (!offlineKey) {
            return false;
        }

        activateOfflineMotoristLinks({ offlineMotoristKey: offlineKey });
        return true;
    }

    function refreshOfflineMotoristLinks() {
        if (!motoristForm) {
            resetOfflineMotoristLinks();
            return;
        }

        if (activateOfflineMotoristLinksFromForm()) {
            return;
        }

        if (!window.TvirsOffline || typeof window.TvirsOffline.findOfflineMotoristForForm !== 'function') {
            resetOfflineMotoristLinks();
            return;
        }

        window.TvirsOffline.findOfflineMotoristForForm(motoristForm).then(function (motorist) {
            if (!motorist) {
                resetOfflineMotoristLinks();
                return;
            }

            if (motorist.offlineMotoristKey) {
                motoristForm.dataset.offlineMotoristKey = motorist.offlineMotoristKey;
            }
            activateOfflineMotoristLinks(motorist);
        }).catch(function () {
            return null;
        });
    }

    syncOfflineMotoristActionVisibility();
    refreshOfflineMotoristLinks();
    if (motoristForm) {
        motoristForm.addEventListener('submit', function () {
            if (!navigator.onLine) {
                window.setTimeout(refreshOfflineMotoristLinks, 180);
                window.setTimeout(refreshOfflineMotoristLinks, 700);
            }
        });
        motoristForm.querySelectorAll('input, textarea, select').forEach(function (field) {
            field.addEventListener('input', function () {
                window.setTimeout(refreshOfflineMotoristLinks, 0);
            });
            field.addEventListener('change', function () {
                window.setTimeout(refreshOfflineMotoristLinks, 0);
            });
        });
    }
    window.addEventListener('tvirs-offline-updated', refreshOfflineMotoristLinks);
    window.addEventListener('online', syncOfflineMotoristActionVisibility);
    window.addEventListener('offline', function () {
        syncOfflineMotoristActionVisibility();
        refreshOfflineMotoristLinks();
    });

})();
</script>
@endpush
