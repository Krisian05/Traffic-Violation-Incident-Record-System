@extends('layouts.app')
@section('title', 'Edit ' . $incident->incident_number)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('incidents.index') }}" style="color:#78716c;">Incidents</a></li>
    <li class="breadcrumb-item"><a href="{{ route('incidents.show', $incident) }}" style="color:#78716c;">{{ $incident->incident_number }}</a></li>
    <li class="breadcrumb-item active" aria-current="page" style="color:#44403c;">Edit</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
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
.inc-section-card {
    background: #fff; border: 1px solid #f0ebe3; border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.06), 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden; margin-bottom: 1.5rem;
}
.inc-card-header {
    display: flex; align-items: center; justify-content: space-between; gap: .75rem;
    padding: .9rem 1.25rem; border-bottom: 1px solid #f0ebe3;
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
.inc-submit-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
    padding: .55rem 1.5rem; border-radius: 10px;
    font-size: .875rem; font-weight: 700;
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    color: #fff; border: none; box-shadow: 0 3px 10px rgba(29,78,216,.3);
    cursor: pointer; transition: all .15s; width: 100%;
}
.inc-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(29,78,216,.4); color:#fff; }
.fw-500 { font-weight: 500; } .fw-600 { font-weight: 600; } .fw-700 { font-weight: 700; }
</style>
@endpush

