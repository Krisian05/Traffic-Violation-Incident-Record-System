@extends('layouts.app')
@section('title', 'SMS Gateway')
@section('topbar-sub', 'Manage Android SIM Gateway (Free) & Semaphore SMS API settings')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#78716c;">Dashboard</a></li>
    <li class="breadcrumb-item active" style="color:#44403c;">SMS Gateway</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center"
             style="width:42px;height:42px;background:linear-gradient(135deg,#0284c7,#0369a1);flex-shrink:0;">
            <i class="bi bi-chat-left-text-fill text-white" style="font-size:1.1rem;"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-700" style="color:#1c1917;">SMS Gateway Center</h5>
            <div style="font-size:.8rem;color:#78716c;">
                Monitor outbound citation SMS messages & configure Android SIM Gateway or Semaphore API
            </div>
        </div>
    </div>
</div>

{{-- ── STATS ROW ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left:4px solid #16a34a!important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted fw-600 mb-1" style="font-size:.78rem;text-transform:uppercase;">SMS Dispatched</div>
                    <div class="fw-700" style="font-size:1.5rem;color:#15803d;">{{ number_format($totalSent) }}</div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#dcfce7;">
                    <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:1.2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left:4px solid #dc2626!important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted fw-600 mb-1" style="font-size:.78rem;text-transform:uppercase;">Failed Dispatches</div>
                    <div class="fw-700" style="font-size:1.5rem;color:#b91c1c;">{{ number_format($totalFailed) }}</div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#fee2e2;">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;font-size:1.2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left:4px solid #0284c7!important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted fw-600 mb-1" style="font-size:.78rem;text-transform:uppercase;">Gateway Mode</div>
                    <div class="fw-700" style="font-size:1.05rem;color:#0369a1;">
                        @if(($lgu?->sms_provider ?? 'textbee') === 'textbee')
                            <i class="bi bi-phone-vibrate me-1 text-success"></i>Android SIM Gateway (Free ₱0)
                        @elseif($lgu?->sms_provider === 'semaphore')
                            <i class="bi bi-cloud-check me-1 text-primary"></i>Semaphore API
                        @else
                            <i class="bi bi-terminal me-1 text-secondary"></i>Local Log Gateway
                        @endif
                    </div>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#e0f2fe;">
                    <i class="bi bi-cpu-fill" style="color:#0284c7;font-size:1.2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ── LEFT COLUMN: SMS CONFIGURATION ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3" style="background:linear-gradient(135deg,#eff6ff 0%,#fff 100%);">
                <span class="rounded d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#dbeafe;">
                    <i class="bi bi-sliders text-primary" style="font-size:.85rem;"></i>
                </span>
                <span class="fw-600" style="font-size:.925rem;color:#1e40af;">SMS Gateway Settings</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('sms.settings') }}">
                    @csrf
                    @if(Auth::user()->isSuperAdmin() && $lgus->count() > 1)
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.82rem;">Select LGU</label>
                        <select name="lgu_id" class="form-select form-select-sm" onchange="window.location.href='?lgu_id='+this.value">
                            @foreach($lgus as $item)
                                <option value="{{ $item->id }}" {{ $lgu?->id === $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="lgu_id" value="{{ $lgu?->id }}">
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.82rem;">Gateway Provider</label>
                        <select name="sms_provider" id="sms_provider_select" class="form-select form-select-sm" onchange="toggleProviderFields(this.value)">
                            <option value="textbee" {{ old('sms_provider', $lgu?->sms_provider ?? 'textbee') === 'textbee' ? 'selected' : '' }}>📱 Android SIM Gateway (Textbee.dev — Free ₱0)</option>
                            <option value="semaphore" {{ old('sms_provider', $lgu?->sms_provider) === 'semaphore' ? 'selected' : '' }}>☁️ Semaphore SMS API (Prepaid Paid Credits)</option>
                            <option value="local" {{ old('sms_provider', $lgu?->sms_provider) === 'local' ? 'selected' : '' }}>💻 Local Test Log Gateway (No SMS Sent)</option>
                        </select>
                    </div>

                    {{-- Textbee Fields --}}
                    <div id="textbee_fields_group" style="{{ old('sms_provider', $lgu?->sms_provider ?? 'textbee') === 'textbee' ? '' : 'display:none;' }}">
                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.82rem;">Textbee API Key</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-key-fill text-muted"></i></span>
                                <input type="password" name="textbee_api_key" class="form-control" placeholder="e.g. tb_key_..." value="{{ old('textbee_api_key', $lgu?->textbee_api_key) }}">
                            </div>
                            <div class="form-text" style="font-size:.72rem;">Free API Key from Textbee app on Android gateway phone.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.82rem;">Textbee Device ID</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-phone-fill text-muted"></i></span>
                                <input type="text" name="textbee_device_id" class="form-control" placeholder="e.g. 65a1b2c3..." value="{{ old('textbee_device_id', $lgu?->textbee_device_id) }}">
                            </div>
                            <div class="form-text" style="font-size:.72rem;">Device ID generated inside Textbee Android app.</div>
                        </div>
                    </div>

                    {{-- Semaphore Fields --}}
                    <div id="semaphore_fields_group" style="{{ old('sms_provider', $lgu?->sms_provider) === 'semaphore' ? '' : 'display:none;' }}">
                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.82rem;">Semaphore API Key</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-key-fill text-muted"></i></span>
                                <input type="password" name="sms_api_key" class="form-control" placeholder="Semaphore API Key" value="{{ old('sms_api_key', $lgu?->sms_api_key) }}">
                            </div>
                            <div class="form-text" style="font-size:.72rem;">Enter API key from Semaphore.co to enable live SMS delivery.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600" style="font-size:.82rem;">SMS Sender Name</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-tag-fill text-muted"></i></span>
                                <input type="text" name="sms_sender_name" class="form-control" maxlength="11" placeholder="e.g. TVIRS" value="{{ old('sms_sender_name', $lgu?->sms_sender_name ?? 'TVIRS') }}">
                            </div>
                            <div class="form-text" style="font-size:.72rem;">Max 11 alphanumeric characters registered with Semaphore.</div>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="sms_auto_send" id="auto_send_toggle" value="1" {{ old('sms_auto_send', $lgu?->sms_auto_send ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="auto_send_toggle" style="font-size:.82rem;color:#374151;">
                            Auto-dispatch SMS citation when enforcer issues ticket
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-600 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-check-lg"></i> Save Gateway Configuration
                    </button>
                </form>
            </div>
        </div>

        @php
            $currentProvider = old('sms_provider', $lgu?->sms_provider ?? 'textbee');
        @endphp

        {{-- 📱 Android SIM Setup Guide Card --}}
        <div id="textbee_guide_card" class="card border-0 shadow-sm p-3 mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0!important;border-radius:12px;{{ $currentProvider === 'textbee' ? '' : 'display:none;' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-700 m-0" style="color:#15803d;font-size:.88rem;">
                    <i class="bi bi-phone-vibrate me-2"></i>How to Setup Free Android SIM Gateway (Textbee.dev)
                </h6>
                <span class="badge bg-success text-white" style="font-size:.68rem;">₱0 Monthly Cost</span>
            </div>
            <p class="mb-3" style="font-size:.76rem;color:#166534;line-height:1.4;">
                Turn any spare Android smartphone into your LGU's dedicated SMS broadcasting hub to automatically text citation notices to violators.
            </p>
            
            <div class="d-flex flex-column gap-2 mb-3" style="font-size:.76rem;color:#14532d;">
                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">1</span>
                    <div>
                        <strong>Prepare Dedicated Android Phone:</strong> Insert an active SIM card loaded with an <em>Unlimited SMS promo to all networks</em> (Globe/Smart/DITO). Connect phone to Wi-Fi or mobile data.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">2</span>
                    <div>
                        <strong>Download Textbee App:</strong> Open Chrome on the phone, visit <a href="https://textbee.dev" target="_blank" class="fw-700 text-decoration-underline" style="color:#15803d;">textbee.dev</a>, download the APK, and tap <em>Install</em> (allow "Install Unknown Apps" if prompted).
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">3</span>
                    <div>
                        <strong>Register Device & Copy Keys:</strong> Open Textbee app ➔ log in or register ➔ tap <strong>Register Device</strong>. Grant SMS/Phone permissions to generate your unique <strong>API Key</strong> and <strong>Device ID</strong>.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">4</span>
                    <div>
                        <strong>Save Credentials in TVIRS:</strong> Paste the <strong>API Key</strong> and <strong>Device ID</strong> into the form above, set Provider to <em>Android SIM Gateway</em>, and click <strong>Save Gateway Configuration</strong>.
                    </div>
                </div>
            </div>

            <div class="p-2.5 rounded border" style="background:#dcfce7;border-color:#86efac!important;font-size:.74rem;color:#14532d;">
                <div class="fw-700 mb-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Crucial 24/7 Operation Tips:</div>
                <ul class="mb-0 ps-3" style="line-height:1.45;">
                    <li>Keep the gateway phone connected to a charger and active Wi-Fi/data.</li>
                    <li>Turn off <em>Battery Saver / App Optimization</em> for Textbee in Android Settings so background dispatching is never paused.</li>
                </ul>
            </div>
        </div>

        {{-- ☁️ Semaphore API Setup Guide Card --}}
        <div id="semaphore_guide_card" class="card border-0 shadow-sm p-3 mb-4" style="background:#eff6ff;border:1px solid #bfdbfe!important;border-radius:12px;{{ $currentProvider === 'semaphore' ? '' : 'display:none;' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-700 m-0" style="color:#1d4ed8;font-size:.88rem;">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>How to Setup Semaphore SMS API
                </h6>
                <span class="badge bg-primary text-white" style="font-size:.68rem;">Direct Telco Cloud</span>
            </div>
            <p class="mb-3" style="font-size:.76rem;color:#1e40af;line-height:1.4;">
                Send high-speed, carrier-grade SMS notifications directly through Philippine cellular networks (Globe, Smart, DITO) without needing a dedicated phone.
            </p>
            
            <div class="d-flex flex-column gap-2 mb-3" style="font-size:.76rem;color:#1e3a8a;">
                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">1</span>
                    <div>
                        <strong>Create Semaphore Account:</strong> Sign up at <a href="https://semaphore.co" target="_blank" class="fw-700 text-decoration-underline" style="color:#1d4ed8;">semaphore.co</a> and verify your email and Philippine mobile number.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">2</span>
                    <div>
                        <strong>Purchase SMS Credits:</strong> Top up prepaid credits on Semaphore (approx. ₱0.50 per SMS) via GCash, credit card, or bank transfer.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">3</span>
                    <div>
                        <strong>Copy API Key:</strong> Navigate to <em>Account Settings ➔ API Key</em> inside your Semaphore dashboard and copy your secret key.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">4</span>
                    <div>
                        <strong>Configure in TVIRS:</strong> Paste your <strong>Semaphore API Key</strong> and optional registered <strong>Sender Name</strong> (e.g. <code>TVIRS</code>) into the form above, then click <strong>Save Gateway Configuration</strong>.
                    </div>
                </div>
            </div>

            <div class="p-2.5 rounded border" style="background:#dbeafe;border-color:#93c5fd!important;font-size:.74rem;color:#1e3a8a;">
                <div class="fw-700 mb-1"><i class="bi bi-info-circle-fill text-primary me-1"></i>Sender ID Information:</div>
                <div style="line-height:1.45;">
                    Custom sender names (e.g. <code>BALAMBAN-LGU</code>) require prior NTC registration through Semaphore. Leave blank or use <code>TVIRS</code> for instant default delivery.
                </div>
            </div>
        </div>

        {{-- 💻 Local Test Log Gateway Guide Card --}}
        <div id="local_guide_card" class="card border-0 shadow-sm p-3 mb-4" style="background:#f8fafc;border:1px solid #cbd5e1!important;border-radius:12px;{{ $currentProvider === 'local' ? '' : 'display:none;' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-700 m-0" style="color:#334155;font-size:.88rem;">
                    <i class="bi bi-terminal-fill me-2"></i>How Local Test Log Gateway Works
                </h6>
                <span class="badge bg-secondary text-white" style="font-size:.68rem;">Testing / Demo Mode</span>
            </div>
            <p class="mb-3" style="font-size:.76rem;color:#475569;line-height:1.4;">
                Simulate outbound SMS dispatching safely without connecting to external carrier APIs or sending real text messages to motorists.
            </p>
            
            <div class="d-flex flex-column gap-2 mb-3" style="font-size:.76rem;color:#334155;">
                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">1</span>
                    <div>
                        <strong>No API Keys Needed:</strong> Simply select <em>Local Test Log Gateway</em> in the dropdown above and click <strong>Save Gateway Configuration</strong>.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">2</span>
                    <div>
                        <strong>Automatic Simulation:</strong> When enforcers issue tickets, send 72h reminders, or cashiers confirm GCash payments, TVIRS will mark citations as <em>SMS Sent</em>.
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <span class="badge rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:20px;height:20px;font-size:.68rem;">3</span>
                    <div>
                        <strong>Inspect Logged Messages:</strong> Open your server terminal or view <code>storage/logs/laravel.log</code> to inspect formatted SMS text, recipient phone numbers, and timestamps.
                    </div>
                </div>
            </div>

            <div class="p-2.5 rounded border" style="background:#f1f5f9;border-color:#cbd5e1!important;font-size:.74rem;color:#334155;">
                <div class="fw-700 mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i>Ideal For:</div>
                <div style="line-height:1.45;">
                    Capstone project defense, system demonstrations, unit testing, and offline staging servers where no real phone or internet gateway is attached.
                </div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT COLUMN: SMS DISPATCH LOGS ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#f3f4f6;">
                        <i class="bi bi-journal-text text-secondary" style="font-size:.85rem;"></i>
                    </span>
                    <span class="fw-600" style="font-size:.925rem;color:#292524;">Outbound SMS Activity Logs</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size:.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket #</th>
                                <th>Driver & Contact</th>
                                <th>Status</th>
                                <th>Dispatched At</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($smsLogs as $violation)
                            <tr>
                                <td class="fw-700">
                                    <a href="{{ route('violations.show', $violation) }}" class="text-decoration-none" style="color:#0284c7;">
                                        {{ $violation->ticket_number ?? 'CIT-'.$violation->id }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-600" style="color:#1c1917;">{{ $violation->violator?->full_name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        <i class="bi bi-telephone me-1"></i>{{ $violation->violator?->contact_number ?? 'No Phone' }}
                                    </div>
                                </td>
                                <td>
                                    @if($violation->sms_status === 'sent')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size:.72rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i>Sent
                                        </span>
                                    @elseif($violation->sms_status === 'failed')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1" style="font-size:.72rem;" title="{{ $violation->sms_error }}">
                                            <i class="bi bi-x-circle-fill me-1"></i>Failed
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1" style="font-size:.72rem;">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($violation->sms_sent_at)
                                        {{ $violation->sms_sent_at->format('M d, Y g:i A') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('violations.send-sms', $violation) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.75rem;" title="Resend SMS">
                                            <i class="bi bi-send-fill me-1"></i>Resend
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-chat-left-dots fs-3 d-block mb-1"></i>
                                    No SMS dispatches recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($smsLogs->hasPages())
                <div class="p-3 border-top">
                    {{ $smsLogs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleProviderFields(provider) {
    const textbeeGroup = document.getElementById('textbee_fields_group');
    const semaphoreGroup = document.getElementById('semaphore_fields_group');
    const textbeeGuide = document.getElementById('textbee_guide_card');
    const semaphoreGuide = document.getElementById('semaphore_guide_card');
    const localGuide = document.getElementById('local_guide_card');

    if (provider === 'textbee') {
        textbeeGroup.style.display = '';
        semaphoreGroup.style.display = 'none';
        if (textbeeGuide) textbeeGuide.style.display = '';
        if (semaphoreGuide) semaphoreGuide.style.display = 'none';
        if (localGuide) localGuide.style.display = 'none';
    } else if (provider === 'semaphore') {
        textbeeGroup.style.display = 'none';
        semaphoreGroup.style.display = '';
        if (textbeeGuide) textbeeGuide.style.display = 'none';
        if (semaphoreGuide) semaphoreGuide.style.display = '';
        if (localGuide) localGuide.style.display = 'none';
    } else {
        textbeeGroup.style.display = 'none';
        semaphoreGroup.style.display = 'none';
        if (textbeeGuide) textbeeGuide.style.display = 'none';
        if (semaphoreGuide) semaphoreGuide.style.display = 'none';
        if (localGuide) localGuide.style.display = '';
    }
}
</script>
@endpush
@endsection
