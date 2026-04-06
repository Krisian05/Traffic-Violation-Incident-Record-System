@extends('layouts.mobile')
@section('title', 'Edit Incident')
@section('back_url', route('officer.incidents.show', $incident))

@section('content')

{{-- Incident context --}}
<div class="mob-card" style="border-left:4px solid #f59e0b;">
    <div class="mob-card-body d-flex align-items-center gap-3">
        <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#d97706,#b45309);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ph-fill ph-warning" style="font-size:1.1rem;color:#fff;"></i>
        </div>
        <div>
            <div style="font-size:.62rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Editing incident</div>
            <div style="font-size:.95rem;font-weight:800;color:#0f172a;">{{ $incident->incident_number }}</div>
        </div>
    </div>
</div>

<div class="mob-card">
    <div class="mob-card-body">
        <form method="POST" action="{{ route('officer.incidents.update', $incident) }}" enctype="multipart/form-data">
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

            <div class="mb-3">
                <label class="mob-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date_of_incident" required max="{{ date('Y-m-d') }}"
                       value="{{ old('date_of_incident', $incident->date_of_incident?->format('Y-m-d')) }}"
                       class="form-control mob-input @error('date_of_incident') is-invalid @enderror">
                @error('date_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Time</label>
                <input type="time" name="time_of_incident"
                       value="{{ old('time_of_incident', $incident->time_of_incident) }}"
                       class="form-control mob-input @error('time_of_incident') is-invalid @enderror">
                @error('time_of_incident')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Location <span class="text-danger">*</span></label>
                <input type="text" name="location" required placeholder="Street / Barangay / Landmark"
                       value="{{ old('location', $incident->location) }}"
                       class="form-control mob-input @error('location') is-invalid @enderror">
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="mob-label">Description</label>
                <textarea name="description" rows="4" class="form-control mob-input @error('description') is-invalid @enderror">{{ old('description', $incident->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Motorists (read-only, managed by operator) --}}
            @if($incident->motorists->isNotEmpty())
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Motorists Involved</span>
                <span class="mob-form-divider-line"></span>
            </div>
            <div style="background:#f8fafc;border-radius:12px;padding:.75rem;margin-bottom:1rem;">
                @foreach($incident->motorists as $m)
                <div style="display:flex;align-items:center;gap:.6rem;padding:.35rem 0;{{ !$loop->last ? 'border-bottom:1px solid #e2e8f0;' : '' }}">
                    <i class="ph ph-user-circle" style="font-size:1.1rem;color:#64748b;flex-shrink:0;"></i>
                    <div>
                        <div style="font-size:.82rem;font-weight:700;color:#0f172a;">{{ $m->violator?->full_name ?? $m->motorist_name ?? 'Unknown' }}</div>
                        @if($m->vehicle_plate || ($m->vehicle && $m->vehicle->plate_number))
                        <div style="font-size:.72rem;color:#64748b;">{{ $m->vehicle?->plate_number ?? $m->vehicle_plate }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
                <div style="font-size:.68rem;color:#94a3b8;margin-top:.5rem;">Contact an operator to modify motorists.</div>
            </div>
            @endif

            {{-- Add scene photos --}}
            <div class="mob-form-divider">
                <span class="mob-form-divider-text">Add Photos</span>
                <span class="mob-form-divider-line"></span>
            </div>

            @if($incident->media->isNotEmpty())
            <div class="row g-2 mb-3">
                @foreach($incident->media as $media)
                <div class="col-4">
                    <img src="{{ uploaded_file_url($media->file_path) }}"
                         style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;"
                         alt="Scene photo">
                </div>
                @endforeach
            </div>
            <div style="font-size:.72rem;color:#64748b;margin-bottom:.75rem;">Existing photos — upload below to add more (max 6 total)</div>
            @endif

            <div class="mb-4">
                <label class="mob-label">Upload Scene Photos</label>
                <input type="file" name="incident_photos[]" accept="image/*" multiple
                       class="form-control mob-input @error('incident_photos') is-invalid @enderror">
                @error('incident_photos')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-100" style="border-radius:12px;font-weight:700;padding:.75rem;">
                <i class="ph ph-floppy-disk me-2"></i>Save Changes
            </button>
        </form>
    </div>
</div>

@endsection