@section('topbar-sub')
    <i class="bi bi-flag-fill me-1" style="color:#dc2626;"></i>
    Edit <span class="font-monospace">{{ $incident->incident_number }}</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:42px;height:42px;background:linear-gradient(135deg,#1d4ed8,#1e40af);flex-shrink:0;">
        <i class="bi bi-flag-fill text-white" style="font-size:1rem;"></i>
    </div>
    <div>
        <h5 class="mb-0 fw-700" style="color:#1c1917;">Edit {{ $incident->incident_number }}</h5>
        <div style="font-size:.8rem;color:#78716c;">Update this traffic incident report</div>
    </div>
</div>

<form method="POST" action="{{ route('incidents.update', $incident) }}" enctype="multipart/form-data" id="incident-form">
@csrf @method('PUT')

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
                                value="{{ old('date_of_incident', $incident->date_of_incident->format('Y-m-d')) }}" required>
                            @error('date_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500" style="font-size:.84rem;">Time of Incident</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-clock-fill" style="color:#0369a1;font-size:.8rem;"></i></span>
                            <input type="text" name="time_of_incident" id="time_of_incident"
                                class="form-control @error('time_of_incident') is-invalid @enderror"
                                value="{{ old('time_of_incident', $incident->time_of_incident ? substr($incident->time_of_incident, 0, 5) : '') }}"
                                placeholder="hh:mm AM/PM">
                            @error('time_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-12">
                        @include('partials.location-selector', [
                            'fieldName'    => 'location',
                            'required'     => true,
                            'label'        => 'Location',
                            'initialValue' => $incident->location,
                        ])
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-500" style="font-size:.84rem;">Description / Narrative</label>
                        <div class="input-group input-group-sm align-items-start">
                            <span class="input-group-text"><i class="bi bi-chat-text-fill" style="color:#78716c;font-size:.8rem;"></i></span>
                            <textarea name="description" rows="3"
                                class="form-control @error('description') is-invalid @enderror">{{ old('description', $incident->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500" style="font-size:.84rem;"><i class="bi bi-circle-fill me-1" style="font-size:.65rem;color:#6b7280;"></i>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm @error('status') is-invalid @enderror">
                            <option value="open"         {{ old('status', $incident->status) === 'open'         ? 'selected' : '' }}>Open</option>
                            <option value="under_review" {{ old('status', $incident->status) === 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="closed"       {{ old('status', $incident->status) === 'closed'       ? 'selected' : '' }}>Closed</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        <div class="inc-section-sub">Motorists are rebuilt on save</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" style="background:#fef3c7;color:#92400e;font-size:.72rem;" id="motorist-count">{{ $incident->motorists->count() }}</span>
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
                    <small style="color:#a8a29e;font-size:.74rem;"><i class="bi bi-info-circle me-1"></i>Minimum 2 motorists required. Existing motorists will be replaced on save.</small>
                </div>
            </div>
        </div>

        {{-- Existing Media --}}
        @if($incident->media->count())
        <div class="inc-section-card">
            <div class="inc-card-header" style="background:linear-gradient(135deg,#f0fdf4 0%,#fff 100%);">
                <div class="inc-card-header-left">
                    <span class="inc-section-icon" style="background:linear-gradient(135deg,#16a34a,#15803d);box-shadow:0 3px 10px rgba(21,128,61,.3);">
                        <i class="bi bi-images" style="color:#fff;font-size:.85rem;"></i>
                    </span>
                    <div>
                        <div class="inc-section-title">Existing Media</div>
                        <div class="inc-section-sub">Remove individual files as needed</div>
                    </div>
                </div>
                <span class="badge rounded-pill" style="background:#dcfce7;color:#15803d;font-size:.72rem;">{{ $incident->media->count() }} file{{ $incident->media->count() != 1 ? 's' : '' }}</span>
            </div>
            <div class="card-body">
                @php
                    $mediaTypeLabels = ['scene' => 'Scene Photo', 'ticket' => 'Citation Ticket', 'document' => 'Document', 'other' => 'Other'];
                @endphp
                <div class="row g-2">
                    @foreach($incident->media as $media)
                    <div class="col-md-3 col-sm-4 col-6">
                        <div class="border rounded overflow-hidden" style="font-size:.75rem;">
                            @if($media->isImage())
                                <img src="{{ uploaded_file_url($media->file_path) }}" class="w-100"
                                    style="height:80px;object-fit:cover;display:block;" alt="">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light" style="height:80px;">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size:1.8rem;"></i>
                                </div>
                            @endif
                            <div class="p-1">
                                <div class="text-muted mb-1">{{ $mediaTypeLabels[$media->media_type] ?? $media->media_type }}</div>
                                <form method="POST" action="{{ route('incident-media.destroy', $media) }}"
                                    data-confirm="Remove this file? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1 w-100" style="font-size:.7rem;">
                                        <i class="bi bi-trash-fill"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Add New Media --}}
        <div class="inc-section-card">
            <div class="inc-card-header" style="background:linear-gradient(135deg,#eff6ff 0%,#fff 100%);">
                <div class="inc-card-header-left">
                    <span class="inc-section-icon" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 3px 10px rgba(29,78,216,.3);">
                        <i class="bi bi-cloud-upload-fill" style="color:#fff;font-size:.85rem;"></i>
                    </span>
                    <div>
                        <div class="inc-section-title">Add More Evidence</div>
                        <div class="inc-section-sub">Photos &amp; documents (max 20MB each)</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-500" style="font-size:.84rem;"><i class="bi bi-paperclip me-1" style="color:#2563eb;"></i>Upload Files <small class="text-muted">(jpg, png, pdf)</small></label>
                    <input type="file" name="media[]" id="media-upload" class="form-control form-control-sm"
                        multiple accept="image/jpg,image/jpeg,image/png,application/pdf"
                        onchange="handleMediaUpload(this)">
                </div>
                <div id="media-preview-container" class="row g-2"></div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-4">
        <div class="inc-section-card mb-3" style="border-color:#bfdbfe;">
            <div class="inc-card-header" style="background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);">
                <div class="inc-card-header-left">
                    <span class="inc-section-icon" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 3px 10px rgba(29,78,216,.3);">
                        <i class="bi bi-info-circle-fill" style="color:#fff;font-size:.85rem;"></i>
                    </span>
                    <div>
                        <div class="inc-section-title" style="color:#1d4ed8;">Editing Record</div>
                        <div class="inc-section-sub font-monospace" style="color:#3b82f6;">{{ $incident->incident_number }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body" style="background:#f0f9ff;">
                <p class="mb-0" style="font-size:.78rem;color:#374151;line-height:1.55;">
                    <i class="bi bi-arrow-repeat me-1" style="color:#2563eb;"></i>Motorist rows are <strong>rebuilt on save</strong> — all current motorists will be replaced.<br><br>
                    <i class="bi bi-images me-1" style="color:#16a34a;"></i>Existing media is kept unless removed individually above.
                </p>
            </div>
        </div>

        <div class="inc-section-card">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="inc-submit-btn">
                    <i class="bi bi-floppy-fill"></i> Save Changes
                </button>
                <a href="{{ route('incidents.show', $incident) }}"
                    class="btn btn-outline-secondary rounded-pill w-100"
                    style="font-size:.875rem;font-weight:600;">
                    <i class="bi bi-x-lg me-1"></i>Cancel
                </a>
            </div>
        </div>
    </div>
</div>

</form>

{{-- Motorist row template --}}
<template id="motorist-row-tpl">
    <div class="motorist-row" data-index="__IDX__" style="border-top:1px solid #f5f5f4;">
        <input type="hidden" name="motorists[__IDX__][motorist_id]" class="motorist-id-input" value="">
        <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="row-badge d-inline-flex align-items-center justify-content-center fw-700"
                    style="width:26px;height:26px;border-radius:7px;font-size:.78rem;background:linear-gradient(135deg,#d97706,#b45309);color:#fff;box-shadow:0 2px 6px rgba(180,83,9,.25);">__NUM__</span>
                <span class="fw-600" style="font-size:.82rem;color:#374151;">Motorist</span>
            </div>
            <button type="button" onclick="removeMotoristRow(this)" title="Remove motorist"
                style="display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .6rem;border-radius:7px;font-size:.75rem;font-weight:600;background:#fff0f0;color:#dc2626;border:1.5px solid #fecaca;cursor:pointer;transition:all .15s;"
                onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff0f0'">
                <i class="bi bi-x-circle-fill" style="font-size:.8rem;"></i> Remove
            </button>
        </div>
        <div class="px-3 pb-2">
            <div class="btn-group btn-group-sm w-100" role="group">
                <input type="radio" class="btn-check" name="motorists[__IDX__][mode]" id="mode-reg-__IDX__" value="registered" checked onchange="toggleMotoristMode(this)">
                <label class="btn btn-outline-primary" for="mode-reg-__IDX__">
                    <i class="bi bi-person-check-fill me-1"></i>Registered Motorist
                </label>
                <input type="radio" class="btn-check" name="motorists[__IDX__][mode]" id="mode-manual-__IDX__" value="manual" onchange="toggleMotoristMode(this)">
                <label class="btn btn-outline-secondary" for="mode-manual-__IDX__">
                    <i class="bi bi-pencil-fill me-1"></i>Enter Manually
                </label>
            </div>
        </div>
        <div class="motorist-reg-section px-3 pb-2">
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-person-check-fill me-1" style="color:#2563eb;"></i>Registered Motorist</label>
                    <select name="motorists[__IDX__][violator_id]" class="form-select form-select-sm violator-select" data-idx="__IDX__">
                        <option value="">— Search by name —</option>
                        @foreach($violators as $v)
                            <option value="{{ $v->id }}">{{ $v->last_name }}, {{ $v->first_name }}{{ $v->middle_name ? ' '.$v->middle_name : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 vehicle-reg-wrap" style="display:none;">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-car-front-fill me-1" style="color:#16a34a;"></i>Registered Vehicle</label>
                    <select name="motorists[__IDX__][vehicle_id]" class="form-select form-select-sm vehicle-reg-select">
                        <option value="">— Select vehicle —</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="motorist-manual-section px-3 pb-2" style="display:none;">
            <div class="row g-2">
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-person-fill me-1" style="color:#374151;"></i>Full Name</label>
                    <input type="text" name="motorists[__IDX__][motorist_name]" class="form-control form-control-sm" placeholder="Full name">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-credit-card-fill me-1" style="color:#374151;"></i>License No.</label>
                    <input type="text" name="motorists[__IDX__][motorist_license]" class="form-control form-control-sm" placeholder="e.g. A01-23-456789">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-patch-check-fill me-1" style="color:#2563eb;"></i>License Type</label>
                    <select name="motorists[__IDX__][license_type]" class="form-select form-select-sm license-type-select">
                        <option value="">— Select —</option>
                        <option value="Professional">Professional</option>
                        <option value="Non-Professional">Non-Professional</option>
                        <option value="Student Permit">Student Permit</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-shield-fill-check me-1" style="color:#ca8a04;"></i>Restriction Code</label>
                    <div class="input-group">
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
                    <small class="text-muted" style="font-size:.72rem;"><i class="bi bi-info-circle"></i> Tap to select all codes printed on the license.</small>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-calendar-x-fill me-1" style="color:#dc2626;"></i>License Expiry</label>
                    <input type="text" name="motorists[__IDX__][license_expiry_date]" class="form-control form-control-sm motorist-expiry-picker" placeholder="YYYY-MM-DD" onchange="checkExpiryWarning(this)">
                    <div class="expiry-warning" style="display:none;font-size:.71rem;color:#dc2626;margin-top:.2rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>This license is already expired.</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-car-front-fill me-1" style="color:#16a34a;"></i>Plate No.</label>
                    <input type="text" name="motorists[__IDX__][vehicle_plate]" class="form-control form-control-sm" placeholder="e.g. ABC 1234">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-truck-front-fill me-1" style="color:#374151;"></i>Vehicle Type</label>
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

        <div class="row g-2 px-3 pb-2">
            <div class="col-md-6">
                <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-shield-exclamation me-1" style="color:#dc2626;"></i>Charge / Offense</label>
                <select name="motorists[__IDX__][incident_charge_type_id]" class="form-select form-select-sm charge-type-select">
                    <option value="">— None —</option>
                    @foreach($chargeTypes as $ct)
                        <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-chat-right-text-fill me-1" style="color:#78716c;"></i>Notes</label>
                <input type="text" name="motorists[__IDX__][notes]" class="form-control form-control-sm" placeholder="e.g. Driver refused to stop">
            </div>
        </div>

        {{-- MC/MV Photos (up to 4) --}}
        <div class="row g-2 px-3 pb-3">
            <div class="col-12">
                <label class="form-label fw-500 mb-1" style="font-size:.8rem;"><i class="bi bi-camera-fill me-1" style="color:#6b7280;"></i>MC/MV Photos <small class="text-muted">(optional · up to 4 · jpg/png · max 20MB each)</small></label>
                <div class="motorist-photos-preview d-flex flex-wrap gap-1 mb-1"></div>
                <input type="file" name="motorist_photos[__IDX__][]" class="form-control form-control-sm motorist-photo-input"
                    accept="image/jpg,image/jpeg,image/png" multiple onchange="previewMotoristPhotos(this)">
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script type="application/json" id="vehicles-by-owner">@json($vehiclesByOwner)</script>
@php
$existingMotoristsData = $incident->motorists->map(fn($m) => [
    'id'                       => $m->id,
    'violator_id'              => $m->violator_id,
    'motorist_name'            => $m->motorist_name,
    'motorist_license'         => $m->motorist_license,
    'motorist_photo'           => $m->motorist_photo,
    'motorist_contact'         => $m->motorist_contact,
    'motorist_address'         => $m->motorist_address,
    'license_type'             => $m->license_type,
    'license_restriction'      => $m->license_restriction,
    'license_expiry_date'      => $m->license_expiry_date?->format('Y-m-d'),
    'vehicle_id'               => $m->vehicle_id,
    'vehicle_plate'            => $m->vehicle_plate,
    'vehicle_type_manual'      => $m->vehicle_type_manual,
    'vehicle_make'             => $m->vehicle_make,
    'vehicle_model'            => $m->vehicle_model,
    'vehicle_color'            => $m->vehicle_color,
    'vehicle_or_number'        => $m->vehicle_or_number,
    'vehicle_cr_number'        => $m->vehicle_cr_number,
    'vehicle_chassis'              => $m->vehicle_chassis,
    'vehicle_photo'                => $m->vehicle_photo,
    'vehicle_owner_violator_id'    => $m->vehicle_owner_violator_id,
    'vehicle_owner_name'           => $m->vehicle_owner_name,
    'vehicle_owner_contact'        => $m->vehicle_owner_contact,
    'incident_charge_type_id'      => $m->incident_charge_type_id,
    'notes'                        => $m->notes,
]);
@endphp
<script type="application/json" id="existing-motorists">@json($existingMotoristsData)</script>
<script>
let motoristIndex = 0;
const vehiclesByOwner  = JSON.parse(document.getElementById('vehicles-by-owner').textContent);
const existingMotorists = JSON.parse(document.getElementById('existing-motorists').textContent);

function populateVehicles(row, violatorId, selectValue) {
    const wrap = row.querySelector('.vehicle-reg-wrap');
    const sel  = row.querySelector('.vehicle-reg-select');
    if (!wrap || !sel) return;

    sel.innerHTML = '<option value="">— Select vehicle —</option>';
    const vehicles = vehiclesByOwner[violatorId] || [];
    if (vehicles.length === 0) { wrap.style.display = 'none'; return; }
    vehicles.forEach(function (v) {
        const label = v.plate_number + ' (' + v.vehicle_type + (v.make ? ' · ' + v.make : '') + (v.model ? ' ' + v.model : '') + ')';
        const opt = document.createElement('option');
        opt.value = v.id;
        opt.textContent = label;
        sel.appendChild(opt);
    });
    wrap.style.display = '';
    if (selectValue) sel.value = selectValue;
}

function addMotoristRow(prefill) {
    const container = document.getElementById('motorists-container');
    const tpl = document.getElementById('motorist-row-tpl').innerHTML;
    const idx  = motoristIndex++;
    const rowNum = container.querySelectorAll('.motorist-row').length + 1;
    const html = tpl.replace(/__IDX__/g, idx).replace(/__NUM__/g, rowNum);

    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    const row = wrapper.firstElementChild;
    container.appendChild(row);

    // Init flatpickr on expiry date field
    const expiryInput = row.querySelector('.motorist-expiry-picker');
    if (expiryInput && window.flatpickr) {
        flatpickr(expiryInput, { dateFormat: 'Y-m-d', allowInput: true });
    }

    const sel = row.querySelector('.violator-select');
    let ts;
    if (sel && window.TomSelect) {
        ts = new TomSelect(sel, {
            maxOptions: 200,
            allowEmptyOption: true,
            onChange: function (value) {
                populateVehicles(row, value, null);
            },
        });
    }

    // Init Tom Select on owner violator select
    const ownerSel = row.querySelector('.owner-violator-select');
    if (ownerSel && window.TomSelect) {
        new TomSelect(ownerSel, { maxOptions: 200, allowEmptyOption: true });
    }

    // Set motorist DB ID for selective update (C2 fix)
    if (prefill && prefill.id) {
        const idInput = row.querySelector('.motorist-id-input');
        if (idInput) idInput.value = prefill.id;
    }

    if (prefill) {
        if (prefill.violator_id) {
            if (ts) {
                ts.setValue(String(prefill.violator_id));
                populateVehicles(row, prefill.violator_id, prefill.vehicle_id ? String(prefill.vehicle_id) : null);
            } else {
                sel.value = prefill.violator_id;
            }
        }
        // If motorist_name is stored (manual entry or auto-registered from manual),
        // switch to manual mode so the user can see and edit all the detailed fields.
        if (prefill.motorist_name) {
            const manualRadio = row.querySelector('input[value="manual"]');
            if (manualRadio) { manualRadio.checked = true; toggleMotoristMode(manualRadio); }
            const nameInput    = row.querySelector('input[name$="[motorist_name]"]');
            const licInput     = row.querySelector('input[name$="[motorist_license]"]');
            const expiryPicker = row.querySelector('.motorist-expiry-picker');
            const plateInput   = row.querySelector('input[name$="[vehicle_plate]"]');
            const vtManSel     = row.querySelector('select[name$="[vehicle_type_manual]"]');
            if (nameInput) nameInput.value = prefill.motorist_name || '';
            if (licInput)  licInput.value  = prefill.motorist_license || '';
            const licTypeSel = row.querySelector('.license-type-select');
            if (licTypeSel && prefill.license_type) licTypeSel.value = prefill.license_type;
            if (prefill.license_restriction) {
                const codes = String(prefill.license_restriction).split(',').map(s => s.trim()).filter(Boolean);
                codes.forEach(function (code) {
                    const cb = row.querySelector('input[type="checkbox"][name$="[license_restriction][]"][value="' + code + '"]');
                    if (cb) cb.checked = true;
                });
            }
            if (expiryPicker && prefill.license_expiry_date) {
                // Set via flatpickr instance if available, else raw
                if (expiryPicker._flatpickr) expiryPicker._flatpickr.setDate(prefill.license_expiry_date);
                else expiryPicker.value = prefill.license_expiry_date;
            }
            if (plateInput && prefill.vehicle_plate) plateInput.value = prefill.vehicle_plate;
            if (vtManSel && prefill.vehicle_type_manual) vtManSel.value = prefill.vehicle_type_manual;

            const makeInput    = row.querySelector('input[name$="[vehicle_make]"]');
            const modelInput   = row.querySelector('input[name$="[vehicle_model]"]');
            const colorInput   = row.querySelector('input[name$="[vehicle_color]"]');
            const orInput      = row.querySelector('input[name$="[vehicle_or_number]"]');
            const crInput      = row.querySelector('input[name$="[vehicle_cr_number]"]');
            const chassisInput = row.querySelector('input[name$="[vehicle_chassis]"]');
            const contactInput = row.querySelector('input[name$="[motorist_contact]"]');
            const addressInput = row.querySelector('input[name$="[motorist_address]"]');
            if (makeInput    && prefill.vehicle_make)      makeInput.value    = prefill.vehicle_make;
            if (modelInput   && prefill.vehicle_model)     modelInput.value   = prefill.vehicle_model;
            if (colorInput   && prefill.vehicle_color)     colorInput.value   = prefill.vehicle_color;
            if (orInput      && prefill.vehicle_or_number) orInput.value      = prefill.vehicle_or_number;
            if (crInput      && prefill.vehicle_cr_number) crInput.value      = prefill.vehicle_cr_number;
            if (chassisInput && prefill.vehicle_chassis)   chassisInput.value = prefill.vehicle_chassis;
            if (contactInput && prefill.motorist_contact)  contactInput.value = prefill.motorist_contact;
            if (addressInput && prefill.motorist_address)  addressInput.value = prefill.motorist_address;

            // Prefill existing motorist ID photo
            if (prefill.motorist_photo) {
                const existingIdInput = row.querySelector('.existing-id-photo-input');
                if (existingIdInput) existingIdInput.value = prefill.motorist_photo;
                const idPreview = row.querySelector('.motorist-id-photo-preview');
                const idImg     = idPreview ? idPreview.querySelector('img') : null;
                if (idPreview && idImg) {
                    idImg.src = '/storage/' + prefill.motorist_photo;
                    idPreview.style.display = '';
                }
            }
        }
        // Prefill vehicle owner
        if (prefill.vehicle_owner_violator_id || prefill.vehicle_owner_name || prefill.vehicle_owner_contact) {
            const ownerCheck = row.querySelector('.owner-not-driver-check');
            if (ownerCheck) { ownerCheck.checked = true; toggleOwnerSection(ownerCheck); }
            const ownerViolSel = row.querySelector('.owner-violator-select');
            if (ownerViolSel && prefill.vehicle_owner_violator_id) {
                if (ownerViolSel.tomselect) ownerViolSel.tomselect.setValue(String(prefill.vehicle_owner_violator_id));
                else ownerViolSel.value = prefill.vehicle_owner_violator_id;
            }
            const ownerNameInput = row.querySelector('input[name$="[vehicle_owner_name]"]');
            if (ownerNameInput && prefill.vehicle_owner_name) ownerNameInput.value = prefill.vehicle_owner_name;
            const ownerContactInput = row.querySelector('input[name$="[vehicle_owner_contact]"]');
            if (ownerContactInput && prefill.vehicle_owner_contact) ownerContactInput.value = prefill.vehicle_owner_contact;
        }

        const ctSel = row.querySelector('.charge-type-select');
        if (ctSel && prefill.incident_charge_type_id) ctSel.value = prefill.incident_charge_type_id;
        const notesInput = row.querySelector('input[name$="[notes]"]');
        if (notesInput && prefill.notes) notesInput.value = prefill.notes;

        // Prefill existing vehicle photos
        if (prefill.vehicle_photo && prefill.vehicle_photo.length) {
            const photoContainer = row.querySelector('.motorist-photos-preview');
            prefill.vehicle_photo.forEach(function (path) {
                const div = document.createElement('div');
                div.className = 'existing-photo-thumb position-relative';
                div.style.cssText = 'width:72px;flex-shrink:0;';
                div.innerHTML =
                    `<img src="/storage/${path}" class="rounded border w-100" style="height:72px;object-fit:cover;display:block;" alt="">` +
                    `<input type="hidden" name="motorists[${idx}][existing_vehicle_photos][]" value="${path}">` +
                    `<button type="button" class="btn btn-danger position-absolute" ` +
                        `style="top:2px;right:2px;padding:1px 5px;font-size:.6rem;line-height:1.2;border-radius:4px;" ` +
                        `onclick="removeExistingVehiclePhoto(this)" title="Remove">` +
                        `<i class="bi bi-x"></i>` +
                    `</button>`;
                photoContainer.appendChild(div);
            });
        }
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
        const badge = row.querySelector('.row-badge');
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
    row.querySelector('.motorist-manual-section').style.display = isManual ? '' : 'none';
    if (isManual) {
        const wrap = row.querySelector('.vehicle-reg-wrap');
        if (wrap) wrap.style.display = 'none';
    }
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
    const row          = btn.closest('.motorist-row');
    const input        = row.querySelector('.motorist-id-photo-input');
    const existingInput= row.querySelector('.existing-id-photo-input');
    const preview      = row.querySelector('.motorist-id-photo-preview');
    input.value        = '';
    if (existingInput) existingInput.value = '';
    preview.style.display = 'none';
}

function previewMotoristPhotos(input) {
    const row       = input.closest('.motorist-row');
    const container = row.querySelector('.motorist-photos-preview');
    container.querySelectorAll('.new-photo-thumb').forEach(el => el.remove());
    const existingCount = container.querySelectorAll('.existing-photo-thumb').length;
    Array.from(input.files).slice(0, 4 - existingCount).forEach(function (file) {
        const div = document.createElement('div');
        div.className = 'new-photo-thumb position-relative';
        div.style.cssText = 'width:72px;flex-shrink:0;';
        div.innerHTML = `<img src="${URL.createObjectURL(file)}" class="rounded border w-100" style="height:72px;object-fit:cover;display:block;" alt="">`;
        container.appendChild(div);
    });
}

function removeExistingVehiclePhoto(btn) {
    btn.closest('.existing-photo-thumb').remove();
}

function handleMediaUpload(input) {
    const container = document.getElementById('media-preview-container');
    container.innerHTML = '';
    const mediaTypes = ['scene', 'ticket', 'document', 'other'];
    const mediaLabels = { scene: 'Scene Photo', ticket: 'Citation Ticket', document: 'Document', other: 'Other' };
    Array.from(input.files).forEach(function (file, i) {
        const isPdf = file.name.toLowerCase().endsWith('.pdf');
        const col = document.createElement('div');
        col.className = 'col-md-4 col-6';
        const thumb = isPdf
            ? `<div class="d-flex align-items-center justify-content-center bg-light rounded mb-1" style="height:80px;font-size:2rem;"><i class="bi bi-file-earmark-pdf-fill text-danger"></i></div>`
            : `<img src="${URL.createObjectURL(file)}" class="rounded mb-1 w-100" style="height:80px;object-fit:cover;" alt="">`;
        col.innerHTML = `
            <div class="border rounded p-2" style="font-size:.75rem;">
                ${thumb}
                <div class="text-truncate text-muted mb-1">${file.name}</div>
                <select name="media_types[]" class="form-select form-select-sm mb-1">
                    ${mediaTypes.map(t => `<option value="${t}">${mediaLabels[t]}</option>`).join('')}
                </select>
                <input type="text" name="captions[]" class="form-control form-control-sm" placeholder="Caption (optional)">
            </div>`;
        container.appendChild(col);
    });
}

function checkExpiryWarning(input) {
    const row = input.closest('.motorist-row');
    if (!row) return;
    const warn = row.querySelector('.expiry-warning');
    if (!warn) return;
    if (input.value && new Date(input.value) < new Date()) {
        warn.style.display = '';
    } else {
        warn.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    flatpickr('#date_of_incident', { dateFormat: 'Y-m-d', maxDate: 'today', allowInput: true });
    flatpickr('#time_of_incident', { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: false, altInput: true, altFormat: 'h:i K', allowInput: true });

    // Pre-populate existing motorists (with null guard — M8)
    if (Array.isArray(existingMotorists)) {
        existingMotorists.forEach(function (m) {
            if (m && typeof m === 'object') { addMotoristRow(m); }
        });
    }

    // Ensure at least 1 row
    while (document.querySelectorAll('#motorists-container .motorist-row').length < 1) {
        addMotoristRow(null);
    }

    // Warn before leaving with unsaved changes (M9)
    let formDirty = false;
    const form = document.getElementById('incident-form');
    form.addEventListener('input', function () { formDirty = true; });
    form.addEventListener('change', function () { formDirty = true; });
    form.addEventListener('submit', function () { formDirty = false; });
    window.addEventListener('beforeunload', function (e) {
        if (formDirty) { e.preventDefault(); e.returnValue = ''; }
    });
});
</script>
@endpush
