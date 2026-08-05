@extends('layouts.app')
@section('title', 'Add LGU')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('lgus.index') }}" style="color:#0369a1;text-decoration:none;">LGUs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add LGU</li>
@endsection

@section('content')

<div class="lgu-form-card">

    <div class="lgu-form-header">
        <span class="lgu-form-icon" style="background:linear-gradient(135deg,#0369a1,#075985);box-shadow:0 3px 10px rgba(3,105,161,.35);">
            <i class="bi bi-building" style="color:#fff;font-size:1rem;"></i>
        </span>
        <div>
            <div class="lgu-form-title">Add LGU</div>
            <div class="lgu-form-sub">Onboard a new municipality/city into TVIRS</div>
        </div>
    </div>

    <div class="lgu-form-body">
        <form method="POST" action="{{ route('lgus.store') }}" enctype="multipart/form-data">
            @csrf

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
                           value="{{ old('code') }}"
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
                           value="{{ old('name') }}"
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
                           value="{{ old('province', 'Cebu') }}"
                           required maxlength="150">
                    @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <input type="hidden" name="psgc_city_code" id="psgc_city_code" value="{{ old('psgc_city_code') }}">
            @error('psgc_city_code')<div class="text-danger mb-3" style="font-size:.82rem;">{{ $message }}</div>@enderror

            <div class="mb-3">
                <label class="lgu-label">Ordinance Reference</label>
                <div class="input-group">
                    <span class="input-group-text lgu-ig-icon" style="background:#faf5ff;border-color:#e9d5ff;">
                        <i class="bi bi-file-earmark-text" style="color:#7e22ce;"></i>
                    </span>
                    <input type="text" name="ordinance_reference"
                           class="form-control lgu-input @error('ordinance_reference') is-invalid @enderror"
                           value="{{ old('ordinance_reference') }}"
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
                           value="{{ old('treasurer_office') }}"
                           maxlength="255" placeholder="e.g. Municipal Treasurer's Office - Balamban">
                    @error('treasurer_office')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>


            <div class="d-flex gap-2 pt-2">
                <button type="submit" class="lgu-submit-btn">
                    <i class="bi bi-check-lg"></i> Save LGU
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
        if (this.value && !name.value) {
            name.value = this.options[this.selectedIndex].dataset.name;
        }
    });
})();
</script>
@endpush
