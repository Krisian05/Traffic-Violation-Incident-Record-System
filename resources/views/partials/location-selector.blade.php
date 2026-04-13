{{--
    Location Selector Partial — Region → Province → City/Municipality → Barangay
    Uses the PSGC public API (psgc.gitlab.io) directly from the browser.

    Parameters:
      $fieldName  — name attr for the hidden location input   (default: 'location')
      $required   — whether location is required              (default: false)
      $label      — section label text                        (default: 'Location')
      $inputSize  — Bootstrap input size: 'sm' or ''          (default: 'sm')
--}}
@php
    $locField    = $fieldName    ?? 'location';
    $locRequired = $required     ?? false;
    $locLabel    = $label        ?? 'Location';
    $locSize     = (isset($inputSize) && $inputSize === '') ? '' : 'sm';
    $locInitial  = $initialValue ?? null;
    // Unique prefix so multiple selectors can coexist on one page
    $uid         = 'loc_' . preg_replace('/[^a-z0-9]/i', '_', $locField);

    // Restore values after validation failure; fall back to the model's saved value on first load
    $oldRegion   = old('_loc_region_code');
    $oldProvince = old('_loc_province_code');
    $oldCity     = old('_loc_city_code');
    $oldBarangay = old('_loc_barangay_code');
    $oldSpecific = old('_loc_specific');
    $oldLocation = old($locField, $locInitial);

    // Show the "current location" banner when editing an existing record
    // that has a saved text value but no PSGC codes yet.
    $showCurrentBanner = !empty($locInitial) && empty($oldRegion);
@endphp

