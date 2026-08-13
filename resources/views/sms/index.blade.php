@extends('layouts.app')
@section('title', 'SMS Gateway')
@section('topbar-sub', 'Monitor Outbound Citation SMS & Configure Android SIM / Semaphore Gateway')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#78716c;">Dashboard</a></li>
    <li class="breadcrumb-item active" style="color:#44403c;">SMS Gateway</li>
@endsection

@push('styles')
<style>
/* ── SMS GATEWAY UI STYLES ── */
.sms-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0369a1 100%);
    border-radius: 16px;
    color: #ffffff;
    padding: 1.75rem 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
}
.sms-hero-card::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.18) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.pulse-live-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(52, 211, 153, 0.4);
    color: #34d399;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .02em;
}
.pulse-dot {
    width: 8px;
    height: 8px;
    background-color: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-green 2s infinite;
}
@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.sms-metric-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    padding: 1.25rem 1.5rem;
    transition: transform .2s ease, box-shadow .2s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}
.sms-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.06);
}
.sms-metric-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.provider-card-option {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all .2s ease;
    background: #ffffff;
    position: relative;
}
.provider-card-option:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.provider-card-option.active {
    border-color: #0284c7;
    background: #f0f9ff;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.12);
}
.provider-card-option input[type="radio"] {
    position: absolute;
    top: 1rem;
    right: 1rem;
    accent-color: #0284c7;
    width: 18px;
    height: 18px;
}

.step-number-badge {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    font-weight: 700;
    font-size: .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sms-table-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    overflow: hidden;
}
.sms-table-header {
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}

.status-badge-sent {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
    font-weight: 600;
    padding: .25rem .65rem;
    border-radius: 999px;
    font-size: .73rem;
}
.status-badge-failed {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
    font-weight: 600;
    padding: .25rem .65rem;
    border-radius: 999px;
    font-size: .73rem;
}
.status-badge-pending {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    font-weight: 600;
    padding: .25rem .65rem;
    border-radius: 999px;
    font-size: .73rem;
}
</style>
@endpush

@section('content')

