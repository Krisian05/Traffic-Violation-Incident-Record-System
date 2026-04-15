@extends('layouts.app')
@section('title', 'Record Incident')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('incidents.index') }}" style="color:#dc2626;text-decoration:none;">Incidents</a></li>
    <li class="breadcrumb-item active" aria-current="page">Record Incident</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
/* ── Chip widget ── */
.restr-box {
    display: flex; flex-wrap: nowrap; overflow-x: auto;
    align-items: center; gap: .35rem; padding: 0 .5rem;
    height: calc(1.5em + .75rem + 2px); scrollbar-width: none;
    -ms-overflow-style: none;
}
.restr-box::-webkit-scrollbar { display: none; }
.restr-chip { cursor: pointer; display: inline-block; flex-shrink: 0; }
.restr-chip input[type="checkbox"],
.restr-chip input[type="radio"] { display: none; }
.restr-chip span {
    display: inline-block; padding: .22rem .6rem; border-radius: 20px;
    font-size: .73rem; font-weight: 700; background: #fff; color: #92400e;
    border: 1.5px solid #fde68a; transition: all .15s; user-select: none; letter-spacing: .02em;
}
.restr-chip span:hover { border-color: #ca8a04; background: #fef9c3; transform: translateY(-1px); }
.restr-chip input[type="checkbox"]:checked + span,
.restr-chip input[type="radio"]:checked + span {
    background: #ca8a04; color: #fff; border-color: #ca8a04;
    box-shadow: 0 2px 6px rgba(202,138,4,.28);
}
/* ── ID photo styled file input ── */
.id-photo-label {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .85rem; border-radius: 8px; cursor: pointer;
    background: #eff6ff; color: #1d4ed8;
    border: 1.5px solid #bfdbfe; font-size: .78rem; font-weight: 600;
    transition: all .15s; white-space: nowrap;
}
.id-photo-label:hover { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
.id-photo-filename {
    font-size: .78rem; color: #78716c;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 120px;
}

/* ── Section cards ── */
.inc-section-card {
    background: #fff;
    border: 1px solid #f0ebe3;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.06), 0 1px 4px rgba(0,0,0,.04);
    overflow: visible;
    margin-bottom: 1.5rem;
}
.inc-section-card .card-body { overflow: visible; }
.inc-card-header { border-radius: 14px 14px 0 0; overflow: hidden; }
.inc-card-header {
    display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    padding: .9rem 1.25rem;
    border-bottom: 1px solid #f0ebe3;
}
.inc-card-header-left { display: flex; align-items: center; gap: .75rem; }
.inc-section-icon {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; flex-shrink: 0;
}
.inc-section-title { font-size: .88rem; font-weight: 700; color: #1c1917; }
.inc-section-sub   { font-size: .72rem; color: #a8a29e; margin-top: .05rem; }
.inc-section-card .card-body { padding: 1.25rem; }

/* ── Flatpickr custom theme ── */
.flatpickr-calendar {
    border-radius: 14px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,.14), 0 2px 8px rgba(0,0,0,.08) !important;
    border: 1px solid #f0ebe3 !important;
    font-family: inherit !important;
    overflow: hidden;
}
.flatpickr-months { background: linear-gradient(135deg,#dc2626,#b91c1c); border-radius: 14px 14px 0 0; padding: .3rem 0; }
.flatpickr-month, .flatpickr-prev-month, .flatpickr-next-month { color: #fff !important; fill: #fff !important; }
.flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg { fill: #fde68a !important; }
.flatpickr-current-month { color: #fff !important; font-size: 1rem !important; font-weight: 700; }
.flatpickr-current-month .flatpickr-monthDropdown-months { color: #fff; background: transparent; font-weight: 700; }
.flatpickr-current-month input.cur-year { color: #fff !important; font-weight: 700; }
.flatpickr-weekdays { background: #fff7f7; border-bottom: 1px solid #fecaca; }
span.flatpickr-weekday { color: #b91c1c !important; font-weight: 700; font-size: .72rem; }
.flatpickr-day { border-radius: 8px !important; font-size: .82rem; font-weight: 500; color: #1c1917; transition: all .12s; }
.flatpickr-day:hover { background: #fff1f2 !important; border-color: #fecaca !important; color: #dc2626 !important; }
.flatpickr-day.selected, .flatpickr-day.selected:hover {
    background: linear-gradient(135deg,#dc2626,#b91c1c) !important;
    border-color: #b91c1c !important; color: #fff !important;
    box-shadow: 0 2px 8px rgba(185,28,28,.35) !important;
    font-weight: 700;
}
.flatpickr-day.today { border-color: #fca5a5 !important; color: #dc2626 !important; font-weight: 700; }
.flatpickr-day.today:hover { background: #fff1f2 !important; }
.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover { color: #d1d5db !important; background: transparent !important; }
.flatpickr-time input, .flatpickr-time .flatpickr-am-pm { color: #1c1917 !important; font-weight: 600; }
.flatpickr-time input:focus { background: #fff1f2 !important; }

/* Scoped overrides for incident date pickers to avoid theme collisions */
.incident-flatpickr-theme.flatpickr-calendar {
    width: 284px !important;
    overflow: hidden;
}
.incident-flatpickr-theme .flatpickr-rContainer,
.incident-flatpickr-theme .flatpickr-days {
    width: 100% !important;
}
.incident-flatpickr-theme .dayContainer {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    padding: .35rem;
}
.incident-flatpickr-theme .flatpickr-months {
    background: linear-gradient(135deg,#dc2626,#b91c1c) !important;
    align-items: center;
}
.incident-flatpickr-theme .flatpickr-months .flatpickr-month {
    background: transparent !important;
    color: #fff !important;
    min-height: 38px;
}
.incident-flatpickr-theme .flatpickr-months .flatpickr-prev-month,
.incident-flatpickr-theme .flatpickr-months .flatpickr-next-month {
    color: #fff !important;
    fill: #fff !important;
    min-height: 38px;
}
.incident-flatpickr-theme .flatpickr-current-month {
    color: #fff !important;
    font-size: .92rem !important;
    font-weight: 700;
}
.incident-flatpickr-theme .flatpickr-current-month .cur-month,
.incident-flatpickr-theme .flatpickr-current-month .flatpickr-monthDropdown-months {
    color: #fff !important;
    background: transparent !important;
    font-weight: 700;
}
.incident-flatpickr-theme .flatpickr-current-month .numInputWrapper { width: 4.2ch; }
.incident-flatpickr-theme .flatpickr-current-month input.cur-year { color: #fff !important; font-weight: 700; }
.incident-flatpickr-theme .flatpickr-weekdays { background: #fff7f7 !important; border-bottom: 1px solid #fecaca; }
.incident-flatpickr-theme span.flatpickr-weekday {
    background: #fff7f7 !important;
    color: #b91c1c !important;
}
.incident-flatpickr-theme .flatpickr-day.nextMonthDay,
.incident-flatpickr-theme .flatpickr-day.prevMonthDay { color: transparent !important; pointer-events: none !important; }

/* ── Submit button ── */
.inc-submit-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .55rem 1.5rem; border-radius: 10px;
    font-size: .875rem; font-weight: 700;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff; border: none;
    box-shadow: 0 3px 10px rgba(220,38,38,.3);
    cursor: pointer; transition: all .15s; width: 100%;
    justify-content: center;
}
.inc-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(220,38,38,.4); color:#fff; }

.fw-500 { font-weight: 500; }
.fw-600 { font-weight: 600; }
.fw-700 { font-weight: 700; }
</style>
@endpush

@section('topbar-sub')
    <i class="bi bi-flag-fill me-1" style="color:#dc2626;"></i>
    New Incident Report
@endsection

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:42px;height:42px;background:linear-gradient(135deg,#1d4ed8,#1e40af);flex-shrink:0;">
        <i class="bi bi-flag-fill text-white" style="font-size:1rem;"></i>
    </div>
    <div>
        <h5 class="mb-0 fw-700" style="color:#1c1917;">Record Incident</h5>
        <div style="font-size:.8rem;color:#78716c;">Create a new traffic incident report</div>
    </div>
</div>

<form method="POST" action="{{ route('incidents.store') }}" enctype="multipart/form-data" id="incident-form">
@csrf

<div class="row g-4">

    {{-- ── LEFT COLUMN ── --}}
    <div class="col-lg-8">

        {{-- Incident Details --}}
        <div class="inc-section-card">
            <div class="inc-card-header" style="background:linear-gradient(135deg,#fff5f5 0%,#fff 100%);">
                <div class="inc-card-header-left">
                    <span class="inc-section-icon" style="background:linear-gradient(135deg,#dc2626,#b91c1c);box-shadow:0 3px 10px rgba(185,28,28,.3);">
                        <i class="bi bi-flag-fill" style="color:#fff;font-size:.85rem;"></i>
                    </span>
                    <div>
                        <div class="inc-section-title">Incident Details</div>
                        <div class="inc-section-sub">Date, time, location &amp; narrative</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-500" style="font-size:.84rem;">Date of Incident <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-calendar-event-fill" style="color:#dc2626;font-size:.8rem;"></i></span>
                            <input type="text" name="date_of_incident" id="date_of_incident"
                                class="form-control @error('date_of_incident') is-invalid @enderror"
                                value="{{ old('date_of_incident') }}" placeholder="YYYY-MM-DD" required>
                            @error('date_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500" style="font-size:.84rem;">Time of Incident</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-clock-fill" style="color:#0369a1;font-size:.8rem;"></i></span>
                            <input type="text" name="time_of_incident" id="time_of_incident"
                                class="form-control @error('time_of_incident') is-invalid @enderror"
                                value="{{ old('time_of_incident') }}" placeholder="hh:mm AM/PM">
                            @error('time_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-12">
                        @include('partials.location-selector', [
                            'fieldName' => 'location',
                            'required'  => true,
                            'label'     => 'Location',
                        ])
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-500" style="font-size:.84rem;">Description / Narrative</label>
                        <div class="input-group input-group-sm align-items-start">
                            <span class="input-group-text"><i class="bi bi-chat-text-fill" style="color:#78716c;font-size:.8rem;"></i></span>
                            <textarea name="description" rows="3"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Brief narrative of what happened...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Involved Motorists --}}
        <div class="inc-section-card">
            <div class="inc-card-header" style="background:linear-gradient(135deg,#fffbeb 0%,#fff 100%);">
                <div class="inc-card-header-left">
                    <span class="inc-section-icon" style="background:linear-gradient(135deg,#d97706,#b45309);box-shadow:0 3px 10px rgba(180,83,9,.3);">
                        <i class="bi bi-people-fill" style="color:#fff;font-size:.85rem;"></i>
                    </span>
                    <div>
                        <div class="inc-section-title">Involved Motorists</div>
                        <div class="inc-section-sub">Minimum 2 motorists required</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;font-size:.72rem;" id="motorist-count">1</span>
                    <button type="button" onclick="addMotoristRow()"
                        style="display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .9rem;border-radius:8px;font-size:.8rem;font-weight:600;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;border:none;box-shadow:0 2px 6px rgba(180,83,9,.3);cursor:pointer;transition:all .15s;"
                        onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform=''">
                        <i class="bi bi-plus-lg"></i> Add Motorist
                    </button>
                </div>
            </div>
            <div class="p-0">
                <div id="motorists-container"></div>
                <div class="px-3 py-2" style="background:#fafaf9;border-top:1px solid #f5f5f4;">
                    <small style="color:#a8a29e;font-size:.74rem;"><i class="bi bi-info-circle me-1"></i>Select a registered motorist OR enter name/license manually.</small>
                </div>
            </div>
        </div>

        {{-- Evidence & Documents --}}
        <div class="inc-section-card">
            <div class="inc-card-header" style="background:linear-gradient(135deg,#f0fdf4 0%,#fff 100%);">
                <div class="inc-card-header-left">
                    <span class="inc-section-icon" style="background:linear-gradient(135deg,#16a34a,#15803d);box-shadow:0 3px 10px rgba(21,128,61,.3);">
                        <i class="bi bi-images" style="color:#fff;font-size:.85rem;"></i>
                    </span>
                    <div>
                        <div class="inc-section-title">Evidence &amp; Documents</div>
                        <div class="inc-section-sub">Photos, PDFs — max 20MB each, up to 20 files</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text"><i class="bi bi-cloud-upload-fill" style="color:#16a34a;font-size:.8rem;"></i></span>
                    <input type="file" name="media[]" id="media-upload" class="form-control"
                        multiple accept="image/jpg,image/jpeg,image/png,application/pdf"
                        onchange="handleMediaUpload(this)">
                </div>
                <div id="media-preview-container" class="row g-2"></div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">

        {{-- Quick Guide --}}
        <div class="inc-section-card mb-3">
            <div class="inc-card-header" style="background:linear-gradient(135deg,#eff6ff 0%,#fff 100%);">
                <div class="inc-card-header-left">
                    <span class="inc-section-icon" style="background:linear-gradient(135deg,#1d4ed8,#1e40af);box-shadow:0 3px 10px rgba(29,78,216,.25);">
                        <i class="bi bi-lightbulb-fill" style="color:#fff;font-size:.85rem;"></i>
                    </span>
                    <div>
                        <div class="inc-section-title">Quick Guide</div>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:1rem 1.25rem;">
                <ul class="mb-0 ps-3" style="font-size:.78rem;color:#374151;line-height:1.8;">
                    <li>Fill in the incident date, time, and location first.</li>
                    <li>Add <strong>at least 1 motorist</strong> involved.</li>
                    <li>Search by name for registered motorists.</li>
                    <li>For unregistered, enter name and license manually.</li>
                    <li>Assign a criminal charge per motorist if applicable.</li>
                    <li>Upload scene photos, tickets, or documents.</li>
                </ul>
            </div>
        </div>

        {{-- Actions --}}
        <div class="inc-section-card">
            <div class="card-body d-flex flex-column gap-2" style="padding:1.1rem;">
                <button type="submit" class="inc-submit-btn" id="incidentSubmitBtn">
                    <i class="bi bi-flag-fill"></i> Save Incident
                </button>
                <a href="{{ route('incidents.index') }}"
                    class="btn d-inline-flex align-items-center justify-content-center gap-2 rounded-pill w-100"
                    style="border:1.5px solid #d6d3d1;color:#78716c;background:#fff;font-weight:500;font-size:.875rem;">
                    <i class="bi bi-x-circle" style="font-size:.85rem;"></i> Cancel
                </a>
            </div>
        </div>

    </div>
</div>

</form>

{{-- Motorist row template (hidden) --}}
<template id="motorist-row-tpl">
    <div class="motorist-row" data-index="__IDX__" style="border-top:1px solid #f5f5f4;">
        <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
            <div class="d-flex align-items-center gap-2">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:6px;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;font-size:.7rem;font-weight:700;flex-shrink:0;" class="row-badge">__NUM__</span>
                <span class="fw-600" style="font-size:.82rem;color:#374151;">Motorist #<span class="row-num">__NUM__</span></span>
            </div>
            <button type="button" onclick="removeMotoristRow(this)" title="Remove motorist"
                style="display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .55rem;border-radius:6px;font-size:.72rem;font-weight:600;background:#fff1f2;color:#dc2626;border:1.5px solid #fecdd3;cursor:pointer;transition:all .15s;"
                onmouseover="this.style.background='#ffe4e6'" onmouseout="this.style.background='#fff1f2'">
                <i class="bi bi-trash3"></i> Remove
            </button>
        </div>

        {{-- Toggle: registered vs manual --}}
        <div class="px-3 pb-2">
            <div class="btn-group btn-group-sm w-100" role="group">
                <input type="radio" class="btn-check" name="motorists[__IDX__][mode]" id="mode-reg-__IDX__" value="registered" checked onchange="toggleMotoristMode(this)">
                <label class="btn btn-outline-primary" for="mode-reg-__IDX__" style="font-size:.78rem;">
                    <i class="bi bi-person-check-fill me-1"></i>Registered
                </label>
                <input type="radio" class="btn-check" name="motorists[__IDX__][mode]" id="mode-manual-__IDX__" value="manual" onchange="toggleMotoristMode(this)">
                <label class="btn btn-outline-secondary" for="mode-manual-__IDX__" style="font-size:.78rem;">
                    <i class="bi bi-pencil-fill me-1"></i>Enter Manually
                </label>
            </div>
        </div>

        {{-- Registered: Tom Select violator search + vehicle dropdown --}}
        <div class="motorist-reg-section px-3 pb-2">
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-person-fill me-1 text-primary"></i>Registered Motorist</label>
                    <select name="motorists[__IDX__][violator_id]" class="form-select form-select-sm violator-select" data-idx="__IDX__">
                        <option value="">— Search by name —</option>
                        @foreach($violators as $v)
                            <option value="{{ $v->id }}">{{ $v->last_name }}, {{ $v->first_name }}{{ $v->middle_name ? ' '.$v->middle_name : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 vehicle-reg-wrap" style="display:none;">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-car-front-fill me-1 text-secondary"></i>Registered Vehicle</label>
                    <select name="motorists[__IDX__][vehicle_id]" class="form-select form-select-sm vehicle-reg-select">
                        <option value="">— Select vehicle —</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Manual: name + license + plate + vehicle type --}}
        <div class="motorist-manual-section px-3 pb-2" style="display:none;">
            <div class="row g-2">
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-person-fill me-1" style="color:#374151;"></i>Full Name</label>
                    <input type="text" name="motorists[__IDX__][motorist_name]" class="form-control form-control-sm" placeholder="Full name">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-upc-scan me-1" style="color:#ca8a04;"></i>License No.</label>
                    <input type="text" name="motorists[__IDX__][motorist_license]" class="form-control form-control-sm" placeholder="e.g. A01-23-456789">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-tag-fill me-1" style="color:#ca8a04;"></i>License Type</label>
                    <select name="motorists[__IDX__][license_type]" class="form-select form-select-sm">
                        <option value="">— Select —</option>
                        <option value="Professional">Professional</option>
                        <option value="Non-Professional">Non-Professional</option>
                        <option value="Student Permit">Student Permit</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-shield-lock-fill me-1" style="color:#ca8a04;"></i>Restriction Code</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-shield-lock-fill" style="color:#ca8a04;font-size:.8rem;"></i></span>
                        <div class="restr-box form-control">
                        @foreach(['A'=>'Motorcycle','A1'=>'MC w/ Sidecar','B'=>'Light Vehicle','B1'=>'Light Vehicle (Prof.)','B2'=>'Light Vehicle w/ Trailer','C'=>'Medium/Heavy Truck','D'=>'Bus','BE'=>'Light + Heavy Trailer','CE'=>'Large Truck + Trailer'] as $val => $desc)
                        <label class="restr-chip" title="{{ $val }} — {{ $desc }}">
                            <input type="checkbox" name="motorists[__IDX__][license_restriction][]" value="{{ $val }}">
                            <span>{{ $val }}</span>
                        </label>
                        @endforeach
                        </div>
                    </div>
                    <small style="display:block;margin-top:.3rem;font-size:.71rem;color:#a8a29e;"><i class="bi bi-info-circle" style="font-size:.68rem;margin-right:.2rem;"></i>Tap to select all codes printed on the license.</small>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-calendar-x-fill me-1 text-danger"></i>License Expiry</label>
                    <input type="text" name="motorists[__IDX__][license_expiry_date]" class="form-control form-control-sm motorist-expiry-picker" placeholder="YYYY-MM-DD" onchange="checkExpiryWarning(this)">
                    <div class="expiry-warning" style="display:none;font-size:.71rem;color:#dc2626;margin-top:.2rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>This license is already expired.</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-car-front-fill me-1 text-secondary"></i>Plate No.</label>
                    <input type="text" name="motorists[__IDX__][vehicle_plate]" class="form-control form-control-sm" placeholder="e.g. ABC 1234">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-truck-front-fill me-1 text-secondary"></i>Vehicle Type</label>
                    <select name="motorists[__IDX__][vehicle_type_manual]" class="form-select form-select-sm">
                        <option value="">— Select —</option>
                        <option value="MV">Motor Vehicle (MV)</option>
                        <option value="MC">Motorcycle (MC)</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-car-front me-1" style="color:#374151;"></i>Make</label>
                    <input type="text" name="motorists[__IDX__][vehicle_make]" class="form-control form-control-sm" placeholder="e.g. Toyota">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-card-list me-1" style="color:#374151;"></i>Model</label>
                    <input type="text" name="motorists[__IDX__][vehicle_model]" class="form-control form-control-sm" placeholder="e.g. Vios">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-palette-fill me-1" style="color:#374151;"></i>Color</label>
                    <input type="text" name="motorists[__IDX__][vehicle_color]" class="form-control form-control-sm" placeholder="e.g. White">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-receipt me-1" style="color:#374151;"></i>OR Number</label>
                    <input type="text" name="motorists[__IDX__][vehicle_or_number]" class="form-control form-control-sm" placeholder="Official Receipt #">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-file-earmark-text me-1" style="color:#374151;"></i>CR Number</label>
                    <input type="text" name="motorists[__IDX__][vehicle_cr_number]" class="form-control form-control-sm" placeholder="Cert. of Reg. #">
                </div>
                <div class="col-12">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-hash me-1" style="color:#374151;"></i>Chassis No.</label>
                    <input type="text" name="motorists[__IDX__][vehicle_chassis]" class="form-control form-control-sm" placeholder="Chassis number">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-telephone-fill me-1" style="color:#16a34a;"></i>Contact No.</label>
                    <input type="text" name="motorists[__IDX__][motorist_contact]" class="form-control form-control-sm" placeholder="09XX-XXX-XXXX">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-geo-alt-fill me-1" style="color:#16a34a;"></i>Address</label>
                    <input type="text" name="motorists[__IDX__][motorist_address]" class="form-control form-control-sm" placeholder="Home address">
                </div>
            </div>
        </div>

        {{-- Vehicle Owner toggle (always visible for both registered & manual modes) --}}
        <div class="px-3 pb-2">
            <div class="form-check form-switch">
                <input class="form-check-input owner-not-driver-check" type="checkbox" role="switch"
                    id="owner-not-driver-__IDX__" onchange="toggleOwnerSection(this)">
                <label class="form-check-label fw-500" for="owner-not-driver-__IDX__" style="font-size:.82rem;color:#374151;">
                    <i class="bi bi-person-x-fill me-1 text-danger"></i>Driver is <strong>NOT</strong> the vehicle owner
                </label>
            </div>
            <div class="owner-section mt-2" style="display:none;">
                <div class="p-2 rounded-3" style="background:#fff7ed;border:1.5px solid #fed7aa;">
                    <div class="fw-600 mb-2" style="font-size:.78rem;color:#c2410c;"><i class="bi bi-person-badge-fill me-1"></i>Vehicle Owner Details</div>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label fw-500 mb-1" style="font-size:.78rem;">Registered Owner (if in system)</label>
                            <select name="motorists[__IDX__][vehicle_owner_violator_id]" class="form-select form-select-sm owner-violator-select">
                                <option value="">— Search registered owner —</option>
                                @foreach($violators as $v)
                                    <option value="{{ $v->id }}">{{ $v->last_name }}, {{ $v->first_name }}{{ $v->middle_name ? ' '.$v->middle_name : '' }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size:.72rem;">Leave blank to enter owner name/contact manually below.</small>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-500 mb-1" style="font-size:.78rem;"><i class="bi bi-person-fill me-1"></i>Owner Full Name</label>
                            <input type="text" name="motorists[__IDX__][vehicle_owner_name]" class="form-control form-control-sm" placeholder="If not registered above">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-500 mb-1" style="font-size:.78rem;"><i class="bi bi-telephone-fill me-1"></i>Owner Contact</label>
                            <input type="text" name="motorists[__IDX__][vehicle_owner_contact]" class="form-control form-control-sm" placeholder="09XX-XXX-XXXX">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charge type + notes --}}
        <div class="row g-2 px-3 pb-2">
            <div class="col-md-6">
                <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-shield-exclamation me-1" style="color:#7c3aed;"></i>Charge / Offense</label>
                <select name="motorists[__IDX__][incident_charge_type_id]" class="form-select form-select-sm">
                    <option value="">— None —</option>
                    @foreach($chargeTypes as $ct)
                        <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-chat-right-text-fill me-1 text-secondary"></i>Notes</label>
                <input type="text" name="motorists[__IDX__][notes]" class="form-control form-control-sm" placeholder="e.g. Driver refused to stop">
            </div>
        </div>

        {{-- MC/MV Photos (up to 4) --}}
        <div class="px-3 pb-3">
            <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-camera-fill me-1 text-secondary"></i>MC/MV Photos <small class="text-muted fw-400">(optional · up to 4 · max 20MB each)</small></label>
            <div class="motorist-photos-preview d-flex flex-wrap gap-1 mb-1"></div>
            <input type="file" name="motorist_photos[__IDX__][]" class="form-control form-control-sm motorist-photo-input"
                accept="image/jpg,image/jpeg,image/png" multiple onchange="previewMotoristPhotos(this)">
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script type="application/json" id="vehicles-by-owner">@json($vehiclesByOwner)</script>
<script>
let motoristIndex = 0;
const vehiclesByOwner = JSON.parse(document.getElementById('vehicles-by-owner').textContent);

function populateVehicles(row, violatorId) {
    const wrap = row.querySelector('.vehicle-reg-wrap');
    const sel  = row.querySelector('.vehicle-reg-select');
    if (!wrap || !sel) return;

    sel.innerHTML = '<option value="">— Select vehicle —</option>';
    const vehicles = vehiclesByOwner[violatorId] || [];
    if (vehicles.length === 0) {
        wrap.style.display = 'none';
        return;
    }
    vehicles.forEach(function (v) {
        const label = v.plate_number + ' (' + v.vehicle_type + (v.make ? ' · ' + v.make : '') + (v.model ? ' ' + v.model : '') + ')';
        const opt = document.createElement('option');
        opt.value = v.id;
        opt.textContent = label;
        sel.appendChild(opt);
    });
    wrap.style.display = '';
}

function addMotoristRow() {
    const container = document.getElementById('motorists-container');
    const tpl = document.getElementById('motorist-row-tpl').innerHTML;
    const idx  = motoristIndex++;
    const rowNum = container.querySelectorAll('.motorist-row').length + 1;
    const html = tpl.replace(/__IDX__/g, idx).replace(/__NUM__/g, rowNum);

    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    const row = wrapper.firstElementChild;
    container.appendChild(row);

    // Init flatpickr on expiry date field in new row
    const expiryInput = row.querySelector('.motorist-expiry-picker');
    initIncidentDatePicker(expiryInput);

    // Init Tom Select on the new violator select
    const sel = row.querySelector('.violator-select');
    if (sel && window.TomSelect) {
        new TomSelect(sel, {
            maxOptions: 200,
            allowEmptyOption: true,
            onChange: function (value) {
                populateVehicles(row, value);
            },
        });
    }

    // Init Tom Select on owner violator select
    const ownerSel = row.querySelector('.owner-violator-select');
    if (ownerSel && window.TomSelect) {
        new TomSelect(ownerSel, { maxOptions: 200, allowEmptyOption: true });
    }

    updateMotoristCount();
}

function removeMotoristRow(btn) {
    const row = btn.closest('.motorist-row');
    const container = document.getElementById('motorists-container');
    if (container.querySelectorAll('.motorist-row').length <= 1) {
        alert('An incident must have at least 1 motorist.');
        return;
    }
    row.remove();
    renumberRows();
    updateMotoristCount();
}

function renumberRows() {
    document.querySelectorAll('#motorists-container .motorist-row').forEach(function(row, i) {
        const num = row.querySelector('.row-num');
        const badge = row.querySelector('.row-badge');
        if (num) num.textContent = i + 1;
        if (badge) badge.textContent = i + 1;
    });
}

function updateMotoristCount() {
    const count = document.querySelectorAll('#motorists-container .motorist-row').length;
    document.getElementById('motorist-count').textContent = count;
}

function toggleOwnerSection(checkbox) {
    const row = checkbox.closest('.motorist-row');
    const section = row.querySelector('.owner-section');
    if (section) section.style.display = checkbox.checked ? '' : 'none';
    if (!checkbox.checked) {
        // Clear owner fields when toggled off
        const ownerViolatorSel = row.querySelector('.owner-violator-select');
        if (ownerViolatorSel && ownerViolatorSel.tomselect) ownerViolatorSel.tomselect.clear();
        const ownerNameInput = row.querySelector('[name*="vehicle_owner_name"]');
        if (ownerNameInput) ownerNameInput.value = '';
        const ownerContactInput = row.querySelector('[name*="vehicle_owner_contact"]');
        if (ownerContactInput) ownerContactInput.value = '';
    }
}

function toggleMotoristMode(radio) {
    const row = radio.closest('.motorist-row');
    const isManual = radio.value === 'manual';
    row.querySelector('.motorist-reg-section').style.display    = isManual ? 'none' : '';
    row.querySelector('.motorist-manual-section').style.display = isManual ? ''     : 'none';
    if (isManual) {
        const wrap = row.querySelector('.vehicle-reg-wrap');
        if (wrap) wrap.style.display = 'none';
    }
}

function checkExpiryWarning(input) {
    const row = input.closest('.motorist-row');
    if (!row) return;
    const warn = row.querySelector('.expiry-warning');
    if (!warn) return;
    const val = input.value;
    if (val && new Date(val) < new Date()) {
        warn.style.display = '';
    } else {
        warn.style.display = 'none';
    }
}

function handleMediaUpload(input) {
    const container = document.getElementById('media-preview-container');
    container.innerHTML = '';

    // Aggregate size check (warn if total > 200MB)
    let totalBytes = 0;
    Array.from(input.files).forEach(f => { totalBytes += f.size; });
    if (totalBytes > 200 * 1024 * 1024) {
        alert('Total upload size exceeds 200MB. Please reduce the number or size of files.');
        input.value = '';
        return;
    }

    const mediaTypes = ['scene', 'ticket', 'document', 'other'];
    const mediaLabels = { scene: 'Scene Photo', ticket: 'Citation Ticket', document: 'Document', other: 'Other' };

    Array.from(input.files).forEach(function (file, i) {
        const isPdf = file.name.toLowerCase().endsWith('.pdf');
        const col   = document.createElement('div');
        col.className = 'col-md-4 col-6';

        const thumb = isPdf
            ? `<div class="d-flex align-items-center justify-content-center bg-light rounded mb-1" style="height:90px;font-size:2rem;color:#6b7280;"><i class="bi bi-file-earmark-pdf-fill text-danger"></i></div>`
            : `<img src="${URL.createObjectURL(file)}" class="rounded mb-1 w-100" style="height:90px;object-fit:cover;" alt="">`;

        col.innerHTML = `
            <div class="border rounded p-2" style="font-size:.75rem;">
                ${thumb}
                <div class="text-truncate text-muted mb-1" title="${file.name}">${file.name}</div>
                <select name="media_types[]" class="form-select form-select-sm mb-1">
                    ${mediaTypes.map(t => `<option value="${t}">${mediaLabels[t]}</option>`).join('')}
                </select>
                <input type="text" name="captions[]" class="form-control form-control-sm" placeholder="Caption (optional)">
            </div>`;
        container.appendChild(col);
    });
}

function previewIdPhoto(input) {
    const row     = input.closest('.motorist-row');
    const preview = row.querySelector('.motorist-id-photo-preview');
    const img     = preview.querySelector('img');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        preview.style.display = '';
    } else {
        preview.style.display = 'none';
    }
}

function clearIdPhoto(btn) {
    const row     = btn.closest('.motorist-row');
    const input   = row.querySelector('.motorist-id-photo-input');
    const preview = row.querySelector('.motorist-id-photo-preview');
    input.value   = '';
    preview.style.display = 'none';
}

function previewMotoristPhotos(input) {
    const row       = input.closest('.motorist-row');
    const container = row.querySelector('.motorist-photos-preview');
    container.querySelectorAll('.new-photo-thumb').forEach(el => el.remove());
    const files = Array.from(input.files).slice(0, 4);
    files.forEach(function (file) {
        const div = document.createElement('div');
        div.className = 'new-photo-thumb position-relative';
        div.style.cssText = 'width:72px;flex-shrink:0;';
        div.innerHTML = `<img src="${URL.createObjectURL(file)}" class="rounded border w-100" style="height:72px;object-fit:cover;display:block;" alt="">`;
        container.appendChild(div);
    });
}

function attachIncidentFlatpickrTheme(instance) {
    if (instance && instance.calendarContainer) {
        instance.calendarContainer.classList.add('incident-flatpickr-theme');
    }
}

function initIncidentDatePicker(target, options) {
    if (!target || !window.flatpickr) return null;
    const extraOptions = options || {};
    const originalOnReady = extraOptions.onReady;

    return flatpickr(target, Object.assign({
        dateFormat: 'Y-m-d',
        allowInput: true,
        appendTo: document.body,
        monthSelectorType: 'static',
        onReady: function (selectedDates, dateStr, instance) {
            attachIncidentFlatpickrTheme(instance);
            if (typeof originalOnReady === 'function') {
                originalOnReady.call(this, selectedDates, dateStr, instance);
            }
        }
    }, extraOptions));
}

document.addEventListener('DOMContentLoaded', function () {
    initIncidentDatePicker('#date_of_incident', { maxDate: 'today' });
    flatpickr('#time_of_incident', { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: false, altInput: true, altFormat: 'h:i K', allowInput: true, appendTo: document.body });

    addMotoristRow();

    // Warn before leaving with unsaved changes
    let formDirty = false;
    const form = document.getElementById('incident-form');
    form.addEventListener('input', function () { formDirty = true; });
    form.addEventListener('change', function () { formDirty = true; });
    form.addEventListener('submit', function () {
        formDirty = false;
        var btn = document.getElementById('incidentSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Saving…';
    });
    window.addEventListener('beforeunload', function (e) {
        if (formDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>
@endpush
