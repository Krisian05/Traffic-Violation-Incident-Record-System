@extends('layouts.mobile')
@section('title', 'Offline Violation')
@section('back_url', route('officer.motorists.index'))

@push('styles')
<style>
.offline-violator-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .24rem .62rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: .66rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.offline-link-note {
    display: flex;
    gap: .7rem;
    align-items: flex-start;
    padding: .88rem .95rem;
    margin-bottom: .85rem;
    border-radius: 16px;
    background: #fff7ed;
    border: 1px solid #fdba74;
    color: #9a3412;
    font-size: .76rem;
    line-height: 1.5;
}
.offline-link-note i {
    font-size: 1rem;
    flex-shrink: 0;
    margin-top: .08rem;
}
.offline-location-guide {
    display: flex;
    gap: .65rem;
    align-items: flex-start;
    padding: .8rem .88rem;
    margin-top: .45rem;
    border-radius: 14px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    font-size: .73rem;
    line-height: 1.5;
}
.offline-location-guide i {
    font-size: .95rem;
    flex-shrink: 0;
    margin-top: .08rem;
}
.offline-location-fixed {
    display: flex;
    align-items: center;
    gap: .55rem;
    min-height: 44px;
    padding: .72rem .82rem;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: .84rem;
    font-weight: 700;
}
.offline-location-fixed i {
    font-size: 1rem;
    color: #1d4ed8;
}
.offline-location-preview {
    display: none;
    margin-top: .55rem;
    padding: .58rem .75rem;
    border-radius: 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    font-size: .74rem;
    line-height: 1.45;
    font-weight: 700;
}
.offline-location-preview i {
    margin-right: .28rem;
}
</style>
@endpush

@php
    // Static barangay lists (PSGC) per supported LGU so the offline violation
    // form can populate a dropdown without needing a live PSGC API call.
    $offlineBarangaysByLgu = [
        'BAL' => ['Abucayan','Aliwanay','Arpili','Bayong','Biasong','Buanoy','Cabagdalan','Cabasiangan','Cambuhawe','Cansomoroy','Cantibas','Cantuod','Duangan','Gaas','Ginatilan','Hingatmonan','Lamesa','Liki','Luca','Matun-og','Nangka','Pondol','Prenza','Singsing','Sunog','Vito','Baliwagan (Pob.)','Santa Cruz-Santo Niño (Pob.)'],
        'CEB' => ['Adlaon','Agsungot','Apas','Babag','Bacayan','Banilad','Basak Pardo','Basak San Nicolas','Binaliw','Bonbon','Budla-an (Pob.)','Buhisan','Bulacao','Buot-Taup Pardo','Busay (Pob.)','Calamba','Cambinocot','Camputhaw (Pob.)','Capitol Site (Pob.)','Carreta','Central (Pob.)','Cogon Pardo','Cogon Ramos (Pob.)','Day-as','Duljo (Pob.)','Ermita (Pob.)','Guadalupe','Guba','Hippodromo','Inayawan','Kalubihan (Pob.)','Kalunasan','Kamagayan (Pob.)','Kasambagan','Kinasang-an Pardo','Labangon','Lahug (Pob.)','Lorega','Lusaran','Luz','Mabini','Mabolo','Malubog','Mambaling','Pahina Central (Pob.)','Pahina San Nicolas','Pamutan','Pardo (Pob.)','Pari-an','Paril','Pasil','Pit-os','Pulangbato','Pung-ol-Sibugay','Punta Princesa','Quiot Pardo','Sambag I (Pob.)','Sambag II (Pob.)','San Antonio (Pob.)','San Jose','San Nicolas Central','San Roque','Santa Cruz (Pob.)','Sapangdaku','Sawang Calero (Pob.)','Sinsin','Sirao','Suba Pob.','Sudlon I','Sudlon II','T. Padilla','Tabunan','Tagbao','Talamban','Taptap','Tejero','Tinago','Tisa','To-ong Pardo','Zapatera'],
        'MAN' => ['Alang-alang','Bakilid','Banilad','Basak','Cabancalan','Cambaro','Canduman','Casili','Casuntingan','Centro (Pob.)','Cubacub','Guizo','Ibabao-Estancia','Jagobiao','Labogon','Looc','Maguikay','Mantuyong','Opao','Pagsabungan','Pakna-an','Subangdaku','Tabok','Tawason','Tingub','Tipolo','Umapad'],
        'DAN' => ['Baliang','Bayabas','Binaliw','Cabungahan','Cagat-Lamac','Cahumayan','Cambanay','Cambubho','Cogon-Cruz','Danasan','Dungga','Dunggoan','Guinacot','Guinsay','Ibo','Langosig','Lawaan','Licos','Looc','Magtagobtob','Malapoc','Manlayag','Mantija','Masaba','Maslog','Nangka','Oguis','Pili','Poblacion','Quisol','Sabang','Sacsac','Sandayong Norte','Sandayong Sur','Santa Rosa','Santican','Sibacan','Suba','Taboc','Taytay','Togonon','Tuburan Sur'],
        'CAR' => ['Bolinawan','Buenavista','Calidngan','Can-asujan','Guadalupe','Liburon','Napo','Ocana','Perrelos','Poblacion I','Poblacion II','Poblacion III','Tuyom','Valencia','Valladolid'],
        'TAL' => ['Biasong','Bulacao','Cadulawan','Camp IV','Cansojong','Dumlog','Jaclupan','Lagtang','Lawaan I','Lawaan II','Lawaan III','Linao','Maghaway','Manipis','Mohon','Poblacion','Pooc','San Isidro','San Roque','Tabunoc','Tangke','Tapul'],
        'TOL' => ['Alegria','Amatugan','Antipolo','Apalan','Bagasawe','Bakyawan','Bangkito','Barangay I (Pob.)','Barangay II (Pob.)','Barangay III (Pob.)','Barangay IV (Pob.)','Barangay V (Pob.)','Barangay VI (Pob.)','Barangay VII (Pob.)','Barangay VIII (Pob.)','Bulwang','Caridad','Carmelo','Cogon','Colonia','Daan Lungsod','Fortaliza','Ga-ang','Gimama-a','Jagbuaya','Kabangkalan','Kabkaban','Kagba-o','Kalangahan','Kamansi','Kampoot','Kan-an','Kanlunsing','Kansi','Kaorasan','Libo','Lusong','Macupa','Mag-alwa','Mag-antoy','Mag-atubang','Maghan-ay','Mangga','Marmol','Molobolo','Montealegre','Putat','San Juan','Sandayong','Santo Niño','Siotes','Sumon','Tominjao','Tomugpa'],
    ];

    $offlineLguCode = Auth::user()?->lgu?->code ?? 'BAL';
    $offlineBarangayOptions = $offlineBarangaysByLgu[$offlineLguCode] ?? $offlineBarangaysByLgu['BAL'];
