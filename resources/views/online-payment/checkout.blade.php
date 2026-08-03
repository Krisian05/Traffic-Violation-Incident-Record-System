<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Merchant Online Checkout - Ticket {{ $violation->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e3a8a 100%);
            min-height: 100vh;
            color: #ffffff;
            padding-bottom: 2.5rem;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }
        .merchant-header {
            background: rgba(255, 255, 255, 0.12);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding: 1.25rem 1.5rem;
            border-radius: 24px 24px 0 0;
        }
        .payment-radio-card {
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.18);
            border-radius: 18px;
            padding: 1.1rem;
            cursor: pointer;
            transition: all .2s ease;
        }
        .payment-radio-card:hover {
            border-color: #93c5fd;
            background: rgba(255, 255, 255, 0.15);
        }
        .payment-radio-card.active {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.25);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.35);
        }
        .btn-merchant-pay {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 800;
            border-radius: 18px;
            padding: 1.1rem;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-merchant-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(37, 99, 235, 0.65);
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="container py-4" style="max-width: 560px;">
        {{-- Navigation Top Bar --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('online-payment.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-3" style="font-size:.84rem;font-weight:600;">
                <i class="bi bi-arrow-left me-1"></i> Back to Search
            </a>
            <span class="badge bg-primary px-3 py-2 rounded-pill fw-800" style="font-size:.78rem;">
                <i class="bi bi-shield-check me-1"></i> Verified Merchant Cashier
            </span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-4 mb-3 border-0 shadow" style="background:rgba(220,38,38,0.9);color:#fff;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- E-Commerce Merchant Order Card --}}
        <div class="glass-card mb-4 overflow-hidden">
            <div class="merchant-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px rgba(29,78,216,0.4);">
                        <i class="bi bi-shop text-white fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-800 text-white mb-0" style="font-size:1.02rem;">{{ $lgu?->name ?: 'Balamban' }} Traffic Enforcement</h6>
                        <small class="text-slate-300" style="font-size:.78rem;color:#cbd5e1;">Official Government Online Cashier</small>
                    </div>
                </div>
                <span class="badge bg-success px-3 py-2 rounded-pill fw-800" style="font-size:.72rem;">ONLINE</span>
            </div>

            <div class="p-4">
                {{-- Order Summary --}}
                <div class="pb-3 mb-3 border-bottom border-secondary d-flex align-items-center justify-content-between" style="border-color:rgba(255,255,255,0.18) !important;">
                    <div>
                        <span class="text-uppercase fw-700" style="font-size:.7rem;letter-spacing:.08em;color:#cbd5e1;">Citation Ticket Number</span>
                        <h5 class="fw-800 text-white mb-0">{{ $violation->ticket_number }}</h5>
                    </div>
                    @if($violation->isSettled())
                        <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-700"><i class="bi bi-check-circle-fill me-1"></i>Settled</span>
                    @elseif($isOverdue)
                        <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-700"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-700"><i class="bi bi-clock-history me-1"></i>Pending</span>
                    @endif
                </div>

                {{-- Motorist & Citation Information --}}
                <div class="row g-3 mb-4" style="font-size:.9rem;">
                    <div class="col-6">
                        <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Motorist Name</span>
                        <strong class="text-white fs-6">{{ $violation->violator?->full_name ?: '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Driver License #</span>
                        <strong class="text-white fs-6">{{ $violation->violator?->license_number ?: 'N/A' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Violation Type</span>
                        <strong class="text-white fs-6">{{ $violation->violationType?->name }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="d-block fw-600" style="font-size:.75rem;color:#cbd5e1;">Vehicle Plate #</span>
                        <strong class="text-white fs-6">{{ $violation->vehicle_plate ?: ($violation->vehicle?->plate_number ?: 'N/A') }}</strong>
                    </div>
                </div>

                {{-- Itemized Merchant Price Table --}}
                <div class="p-3 rounded-4" style="background: rgba(0,0,0,0.35); border: 1px dashed rgba(255,255,255,0.25);">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.88rem;color:#e2e8f0;font-weight:500;">
                        <span>Base Fine Subtotal</span>
                        <span class="fw-700">₱{{ number_format($baseFine, 2) }}</span>
                    </div>
                    @if($latePenalty > 0)
                    <div class="d-flex justify-content-between mb-1 text-danger fw-700" style="font-size:.88rem;">
                        <span>Overdue Penalty Surcharge</span>
                        <span>+₱{{ number_format($latePenalty, 2) }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between mb-1" style="font-size:.88rem;color:#34d399;">
                        <span>Merchant Platform Fee</span>
                        <span class="fw-700">₱0.00 (FREE)</span>
                    </div>
                    <hr style="border-color: rgba(255,255,255,0.25);" class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-800 text-white" style="font-size:1.05rem;">TOTAL PAYABLE DUE</span>
                        <span class="fw-900 text-warning fs-3">₱{{ number_format($balanceRemaining, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Selection Form --}}
        @if($balanceRemaining > 0 && !$violation->isSettled())
        <form action="{{ route('online-payment.process', $violation) }}" method="POST" id="merchantForm">
            @csrf
            <input type="hidden" name="payment_method" id="selected_method" value="gcash">

            <h6 class="fw-800 text-white mb-3" style="font-size:.95rem;letter-spacing:.03em;">Select Merchant Payment Channel</h6>

            <div class="d-flex flex-column gap-2 mb-4">
                {{-- GCash --}}
                <div class="payment-radio-card active d-flex align-items-center justify-content-between" id="card_gcash" onclick="selectPaymentMethod('gcash')">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;border-radius:14px;background:#005ce6;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.2rem;box-shadow:0 4px 12px rgba(0,92,230,0.4);">
                            G
                        </div>
                        <div>
                            <strong class="d-block text-white" style="font-size:.92rem;">GCash Wallet / QR Ph</strong>
                            <small style="font-size:.74rem;color:#cbd5e1;">Instant wallet payment & QR scanner</small>
                        </div>
                    </div>
                    <i class="bi bi-check-circle-fill text-primary fs-4" id="icon_gcash"></i>
                </div>

                {{-- Maya --}}
                <div class="payment-radio-card d-flex align-items-center justify-content-between" id="card_maya" onclick="selectPaymentMethod('maya')">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;border-radius:14px;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.2rem;box-shadow:0 4px 12px rgba(16,185,129,0.4);">
                            M
                        </div>
                        <div>
                            <strong class="d-block text-white" style="font-size:.92rem;">Maya Wallet / QR Ph</strong>
                            <small style="font-size:.74rem;color:#cbd5e1;">PayMaya Express Merchant Gateway</small>
                        </div>
                    </div>
                    <i class="bi bi-circle text-slate-400 fs-4" id="icon_maya"></i>
                </div>

                {{-- Credit / Debit Card --}}
                <div class="payment-radio-card d-flex align-items-center justify-content-between" id="card_card" onclick="selectPaymentMethod('card')">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;border-radius:14px;background:#8b5cf6;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.2rem;box-shadow:0 4px 12px rgba(139,92,246,0.4);">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                        <div>
                            <strong class="d-block text-white" style="font-size:.92rem;">Credit / Debit Card</strong>
                            <small style="font-size:.74rem;color:#cbd5e1;">Visa, Mastercard, JCB, 3D Secure</small>
                        </div>
                    </div>
                    <i class="bi bi-circle text-slate-400 fs-4" id="icon_card"></i>
                </div>
            </div>

            {{-- Checkout Button --}}
            <button type="submit" class="btn btn-merchant-pay w-100 mb-2">
                <i class="bi bi-shield-lock-fill me-2"></i> Pay Now ₱{{ number_format($balanceRemaining, 2) }}
            </button>
            <div class="text-center fw-600" style="font-size:.78rem;color:#cbd5e1;">
                <i class="bi bi-shield-check text-success me-1"></i> Proceeds directly to live payment verification gateway. Ticket marks settled ONLY when money is received.
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

    <script>
        function selectPaymentMethod(method) {
            document.getElementById('selected_method').value = method;
            ['gcash', 'maya', 'card'].forEach(m => {
                const card = document.getElementById('card_' + m);
                const icon = document.getElementById('icon_' + m);
                if (m === method) {
                    card.classList.add('active');
                    icon.className = 'bi bi-check-circle-fill text-primary fs-4';
                } else {
                    card.classList.remove('active');
                    icon.className = 'bi bi-circle text-slate-400 fs-4';
                }
            });
        }
    </script>
</body>
</html>