<div>
    {{-- Label --}}
    <label class="form-label fw-500 d-flex align-items-center gap-1" style="font-size:.84rem;">
        <i class="bi bi-geo-alt-fill" style="color:#16a34a;"></i>
        {{ $locLabel }}
        @if($locRequired)<span class="text-danger">*</span>@endif
    </label>

    {{-- Hidden: assembled location string (what gets validated & saved) --}}
    <input type="hidden" name="{{ $locField }}"      id="{{ $uid }}_value"  value="{{ $oldLocation }}">

    {{-- Hidden: PSGC codes — preserved across validation failures --}}
    <input type="hidden" name="_loc_region_code"   id="{{ $uid }}_rcode"  value="{{ $oldRegion }}">
    <input type="hidden" name="_loc_province_code" id="{{ $uid }}_pcode"  value="{{ $oldProvince }}">
    <input type="hidden" name="_loc_city_code"     id="{{ $uid }}_ccode"  value="{{ $oldCity }}">
    <input type="hidden" name="_loc_barangay_code" id="{{ $uid }}_bcode"  value="{{ $oldBarangay }}">

    {{-- Fallback: shown when PSGC API is unreachable --}}
    <div id="{{ $uid }}_fallback" style="display:none;">
        <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:.79rem;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Address lookup unavailable. Please type the location manually.
        </div>
        <input type="text" id="{{ $uid }}_fb_input"
               class="form-control form-control-{{ $locSize }}"
               placeholder="e.g. Brgy. San Pedro, Caloocan City, Metro Manila"
               value="{{ $oldLocation }}">
    </div>

    {{-- Current-location banner (edit forms: existing text value, no PSGC codes yet) --}}
    @if($showCurrentBanner)
    <div id="{{ $uid }}_current_banner" class="mb-2 p-2" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;font-size:.79rem;color:#0369a1;">
        <i class="bi bi-geo-alt-fill me-1"></i>
        <strong>Current:</strong> {{ $oldLocation }}
        <br><span style="font-size:.73rem;color:#0284c7;">Use the dropdowns below to update, or leave them blank to keep the current value.</span>
    </div>
    @endif

    {{-- Cascading dropdowns --}}
    <div id="{{ $uid }}_selects">

        {{-- Region --}}
        <div class="mb-2">
            <label class="form-label mb-1" style="font-size:.74rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
                Region
            </label>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-{{ $locSize }} flex-fill">
                    <span class="input-group-text"><i class="bi bi-map"></i></span>
                    <select id="{{ $uid }}_region" class="form-select">
                        <option value="">— Loading regions… —</option>
                    </select>
                </div>
                <span id="{{ $uid }}_spin_r" style="display:none;">
                    <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                </span>
            </div>
        </div>

        {{-- Province (hidden for no-province regions like NCR) --}}
        <div class="mb-2" id="{{ $uid }}_prow">
            <label class="form-label mb-1" style="font-size:.74rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
                Province
            </label>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-{{ $locSize }} flex-fill">
                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                    <select id="{{ $uid }}_province" class="form-select" disabled>
                        <option value="">— Select Region first —</option>
                    </select>
                </div>
                <span id="{{ $uid }}_spin_p" style="display:none;">
                    <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                </span>
            </div>
        </div>

        {{-- City / Municipality --}}
        <div class="mb-2">
            <label class="form-label mb-1" style="font-size:.74rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
                City / Municipality
            </label>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-{{ $locSize }} flex-fill">
                    <span class="input-group-text"><i class="bi bi-signpost-split"></i></span>
                    <select id="{{ $uid }}_city" class="form-select" disabled>
                        <option value="">— Select Province first —</option>
                    </select>
                </div>
                <span id="{{ $uid }}_spin_c" style="display:none;">
                    <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                </span>
            </div>
        </div>

        {{-- Barangay --}}
        <div class="mb-2">
            <label class="form-label mb-1" style="font-size:.74rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
                Barangay
            </label>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-{{ $locSize }} flex-fill">
                    <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                    <select id="{{ $uid }}_barangay" class="form-select" disabled>
                        <option value="">— Select City first —</option>
                    </select>
                </div>
                <span id="{{ $uid }}_spin_b" style="display:none;">
                    <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                </span>
            </div>
        </div>

        {{-- Specific Address --}}
        <div class="mb-1">
            <label class="form-label mb-1" style="font-size:.74rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
                Street / Landmark / Specific Address <span style="font-weight:400;text-transform:none;">(optional)</span>
            </label>
            <div class="input-group input-group-{{ $locSize }}">
                <span class="input-group-text"><i class="bi bi-pin-map-fill"></i></span>
                <input type="text" id="{{ $uid }}_specific" name="_loc_specific"
                       class="form-control" value="{{ $oldSpecific }}"
                       placeholder="e.g. Rizal Ave. cor. Mabini St., near Shell station">
            </div>
            <div class="form-text">Exact street, intersection, or landmark within the barangay.</div>
        </div>

        {{-- Location preview --}}
        <div id="{{ $uid }}_preview" class="mt-2 mb-1" style="display:none;">
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:7px 11px;font-size:.79rem;color:#15803d;line-height:1.4;">
                <i class="bi bi-check-circle-fill me-1"></i>
                <strong>Location:</strong> <span id="{{ $uid }}_preview_text"></span>
            </div>
        </div>

    </div>{{-- /#selects --}}

    @error($locField)
        <div class="text-danger mt-1" style="font-size:.82rem;">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
        </div>
    @enderror
</div>

@push('scripts')
<script>
(function () {
    'use strict';
    const PSGC = 'https://psgc.gitlab.io/api';
    const uid  = '{{ $uid }}';

    const $ = id => document.getElementById(id);

    const selR   = $(uid + '_region');
    const selP   = $(uid + '_province');
    const selC   = $(uid + '_city');
    const selB   = $(uid + '_barangay');
    const inpS   = $(uid + '_specific');
    const hidV   = $(uid + '_value');
    const hidRC  = $(uid + '_rcode');
    const hidPC  = $(uid + '_pcode');
    const hidCC  = $(uid + '_ccode');
    const hidBC  = $(uid + '_bcode');
    const prow   = $(uid + '_prow');
    const prev   = $(uid + '_preview');
    const prevTx = $(uid + '_preview_text');
    const spins  = { r: $(uid+'_spin_r'), p: $(uid+'_spin_p'), c: $(uid+'_spin_c'), b: $(uid+'_spin_b') };

    // True when the selected region has provinces
    let hasProvinces = true;

    // ── Helpers ────────────────────────────────────────────────────────────────

    function spin(key, on) { spins[key].style.display = on ? '' : 'none'; }

    function populate(sel, items, placeholder) {
        sel.innerHTML = '';
        const def = document.createElement('option');
        def.value = '';
        def.textContent = placeholder;
        sel.appendChild(def);
        items
            .slice()
            .sort((a, b) => a.name.localeCompare(b.name))
            .forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.code;
                opt.textContent = item.name;
                sel.appendChild(opt);
            });
        sel.disabled = false;
    }

    function resetBelow(level) {
        // Reset dropdowns at and below the given level
        const cfg = [
            { key: 'province', sel: selP, placeholder: '— Select Province —',           hid: hidPC },
            { key: 'city',     sel: selC, placeholder: '— Select City / Municipality —', hid: hidCC },
            { key: 'barangay', sel: selB, placeholder: '— Select Barangay —',            hid: hidBC },
        ];
        const idx = cfg.findIndex(c => c.key === level);
        cfg.slice(idx).forEach(({ sel, placeholder, hid }) => {
            sel.innerHTML = `<option value="">${placeholder}</option>`;
            sel.disabled  = true;
            hid.value     = '';
        });
        assembleLocation();
    }

    function assembleLocation() {
        const rName = selR.selectedIndex > 0 ? selR.options[selR.selectedIndex].text : '';
        const pName = (hasProvinces && selP.value) ? selP.options[selP.selectedIndex].text : '';
        const cName = selC.value ? selC.options[selC.selectedIndex].text : '';
        const bName = selB.value ? selB.options[selB.selectedIndex].text : '';
        const spec  = inpS.value.trim();

        const parts    = [spec, bName, cName, pName, rName].filter(Boolean);
        const location = parts.join(', ');

        hidV.value = location;

        if (location && selR.value) {
            prevTx.textContent = location;
            prev.style.display = '';
        } else {
            prev.style.display = 'none';
        }
    }

    async function psgcFetch(path, spinKey) {
        spin(spinKey, true);
        try {
            const res = await fetch(PSGC + path);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return await res.json();
        } catch {
            return null;
        } finally {
            spin(spinKey, false);
        }
    }

    // ── Load Regions ──────────────────────────────────────────────────────────

    async function loadRegions() {
        selR.innerHTML  = '<option value="">— Loading… —</option>';
        selR.disabled   = true;
        const data = await psgcFetch('/regions/', 'r');

        if (!data) {
            // API unreachable — show text fallback
            $(uid + '_selects').style.display  = 'none';
            $(uid + '_fallback').style.display = '';
            const fb = $(uid + '_fb_input');
            if (fb) fb.addEventListener('input', () => { hidV.value = fb.value; });
            return;
        }

        populate(selR, data, '— Select Region —');

        // Restore saved region (after validation failure)
        if (hidRC.value) {
            selR.value = hidRC.value;
            if (selR.value) selR.dispatchEvent(new Event('change'));
        }
    }

    // ── Region → Province / City ──────────────────────────────────────────────

    selR.addEventListener('change', async function () {
        hidRC.value = this.value;
        // Hide the "current location" banner once the user starts picking from dropdowns
        const banner = $(uid + '_current_banner');
        if (banner) banner.style.display = 'none';
        resetBelow('province');
        prow.style.display = '';
        if (!this.value) return;

        const provinces = await psgcFetch('/regions/' + this.value + '/provinces/', 'p');
        if (!provinces) return;

        if (provinces.length === 0) {
            // No provinces (NCR, BARMM districts) — load cities directly from region
            hasProvinces          = false;
            prow.style.display    = 'none';
            selP.innerHTML        = '<option value=""></option>';
            selP.disabled         = true;
            hidPC.value           = '';

            const cities = await psgcFetch('/regions/' + this.value + '/cities-municipalities/', 'c');
            if (!cities) return;
            populate(selC, cities, '— Select City / Municipality —');

            if (hidCC.value) {
                selC.value = hidCC.value;
                if (selC.value) selC.dispatchEvent(new Event('change'));
            }
        } else {
            hasProvinces = true;
            prow.style.display = '';
            populate(selP, provinces, '— Select Province —');

            if (hidPC.value) {
                selP.value = hidPC.value;
                if (selP.value) selP.dispatchEvent(new Event('change'));
            }
        }
        assembleLocation();
    });

    // ── Province → City ───────────────────────────────────────────────────────

    selP.addEventListener('change', async function () {
        hidPC.value = this.value;
        resetBelow('city');
        if (!this.value) return;

        const cities = await psgcFetch('/provinces/' + this.value + '/cities-municipalities/', 'c');
        if (!cities) return;
        populate(selC, cities, '— Select City / Municipality —');

        if (hidCC.value) {
            selC.value = hidCC.value;
            if (selC.value) selC.dispatchEvent(new Event('change'));
        }
        assembleLocation();
    });

    // ── City → Barangay ───────────────────────────────────────────────────────

    selC.addEventListener('change', async function () {
        hidCC.value = this.value;
        resetBelow('barangay');
        if (!this.value) { assembleLocation(); return; }

        const brgys = await psgcFetch('/cities-municipalities/' + this.value + '/barangays/', 'b');
        if (!brgys) return;
        populate(selB, brgys, '— Select Barangay —');

        if (hidBC.value) {
            selB.value = hidBC.value;
            if (selB.value) assembleLocation();
        }
        assembleLocation();
    });

    // ── Barangay selected ─────────────────────────────────────────────────────

    selB.addEventListener('change', function () {
        hidBC.value = this.value;
        assembleLocation();
    });

    // ── Specific address typed ────────────────────────────────────────────────

    inpS.addEventListener('input', assembleLocation);

    // ── Boot ──────────────────────────────────────────────────────────────────

    loadRegions();
})();
</script>
@endpush
