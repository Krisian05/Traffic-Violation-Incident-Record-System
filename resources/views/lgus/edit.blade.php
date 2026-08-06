@extends('layouts.app')
@section('title', 'Edit LGU')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('lgus.index') }}" style="color:#0369a1;text-decoration:none;">LGUs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit LGU</li>
@endsection

@section('content')

<div class="lgu-form-card">

    <div class="lgu-form-header">
        <span class="lgu-form-icon" style="background:linear-gradient(135deg,#0369a1,#075985);box-shadow:0 3px 10px rgba(3,105,161,.35);">
            <i class="bi bi-building" style="color:#fff;font-size:1rem;"></i>
        </span>
        <div>
            <div class="lgu-form-title">Edit LGU</div>
            <div class="lgu-form-sub">{{ $lgu->name }}, {{ $lgu->province }}</div>
        </div>
    </div>

    <div class="lgu-form-body">
        <form method="POST" action="{{ route('lgus.update', $lgu) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="lgu-label">City / Municipality Lookup (Cebu)</label>
                <div class="input-group">
                    <span class="input-group-text lgu-ig-icon" style="background:#f0f9ff;border-color:#bae6fd;">
                        <i class="bi bi-geo-alt-fill" style="color:#0369a1;"></i>
                    </span>
                    <select id="psgc_city_select" class="form-control lgu-input">
                        <option value="">— Optional: pick to auto-fill name &amp; PSGC code —</option>
                    </select>
                </div>
                <div class="form-text">Selecting a city/municipality auto-fills its name and PSGC code below — used to auto-tag citations and incidents recorded in that area.</div>
            </div>

            <div class="mb-3">
                <label class="lgu-label">LGU Code <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text lgu-ig-icon" style="background:#fff1f2;border-color:#fecdd3;">
                        <i class="bi bi-hash" style="color:#dc2626;"></i>
                    </span>
                    <input type="text" name="code"
                           class="form-control lgu-input @error('code') is-invalid @enderror"
                           value="{{ old('code', $lgu->code) }}"
                           maxlength="10" required placeholder="e.g. BAL"
                           style="text-transform:uppercase;">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-text">Short code used in ticket numbers, e.g. TVIRS-CEB-<strong>BAL</strong>-2026-000001.</div>
            </div>

            <div class="mb-3">
                <label class="lgu-label">Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text lgu-ig-icon" style="background:#eff6ff;border-color:#bfdbfe;">
                        <i class="bi bi-signpost-split" style="color:#1d4ed8;"></i>
                    </span>
                    <input type="text" name="name" id="lgu_name"
                           class="form-control lgu-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $lgu->name) }}"
                           required placeholder="e.g. Balamban">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="lgu-label">Province <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text lgu-ig-icon" style="background:#f0fdf4;border-color:#86efac;">
                        <i class="bi bi-map" style="color:#15803d;"></i>
                    </span>
                    <input type="text" name="province"
                           class="form-control lgu-input @error('province') is-invalid @enderror"
                           value="{{ old('province', $lgu->province) }}"
                           required maxlength="150">
                    @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <input type="hidden" name="psgc_city_code" id="psgc_city_code" value="{{ old('psgc_city_code', $lgu->psgc_city_code) }}">
            @error('psgc_city_code')<div class="text-danger mb-3" style="font-size:.82rem;">{{ $message }}</div>@enderror

            <div class="mb-3">
                <label class="lgu-label">Ordinance Reference</label>
                <div class="input-group">
                    <span class="input-group-text lgu-ig-icon" style="background:#faf5ff;border-color:#e9d5ff;">
                        <i class="bi bi-file-earmark-text" style="color:#7e22ce;"></i>
                    </span>
                    <input type="text" name="ordinance_reference"
                           class="form-control lgu-input @error('ordinance_reference') is-invalid @enderror"
                           value="{{ old('ordinance_reference', $lgu->ordinance_reference) }}"
                           maxlength="255" placeholder="e.g. Municipal Ordinance No. 12, s. 2024">
                    @error('ordinance_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="lgu-label">Treasurer's Office</label>
                <div class="input-group">
                    <span class="input-group-text lgu-ig-icon" style="background:#fffbeb;border-color:#fde68a;">
                        <i class="bi bi-cash-coin" style="color:#b45309;"></i>
                    </span>
                    <input type="text" name="treasurer_office"
                           class="form-control lgu-input @error('treasurer_office') is-invalid @enderror"
                           value="{{ old('treasurer_office', $lgu->treasurer_office) }}"
                           maxlength="255" placeholder="e.g. Municipal Treasurer's Office - Balamban">
                    @error('treasurer_office')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Police Station Header Configuration --}}
            <div class="card border-0 mb-4 p-3" style="background:#fef2f2;border-radius:12px;border:1px solid #fecdd3!important;">
                <h6 class="fw-700 mb-3" style="color:#b91c1c;"><i class="bi bi-shield-lock-fill me-2"></i>Police Station &amp; Document Header Settings</h6>

                <div class="mb-3">
                    <label class="lgu-label">Police Station Name</label>
                    <input type="text" name="police_station_name" class="form-control lgu-input"
                           placeholder="e.g. BALAMBAN MUNICIPAL POLICE STATION"
                           value="{{ old('police_station_name', $lgu->police_station_name) }}">
                    <div class="form-text">Displayed on citation documents, motorist records, and incident reports. Auto-generated if left blank.</div>
                </div>

                <div class="mb-3">
                    <label class="lgu-label">Police Station Address</label>
                    <input type="text" name="police_station_address" class="form-control lgu-input"
                           placeholder="e.g. Brgy. Sta Cruz-Sto Nino, Balamban, Cebu"
                           value="{{ old('police_station_address', $lgu->police_station_address) }}">
                    <div class="form-text">Full address printed below the police station name on reports.</div>
                </div>

                <div class="mb-3">
                    <label class="lgu-label">Official LGU Seal / Logo</label>
                    @if($lgu->seal_url)
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="{{ $lgu->seal_url }}" alt="Seal" style="width:48px;height:48px;object-fit:contain;border-radius:6px;border:1px solid #e2e8f0;padding:2px;background:#fff;">
                            <span class="text-muted small">Current LGU seal logo</span>
                        </div>
                    @endif
                    <input type="file" name="seal" class="form-control lgu-input" accept="image/*">
                    <div class="form-text">Upload custom LGU logo/seal image (PNG, JPG, max 10MB).</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="lgu-label">Police Chief / Supervisor ("Noted by:")</label>
                        <input type="text" name="police_chief_name" class="form-control lgu-input"
                               placeholder="e.g. PLTCOL RUEL L BURLAT"
                               value="{{ old('police_chief_name', $lgu->police_chief_name) }}">
                        <div class="form-text">Name printed under "Noted by:" on document signatures.</div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="lgu-label">Police Chief Title / Designation</label>
                        <input type="text" name="police_chief_title" class="form-control lgu-input"
                               placeholder="e.g. Chief of Police"
                               value="{{ old('police_chief_title', $lgu->police_chief_title) }}">
                        <div class="form-text">Designation printed below the police chief name.</div>
                    </div>
                </div>
            </div>

            {{-- SMS Gateway Configuration --}}
            <div class="card border-0 mb-4 p-3" style="background:#f0f9ff;border-radius:12px;border:1px solid #bae6fd!important;">
                <h6 class="fw-700 mb-3" style="color:#0369a1;"><i class="bi bi-chat-left-text-fill me-2"></i>SMS Gateway Configuration</h6>
                
                <div class="mb-3">
                    <label class="lgu-label">Gateway Provider</label>
                    <select name="sms_provider" class="form-select lgu-input" onchange="document.getElementById('edit_tb_fields').style.display = (this.value === 'textbee' ? '' : 'none'); document.getElementById('edit_sem_fields').style.display = (this.value === 'semaphore' ? '' : 'none');">
                        <option value="textbee" {{ old('sms_provider', $lgu->sms_provider ?? 'textbee') === 'textbee' ? 'selected' : '' }}>📱 Android SIM Gateway (Textbee.dev — Free ₱0)</option>
                        <option value="semaphore" {{ old('sms_provider', $lgu->sms_provider) === 'semaphore' ? 'selected' : '' }}>☁️ Semaphore SMS API (Paid Credits)</option>
                        <option value="local" {{ old('sms_provider', $lgu->sms_provider) === 'local' ? 'selected' : '' }}>💻 Local Test Log Gateway (No SMS Sent)</option>
                    </select>
                </div>

                {{-- Textbee --}}
                <div id="edit_tb_fields" style="{{ old('sms_provider', $lgu->sms_provider ?? 'textbee') === 'textbee' ? '' : 'display:none;' }}">
                    <div class="mb-3">
                        <label class="lgu-label">Textbee API Key</label>
                        <input type="password" name="textbee_api_key" class="form-control lgu-input" placeholder="e.g. tb_key_..." value="{{ old('textbee_api_key', $lgu->textbee_api_key) }}">
                    </div>
                    <div class="mb-3">
                        <label class="lgu-label">Textbee Device ID</label>
                        <input type="text" name="textbee_device_id" class="form-control lgu-input" placeholder="e.g. 65a1b2c3..." value="{{ old('textbee_device_id', $lgu->textbee_device_id) }}">
                    </div>
                </div>

                {{-- Semaphore --}}
                <div id="edit_sem_fields" style="{{ old('sms_provider', $lgu->sms_provider) === 'semaphore' ? '' : 'display:none;' }}">
                    <div class="mb-3">
                        <label class="lgu-label">Semaphore SMS API Key</label>
                        <input type="password" name="sms_api_key" class="form-control lgu-input" placeholder="e.g. 9a8b7c6d..." value="{{ old('sms_api_key', $lgu->sms_api_key) }}">
                    </div>
                    <div class="mb-3">
                        <label class="lgu-label">SMS Sender Name</label>
                        <input type="text" name="sms_sender_name" class="form-control lgu-input" maxlength="11" placeholder="e.g. TVIRS" value="{{ old('sms_sender_name', $lgu->sms_sender_name ?? 'TVIRS') }}">
                    </div>
                </div>

                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="sms_auto_send" id="sms_auto_send" value="1" {{ old('sms_auto_send', $lgu->sms_auto_send ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-600" for="sms_auto_send" style="font-size:.85rem;color:#0369a1;">
                        Automatically send SMS citation upon ticket issuance by enforcers
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2 pt-2">
                <button type="submit" class="lgu-submit-btn">
                    <i class="bi bi-check-lg"></i> Update LGU
                </button>
                <a href="{{ route('lgus.index') }}"
                   class="btn d-inline-flex align-items-center gap-2 rounded-pill"
                   style="border:1.5px solid #d6d3d1;color:#78716c;background:#fff;font-weight:500;">
                    <i class="bi bi-x-circle" style="font-size:.85rem;"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.lgu-form-card {
    max-width: 560px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 6px 24px rgba(0,0,0,.06);
    overflow: hidden;
}
.lgu-form-header {
    display: flex; align-items: center; gap: 1rem;
    padding: 1.1rem 1.4rem;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-bottom: 1.5px solid #ece5da;
}
.lgu-form-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.lgu-form-title { font-size: .95rem; font-weight: 700; color: #1c1917; }
.lgu-form-sub   { font-size: .74rem; color: #a8a29e; margin-top: .1rem; }
.lgu-form-body  { padding: 1.4rem; }

.lgu-label {
    font-size: .72rem;
    font-weight: 700;
    color: #78716c;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .4rem;
    display: block;
}
.lgu-ig-icon {
    border-right: none;
    padding: .45rem .75rem;
    border-radius: 10px 0 0 10px !important;
}
.lgu-input {
    border-left: none;
    border-radius: 0 10px 10px 0 !important;
    font-size: .875rem;
}
.lgu-input:focus { box-shadow: none; border-color: #e2d9cf; }

.lgu-submit-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1.25rem;
    border-radius: 10px;
    font-size: .84rem; font-weight: 700;
    background: linear-gradient(135deg, #0369a1, #075985);
    color: #fff;
    border: none;
    box-shadow: 0 2px 8px rgba(3,105,161,.3);
    cursor: pointer;
    transition: all .15s;
}
.lgu-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(3,105,161,.45); }
</style>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const CEBU_PROVINCE_CODE = '072200000';
    const sel  = document.getElementById('psgc_city_select');
    const code = document.getElementById('psgc_city_code');
    const name = document.getElementById('lgu_name');

    fetch('https://psgc.gitlab.io/api/provinces/' + CEBU_PROVINCE_CODE + '/cities-municipalities/')
        .then(res => res.ok ? res.json() : Promise.reject())
        .then(cities => {
            cities
                .slice()
                .sort((a, b) => a.name.localeCompare(b.name))
                .forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.code;
                    opt.textContent = c.name;
                    opt.dataset.name = c.name;
                    sel.appendChild(opt);
                });
            if (code.value) sel.value = code.value;
        })
        .catch(() => {
            sel.innerHTML = '<option value="">— Lookup unavailable, enter details manually —</option>';
            sel.disabled = true;
        });

    sel.addEventListener('change', function () {
        code.value = this.value;
        if (this.value) {
            name.value = this.options[this.selectedIndex].dataset.name;
        }
    });
})();
</script>
@endpush