@endphp

@section('content')

<div class="mob-card" style="border-left:4px solid #1d4ed8;">
    <div class="mob-card-body d-flex align-items-center gap-3">
        <div id="offlineMotoristAvatar" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#1d4ed8,#1e40af);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.96rem;font-weight:800;color:#fff;">
            OF
        </div>
        <div style="min-width:0;">
            <div class="offline-violator-chip">
                <i class="ph-fill ph-wifi-slash"></i>
                Queued Motorist
            </div>
            <div id="offlineMotoristName" style="font-size:.96rem;font-weight:800;color:#0f172a;margin-top:.42rem;">Loading motorist...</div>
            <div id="offlineMotoristMeta" style="font-size:.73rem;color:#64748b;margin-top:.12rem;">Checking the local offline queue on this device.</div>
        </div>
    </div>
</div>

<div class="offline-link-note">
    <i class="ph-fill ph-link"></i>
    <div>
        This violation will stay in the offline queue until the linked motorist record syncs. Once that motorist reaches the server, this violation publishes automatically after it.
    </div>
</div>

<div id="offlineMotoristMissing" class="mob-alert mob-alert-danger" style="display:none;">
    <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
    <div>The linked offline motorist could not be found on this device anymore.</div>
</div>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST"
              action="{{ route('officer.offline.violations.create') }}"
              enctype="multipart/form-data"
              id="offlineViolationForm"
              data-offline-sync="true"
              data-offline-label="Violation"
              data-offline-record-type="offline-violation-create">
            @csrf
            <input type="hidden" name="offline_motorist_key" id="offline_motorist_key" value="">

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Violation</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Violation Type <span class="text-danger">*</span></label>
                <select name="violation_type_id" id="offline_violation_type_id" class="form-select mob-select" required>
                    <option value="">Select violation</option>
                    @foreach($violationTypes as $type)
                    <option value="{{ $type->id }}" data-fine="{{ $type->fine_amount }}">
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
                <div id="offlineFinePreview" style="display:none;margin-top:.45rem;padding:.45rem .75rem;background:#fef2f2;border-radius:8px;font-size:.8rem;font-weight:700;color:#b91c1c;border:1px solid #fca5a5;">
                    <i class="ph-fill ph-money me-1"></i>Fine: ₱<span id="offlineFineAmount">0.00</span>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-7">
                    <label class="mob-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_violation" class="form-control mob-input" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="col-5">
                    <label class="mob-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select mob-select" required>
                        <option value="pending" selected>Pending</option>
                        <option value="settled">Settled</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Location</label>
                <input type="hidden" name="location" id="offline_location_value" value="">
                <div class="row g-2">
                    <div class="col-12">
                        <input type="text" id="offline_loc_street" class="form-control mob-input"
                               placeholder="Street / specific spot (optional)">
                    </div>
                    <div class="col-6">
                        <select id="offline_loc_barangay" class="form-select mob-select">
                            <option value="">Select Barangay</option>
                            @foreach($offlineBarangayOptions as $offlineBrgy)
                            <option value="{{ $offlineBrgy }}">{{ $offlineBrgy }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="text" id="offline_loc_municipality" class="form-control mob-input"
                               placeholder="Municipality" value="{{ Auth::user()?->lgu ? (Auth::user()->lgu->name . (Auth::user()->lgu->province ? ', ' . Auth::user()->lgu->province : '')) : 'Balamban, Cebu' }}" autocomplete="off">
                    </div>
                </div>
                <div id="offline_location_preview" class="offline-location-preview">
                    <i class="ph-fill ph-check-circle"></i>
                    <span id="offline_location_preview_text"></span>
                </div>
            </div>

            @include('partials.gps-locator', ['uid' => 'gps_offline_violation'])

            <div class="mb-3">
                <label class="mob-label">Ticket Number</label>
                <input type="text" class="form-control mob-input" value="[Auto-generated on Save]" readonly style="background-color: #f1f5f9; color: #64748b;">
                <input type="hidden" name="ticket_number" value="">
                <div class="form-text text-muted" style="font-size: 0.72rem;">The system will automatically generate a unique ticket number matching the TVIRS standard.</div>
            </div>

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Vehicle Involved</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="mob-label">Plate No.</label>
                    <input type="text" name="vehicle_plate" class="form-control mob-input" placeholder="e.g. ABC 1234">
                </div>
                <div class="col-6">
                    <label class="mob-label">Type</label>
                    <select name="vehicle_type" class="form-select mob-select">
                        <option value="">Select</option>
                        <option value="MV">MV</option>
                        <option value="MC">MC</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="mob-label">Color</label>
                    <input type="text" name="vehicle_color" class="form-control mob-input" placeholder="e.g. Red">
                </div>
                <div class="col-6">
                    <label class="mob-label">Make</label>
                    <input type="text" name="vehicle_make" class="form-control mob-input" placeholder="e.g. Honda">
                </div>
                <div class="col-6">
                    <label class="mob-label">Model</label>
                    <input type="text" name="vehicle_model" class="form-control mob-input" placeholder="e.g. Click 125">
                </div>
                <div class="col-6">
                    <label class="mob-label">Registered Owner Name</label>
                    <input type="text" name="vehicle_owner_name" class="form-control mob-input" placeholder="Leave blank if same as driver">
                </div>
                <div class="col-6">
                    <label class="mob-label">OR Number</label>
                    <input type="text" name="vehicle_or_number" class="form-control mob-input" placeholder="Official Receipt #">
                </div>
                <div class="col-6">
                    <label class="mob-label">CR Number</label>
                    <input type="text" name="vehicle_cr_number" class="form-control mob-input" placeholder="Certificate of Reg. #">
                </div>
                <div class="col-12">
                    <label class="mob-label">Chassis Number</label>
                    <input type="text" name="vehicle_chassis" class="form-control mob-input" placeholder="Frame / chassis number">
                </div>
            </div>

            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Documentation</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="mb-3">
                <label class="mob-label">Citation Ticket Photo</label>
                <div id="picker-offline-citation"></div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Valid ID Photo</label>
                <div id="picker-offline-valid-id"></div>
            </div>

            <div class="mb-3">
                <label class="mob-label">Vehicle Photos <span style="font-size:.68rem;color:#94a3b8;">(up to 4)</span></label>
                <div id="picker-offline-veh-photos"></div>
            </div>

            <div class="mb-4">
                <label class="mob-label">Notes</label>
                <textarea name="notes" rows="2" class="form-control mob-input" style="min-height:auto;resize:none;" placeholder="Optional remarks..."></textarea>
            </div>

            <button type="submit" class="mob-btn-primary mob-btn-danger mb-2" id="offlineViolationSubmitBtn" disabled>
                <i class="ph-bold ph-cloud-arrow-up"></i> Queue Violation
            </button>
            <a href="{{ route('officer.motorists.index') }}" class="mob-btn-outline">
                <i class="ph ph-arrow-left"></i> Back to Motorists
            </a>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initPhotoPicker('picker-offline-citation',  'citation_ticket_photo', { multiple: false });
    initPhotoPicker('picker-offline-valid-id',   'valid_id_photo',        { multiple: false });
    initPhotoPicker('picker-offline-veh-photos', 'photos',                { multiple: true  });

    var form = document.getElementById('offlineViolationForm');
    var keyInput = document.getElementById('offline_motorist_key');
    var nameEl = document.getElementById('offlineMotoristName');
    var metaEl = document.getElementById('offlineMotoristMeta');
    var avatarEl = document.getElementById('offlineMotoristAvatar');
    var missingAlert = document.getElementById('offlineMotoristMissing');
    var submitBtn = document.getElementById('offlineViolationSubmitBtn');
    var baseCreatePath = "{{ url('/officer/motorists') }}";

    function hashMotoristKey() {
        var hash = String(window.location.hash || '').replace(/^#/, '');
        var params = new URLSearchParams(hash);
        return params.get('motorist') || '';
    }

    function setMotoristState(motorist) {
        keyInput.value = motorist.offlineMotoristKey;
        form.dataset.offlineParentKey = motorist.offlineMotoristKey;
        form.dataset.offlineLabel = 'Violation for ' + motorist.summary.displayName;
        nameEl.textContent = motorist.summary.displayName;

        var metaParts = [];
        if (motorist.summary.licenseNumber) metaParts.push('License ' + motorist.summary.licenseNumber);
        if (motorist.summary.gender) metaParts.push(motorist.summary.gender);
        if (motorist.queuedViolations > 0) metaParts.push(motorist.queuedViolations + ' queued violation' + (motorist.queuedViolations === 1 ? '' : 's'));
        metaEl.textContent = metaParts.join(' • ') || 'Saved only on this device until internet returns.';
        avatarEl.textContent = motorist.summary.initials || 'OF';
        missingAlert.style.display = 'none';
        submitBtn.disabled = false;
        keyInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setMissingState(message) {
        nameEl.textContent = 'Offline motorist not available';
        metaEl.textContent = 'Go back to the motorists list and open a queued motorist from this device.';
        avatarEl.textContent = '!';
        missingAlert.style.display = '';
        missingAlert.querySelector('div').textContent = message;
        submitBtn.disabled = true;
    }

    var fineField = document.getElementById('offline_violation_type_id');
    var finePreview = document.getElementById('offlineFinePreview');
    var fineAmount = document.getElementById('offlineFineAmount');

    fineField.addEventListener('change', function () {
        var option = fineField.options[fineField.selectedIndex];
        var fine = option ? parseFloat(option.dataset.fine || '0') : 0;

        if (fine > 0) {
            fineAmount.textContent = fine.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            finePreview.style.display = 'block';
            return;
        }

        finePreview.style.display = 'none';
    });

    fineField.dispatchEvent(new Event('change'));

    // Location auto-compose
    var locStreet       = document.getElementById('offline_loc_street');
    var locBarangay     = document.getElementById('offline_loc_barangay');
    var locMunicipality = document.getElementById('offline_loc_municipality');
    var locValue        = document.getElementById('offline_location_value');
    var locPreview      = document.getElementById('offline_location_preview');
    var locPreviewText  = document.getElementById('offline_location_preview_text');

    function syncLocation() {
        var street   = (locStreet.value || '').trim();
        var barangay = (locBarangay.value || '').trim();
        var muni     = (locMunicipality.value || '').trim();

        var parts = [];
        if (street)   parts.push(street);
        if (barangay) parts.push('Brgy. ' + barangay);
        if (muni)     parts.push(muni);

        var composed = parts.join(', ');
        locValue.value = composed;

        if (composed) {
            locPreviewText.textContent = composed;
            locPreview.style.display = 'block';
        } else {
            locPreview.style.display = 'none';
        }
    }

    [locStreet, locBarangay, locMunicipality].forEach(function (el) {
        el.addEventListener('input', syncLocation);
        el.addEventListener('change', syncLocation);
    });
    syncLocation();

    var motoristKey = hashMotoristKey();
    if (!motoristKey) {
        setMissingState('No offline motorist was selected for this violation.');
        return;
    }

    if (!window.TvirsOffline || typeof window.TvirsOffline.getOfflineMotoristByKey !== 'function') {
        setMissingState('Offline tools did not load correctly on this page.');
        return;
    }

    window.TvirsOffline.getOfflineMotoristByKey(motoristKey).then(function (motorist) {
        if (motorist) {
            setMotoristState(motorist);
            return;
        }

        var syncedId = typeof window.TvirsOffline.getSyncedMotoristId === 'function'
            ? window.TvirsOffline.getSyncedMotoristId(motoristKey)
            : '';

        if (syncedId) {
            window.location.replace(baseCreatePath + '/' + encodeURIComponent(syncedId) + '/violations/create');
            return;
        }

        setMissingState('The linked offline motorist is no longer queued on this device.');
    }).catch(function () {
        setMissingState('Unable to read the offline motorist queue on this device.');
    });
});
</script>
@endpush
