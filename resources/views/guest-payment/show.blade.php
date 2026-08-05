<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pay Citation {{ $violation->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding-bottom: 2.5rem; }
        .card { border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .status-pill { border-radius: 20px; font-weight: 700; padding: .5rem 1rem; font-size: .85rem; }
        .nav-pills .nav-link { border-radius: 12px; font-weight: 700; font-size: .88rem; color: #475569; padding: .65rem 1rem; }
        .nav-pills .nav-link.active { background-color: #0284c7; color: #fff; }
        .btn-paymongo { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; border: none; font-weight: 700; border-radius: 12px; transition: transform 0.15s ease; }
        .btn-paymongo:hover { background: linear-gradient(135deg, #0369a1 0%, #075985 100%); color: #fff; transform: translateY(-1px); }
        .payment-method-badge { background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; padding: 4px 10px; font-size: .78rem; font-weight: 600; color: #334155; display: inline-flex; align-items: center; gap: 4px; }
    </style>
</head>
<body>
<div class="container py-4" style="max-width: 500px;">

    <div class="text-center mb-3">
        <div class="fw-800 text-uppercase text-muted" style="font-size: .75rem; letter-spacing: .05em;">Traffic Citation Payment</div>
        <h5 class="fw-800 mb-0">{{ $violation->ticket_number }}</h5>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3">{{ session('error') }}</div>
    @endif

    @php
        $isSettled = $violation->isSettled();
        $isOverdue = $violation->isOverdue();
        $displayStatus = $isSettled ? 'settled' : ($isOverdue ? 'overdue' : $violation->status);
        $statusStyles = [
            'overdue' => 'background-color:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;',
            'pending' => 'background-color:#fffbeb;color:#b45309;border:1px solid #fde68a;',
            'partial' => 'background-color:#fff7ed;color:#c2410c;border:1px solid #fdba74;',
            'settled' => 'background-color:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;',
        ];
    @endphp

    <div class="card shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="text-muted" style="font-size: .8rem;">Status</span>
                <span class="status-pill" style="{{ $statusStyles[$displayStatus] ?? '' }}">{{ ucfirst($displayStatus) }}</span>
            </div>

            <table class="table table-borderless align-middle mb-0" style="font-size: .9rem;">
                <tbody>
                    <tr>
                        <td class="text-muted py-1" style="width: 42%;">Violator</td>
                        <td class="fw-700 py-1">{{ $violation->violator?->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Plate Number</td>
                        <td class="fw-600 py-1" style="font-family: ui-monospace, monospace;">{{ $violation->vehicle?->plate_number ?? $violation->vehicle_plate }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Violation</td>
                        <td class="fw-600 py-1">{{ $violation->violationType?->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Due Date</td>
                        <td class="fw-600 py-1">{{ $violation->due_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($isSettled)
        @php $payment = $violation->latestPayment; @endphp
        <div class="card shadow-sm mb-3" style="background-color: #f0fdf4; border-color: #bbf7d0;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-patch-check-fill text-success fs-3"></i>
                    <h6 class="fw-800 text-success mb-0">Citation Settled</h6>
                </div>
                <table class="table table-borderless align-middle mb-0" style="font-size: .88rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted py-1" style="width: 42%;">OR Number</td>
                            <td class="fw-700 py-1" style="font-family: ui-monospace, monospace;">{{ $violation->or_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Amount Paid</td>
                            <td class="fw-700 py-1">₱{{ number_format($payment?->amount_paid ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Payment Method</td>
                            <td class="fw-600 py-1">{{ strtoupper($violation->payment_method ?: 'ONLINE') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Date Paid</td>
                            <td class="fw-600 py-1">{{ $violation->settled_at?->format('M d, Y g:i A') ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($violation->pendingPaymentClaim)
        <div class="card shadow-sm mb-3" style="background-color: #fffbeb; border-color: #fde68a;">
            <div class="card-body p-4 text-center">
                <i class="bi bi-hourglass-split text-warning fs-2 mb-2 d-block"></i>
                <h6 class="fw-800 mb-1">Awaiting Verification</h6>
                <p class="text-muted mb-0" style="font-size: .85rem;">Your payment claim (Ref: {{ $violation->pendingPaymentClaim->claimed_reference }}) is being verified by our staff. Check back soon — this page will show "Settled" once confirmed.</p>
            </div>
        </div>
    @else
        <div class="p-3 mb-3" style="background-color: #fef2f2; border-radius: 12px; border: 1px solid #fca5a5;">
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted fw-600" style="font-size: .75rem;">TOTAL AMOUNT DUE</span>
            </div>
            <strong style="font-size: 1.8rem; color: #b91c1c;">₱{{ number_format($violation->balanceRemaining(), 2) }}</strong>
        </div>

        <ul class="nav nav-pills nav-justified mb-3 p-1 bg-white rounded-3 border" id="paymentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="paymongo-tab" data-bs-toggle="pill" data-bs-target="#paymongo-panel" type="button" role="tab">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Pay Online (Instant)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="manual-tab" data-bs-toggle="pill" data-bs-target="#manual-panel" type="button" role="tab">
                    <i class="bi bi-qr-code me-1"></i> Static QR / Claim
                </button>
            </li>
        </ul>

        <div class="tab-content" id="paymentTabsContent">
            <!-- TAB 1: PAYMONGO INSTANT ONLINE CHECKOUT -->
            <div class="tab-pane fade show active" id="paymongo-panel" role="tabpanel">
                <div class="card shadow-sm mb-3">
                    <div class="card-body p-4 text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                            <span class="payment-method-badge"><i class="bi bi-phone"></i> GCash</span>
                            <span class="payment-method-badge"><i class="bi bi-qr-code-scan"></i> QR Ph</span>
                            <span class="payment-method-badge"><i class="bi bi-wallet2"></i> Maya</span>
                            <span class="payment-method-badge"><i class="bi bi-credit-card"></i> Cards</span>
                        </div>
                        <h6 class="fw-800 text-dark mb-2">Instant Online Settlement via PayMongo</h6>
                        <p class="text-muted mb-4" style="font-size: .85rem;">
                            Pay securely using your preferred e-wallet or bank card. Your citation will be automatically marked as <strong>Settled</strong> within seconds upon completion.
                        </p>

                        <form action="{{ route('guest-payment.paymongo-checkout', $violation->public_payment_token) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-paymongo w-100 py-3 fs-6">
                                <i class="bi bi-shield-lock-fill me-2"></i> Proceed to PayMongo Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 2: MANUAL QR CODE & REFERENCE CLAIM -->
            <div class="tab-pane fade" id="manual-panel" role="tabpanel">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h6 class="fw-800 mb-0"><i class="bi bi-phone-fill me-1" style="color:#0069f5;"></i> Pay via Static Municipal QR</h6>
                    </div>
                    <div class="card-body p-4">
                        @if($lgu?->gcash_qr_path)
                            <div class="text-center mb-3">
                                <img src="{{ uploaded_file_url($lgu->gcash_qr_path) }}" alt="GCash QR" style="width: 220px; height: 220px; object-fit: contain; border: 1px solid #e7e2db; border-radius: 12px; padding: 8px;">
                            </div>
                        @endif
                        <ol class="mb-4" style="font-size: .85rem; padding-left: 1.2rem;">
                            <li class="mb-2">Open GCash/Maya app and scan the QR above.</li>
                            <li class="mb-2">Send exactly <strong>₱{{ number_format($violation->balanceRemaining(), 2) }}</strong>.</li>
                            <li class="mb-2">Write your ticket number in payment note: <strong>{{ $violation->ticket_number }}</strong>.</li>
                            <li>Submit your transaction reference number below for staff verification.</li>
                        </ol>

                        <form action="{{ route('guest-payment.claim', $violation->public_payment_token) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-700" style="font-size: .82rem;">GCash Reference Number <span class="text-danger">*</span></label>
                                <input type="text" name="claimed_reference" class="form-control" required maxlength="100" placeholder="e.g., 1234567890123">
                                @error('claimed_reference')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-700" style="font-size: .82rem;">Amount Sent <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="claimed_amount" class="form-control" step="0.01" min="0.01" max="{{ $violation->balanceRemaining() }}" value="{{ old('claimed_amount', number_format($violation->balanceRemaining(), 2, '.', '')) }}" required>
                                </div>
                                @error('claimed_amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-700" style="font-size: .82rem;">Your Name <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                <input type="text" name="claimant_name" class="form-control" maxlength="150" value="{{ old('claimant_name') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-700" style="font-size: .82rem;">Contact Number <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                <input type="text" name="claimant_contact" class="form-control" maxlength="30" value="{{ old('claimant_contact') }}">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-700" style="font-size: .82rem;">Screenshot <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                <input type="file" name="screenshot" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-success fw-700 w-100 py-2">
                                <i class="bi bi-send-check me-1"></i> Submit Manual Claim
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @endif

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
