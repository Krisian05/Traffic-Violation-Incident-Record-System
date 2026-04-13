@extends('layouts.mobile')
@section('title', 'Edit Incident')
@section('back_url', route('officer.incidents.show', $incident))

@push('styles')
@include('partials.motshow-styles')
<style>
.inc-edit-mot-item { display:flex;align-items:center;gap:.65rem;padding:.4rem 0; }
.inc-edit-mot-sep  { border-bottom:1px solid #e2e8f0; }
</style>
@endpush

@section('content')

{{-- Incident context --}}
<div class="mob-card" style="border-left:4px solid #dc2626;">
    <div class="mob-card-body d-flex align-items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#dc2626,#b91c1c);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-flag" style="font-size:1.1rem;color:#fff;"></i>
        </div>
        <div>
            <div style="font-size:.62rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Editing incident</div>
            <div style="font-size:.95rem;font-weight:800;color:#0f172a;">{{ $incident->incident_number }}</div>
        </div>
    </div>
</div>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.incidents.update', $incident) }}" enctype="multipart/form-data" data-offline-sync="true" data-offline-label="Incident Update">
            @csrf
            @method('PUT')

            @if($errors->any())
            <div class="mob-alert mob-alert-danger">
                <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            {{-- Incident Details --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Incident Details</span>
                <span class="mob-form-divider-line"></span>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-7">
                    <label class="mob-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_incident" required max="{{ date('Y-m-d') }}"
                           value="{{ old('date_of_incident', $incident->date_of_incident?->format('Y-m-d')) }}"
                           class="form-control mob-input @error('date_of_incident') is-invalid @enderror">
                    @error('date_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-5">
                    <label class="mob-label">Time</label>
                    <input type="time" name="time_of_incident"
                           value="{{ old('time_of_incident', $incident->time_of_incident) }}"
                           class="form-control mob-input @error('time_of_incident') is-invalid @enderror">
                    @error('time_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                @include('partials.location-selector', [
                    'fieldName'    => 'location',
                    'required'     => true,
                    'label'        => 'Location',
                    'inputSize'    => '',
                    'initialValue' => $incident->location,
                ])
            </div>

            <div class="mb-3">
                <label class="mob-label">Description</label>
                <textarea name="description" rows="3" class="form-control mob-input @error('description') is-invalid @enderror" style="min-height:auto;resize:none;" placeholder="Brief description of what happened...">{{ old('description', $incident->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Motorists (read-only) --}}
            @if($incident->motorists->isNotEmpty())
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Motorists Involved</span>
                <span class="mob-form-divider-line"></span>
            </div>
            <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:.75rem;margin-bottom:1rem;">
                @foreach($incident->motorists as $m)
                <div class="inc-edit-mot-item {{ !$loop->last ? 'inc-edit-mot-sep' : '' }}">
                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="ph-fill ph-user" style="color:#1d4ed8;font-size:.78rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:.82rem;font-weight:700;color:#0f172a;">{{ $m->violator?->full_name ?? $m->motorist_name ?? 'Unknown' }}</div>
                        @if($m->vehicle_plate || ($m->vehicle && $m->vehicle->plate_number))
                        <div style="font-size:.7rem;color:#64748b;font-family:ui-monospace,monospace;">{{ $m->vehicle?->plate_number ?? $m->vehicle_plate }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
                <div style="font-size:.68rem;color:#94a3b8;margin-top:.55rem;display:flex;align-items:center;gap:.35rem;">
                    <i class="ph ph-info" style="font-size:.75rem;"></i>
                    Contact an operator to modify motorists.
                </div>
            </div>
            @endif

            {{-- Add scene photos --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Scene Photos</span>
                <span class="mob-form-divider-line"></span>
            </div>

            @if($incident->media->isNotEmpty())
            <div style="margin-bottom:.65rem;">
                <div style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Existing Photos</div>
                <div class="row g-2">
                    @foreach($incident->media as $media)
                    <div class="col-4">
                        <img src="{{ uploaded_file_url($media->file_path) }}"
                             style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);"
                             alt="Scene photo">
                    </div>
                    @endforeach
                </div>
                <div style="font-size:.68rem;color:#94a3b8;margin-top:.45rem;">Upload below to add more (max 6 total)</div>
            </div>
            @endif

            <div class="mb-4">
                <label class="mob-label">Upload Scene Photos <span style="font-size:.68rem;color:#94a3b8;">(up to 6)</span></label>
                <div id="picker-incident-photos"></div>
                @error('incident_photos')<div style="font-size:.72rem;color:#dc2626;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="mob-btn-primary mob-btn-danger mb-2" id="incidentEditSubmitBtn">
                <i class="ph-bold ph-check"></i> Save Changes
            </button>
            <a href="{{ route('officer.incidents.show', $incident) }}" class="mob-btn-outline">
                <i class="ph ph-x-circle"></i> Cancel
            </a>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initPhotoPicker('picker-incident-photos', 'incident_photos[]', { multiple: true });

    document.querySelector('form').addEventListener('submit', function () {
        var btn = document.getElementById('incidentEditSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-hourglass"></i> Saving…';
    });
});
</script>
@endpush
