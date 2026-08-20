@extends('layouts.app')
@section('title', 'Edit LGU — ' . $lgu->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('lgus.index') }}" style="color:#0369a1;text-decoration:none;">LGUs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit LGU</li>
@endsection

@section('content')

<div class="container-fluid px-0">
    <form method="POST" action="{{ route('lgus.update', $lgu) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- Page Banner --}}
        <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-white rounded-3 shadow-sm border">
            <div class="d-flex align-items-center gap-3">
                <span class="lgu-header-icon">
                    <i class="bi bi-building" style="color:#fff;font-size:1.25rem;"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Edit LGU Configuration</h5>
                    <span class="text-muted small">{{ $lgu->name }}, {{ $lgu->province }} &middot; Code: <strong>{{ $lgu->code }}</strong></span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('lgus.index') }}" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-600">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">
                    <i class="bi bi-check-lg me-1"></i> Update LGU
                </button>
            </div>
        </div>

        <div class="row g-4">
            {{-- CARD 1: Basic LGU Information --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-building text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">1. Basic Information</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="lgu-label">City / Municipality Lookup (Cebu)</label>
                            <div class="input-group">
                                <span class="input-group-text lgu-ig-icon style-sky">
                                    <i class="bi bi-geo-alt-fill text-primary"></i>
                                </span>
                                <select id="psgc_city_select" class="form-select lgu-input">
                                    <option value="">— Optional: pick to auto-fill —</option>
                                </select>
                            </div>
                            <div class="form-text">Selecting a city/municipality auto-fills its name &amp; code.</div>
                        </div>

                        <div class="mb-3">
                            <label class="lgu-label">LGU Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text lgu-ig-icon style-rose">
                                    <i class="bi bi-hash text-danger"></i>
                                </span>
                                <input type="text" name="code"
                                       class="form-control lgu-input @error('code') is-invalid @enderror"
                                       value="{{ old('code', $lgu->code) }}"
                                       maxlength="10" required placeholder="e.g. BAL"
                                       style="text-transform:uppercase;">
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="lgu-label">Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text lgu-ig-icon style-blue">
                                    <i class="bi bi-signpost-split text-primary"></i>
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
                                <span class="input-group-text lgu-ig-icon style-green">
                                    <i class="bi bi-map text-success"></i>
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
                                <span class="input-group-text lgu-ig-icon style-purple">
                                    <i class="bi bi-file-earmark-text text-purple"></i>
                                </span>
                                <input type="text" name="ordinance_reference"
                                       class="form-control lgu-input @error('ordinance_reference') is-invalid @enderror"
                                       value="{{ old('ordinance_reference', $lgu->ordinance_reference) }}"
                                       maxlength="255" placeholder="e.g. Municipal Ordinance No. 12, s. 2024">
                                @error('ordinance_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="lgu-label">Treasurer's Office</label>
                            <div class="input-group">
                                <span class="input-group-text lgu-ig-icon style-amber">
                                    <i class="bi bi-cash-coin text-warning"></i>
                                </span>
                                <input type="text" name="treasurer_office"
                                       class="form-control lgu-input @error('treasurer_office') is-invalid @enderror"
                                       value="{{ old('treasurer_office', $lgu->treasurer_office) }}"
                                       maxlength="255" placeholder="e.g. Municipal Treasurer's Office - Balamban">
                                @error('treasurer_office')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: Police Station & Document Header Settings --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-danger fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">2. Police Station &amp; Document Header</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="lgu-label">Police Station Name</label>
                            <input type="text" name="police_station_name" class="form-control lgu-input"
                                   placeholder="e.g. BALAMBAN MUNICIPAL POLICE STATION"
                                   value="{{ old('police_station_name', $lgu->police_station_name) }}">
                            <div class="form-text">Printed on citation documents &amp; reports. Auto-generated if blank.</div>
                        </div>

                        <div class="mb-3">
                            <label class="lgu-label">Police Station Address</label>
                            <input type="text" name="police_station_address" class="form-control lgu-input"
                                   placeholder="e.g. Brgy. Sta Cruz-Sto Nino, Balamban, Cebu"
                                   value="{{ old('police_station_address', $lgu->police_station_address) }}">
                            <div class="form-text">Full address printed below station name.</div>
                        </div>

                        <div class="mb-3">
                            <label class="lgu-label">Official LGU Seal / Logo</label>
                            @if($lgu->seal_url)
                                <div class="d-flex align-items-center gap-3 mb-2 p-2 bg-light rounded-2 border">
                                    <img src="{{ $lgu->seal_url }}" alt="Seal" style="width:44px;height:44px;object-fit:contain;border-radius:6px;border:1px solid #cbd5e1;padding:2px;background:#fff;">
                                    <div>
                                        <div class="fw-bold text-dark small">Current Seal Image</div>
                                        <div class="text-muted" style="font-size:0.72rem;">Active on official printouts</div>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="seal" class="form-control lgu-input" accept="image/*">
                            <div class="form-text">Upload custom logo/seal (PNG, JPG, max 10MB).</div>
                        </div>

                        <div class="mb-3">
                            <label class="lgu-label">Police Chief / Supervisor ("Noted by:")</label>
                            <input type="text" name="police_chief_name" class="form-control lgu-input"
                                   placeholder="e.g. PLTCOL RUEL L BURLAT"
                                   value="{{ old('police_chief_name', $lgu->police_chief_name) }}">
                            <div class="form-text">Name under "Noted by:" on document signatures.</div>
                        </div>

                        <div class="mb-0">
                            <label class="lgu-label">Chief Title / Designation</label>
                            <input type="text" name="police_chief_title" class="form-control lgu-input"
                                   placeholder="e.g. Chief of Police"
                                   value="{{ old('police_chief_title', $lgu->police_chief_title) }}">
                            <div class="form-text">Designation below police chief name.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 3: SMS Gateway Configuration --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-chat-left-text-fill text-info fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">3. SMS Gateway Configuration</h6>
                    </div>
                    <div class="card-body p-4">
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
                                <input type="password" name="textbee_api_key" class="form-control lgu-input"
                                       placeholder="{{ $lgu->textbee_api_key ? 'Leave blank to keep existing key' : 'e.g. tb_key_...' }}"
                                       autocomplete="new-password">
                                <div class="form-text text-muted">@if($lgu->textbee_api_key)<span class="text-success">✓ Key is set (encrypted)</span> — leave blank to keep it.@else Not configured.@endif</div>
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
                                <input type="password" name="sms_api_key" class="form-control lgu-input"
                                       placeholder="{{ $lgu->sms_api_key ? 'Leave blank to keep existing key' : 'e.g. 9a8b7c6d...' }}"
                                       autocomplete="new-password">
                                <div class="form-text text-muted">@if($lgu->sms_api_key)<span class="text-success">✓ Key is set (encrypted)</span> — leave blank to keep it.@else Not configured.@endif</div>
                            </div>
                            <div class="mb-3">
                                <label class="lgu-label">SMS Sender Name</label>
                                <input type="text" name="sms_sender_name" class="form-control lgu-input" maxlength="11" placeholder="e.g. TVIRS" value="{{ old('sms_sender_name', $lgu->sms_sender_name ?? 'TVIRS') }}">
                            </div>
                        </div>

                        <div class="form-check form-switch mt-3 pt-2 border-top">
                            <input class="form-check-input" type="checkbox" name="sms_auto_send" id="sms_auto_send" value="1" {{ old('sms_auto_send', $lgu->sms_auto_send ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark small" for="sms_auto_send">
                                Automatically send SMS citation upon ticket issuance by enforcers
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- end .row --}}

        {{-- CARD 4: PayMongo Payment Gateway --}}
        <div class="card border-0 shadow-sm rounded-3 mt-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card-2-front-fill text-success fs-5"></i>
                    <h6 class="fw-bold mb-0 text-dark">4. PayMongo Online Payment Gateway</h6>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-600" style="font-size:.72rem;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Keys Encrypted at Rest (AES-256)
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">

                    <div class="col-lg-3">
                        <label class="lgu-label">Gateway Provider</label>
                        <div class="input-group">
                            <span class="input-group-text lgu-ig-icon style-green">
                                <i class="bi bi-wallet2 text-success"></i>
                            </span>
                            <select name="gateway_provider" id="gateway_provider_select" class="form-select lgu-input" onchange="togglePaymongoFields(this.value)">
                                <option value="paymongo" @selected(($lgu->gateway_provider ?? 'paymongo') === 'paymongo')>💳 PayMongo (Active)</option>
                                <option value="none" @selected(($lgu->gateway_provider ?? 'paymongo') === 'none')>🚫 Disabled</option>
                            </select>
                        </div>
                        <div class="form-text">Controls online settlement availability for motorists.</div>
                    </div>

                    <div class="col-lg-9" id="paymongo_fields_wrapper" style="{{ ($lgu->gateway_provider ?? 'paymongo') === 'none' ? 'display:none;' : '' }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="lgu-label">Public Key <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem;">pk_live_...</span></label>
                                <div class="input-group">
                                    <span class="input-group-text lgu-ig-icon style-sky">
                                        <i class="bi bi-key-fill text-primary"></i>
                                    </span>
                                    <input type="text" name="paymongo_public_key"
                                           class="form-control lgu-input @error('paymongo_public_key') is-invalid @enderror"
                                           value="{{ old('paymongo_public_key', $lgu->paymongo_public_key) }}"
                                           placeholder="pk_live_xxxxxxxxxxxx">
                                    @error('paymongo_public_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">Safe to display — publishable client key.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="lgu-label">Secret Key <span class="badge bg-danger-subtle text-danger border" style="font-size:.65rem;">sk_live_...</span></label>
                                <div class="input-group">
                                    <span class="input-group-text lgu-ig-icon style-rose">
                                        <i class="bi bi-shield-lock-fill text-danger"></i>
                                    </span>
                                    <input type="password" name="paymongo_secret_key" id="paymongo_secret_key_input"
                                           class="form-control lgu-input @error('paymongo_secret_key') is-invalid @enderror"
                                           placeholder="{{ $lgu->paymongo_secret_key ? 'Leave blank to keep existing' : 'sk_live_xxxxxxxxxxxx' }}"
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassVisibility('paymongo_secret_key_input', this)" style="border-radius: 0 8px 8px 0 !important; border-color: #cbd5e1; background: #fff;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('paymongo_secret_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">
                                    @if($lgu->paymongo_secret_key)
                                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Key configured (encrypted)</span> — leave blank to keep.
                                    @else
                                        <span class="text-danger fw-600">Not configured.</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="lgu-label">Webhook Secret <span class="badge bg-danger-subtle text-danger border" style="font-size:.65rem;">whsk_...</span></label>
                                <div class="input-group">
                                    <span class="input-group-text lgu-ig-icon style-amber">
                                        <i class="bi bi-link-45deg text-warning"></i>
                                    </span>
                                    <input type="password" name="paymongo_webhook_secret" id="paymongo_webhook_secret_input"
                                           class="form-control lgu-input @error('paymongo_webhook_secret') is-invalid @enderror"
                                           placeholder="{{ $lgu->paymongo_webhook_secret ? 'Leave blank to keep existing' : 'whsk_xxxxxxxxxxxx' }}"
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassVisibility('paymongo_webhook_secret_input', this)" style="border-radius: 0 8px 8px 0 !important; border-color: #cbd5e1; background: #fff;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('paymongo_webhook_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">
                                    @if($lgu->paymongo_webhook_secret)
                                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Secret configured (encrypted)</span> — leave blank to keep.
                                    @else
                                        <span class="text-danger fw-600">From PayMongo Dashboard &rarr; Webhooks.</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9" id="paymongo_disabled_banner" style="{{ ($lgu->gateway_provider ?? 'paymongo') !== 'none' ? 'display:none;' : '' }}">
                        <div class="p-3 bg-light rounded-3 border text-muted d-flex align-items-center gap-2 h-100">
                            <i class="bi bi-info-circle text-secondary fs-5"></i>
                            <span class="small">Online payment gateway is currently <strong>disabled</strong> for this LGU. Motorists will not be presented with the PayMongo checkout option.</span>
                        </div>
                    </div>

                </div>{{-- end .row.g-4 --}}

                <div class="form-check form-switch mt-4 pt-3 border-top">
                    <input class="form-check-input" type="checkbox" name="enable_manual_qr_claim"
                           id="enable_manual_qr_claim" value="1"
                           {{ $lgu->enable_manual_qr_claim ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark small" for="enable_manual_qr_claim">
                        Allow motorists to submit a manual GCash / Maya QR payment claim (with transaction reference screenshot)
                    </label>
                </div>
            </div>
        </div>{{-- end PayMongo card --}}

    </form>
</div>

<style>
.lgu-header-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0369a1, #075985);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(3,105,161,.35);
}
.lgu-label {
    font-size: .72rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .35rem;
    display: block;
}
.lgu-ig-icon {
    border-right: none;
    padding: .45rem .75rem;
    border-radius: 8px 0 0 8px !important;
}
.lgu-input {
    border-radius: 8px !important;
    font-size: .875rem;
}
.input-group .lgu-input {
    border-left: none;
    border-radius: 0 8px 8px 0 !important;
}
.lgu-input:focus { box-shadow: none; border-color: #0369a1; }
.style-sky { background: #f0f9ff; border-color: #bae6fd; }
.style-rose { background: #fff1f2; border-color: #fecdd3; }
.style-blue { background: #eff6ff; border-color: #bfdbfe; }
.style-green { background: #f0fdf4; border-color: #bbf7d0; }
.style-purple { background: #faf5ff; border-color: #e9d5ff; }
.style-amber { background: #fffbeb; border-color: #fde68a; }
.text-purple { color: #7e22ce; }
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

function togglePaymongoFields(val) {
    const fields = document.getElementById('paymongo_fields_wrapper');
    const banner = document.getElementById('paymongo_disabled_banner');
    if (fields) fields.style.display = (val === 'none' ? 'none' : '');
    if (banner) banner.style.display = (val === 'none' ? '' : 'none');
}

function togglePassVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye"></i>';
    }
}
</script>
@endpush
