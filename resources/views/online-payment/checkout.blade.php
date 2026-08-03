<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pay Ticket {{ $violation->ticket_number }} - TVIRS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --blue: #1d4ed8;
            --blue-dark: #1e40af;
            --bg-slate: #0f172a;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e3a8a 100%);
            min-height: 100vh;
            color: #ffffff;
            padding-bottom: 2rem;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }
        .method-card {
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 18px;
            padding: 1rem;
            cursor: pointer;
            transition: all .2s ease;
        }
        .method-card:hover {
            border-color: #93c5fd;
            background: rgba(255, 255, 255, 0.15);
        }
        .method-card.active {
            border-color: #60a5fa;
            background: rgba(59, 130, 246, 0.25);
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.35);
        }
        .form-control-custom {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #ffffff;
            border-radius: 12px;
            padding: .75rem 1rem;
            font-weight: 500;
        }
        .form-control-custom:focus {
            background: rgba(255, 255, 255, 0.28);
            border-color: #93c5fd;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.35);
        }
        .form-control-custom::placeholder {
            color: #cbd5e1;
            opacity: 0.95;
        }
        .btn-confirm {
            background: linear-gradient(135deg, #005ce6, #0041a8);
            border: none;
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 800;
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 6px 20px rgba(0, 92, 230, 0.45);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0, 92, 230, 0.6);
            color: #ffffff;
        }
        /* Gateway Portal Modals */
        .gateway-modal .modal-content {
            border-radius: 28px;
            border: none;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .gcash-header {
            background: #005ce6;
            color: #fff;
            padding: 1.5rem;
            text-align: center;
        }
        .maya-header {
            background: #10b981;
            color: #fff;
            padding: 1.5rem;
            text-align: center;
        }
        .card-header-modal {
            background: #1e1b4b;
            color: #fff;
            padding: 1.5rem;
            text-align: center;
        }
        .pin-input {
            width: 50px;
            height: 55px;
            font-size: 1.5rem;
            text-align: center;
            border-radius: 12px;
            border: 2px solid #cbd5e1;
        }
        .pin-input:focus {
            border-color: #005ce6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,92,230,0.2);
        }
    </style>
</head>
<body>

    <div class="container py-4" style="max-width: 540px;">
        {{-- Top Bar --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('online-payment.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-3" style="font-size:.84rem;font-weight:600;">
                <i class="bi bi-arrow-left me-1"></i> Back to Search
            </a>
            <span class="badge bg-primary px-3 py-2 rounded-pill" style="font-size:.78rem;font-weight:700;">
                <i class="bi bi-shield-lock-fill me-1"></i> Secure Checkout
            </span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-4 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Citation Ticket Context Card --}}
        <div class="glass-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.2) !important;">
                <div>
                    <span class="text-uppercase fw-700" style="font-size:.72rem;letter-spacing:.08em;color:#e2e8f0;">Citation Ticket Number</span>
                    <h4 class="fw-800 text-white mb-0" style="letter-spacing:-.02em;">{{ $violation->ticket_number }}</h4>
                </div>
                @if($violation->isSettled())
                    <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-700"><i class="bi bi-check-circle-fill me-1"></i>Settled</span>
                @elseif($isOverdue)
                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-700"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>
                @else
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-700"><i class="bi bi-clock-history me-1"></i>Pending</span>
                @endif
            </div>

            <div class="row g-3 mb-3" style="font-size:.9rem;">
                <div class="col-6">
                    <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Violator Name</span>
                    <strong class="text-white fs-6">{{ $violation->violator?->full_name ?: '—' }}</strong>
                </div>
                <div class="col-6">
                    <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">License Number</span>
                    <strong class="text-white fs-6">{{ $violation->violator?->license_number ?: 'N/A' }}</strong>
                </div>
                <div class="col-6">
                    <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Violation Type</span>
                    <strong class="text-white fs-6">{{ $violation->violationType?->name }}</strong>
                </div>
                <div class="col-6">
                    <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Vehicle Plate</span>
                    <strong class="text-white fs-6">{{ $violation->vehicle_plate ?: ($violation->vehicle?->plate_number ?: 'N/A') }}</strong>
                </div>
                <div class="col-6">
                    <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Date of Citation</span>
                    <strong class="text-white fs-6">{{ $violation->date_of_violation?->format('M d, Y') }}</strong>
                </div>
                <div class="col-6">
                    <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Issuing LGU</span>
                    <strong class="text-white fs-6">{{ $lgu?->name ?: 'Cebu LGU' }}</strong>
                </div>
            </div>

            {{-- Itemized Financial Breakdown --}}
            <div class="p-3 rounded-4" style="background: rgba(0,0,0,0.35); border: 1px dashed rgba(255,255,255,0.25);">
                <div class="d-flex justify-content-between mb-1" style="font-size:.88rem;color:#e2e8f0;font-weight:500;">
                    <span>Base Fine Amount</span>
                    <span class="fw-700">₱{{ number_format($baseFine, 2) }}</span>
                </div>
                @if($latePenalty > 0)
                <div class="d-flex justify-content-between mb-1 text-danger fw-700" style="font-size:.88rem;">
                    <span>Late Overdue Penalty</span>
                    <span>+₱{{ number_format($latePenalty, 2) }}</span>
                </div>
                @endif
                @if($totalPaid > 0)
                <div class="d-flex justify-content-between mb-1 text-success fw-700" style="font-size:.88rem;">
                    <span>Previous Payments</span>
                    <span>-₱{{ number_format($totalPaid, 2) }}</span>
                </div>
                @endif
                <hr style="border-color: rgba(255,255,255,0.25);" class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-700 text-white" style="font-size:1rem;">Total Balance Due</span>
                    <span class="fw-800 text-warning" style="font-size:1.45rem;">₱{{ number_format($balanceRemaining, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Method Selection & Gateway Checkout Form --}}
        @if($balanceRemaining > 0 && !$violation->isSettled())
        <form action="{{ route('online-payment.process', $violation) }}" method="POST" id="mainPaymentForm">
            @csrf
            <input type="hidden" name="payment_method" id="selected_method" value="gcash">
            <input type="hidden" name="mobile_number" id="final_mobile_number" value="">
            <input type="hidden" name="card_number" id="final_card_number" value="">
            <input type="hidden" name="transaction_ref" id="final_transaction_ref" value="">

            <h6 class="fw-700 text-white mb-3" style="font-size:.95rem;letter-spacing:.03em;">Select Online Payment Gateway</h6>

            <div class="row g-2 mb-4">
                {{-- GCash Option --}}
                <div class="col-4">
                    <div class="method-card text-center active" id="card_gcash" onclick="selectMethod('gcash')">
                        <div class="mb-1" style="width:44px;height:44px;border-radius:14px;background:#005ce6;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:1.25rem;box-shadow:0 4px 12px rgba(0,92,230,0.4);">
                            G
                        </div>
                        <div class="fw-800 text-white" style="font-size:.85rem;">GCash</div>
                        <small class="d-block fw-600" style="font-size:.68rem;color:#cbd5e1;">Express Gateway</small>
                    </div>
                </div>

                {{-- Maya Option --}}
                <div class="col-4">
                    <div class="method-card text-center" id="card_maya" onclick="selectMethod('maya')">
                        <div class="mb-1" style="width:44px;height:44px;border-radius:14px;background:#10b981;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:1.25rem;box-shadow:0 4px 12px rgba(16,185,129,0.4);">
                            M
                        </div>
                        <div class="fw-800 text-white" style="font-size:.85rem;">Maya</div>
                        <small class="d-block fw-600" style="font-size:.68rem;color:#cbd5e1;">PayMaya Gateway</small>
                    </div>
                </div>

                {{-- Credit/Debit Card Option --}}
                <div class="col-4">
                    <div class="method-card text-center" id="card_card" onclick="selectMethod('card')">
                        <div class="mb-1" style="width:44px;height:44px;border-radius:14px;background:#8b5cf6;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:1.25rem;box-shadow:0 4px 12px rgba(139,92,246,0.4);">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                        <div class="fw-800 text-white" style="font-size:.85rem;">Card</div>
                        <small class="d-block fw-600" style="font-size:.68rem;color:#cbd5e1;">Visa / Master</small>
                    </div>
                </div>
            </div>

            {{-- Dynamic Gateway Summary Card --}}
            <div class="glass-card p-4 mb-4">
                {{-- GCash Details --}}
                <div id="details_gcash">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-primary px-3 py-2 rounded-pill fw-700" style="background:#005ce6 !important;font-size:.85rem;">
                            <i class="bi bi-phone-fill me-1"></i> GCash Express Checkout
                        </span>
                    </div>
                    @if($lgu?->gcash_qr_path)
                        <div class="text-center mb-3">
                            <img src="{{ Storage::url($lgu->gcash_qr_path) }}" alt="LGU GCash QR" style="max-width:180px;border-radius:16px;box-shadow:0 4px 15px rgba(0,0,0,0.3);">
                            <div class="small fw-600 mt-2" style="font-size:.78rem;color:#e2e8f0;">Official LGU GCash Merchant QR</div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.82rem;color:#f1f5f9;">GCash Mobile Number</label>
                        <input type="text" id="input_gcash_mobile" class="form-control form-control-custom" placeholder="e.g. 09171234567" value="09171234567">
                    </div>
                </div>

                {{-- Maya Details --}}
                <div id="details_maya" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-success px-3 py-2 rounded-pill fw-700" style="background:#10b981 !important;font-size:.85rem;">
                            <i class="bi bi-qr-code-scan me-1"></i> Maya Express Checkout
                        </span>
                    </div>
                    @if($lgu?->maya_qr_path)
                        <div class="text-center mb-3">
                            <img src="{{ Storage::url($lgu->maya_qr_path) }}" alt="LGU Maya QR" style="max-width:180px;border-radius:16px;box-shadow:0 4px 15px rgba(0,0,0,0.3);">
                            <div class="small fw-600 mt-2" style="font-size:.78rem;color:#e2e8f0;">Official LGU Maya Merchant QR</div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.82rem;color:#f1f5f9;">Maya Mobile / Account Number</label>
                        <input type="text" id="input_maya_mobile" class="form-control form-control-custom" placeholder="e.g. 09181234567" value="09181234567">
                    </div>
                </div>

                {{-- Card Details --}}
                <div id="details_card" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-purple px-3 py-2 rounded-pill fw-700" style="background:#8b5cf6 !important;font-size:.85rem;">
                            <i class="bi bi-credit-card-2-front-fill me-1"></i> Online Banking / Card Payment
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.82rem;color:#f1f5f9;">Card Number</label>
                        <input type="text" id="input_card_number" class="form-control form-control-custom" placeholder="4532 •••• •••• 8901" value="4532 8901 2345 6789">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-700" style="font-size:.82rem;color:#f1f5f9;">Expiry (MM/YY)</label>
                            <input type="text" placeholder="12/28" class="form-control form-control-custom" value="12/28">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-700" style="font-size:.82rem;color:#f1f5f9;">CVV</label>
                            <input type="password" placeholder="•••" maxlength="4" class="form-control form-control-custom" value="123">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Action --}}
            <button type="button" class="btn btn-confirm w-100 py-3" id="btnGatewaySubmit" onclick="openGatewayPortal()">
                <i class="bi bi-arrow-right-circle-fill me-2"></i> Proceed to <span id="gatewayBtnText">GCash Payment</span> (₱{{ number_format($balanceRemaining, 2) }})
            </button>
            <div class="text-center mt-2 fw-600" style="font-size:.78rem;color:#cbd5e1;">
                <i class="bi bi-shield-check text-success me-1"></i> Instant electronic receipt and automatic database settlement.
            </div>
        </form>
        @else
            <div class="glass-card p-4 text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size:3.5rem;"></i>
                <h5 class="fw-800 text-white mt-2">Citation Fully Settled</h5>
                <p style="font-size:.9rem;color:#e2e8f0;">This citation ticket has no outstanding balance.</p>
                @if($violation->latestPayment)
                    <a href="{{ route('online-payment.receipt', $violation->latestPayment) }}" class="btn btn-primary rounded-pill px-4 py-2 fw-700">
                        <i class="bi bi-receipt me-1"></i> View Official Receipt
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- REALISTIC GCASH PAYMENT GATEWAY MODAL --}}
    {{-- ========================================================================= --}}
    <div class="modal fade gateway-modal" id="gcashModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
                <div class="gcash-header">
                    <div class="fw-900 fs-3 tracking-tight">GCash</div>
                    <div class="small opacity-90 fw-600">Official Merchant Payment Portal</div>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="p-3 bg-light rounded-4 mb-4 border text-center">
                        <div class="text-muted small fw-700 text-uppercase">Merchant Name</div>
                        <strong class="fs-6 text-dark d-block mb-1">TVIRS — {{ $lgu?->name ?: 'Traffic Enforcement' }}</strong>
                        <div class="text-muted small fw-700 text-uppercase">Amount to Pay</div>
                        <div class="fw-900 fs-2 text-primary">₱{{ number_format($balanceRemaining, 2) }}</div>
                    </div>

                    {{-- Step 1: Mobile Number --}}
                    <div id="gcash_step_1">
                        <h6 class="fw-800 text-dark mb-2">Login to pay with GCash</h6>
                        <p class="text-muted small mb-3">Enter your 11-digit GCash mobile number to proceed.</p>
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text fw-700">+63</span>
                                <input type="text" id="gcash_modal_mobile" class="form-control form-control-lg fw-700" placeholder="9171234567" value="9171234567">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary w-100 py-3 rounded-4 fw-800" style="background:#005ce6;border:none;" onclick="goToGcashStep2()">
                            Next <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>

                    {{-- Step 2: MPIN Authentication --}}
                    <div id="gcash_step_2" style="display:none;">
                        <h6 class="fw-800 text-dark text-center mb-2">Enter 4-Digit MPIN</h6>
                        <p class="text-muted small text-center mb-3">Enter your MPIN to authorize ₱{{ number_format($balanceRemaining, 2) }} payment.</p>
                        <div class="d-flex justify-content-center gap-2 mb-4">
                            <input type="password" maxlength="1" class="pin-input" value="1">
                            <input type="password" maxlength="1" class="pin-input" value="2">
                            <input type="password" maxlength="1" class="pin-input" value="3">
                            <input type="password" maxlength="1" class="pin-input" value="4">
                        </div>
                        <button type="button" class="btn btn-primary w-100 py-3 rounded-4 fw-800" style="background:#005ce6;border:none;" onclick="submitFinalPayment('gcash')">
                            Pay ₱{{ number_format($balanceRemaining, 2) }} Now
                        </button>
                    </div>

                    {{-- Step 3: Processing --}}
                    <div id="gcash_step_processing" style="display:none;" class="text-center py-4">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                        <h5 class="fw-800 text-dark mb-1">Processing GCash Payment...</h5>
                        <p class="text-muted small mb-0">Connecting to GCash Payment Gateway. Please do not close this window.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REALISTIC MAYA PAYMENT GATEWAY MODAL --}}
    {{-- ========================================================================= --}}
    <div class="modal fade gateway-modal" id="mayaModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
                <div class="maya-header">
                    <div class="fw-900 fs-3 tracking-tight">maya</div>
                    <div class="small opacity-90 fw-600">Express Online Checkout</div>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="p-3 bg-light rounded-4 mb-4 border text-center">
                        <div class="text-muted small fw-700 text-uppercase">Merchant</div>
                        <strong class="fs-6 text-dark d-block mb-1">TVIRS — {{ $lgu?->name ?: 'Traffic Enforcement' }}</strong>
                        <div class="text-muted small fw-700 text-uppercase">Total Payable</div>
                        <div class="fw-900 fs-2 text-success">₱{{ number_format($balanceRemaining, 2) }}</div>
                    </div>

                    <div id="maya_step_1">
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted">Maya Account Mobile / Email</label>
                            <input type="text" id="maya_modal_mobile" class="form-control form-control-lg fw-700" value="09181234567">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted">Password</label>
                            <input type="password" class="form-control form-control-lg" value="••••••••">
                        </div>
                        <button type="button" class="btn btn-success w-100 py-3 rounded-4 fw-800" style="background:#10b981;border:none;" onclick="submitFinalPayment('maya')">
                            Authorize Pay ₱{{ number_format($balanceRemaining, 2) }}
                        </button>
                    </div>

                    <div id="maya_step_processing" style="display:none;" class="text-center py-4">
                        <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                        <h5 class="fw-800 text-dark mb-1">Processing Maya Payment...</h5>
                        <p class="text-muted small mb-0">Verifying transaction with Maya. Please wait...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REALISTIC CARD PAYMENT GATEWAY MODAL --}}
    {{-- ========================================================================= --}}
    <div class="modal fade gateway-modal" id="cardModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
                <div class="card-header-modal">
                    <div class="fw-900 fs-4 tracking-tight"><i class="bi bi-shield-lock-fill me-2 text-warning"></i>3D Secure Card Checkout</div>
                    <div class="small opacity-90 fw-600">Visa / Mastercard Secured</div>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="p-3 bg-light rounded-4 mb-4 border text-center">
                        <div class="text-muted small fw-700 text-uppercase">Payment Amount</div>
                        <div class="fw-900 fs-2 text-purple" style="color:#8b5cf6;">₱{{ number_format($balanceRemaining, 2) }}</div>
                    </div>

                    <div id="card_step_1">
                        <div class="mb-3">
                            <label class="form-label fw-700 small text-muted">Card Number</label>
                            <input type="text" id="card_modal_num" class="form-control form-control-lg fw-700" value="4532 8901 2345 6789">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-700 small text-muted">Expiry Date</label>
                                <input type="text" class="form-control" value="12/28">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-700 small text-muted">CVV Code</label>
                                <input type="password" class="form-control" value="123">
                            </div>
                        </div>
                        <button type="button" class="btn btn-dark w-100 py-3 rounded-4 fw-800" style="background:#1e1b4b;border:none;" onclick="submitFinalPayment('card')">
                            Pay ₱{{ number_format($balanceRemaining, 2) }}
                        </button>
                    </div>

                    <div id="card_step_processing" style="display:none;" class="text-center py-4">
                        <div class="spinner-border text-dark mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                        <h5 class="fw-800 text-dark mb-1">Authenticating 3D-Secure...</h5>
                        <p class="text-muted small mb-0">Verifying with your card issuing bank...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentMethod = 'gcash';

        function selectMethod(method) {
            currentMethod = method;
            document.getElementById('selected_method').value = method;
            ['gcash', 'maya', 'card'].forEach(m => {
                const card = document.getElementById('card_' + m);
                const details = document.getElementById('details_' + m);
                if (m === method) {
                    card.classList.add('active');
                    details.style.display = 'block';
                } else {
                    card.classList.remove('active');
                    details.style.display = 'none';
                }
            });

            const btnText = document.getElementById('gatewayBtnText');
            if (method === 'gcash') btnText.innerText = 'GCash Express';
            else if (method === 'maya') btnText.innerText = 'Maya Gateway';
            else btnText.innerText = 'Card Gateway';
        }

        function openGatewayPortal() {
            if (currentMethod === 'gcash') {
                const modal = new bootstrap.Modal(document.getElementById('gcashModal'));
                modal.show();
            } else if (currentMethod === 'maya') {
                const modal = new bootstrap.Modal(document.getElementById('mayaModal'));
                modal.show();
            } else if (currentMethod === 'card') {
                const modal = new bootstrap.Modal(document.getElementById('cardModal'));
                modal.show();
            }
        }

        function goToGcashStep2() {
            document.getElementById('gcash_step_1').style.display = 'none';
            document.getElementById('gcash_step_2').style.display = 'block';
        }

        function submitFinalPayment(method) {
            if (method === 'gcash') {
                document.getElementById('gcash_step_2').style.display = 'none';
                document.getElementById('gcash_step_processing').style.display = 'block';
                document.getElementById('final_mobile_number').value = document.getElementById('gcash_modal_mobile').value;
            } else if (method === 'maya') {
                document.getElementById('maya_step_1').style.display = 'none';
                document.getElementById('maya_step_processing').style.display = 'block';
                document.getElementById('final_mobile_number').value = document.getElementById('maya_modal_mobile').value;
            } else if (method === 'card') {
                document.getElementById('card_step_1').style.display = 'none';
                document.getElementById('card_step_processing').style.display = 'block';
                document.getElementById('final_card_number').value = document.getElementById('card_modal_num').value;
            }

            setTimeout(() => {
                document.getElementById('mainPaymentForm').submit();
            }, 1500);
        }
    </script>
</body>
</html>
