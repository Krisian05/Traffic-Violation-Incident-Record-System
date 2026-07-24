@extends('layouts.app')

@section('title', 'Overall System Configuration')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Overall System Configuration</li>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #292524;"><i class="bi bi-sliders text-warning me-2"></i>Overall System Configuration</h4>
            <p class="text-muted small mb-0">Manage global application branding, fine & penalty policies, smart OCR configuration, financial rules, and maintenance settings.</p>
        </div>
        <div>
            <a href="{{ route('lgus.index') }}" class="btn btn-outline-primary btn-sm rounded-3">
                <i class="bi bi-building me-1"></i> Manage LGUs
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.system-config.update') }}">
        @csrf

        <div class="row g-4">
            <!-- Pillar 1: General Branding -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-3"><i class="bi bi-globe fs-5"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0">General System Branding</h6>
                                <span class="text-muted small">Application metadata & contact details</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label">System Application Title</label>
                            <input type="text" name="system_name" class="form-control @error('system_name') is-invalid @enderror" value="{{ old('system_name', \App\Models\SystemSetting::get('system_name')) }}" required>
                            @error('system_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">System Acronym / Short Name</label>
                            <input type="text" name="system_short_name" class="form-control @error('system_short_name') is-invalid @enderror" value="{{ old('system_short_name', \App\Models\SystemSetting::get('system_short_name')) }}" required>
                            @error('system_short_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Support Email</label>
                                <input type="email" name="support_email" class="form-control @error('support_email') is-invalid @enderror" value="{{ old('support_email', \App\Models\SystemSetting::get('support_email')) }}" required>
                                @error('support_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Support Phone</label>
                                <input type="text" name="support_phone" class="form-control @error('support_phone') is-invalid @enderror" value="{{ old('support_phone', \App\Models\SystemSetting::get('support_phone')) }}" required>
                                @error('support_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pillar 2: Violation & Fine Policies -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-subtle text-warning p-2 rounded-3"><i class="bi bi-cash-stack fs-5"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0">Violation & Fine Policies</h6>
                                <span class="text-muted small">Default grace period & late surcharge rules</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Grace Period (Days)</label>
                                <input type="number" name="default_grace_period_days" class="form-control @error('default_grace_period_days') is-invalid @enderror" value="{{ old('default_grace_period_days', \App\Models\SystemSetting::get('default_grace_period_days', 15)) }}" required min="1" max="365">
                                @error('default_grace_period_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Late Penalty Surcharge (%)</label>
                                <input type="number" step="0.01" name="late_penalty_rate" class="form-control @error('late_penalty_rate') is-invalid @enderror" value="{{ old('late_penalty_rate', \App\Models\SystemSetting::get('late_penalty_rate', 10.00)) }}" required min="0" max="100">
                                @error('late_penalty_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="auto_due_date_enabled" name="auto_due_date_enabled" value="1" {{ \App\Models\SystemSetting::get('auto_due_date_enabled', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="auto_due_date_enabled">Auto-calculate due date on citation issuance</label>
                            <div class="text-muted small">Automatically assigns due date based on LGU/system grace period.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pillar 3: Smart Scan & OCR API Configuration -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info-subtle text-info p-2 rounded-3"><i class="bi bi-cpu fs-5"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0">OCR & Document Smart Scanning</h6>
                                <span class="text-muted small">AI license recognition parameters</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="ocr_enabled" name="ocr_enabled" value="1" {{ \App\Models\SystemSetting::get('ocr_enabled', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="ocr_enabled">Enable Smart OCR Scanning</label>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Primary OCR Engine</label>
                                <select name="ocr_primary_engine" class="form-select @error('ocr_primary_engine') is-invalid @enderror">
                                    <option value="gemini" {{ \App\Models\SystemSetting::get('ocr_primary_engine') === 'gemini' ? 'selected' : '' }}>Google Gemini Vision</option>
                                    <option value="ocr_space" {{ \App\Models\SystemSetting::get('ocr_primary_engine') === 'ocr_space' ? 'selected' : '' }}>OCR.Space Engine</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Min Confidence Threshold (%)</label>
                                <input type="number" name="ocr_confidence_min" class="form-control @error('ocr_confidence_min') is-invalid @enderror" value="{{ old('ocr_confidence_min', \App\Models\SystemSetting::get('ocr_confidence_min', 75)) }}" min="1" max="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pillar 4: Digital Payments & Financial -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success p-2 rounded-3"><i class="bi bi-wallet2 fs-5"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0">Financial & Payment Config</h6>
                                <span class="text-muted small">Receipt prefix & collection settings</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="online_payments_enabled" name="online_payments_enabled" value="1" {{ \App\Models\SystemSetting::get('online_payments_enabled', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="online_payments_enabled">Enable Digital Online Payment Gateways (GCash / Maya)</label>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Official Receipt (OR) Number Prefix</label>
                            <input type="text" name="receipt_prefix" class="form-control @error('receipt_prefix') is-invalid @enderror" value="{{ old('receipt_prefix', \App\Models\SystemSetting::get('receipt_prefix', 'OR-')) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pillar 5: Security Policy & Timeout -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger-subtle text-danger p-2 rounded-3"><i class="bi bi-shield-lock fs-5"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0">Security Policy Defaults</h6>
                                <span class="text-muted small">2FA enforcement & account lockout rules</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="enforce_2fa_admin" name="enforce_2fa_admin" value="1" {{ \App\Models\SystemSetting::get('enforce_2fa_admin', false) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="enforce_2fa_admin">Enforce Mandatory 2FA for Administrators</label>
                        </div>
                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <label class="form-label">Session Timeout (Min)</label>
                                <input type="number" name="session_timeout_minutes" class="form-control" value="{{ old('session_timeout_minutes', \App\Models\SystemSetting::get('session_timeout_minutes', 120)) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Failed Logins</label>
                                <input type="number" name="max_login_attempts" class="form-control" value="{{ old('max_login_attempts', \App\Models\SystemSetting::get('max_login_attempts', 5)) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lockout Duration (Min)</label>
                                <input type="number" name="lockout_duration_minutes" class="form-control" value="{{ old('lockout_duration_minutes', \App\Models\SystemSetting::get('lockout_duration_minutes', 15)) }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pillar 6: System Maintenance Mode -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary-subtle text-secondary p-2 rounded-3"><i class="bi bi-tools fs-5"></i></span>
                            <div>
                                <h6 class="fw-bold mb-0">System Maintenance & Retention</h6>
                                <span class="text-muted small">Maintenance notice & backup retention</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" value="1" {{ \App\Models\SystemSetting::get('maintenance_mode', false) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-danger" for="maintenance_mode">Enable System Maintenance Mode</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Public Maintenance Banner Notice</label>
                            <input type="text" name="maintenance_message" class="form-control" value="{{ old('maintenance_message', \App\Models\SystemSetting::get('maintenance_message')) }}" required>
                        </div>
                        <div>
                            <label class="form-label">Snapshot Backup Retention (Days)</label>
                            <input type="number" name="backup_retention_days" class="form-control" value="{{ old('backup_retention_days', \App\Models\SystemSetting::get('backup_retention_days', 30)) }}" required min="1" max="3650">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-3">
                <i class="bi bi-save me-1"></i> Save Configuration Settings
            </button>
        </div>
    </form>
</div>
@endsection
