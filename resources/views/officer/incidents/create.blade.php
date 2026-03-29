@extends('layouts.mobile')
@section('title', 'Record Incident')
@section('back_url', route('officer.incidents.index'))

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

/* Photo preview grid */
#photoGrid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .5rem;
    margin-top: .65rem;
}
.photo-thumb {
    position: relative;
    aspect-ratio: 1;
    border-radius: 10px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
}
.photo-thumb img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.photo-thumb-count {
    display: flex; align-items: center; justify-content: center;
    aspect-ratio: 1;
    border-radius: 10px;
    background: #e2e8f0;
    font-size: .75rem; font-weight: 700; color: #64748b;
    border: 1.5px dashed #94a3b8;
}
</style>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.incidents.store') }}" enctype="multipart/form-data" id="incidentForm">
            @csrf

            @if($errors->any())
            <div class="mob-alert mob-alert-danger">
                <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            {{-- ── Incident Info ── --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Incident Info</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-7">
                    <label class="mob-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_incident" id="date_of_incident"
                           value="{{ old('date_of_incident', now()->format('Y-m-d')) }}"
                           max="{{ now()->format('Y-m-d') }}" required
                           class="form-control mob-input @error('date_of_incident') is-invalid @enderror">
                    @error('date_of_incident')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <span class="mob-hint" id="hint-date">Must not be a future date.</span>
                    @enderror
                </div>
                <div class="col-5">
                    <label class="mob-label">Time</label>
                    <input type="time" name="time_of_incident" id="time_of_incident"
                           value="{{ old('time_of_incident') }}"
                           class="form-control mob-input @error('time_of_incident') is-invalid @enderror">
                    @error('time_of_incident')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <span class="mob-hint">24-hr format. Optional.</span>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Location <span class="text-danger">*</span></label>
                <input type="text" name="location" id="location" value="{{ old('location') }}" required
                       class="form-control mob-input @error('location') is-invalid @enderror"
                       placeholder="Street / Barangay / Municipality" autocomplete="off">
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <span class="mob-hint" id="hint-location">Be specific: Street name, Barangay, Municipality.</span>
                @enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Description</label>
                <textarea name="description" id="description" rows="2"
                          class="form-control mob-input @error('description') is-invalid @enderror"
                          style="min-height:auto;resize:none;"
                          placeholder="Brief description of what happened...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <span class="mob-hint" id="hint-desc">Optional. Max 2,000 characters. <span id="desc-count">0</span>/2000</span>
                @enderror
            </div>

            {{-- ── Motorists ── --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Motorists Involved</span>
                <span class="mob-form-divider-line"></span>
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-bottom:.75rem;">Minimum 2 motorists required</div>

            <div id="motorists-container">
                @php $oldMotorists = old('motorists', [
                    ['violator_id'=>'','motorist_name'=>'','motorist_license'=>'','incident_charge_type_id'=>'','notes'=>''],
                    ['violator_id'=>'','motorist_name'=>'','motorist_license'=>'','incident_charge_type_id'=>'','notes'=>'']
                ]); @endphp

                @foreach($oldMotorists as $mi => $m)
                <div class="motorist-row mb-2" style="background:#f8fafc;border-radius:12px;border:1.5px solid #e2e8f0;padding:.875rem;" data-index="{{ $mi }}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div style="font-size:.7rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">
                            Motorist #{{ $mi + 1 }}
                        </div>
                        @if($mi >= 2)
                        <button type="button" class="btn btn-sm remove-motorist"
                                style="color:#dc2626;background:none;border:none;font-size:.75rem;padding:0;font-weight:600;">
                            <i class="ph ph-x-circle me-1"></i>Remove
                        </button>
                        @endif
                    </div>

                    <div class="mb-2">
                        <label class="mob-label" style="font-size:.62rem;">Link to registered motorist</label>
                        <select name="motorists[{{ $mi }}][violator_id]"
                                class="form-select mob-select violator-select"
                                style="font-size:.85rem;">
                            <option value="">— Unregistered / enter name below —</option>
                            @foreach($violators as $v)
                            <option value="{{ $v->id }}" data-license="{{ $v->license_number }}"
                                    {{ ($m['violator_id'] ?? '') == $v->id ? 'selected' : '' }}>
                                {{ $v->last_name }}, {{ $v->first_name }}
                                @if($v->license_number) ({{ $v->license_number }}) @endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-12">
                            <input type="text" name="motorists[{{ $mi }}][motorist_name]"
                                   value="{{ $m['motorist_name'] ?? '' }}"
                                   class="form-control mob-input motorist-name-field"
                                   placeholder="Full name (if unregistered)">
                            @error("motorists.{$mi}.motorist_name")
                            <div style="font-size:.72rem;color:#dc2626;margin-top:.2rem;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-7">
                            <input type="text" name="motorists[{{ $mi }}][motorist_license]"
                                   value="{{ $m['motorist_license'] ?? '' }}"
                                   class="form-control mob-input"
                                   placeholder="License number">
                        </div>
                        <div class="col-5">
                            <select name="motorists[{{ $mi }}][incident_charge_type_id]"
                                    class="form-select mob-select">
                                <option value="">— Charge —</option>
                                @foreach($chargeTypes as $ct)
                                <option value="{{ $ct->id }}" {{ ($m['incident_charge_type_id'] ?? '') == $ct->id ? 'selected' : '' }}>
                                    {{ $ct->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <input type="text" name="motorists[{{ $mi }}][notes]"
                                   value="{{ $m['notes'] ?? '' }}"
                                   class="form-control mob-input"
                                   placeholder="Notes (optional)">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <button type="button" id="add-motorist"
                    style="display:flex;align-items:center;justify-content:center;gap:.4rem;width:100%;min-height:42px;border-radius:10px;border:1.5px dashed #93c5fd;background:transparent;color:#1d4ed8;font-weight:700;font-size:.85rem;cursor:pointer;margin-bottom:1.1rem;transition:background .15s;"
                    onmouseenter="this.style.background='#eff6ff'" onmouseleave="this.style.background='transparent'">
                <i class="ph ph-plus-circle"></i> Add Another Motorist
            </button>

            {{-- ── Scene Photos ── --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Scene Photos</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-4">
                <label class="mob-label">Incident Photos <span style="font-size:.68rem;color:#94a3b8;">(up to 6)</span></label>

                {{-- Upload trigger area --}}
                <div id="photoUploadArea"
                     onclick="document.getElementById('incidentPhotos').click()"
                     style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.4rem;min-height:80px;border-radius:12px;border:2px dashed #93c5fd;background:#f8fafc;cursor:pointer;padding:.75rem;transition:background .15s;"
                     onmouseenter="this.style.background='#eff6ff'" onmouseleave="this.style.background='#f8fafc'">
                    <i class="ph ph-camera-plus" style="font-size:1.8rem;color:#93c5fd;"></i>
                    <div style="font-size:.78rem;font-weight:600;color:#1d4ed8;">Tap to add photos</div>
                </div>
                <input type="file" name="incident_photos[]" id="incidentPhotos"
                       accept="image/*" capture="environment" multiple
                       class="d-none @error('incident_photos') is-invalid @enderror">

                {{-- Thumbnails --}}
                <div id="photoGrid"></div>
                <span class="mob-hint" id="hint-photos">Scene photos, ticket photos, etc. JPG/PNG, max 20 MB each.</span>
                @error('incident_photos')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
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

@endsection

@php
    $violatorsForJs = $violators->map(fn($v) => [
        'id'      => $v->id,
        'name'    => "{$v->last_name}, {$v->first_name}" . ($v->license_number ? " ({$v->license_number})" : ''),
        'license' => $v->license_number ?? '',
    ]);
    $chargeTypesForJs = $chargeTypes->map(fn($c) => ['id' => $c->id, 'name' => $c->name]);
@endphp

<div id="page-data"
     data-motorist-count="{{ count($oldMotorists ?? []) }}"
     data-violators="{{ json_encode($violatorsForJs, JSON_HEX_TAG|JSON_HEX_QUOT) }}"
     data-charge-types="{{ json_encode($chargeTypesForJs, JSON_HEX_TAG|JSON_HEX_QUOT) }}"
     hidden></div>

@push('scripts')
<script>
// @ts-nocheck
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

    /* ── Location: min length guidance ── */
    var locationEl   = document.getElementById('location');
    var locationHint = document.getElementById('hint-location');
    if (locationEl) {
        locationEl.addEventListener('input', function () {
            var v = this.value.trim();
            if (!v) { setNeutral(this, locationHint, 'Be specific: Street name, Barangay, Municipality.'); return; }
            if (v.length < 10) {
                setWarn(this, locationHint, 'Too short. Be more specific (e.g. "P. Gomez St., Brgy. 1, Balamban").');
            } else {
                setOk(this, locationHint, 'Looks good.');
            }
        });
    }

    /* ── Date: warn future date ── */
    var dateEl   = document.getElementById('date_of_incident');
    var dateHint = document.getElementById('hint-date');
    if (dateEl) {
        dateEl.addEventListener('change', function () {
            var v = this.value;
            if (!v) { setNeutral(this, dateHint, 'Must not be a future date.'); return; }
            var chosen = new Date(v), today = new Date(); today.setHours(23,59,59,999);
            if (chosen > today) {
                setWarn(this, dateHint, 'Date cannot be in the future.');
            } else {
                setOk(this, dateHint, 'Valid date.');
            }
        });
    }

    /* ── Description: char counter ── */
    var descEl    = document.getElementById('description');
    var descCount = document.getElementById('desc-count');
    if (descEl && descCount) {
        descEl.addEventListener('input', function () {
            var len = this.value.length;
            descCount.textContent = len;
            if (len > 1800) descCount.style.color = '#dc2626';
            else descCount.style.color = '';
        });
    }

    /* ── Photo preview thumbnails ── */
    var photoInput = document.getElementById('incidentPhotos');
    var photoGrid  = document.getElementById('photoGrid');
    var photoHint  = document.getElementById('hint-photos');

    photoInput.addEventListener('change', function () {
        photoGrid.innerHTML = '';
        var files = Array.from(this.files).slice(0, 6);
        if (!files.length) return;

        files.forEach(function (file, i) {
            var thumb = document.createElement('div');
            thumb.className = 'photo-thumb';
            var reader = new FileReader();
            reader.onload = function (e) {
                thumb.innerHTML = '<img src="' + e.target.result + '" alt="Photo ' + (i+1) + '">';
            };
            reader.readAsDataURL(file);
            photoGrid.appendChild(thumb);
        });

        // Show count badge if exactly 6
        if (this.files.length > 6) {
            var badge = document.createElement('div');
            badge.className = 'photo-thumb-count';
            badge.textContent = '+' + (this.files.length - 6) + ' ignored';
            photoGrid.appendChild(badge);
        }

        if (photoHint) {
            photoHint.className = 'mob-hint hint-ok';
            photoHint.textContent = '✓ ' + files.length + ' photo' + (files.length > 1 ? 's' : '') + ' selected.';
        }
    });

    /* ── Motorist rows ── */
    var _pd = document.getElementById('page-data').dataset;
    var motoristCount   = parseInt(_pd.motoristCount, 10);
    var violatorsJson   = JSON.parse(_pd.violators);
    var chargeTypesJson = JSON.parse(_pd.chargeTypes);

    function buildMotoristRow(index) {
        var options = '<option value="">— Unregistered / enter name below —</option>';
        violatorsJson.forEach(function(v) {
            options += '<option value="'+v.id+'" data-license="'+v.license+'">' + v.name + '</option>';
        });
        var chargeOptions = '<option value="">— Charge —</option>';
        chargeTypesJson.forEach(function(c) {
            chargeOptions += '<option value="'+c.id+'">'+c.name+'</option>';
        });

        return '<div class="motorist-row mb-2" style="background:#f8fafc;border-radius:12px;border:1.5px solid #e2e8f0;padding:.875rem;" data-index="'+index+'">'
             + '<div class="d-flex align-items-center justify-content-between mb-2">'
             + '<div style="font-size:.7rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Motorist #'+(index+1)+'</div>'
             + '<button type="button" class="btn btn-sm remove-motorist" style="color:#dc2626;background:none;border:none;font-size:.75rem;padding:0;font-weight:600;"><i class="ph ph-x-circle me-1"></i>Remove</button>'
             + '</div>'
             + '<div class="mb-2"><label class="mob-label" style="font-size:.62rem;">Link to registered motorist</label>'
             + '<select name="motorists['+index+'][violator_id]" class="form-select mob-select violator-select" style="font-size:.85rem;">'+options+'</select></div>'
             + '<div class="row g-2">'
             + '<div class="col-12"><input type="text" name="motorists['+index+'][motorist_name]" class="form-control mob-input motorist-name-field" placeholder="Full name (if unregistered)"></div>'
             + '<div class="col-7"><input type="text" name="motorists['+index+'][motorist_license]" class="form-control mob-input" placeholder="License number"></div>'
             + '<div class="col-5"><select name="motorists['+index+'][incident_charge_type_id]" class="form-select mob-select">'+chargeOptions+'</select></div>'
             + '<div class="col-12"><input type="text" name="motorists['+index+'][notes]" class="form-control mob-input" placeholder="Notes (optional)"></div>'
             + '</div></div>';
    }

    document.getElementById('add-motorist').addEventListener('click', function() {
        var container = document.getElementById('motorists-container');
        var div = document.createElement('div');
        div.innerHTML = buildMotoristRow(motoristCount);
        container.appendChild(div.firstChild);
        motoristCount++;
        attachViolatorSelect();
    });

    document.getElementById('motorists-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-motorist')) {
            var row = e.target.closest('.motorist-row');
            if (row) row.remove();
        }
    });

    function attachViolatorSelect() {
        document.querySelectorAll('.violator-select').forEach(function(sel) {
            if (sel._attached) return;
            sel._attached = true;
            sel.addEventListener('change', function() {
                var opt = sel.options[sel.selectedIndex];
                var license = opt.dataset.license || '';
                var row = sel.closest('.motorist-row');
                if (row) {
                    var licenseField = row.querySelectorAll('input.mob-input')[1];
                    if (license) licenseField.value = license;
                }
            });
        });
    }
    attachViolatorSelect();

    /* ── Double-submit protection ── */
    document.getElementById('incidentForm').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving…';
        }
    });

})();
</script>
@endpush
