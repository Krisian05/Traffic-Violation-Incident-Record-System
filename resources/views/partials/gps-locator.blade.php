{{--
    GPS Locator Partial — captures gps_lat / gps_lng via the device's geolocation API.
    Requires layouts.mobile (defines the shared tvirsGetGpsLocation() JS helper).

    Parameters:
      $uid       — unique id prefix so multiple instances can coexist (default: 'gps')
      $latValue  — initial latitude value (e.g. $violation->gps_lat)
      $lngValue  — initial longitude value
--}}
@php
    $gpsUid = $uid ?? 'gps';
    $gpsLat = old('gps_lat', $latValue ?? '');
    $gpsLng = old('gps_lng', $lngValue ?? '');
@endphp

<div class="mb-3">
    <label class="mob-label">GPS Coordinates <span style="font-size:.68rem;color:#94a3b8;">(optional)</span></label>
    <div class="row g-2">
        <div class="col-6">
            <input type="number" step="0.0000001" min="-90" max="90"
                   name="gps_lat" id="{{ $gpsUid }}_lat"
                   class="form-control mob-input @error('gps_lat') is-invalid @enderror"
                   placeholder="Latitude" value="{{ $gpsLat }}">
            @error('gps_lat')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-6">
            <input type="number" step="0.0000001" min="-180" max="180"
                   name="gps_lng" id="{{ $gpsUid }}_lng"
                   class="form-control mob-input @error('gps_lng') is-invalid @enderror"
                   placeholder="Longitude" value="{{ $gpsLng }}">
            @error('gps_lng')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <button type="button" class="mob-btn-outline w-100 mt-2" id="{{ $gpsUid }}_btn"
            onclick="tvirsGetGpsLocation('{{ $gpsUid }}')">
        <i class="ph-bold ph-crosshair"></i> <span id="{{ $gpsUid }}_btn_label">Get Current Location</span>
    </button>
    <span class="mob-hint" id="{{ $gpsUid }}_status"></span>
</div>
