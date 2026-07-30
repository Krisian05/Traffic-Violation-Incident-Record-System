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
                    <div class="col-md-6 position-relative">
                        <label class="form-label fw-600 d-flex align-items-center justify-content-between" style="font-size:.85rem;">
                            <span>Full Name <span class="text-danger">*</span></span>
                            <span class="badge bg-light text-muted border fw-500" style="font-size:0.68rem;">
                                <i class="bi bi-search me-0.5 text-warning"></i> Type name to search
                            </span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="full_name" id="fullNameInput" 
                                   class="form-control border-start-0 @error('full_name') is-invalid @enderror" 
                                   value="{{ old('full_name') }}" required 
                                   placeholder="Type name (e.g. Juan Dela Cruz)" 
                                   autocomplete="off">
                            <span class="input-group-text bg-white d-none" id="fullNameSpinner">
                                <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
                            </span>
                        </div>
                        @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- Interactive Autocomplete Suggestions List --}}
                        <div class="motorist-autocomplete-list shadow-lg border rounded-3 p-0 mt-1 w-100 position-absolute start-0 overflow-hidden d-none" 
                             id="motoristDropdown" 
                             style="z-index: 1060; max-height: 290px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1 !important; display: none;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Email Address (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" id="emailInput" 
                                   class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" 
                                   placeholder="juan@example.com">
                        </div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Contact Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-telephone-fill"></i></span>
                            <input type="text" name="contact_number" id="contactInput" 
                                   class="form-control border-start-0 @error('contact_number') is-invalid @enderror" 
                                   value="{{ old('contact_number') }}" required 
                                   placeholder="09171234567">
                        </div>
                        @error('contact_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Driver's License No. (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-card-text"></i></span>
                            <input type="text" name="license_number" id="licenseInput" 
                                   class="form-control border-start-0 @error('license_number') is-invalid @enderror" 
                                   value="{{ old('license_number') }}" 
                                   placeholder="A01-12-345678">
                        </div>
                        @error('license_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:.85rem;">Citation Ticket No. (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-ticket-perforated-fill"></i></span>
                            <input type="text" name="ticket_number" id="ticketInput" 
                                   class="form-control border-start-0 @error('ticket_number') is-invalid @enderror" 
                                   value="{{ old('ticket_number') }}" 
                                   placeholder="TVIRS-CEB-BAL-2026-000001">
                        </div>
                        @error('ticket_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                        <i class="bi bi-lock me-1 text-success"></i> Your request is transmitted securely and will be processed strictly by authorized Data Protection Officers (DPO).
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

<style>
.motorist-select-item {
    background: #ffffff;
    cursor: pointer;
    border-color: #f1f5f9 !important;
    text-align: left;
    width: 100%;
}
.motorist-select-item:hover, .motorist-select-item:focus {
    background-color: #f8fafc !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchUrl = "{{ route('privacy.dsr.search') }}";
    const nameInput = document.getElementById('fullNameInput');
    const emailInput = document.getElementById('emailInput');
    const contactInput = document.getElementById('contactInput');
    const licenseInput = document.getElementById('licenseInput');
    const ticketInput = document.getElementById('ticketInput');
    const dropdown = document.getElementById('motoristDropdown');
    const spinner = document.getElementById('fullNameSpinner');

    let debounceTimer = null;

    function showDropdown(htmlContent) {
        if (htmlContent) dropdown.innerHTML = htmlContent;
        dropdown.classList.remove('d-none');
        dropdown.style.display = 'block';
    }

    function hideDropdown() {
        dropdown.classList.add('d-none');
        dropdown.style.display = 'none';
    }

    function clearFormFields() {
        if (emailInput) emailInput.value = '';
        if (contactInput) contactInput.value = '';
        if (licenseInput) licenseInput.value = '';
        if (ticketInput) ticketInput.value = '';
    }

    nameInput.addEventListener('input', function () {
        const query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 1) {
            hideDropdown();
            clearFormFields();
            return;
        }

        if (spinner) spinner.classList.remove('d-none');

        debounceTimer = setTimeout(() => {
            fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (spinner) spinner.classList.add('d-none');

                if (!Array.isArray(data) || data.length === 0) {
                    showDropdown(`
                        <div class="px-3 py-2.5 text-muted small">
                            <i class="bi bi-info-circle me-1"></i> No matching motorist profiles found
                        </div>`);
                    return;
                }

                let html = '<div class="px-3 py-2 bg-light border-bottom text-muted fw-700 text-uppercase" style="font-size:0.68rem; letter-spacing: 0.05em;">Select Registered Motorist</div>';
                data.forEach(m => {
                    const licenseBadge = m.license_number ? `<span class="badge bg-secondary opacity-75 ms-1" style="font-size:0.7rem;"><i class="bi bi-card-text me-0.5"></i>${escapeHtml(m.license_number)}</span>` : '';
                    const emailTxt = m.email ? `<span class="text-muted ms-2" style="font-size:0.75rem;"><i class="bi bi-envelope me-1"></i>${escapeHtml(m.email)}</span>` : '';
                    const ticketTxt = m.ticket_number ? `<span class="text-muted ms-2" style="font-size:0.75rem;"><i class="bi bi-ticket-perforated me-1"></i>Ticket: ${escapeHtml(m.ticket_number)}</span>` : '';

                    html += `
                        <button type="button" class="list-group-item list-group-item-action p-2.5 border-bottom text-wrap motorist-select-item"
                                data-name="${escapeHtml(m.full_name)}"
                                data-email="${escapeHtml(m.email)}"
                                data-contact="${escapeHtml(m.contact_number)}"
                                data-license="${escapeHtml(m.license_number)}"
                                data-ticket="${escapeHtml(m.ticket_number)}"
                                style="transition: background-color 0.15s ease;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-700 text-dark" style="font-size:0.88rem;">
                                    <i class="bi bi-person-circle text-warning me-1.5"></i> ${escapeHtml(m.full_name)}
                                </span>
                                ${licenseBadge}
                            </div>
                            <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                ${emailTxt}
                                ${ticketTxt}
                            </div>
                        </button>`;
                });

                showDropdown(html);
            })
            .catch(err => {
                console.error('Error fetching motorists:', err);
                if (spinner) spinner.classList.add('d-none');
                hideDropdown();
            });
        }, 200);
    });

    // Auto-fill form fields when clicking a motorist item
    dropdown.addEventListener('click', function (e) {
        const btn = e.target.closest('.motorist-select-item');
        if (!btn) return;

        const name = btn.getAttribute('data-name');
        const email = btn.getAttribute('data-email');
        const contact = btn.getAttribute('data-contact');
        const license = btn.getAttribute('data-license');
        const ticket = btn.getAttribute('data-ticket');

        if (name) nameInput.value = name;
        if (emailInput) emailInput.value = email || '';
        if (contactInput && contact) contactInput.value = contact;
        if (licenseInput && license) licenseInput.value = license;
        if (ticketInput && ticket) ticketInput.value = ticket;

        hideDropdown();

        // Visual flash feedback on auto-filled inputs
        [nameInput, emailInput, contactInput, licenseInput, ticketInput].forEach(el => {
            if (el && el.value) {
                el.classList.add('bg-warning-subtle');
                setTimeout(() => el.classList.remove('bg-warning-subtle'), 1000);
            }
        });
    });

    // Close dropdown on outside click
    document.addEventListener('click', function (e) {
        if (!nameInput.contains(e.target) && !dropdown.contains(e.target)) {
            hideDropdown();
        }
    });

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }
});
</script>
@endsection
