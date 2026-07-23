@extends('layouts.app')
@section('title', 'Data Subject Request Portal')

@section('content')
<div class="container py-4" style="max-width:760px;">

    <div class="mb-4">
        <a href="{{ route('privacy.policy') }}" class="text-decoration-none text-muted" style="font-size:.88rem;">
            <i class="bi bi-arrow-left me-1"></i> Back to Data Privacy Policy
        </a>
        <h3 class="fw-700 mt-2 mb-1" style="color:#0f172a;">Data Subject Request (DSR) Portal</h3>
        <p class="text-muted mb-0" style="font-size:.9rem;">
            Submit a formal privacy request under Republic Act No. 10173 (Data Privacy Act of 2012).
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('privacy.dsr.submit') }}">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required placeholder="Juan Dela Cruz">
                        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="juan@example.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Contact Number <span class="text-danger">*</span></label>
                        <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number') }}" required placeholder="09171234567">
                        @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Driver's License No. (Optional)</label>
                        <input type="text" name="license_number" class="form-control @error('license_number') is-invalid @enderror" value="{{ old('license_number') }}" placeholder="A01-12-345678">
                        @error('license_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Citation Ticket No. (Optional)</label>
                        <input type="text" name="ticket_number" class="form-control @error('ticket_number') is-invalid @enderror" value="{{ old('ticket_number') }}" placeholder="TVIRS-CEB-BAL-2026-000001">
                        @error('ticket_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Request Type <span class="text-danger">*</span></label>
                        <select name="request_type" class="form-select @error('request_type') is-invalid @enderror" required>
                            <option value="access" {{ old('request_type') == 'access' ? 'selected' : '' }}>Request Access to My Records</option>
                            <option value="correction" {{ old('request_type') == 'correction' ? 'selected' : '' }}>Request Correction of Personal Data</option>
                            <option value="erasure" {{ old('request_type') == 'erasure' ? 'selected' : '' }}>Request Erasure / Blocking of Information</option>
                            <option value="objection" {{ old('request_type') == 'objection' ? 'selected' : '' }}>Objection to Processing</option>
                            <option value="inquiry" {{ old('request_type') == 'inquiry' ? 'selected' : '' }}>General Privacy Inquiry</option>
                        </select>
                        @error('request_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-600" style="font-size:.85rem;">Request Details <span class="text-danger">*</span></label>
                    <textarea name="details" rows="5" class="form-control @error('details') is-invalid @enderror" required placeholder="Please describe your specific privacy request or the details you wish to access or correct...">{{ old('details') }}</textarea>
                    @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text" style="font-size:.78rem;">
                        <i class="bi bi-lock me-1"></i> Your request is transmitted securely and will be processed strictly by authorized Data Protection Officers (DPO).
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('privacy.policy') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-warning px-4 fw-600">
                        <i class="bi bi-send-fill me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