{{-- ── HERO HEADER ── --}}
<div class="sms-hero-card mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center"
                 style="width:52px;height:52px;background:rgba(255,255,255,0.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);">
                <i class="bi bi-chat-dots-fill text-white fs-3"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="mb-0 fw-700" style="letter-spacing:-.02em;">SMS Gateway Center</h4>
                    <span class="pulse-live-badge">
                        <span class="pulse-dot"></span> Gateway Active
                    </span>
                </div>
                <div style="font-size:.85rem;color:#94a3b8;">
                    Automated citation notifications via Android SIM Gateway (Free ₱0) or Semaphore API
                    @if($lgu)
                        &nbsp;·&nbsp; <strong class="text-white"><i class="bi bi-building me-1"></i>{{ $lgu->name }}</strong>
                    @endif
                </div>
            </div>
        </div>

        @if(Auth::user()->isSuperAdmin() && $lgus->count() > 1)
        <div style="min-width: 210px;">
            <label class="form-label text-white-50 fw-600 mb-1" style="font-size:.75rem;">Selected LGU</label>
            <select class="form-select form-select-sm border-0 shadow-none fw-600"
                    style="background:rgba(255,255,255,0.92);color:#0f172a;border-radius:8px;font-size:.84rem;"
                    onchange="window.location.href='?lgu_id='+this.value">
                @foreach($lgus as $item)
                    <option value="{{ $item->id }}" {{ $lgu?->id === $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>
</div>

{{-- ── STATS ROW ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="sms-metric-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted fw-600 mb-1" style="font-size:.75rem;letter-spacing:.03em;text-transform:uppercase;">Total SMS Dispatched</div>
                <div class="fw-800" style="font-size:1.65rem;color:#0f172a;letter-spacing:-.03em;">{{ number_format($totalSent) }}</div>
                <div class="mt-1" style="font-size:.74rem;color:#16a34a;font-weight:600;">
                    <i class="bi bi-check2-circle me-1"></i>Successfully delivered
                </div>
            </div>
            <div class="sms-metric-icon" style="background:#dcfce7;color:#16a34a;">
                <i class="bi bi-send-check-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="sms-metric-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted fw-600 mb-1" style="font-size:.75rem;letter-spacing:.03em;text-transform:uppercase;">Failed Dispatches</div>
                <div class="fw-800" style="font-size:1.65rem;color:#b91c1c;letter-spacing:-.03em;">{{ number_format($totalFailed) }}</div>
                <div class="mt-1" style="font-size:.74rem;color:#dc2626;font-weight:600;">
                    <i class="bi bi-exclamation-octagon me-1"></i>Needs resend or check SIM
                </div>
            </div>
            <div class="sms-metric-icon" style="background:#fee2e2;color:#dc2626;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="sms-metric-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted fw-600 mb-1" style="font-size:.75rem;letter-spacing:.03em;text-transform:uppercase;">Active Provider Mode</div>
                <div class="fw-700" style="font-size:1.05rem;color:#0284c7;">
                    @if(($lgu?->sms_provider ?? 'textbee') === 'textbee')
                        <i class="bi bi-phone-vibrate text-success me-1"></i>Android SIM Gateway
                    @elseif($lgu?->sms_provider === 'semaphore')
                        <i class="bi bi-cloud-check text-primary me-1"></i>Semaphore SMS API
                    @else
                        <i class="bi bi-terminal text-secondary me-1"></i>Local Log Gateway
                    @endif
                </div>
                <div class="mt-1" style="font-size:.74rem;color:#64748b;font-weight:500;">
                    @if(($lgu?->sms_provider ?? 'textbee') === 'textbee')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5">₱0 Monthly Fee</span>
                    @elseif($lgu?->sms_provider === 'semaphore')
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5">Prepaid API</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1.5 py-0.5">Development Mode</span>
                    @endif
                </div>
            </div>
            <div class="sms-metric-icon" style="background:#e0f2fe;color:#0284c7;">
                <i class="bi bi-broadcast"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ── LEFT COLUMN: GATEWAY CONFIGURATION & GUIDE ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;overflow:hidden;">
            <div class="card-header py-3 px-4" style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded d-flex align-items-center justify-content-center" style="width:30px;height:30px;background:#e0f2fe;">
                        <i class="bi bi-sliders text-primary" style="font-size:.9rem;"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-700" style="color:#0f172a;font-size:.92rem;">Gateway Configuration</h6>
                        <div style="font-size:.73rem;color:#64748b;">Configure SMS broadcasting provider for {{ $lgu?->name ?? 'LGU' }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('sms.settings') }}">
                    @csrf
                    <input type="hidden" name="lgu_id" value="{{ $lgu?->id }}">

                    <label class="form-label fw-600 mb-2" style="font-size:.82rem;color:#334155;">Select Gateway Provider</label>
                    <div class="d-flex flex-column gap-2 mb-4">
                        {{-- Option 1: Textbee --}}
                        <div class="provider-card-option {{ old('sms_provider', $lgu?->sms_provider ?? 'textbee') === 'textbee' ? 'active' : '' }}"
                             onclick="selectProvider('textbee')">
                            <input type="radio" name="sms_provider" value="textbee" id="prov_textbee"
                                   {{ old('sms_provider', $lgu?->sms_provider ?? 'textbee') === 'textbee' ? 'checked' : '' }}>
                            <div class="d-flex align-items-start gap-2.5 pe-4">
                                <i class="bi bi-phone-vibrate text-success fs-5"></i>
                                <div>
                                    <div class="fw-700" style="font-size:.85rem;color:#0f172a;">Android SIM Gateway <span class="badge bg-success-subtle text-success ms-1" style="font-size:.65rem;">Free ₱0</span></div>
                                    <div style="font-size:.73rem;color:#64748b;line-height:1.35;">Sends SMS via any connected Android phone with an unlimited text promo card (Textbee.dev).</div>
                                </div>
                            </div>
                        </div>

                        {{-- Option 2: Semaphore --}}
                        <div class="provider-card-option {{ old('sms_provider', $lgu?->sms_provider) === 'semaphore' ? 'active' : '' }}"
                             onclick="selectProvider('semaphore')">
                            <input type="radio" name="sms_provider" value="semaphore" id="prov_semaphore"
                                   {{ old('sms_provider', $lgu?->sms_provider) === 'semaphore' ? 'checked' : '' }}>
                            <div class="d-flex align-items-start gap-2.5 pe-4">
                                <i class="bi bi-cloud-check text-primary fs-5"></i>
                                <div>
                                    <div class="fw-700" style="font-size:.85rem;color:#0f172a;">Semaphore SMS API <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:.65rem;">Paid API</span></div>
                                    <div style="font-size:.73rem;color:#64748b;line-height:1.35;">Prepaid cloud SMS service for Philippines mobile networks (Semaphore.co).</div>
                                </div>
                            </div>
                        </div>

                        {{-- Option 3: Local Log --}}
                        <div class="provider-card-option {{ old('sms_provider', $lgu?->sms_provider) === 'local' ? 'active' : '' }}"
                             onclick="selectProvider('local')">
                            <input type="radio" name="sms_provider" value="local" id="prov_local"
                                   {{ old('sms_provider', $lgu?->sms_provider) === 'local' ? 'checked' : '' }}>
                            <div class="d-flex align-items-start gap-2.5 pe-4">
                                <i class="bi bi-terminal text-secondary fs-5"></i>
                                <div>
                                    <div class="fw-700" style="font-size:.85rem;color:#0f172a;">Local Test Gateway</div>
                                    <div style="font-size:.73rem;color:#64748b;line-height:1.35;">Logs outbound SMS to application database without sending actual cellular messages.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Textbee Credentials Group --}}
                    <div id="textbee_fields_group" style="{{ old('sms_provider', $lgu?->sms_provider ?? 'textbee') === 'textbee' ? '' : 'display:none;' }}">
                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.82rem;color:#334155;">Textbee API Key</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="bi bi-key-fill text-muted"></i></span>
                                <input type="password" name="textbee_api_key" id="textbee_key_input" class="form-control form-control-sm"
                                       placeholder="e.g. tb_key_..." value="{{ old('textbee_api_key', $lgu?->textbee_api_key) }}">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('textbee_key_input')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text" style="font-size:.71rem;">API Key generated from Textbee app or dashboard.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.82rem;color:#334155;">Textbee Device ID</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="bi bi-phone-fill text-muted"></i></span>
                                <input type="text" name="textbee_device_id" class="form-control form-control-sm"
                                       placeholder="e.g. 65a1b2c3..." value="{{ old('textbee_device_id', $lgu?->textbee_device_id) }}">
                            </div>
                            <div class="form-text" style="font-size:.71rem;">Device ID assigned to your Android gateway smartphone.</div>
                        </div>
                    </div>

                    {{-- Semaphore Credentials Group --}}
                    <div id="semaphore_fields_group" style="{{ old('sms_provider', $lgu?->sms_provider) === 'semaphore' ? '' : 'display:none;' }}">
                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.82rem;color:#334155;">Semaphore API Key</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="bi bi-key-fill text-muted"></i></span>
                                <input type="password" name="sms_api_key" id="semaphore_key_input" class="form-control form-control-sm"
                                       placeholder="Semaphore API Key" value="{{ old('sms_api_key', $lgu?->sms_api_key) }}">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('semaphore_key_input')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text" style="font-size:.71rem;">API Key from your Semaphore.co account.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.82rem;color:#334155;">SMS Sender Name</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="bi bi-tag-fill text-muted"></i></span>
                                <input type="text" name="sms_sender_name" class="form-control form-control-sm" maxlength="11"
                                       placeholder="e.g. TVIRS" value="{{ old('sms_sender_name', $lgu?->sms_sender_name ?? 'TVIRS') }}">
                            </div>
                            <div class="form-text" style="font-size:.71rem;">Registered Semaphore Sender ID (Max 11 alphanumeric chars).</div>
                        </div>
                    </div>

                    {{-- Auto-send Toggle --}}
                    <div class="p-3 rounded mb-4" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="sms_auto_send" id="auto_send_toggle" value="1"
                                   {{ old('sms_auto_send', $lgu?->sms_auto_send ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-600" for="auto_send_toggle" style="font-size:.82rem;color:#1e293b;">
                                Automatic SMS Dispatch
                            </label>
                            <div style="font-size:.72rem;color:#64748b;margin-top:2px;">
                                Send instant SMS citation notice to motorist upon ticket issuance by traffic enforcer.
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-600 py-2 d-inline-flex align-items-center justify-content-center gap-2"
                            style="background:linear-gradient(135deg,#0284c7,#0369a1);border:none;border-radius:10px;font-size:.88rem;box-shadow:0 4px 12px rgba(2,132,199,0.25);">
                        <i class="bi bi-check-lg"></i> Save Gateway Configuration
                    </button>
                </form>
            </div>
        </div>

        {{-- 📱 Android SIM Setup Guide --}}
        <div class="card border-0 shadow-sm p-4" style="background:linear-gradient(135deg,#f0fdf4 0%,#ecfdf5 100%);border:1px solid #a7f3d0!important;border-radius:14px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-phone-vibrate text-success fs-5"></i>
                    <h6 class="fw-700 m-0" style="color:#065f46;font-size:.9rem;">
                        Free Android SIM Gateway Guide
                    </h6>
                </div>
                <span class="badge bg-success text-white px-2 py-1" style="font-size:.68rem;">₱0 Monthly Cost</span>
            </div>

            <p class="mb-3" style="font-size:.78rem;color:#047857;line-height:1.45;">
                Turn any Android smartphone with an unlimited text SIM into your LGU's dedicated 24/7 SMS citation dispatch server.
            </p>

            <div class="d-flex flex-column gap-3 mb-3">
                <div class="d-flex align-items-start gap-2.5">
                    <span class="step-number-badge">1</span>
                    <div style="font-size:.77rem;color:#064e3b;line-height:1.4;">
                        <strong>Prepare Smartphone:</strong> Insert a SIM card loaded with an <em>Unlimited SMS promo</em> (Globe/Smart/DITO). Connect phone to Wi-Fi or data.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2.5">
                    <span class="step-number-badge">2</span>
                    <div style="font-size:.77rem;color:#064e3b;line-height:1.4;">
                        <strong>Install Textbee:</strong> Open Chrome on Android, visit <a href="https://textbee.dev" target="_blank" class="fw-700 text-decoration-underline" style="color:#047857;">textbee.dev</a>, download APK & tap <em>Install</em>.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2.5">
                    <span class="step-number-badge">3</span>
                    <div style="font-size:.77rem;color:#064e3b;line-height:1.4;">
                        <strong>Register Device:</strong> Open Textbee app ➔ log in ➔ tap <strong>Register Device</strong> to get your <strong>API Key</strong> and <strong>Device ID</strong>.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2.5">
                    <span class="step-number-badge">4</span>
                    <div style="font-size:.77rem;color:#064e3b;line-height:1.4;">
                        <strong>Save in TVIRS:</strong> Paste keys into the form above, select <em>Android SIM Gateway</em>, and click <strong>Save</strong>.
                    </div>
                </div>
            </div>

            <div class="p-3 rounded border" style="background:#dcfce7;border-color:#6ee7b7!important;font-size:.75rem;color:#064e3b;">
                <div class="fw-700 mb-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>24/7 Operations Tip:</div>
                <div style="line-height:1.4;">
                    Keep phone connected to charger and turn off <em>Battery Optimization / App Sleep</em> for Textbee in Android settings.
                </div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT COLUMN: SMS DISPATCH LOGS ── --}}
    <div class="col-lg-7">
        <div class="sms-table-card">
            <div class="sms-table-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded d-flex align-items-center justify-content-center" style="width:30px;height:30px;background:#e2e8f0;">
                        <i class="bi bi-journal-text text-slate" style="font-size:.9rem;color:#475569;"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-700" style="color:#0f172a;font-size:.92rem;">Outbound SMS Activity Logs</h6>
                        <div style="font-size:.72rem;color:#64748b;">Real-time citation SMS delivery history & status</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:.83rem;">
                    <thead style="background:#f8fafc;color:#475569;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.02em;">
                        <tr>
                            <th class="ps-3" style="padding-top:10px;padding-bottom:10px;">Ticket #</th>
                            <th>Violator & Contact</th>
                            <th class="text-center">Status</th>
                            <th>Dispatched At</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($smsLogs as $violation)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-3 fw-700">
                                <a href="{{ route('violations.show', $violation) }}" class="text-decoration-none" style="color:#0284c7;">
                                    {{ $violation->ticket_number ?? 'CIT-'.$violation->id }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-600" style="color:#0f172a;">{{ $violation->violator?->full_name ?? '—' }}</div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    <i class="bi bi-telephone me-1" style="font-size:.7rem;"></i>{{ $violation->violator?->contact_number ?? 'No Phone Number' }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($violation->sms_status === 'sent')
                                    <span class="status-badge-sent">
                                        <i class="bi bi-check-circle-fill me-1"></i>Sent
                                    </span>
                                @elseif($violation->sms_status === 'failed')
                                    <span class="status-badge-failed" title="{{ $violation->sms_error }}">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Failed
                                    </span>
                                @else
                                    <span class="status-badge-pending">
                                        <i class="bi bi-hourglass-split me-1"></i>Pending
                                    </span>
                                @endif
                            </td>
                            <td style="color:#475569;font-size:.79rem;">
                                @if($violation->sms_sent_at)
                                    <i class="bi bi-clock me-1 text-muted"></i>{{ $violation->sms_sent_at->format('M d, Y g:i A') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <form method="POST" action="{{ route('violations.send-sms', $violation) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2.5 d-inline-flex align-items-center gap-1"
                                            style="font-size:.75rem;border-radius:6px;font-weight:600;" title="Resend SMS">
                                        <i class="bi bi-send-fill"></i> Resend
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                     style="width:48px;height:48px;background:#f1f5f9;">
                                    <i class="bi bi-chat-left-dots text-secondary fs-4"></i>
                                </div>
                                <div class="fw-600" style="color:#334155;font-size:.9rem;">No Outbound SMS Logs</div>
                                <div style="font-size:.78rem;color:#94a3b8;">SMS citation logs will automatically appear here upon ticket issuance.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($smsLogs->hasPages())
            <div class="p-3 border-top bg-light">
                {{ $smsLogs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectProvider(provider) {
    document.querySelectorAll('.provider-card-option').forEach(el => el.classList.remove('active'));
    const radio = document.getElementById('prov_' + provider);
    if (radio) {
        radio.checked = true;
        radio.closest('.provider-card-option').classList.add('active');
    }

    const textbeeGroup = document.getElementById('textbee_fields_group');
    const semaphoreGroup = document.getElementById('semaphore_fields_group');
    if (provider === 'textbee') {
        textbeeGroup.style.display = '';
        semaphoreGroup.style.display = 'none';
    } else if (provider === 'semaphore') {
        textbeeGroup.style.display = 'none';
        semaphoreGroup.style.display = '';
    } else {
        textbeeGroup.style.display = 'none';
        semaphoreGroup.style.display = 'none';
    }
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>
@endpush
@endsection
