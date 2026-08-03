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
            border-radius: 16px;
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
            background: linear-gradient(135deg, #16a34a, #15803d);
            border: none;
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 800;
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.45);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(22, 163, 74, 0.6);
            color: #ffffff;
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

        {{-- Payment Method Selection & Checkout Form --}}
        @if($balanceRemaining > 0 && !$violation->isSettled())
        <form action="{{ route('online-payment.process', $violation) }}" method="POST">
            @csrf
            <input type="hidden" name="payment_method" id="selected_method" value="gcash">

            <h6 class="fw-700 text-white mb-3" style="font-size:.95rem;letter-spacing:.03em;">Select Online Payment Gateway</h6>

            <div class="row g-2 mb-4">
                {{-- GCash Option --}}
                <div class="col-4">
                    <div class="method-card text-center active" id="card_gcash" onclick="selectMethod('gcash')">
                        <div class="mb-1" style="width:42px;height:42px;border-radius:12px;background:#005ce6;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:1.15rem;">
                            G
                        </div>
                        <div class="fw-800 text-white" style="font-size:.85rem;">GCash</div>
                        <small class="d-block fw-600" style="font-size:.68rem;color:#cbd5e1;">Express Wallet</small>
                    </div>
                </div>

                {{-- Maya Option --}}
                <div class="col-4">
                    <div class="method-card text-center" id="card_maya" onclick="selectMethod('maya')">
                        <div class="mb-1" style="width:42px;height:42px;border-radius:12px;background:#10b981;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:1.15rem;">
                            M
                        </div>
                        <div class="fw-800 text-white" style="font-size:.85rem;">Maya</div>
                        <small class="d-block fw-600" style="font-size:.68rem;color:#cbd5e1;">PayMaya</small>
                    </div>
                </div>

                {{-- Credit/Debit Card Option --}}
                <div class="col-4">
                    <div class="method-card text-center" id="card_card" onclick="selectMethod('card')">
                        <div class="mb-1" style="width:42px;height:42px;border-radius:12px;background:#8b5cf6;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:1.15rem;">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                        <div class="fw-800 text-white" style="font-size:.85rem;">Card</div>
                        <small class="d-block fw-600" style="font-size:.68rem;color:#cbd5e1;">Visa / Master</small>
                    </div>
                </div>
            </div>

            {{-- Method Details Dynamic Card --}}
            <div class="glass-card p-4 mb-4">
                {{-- GCash Details --}}
                <div id="details_gcash">
                    <div class="d-flex align-items-center gap-2 mb-3 text-info">
                        <i class="bi bi-phone-fill fs-5" style="color:#60a5fa;"></i>
                        <span class="fw-700" style="font-size:.95rem;color:#93c5fd;">GCash Payment Details</span>
                    </div>
                    @if($lgu?->gcash_qr_path)
                        <div class="text-center mb-3">
                            <img src="{{ Storage::url($lgu->gcash_qr_path) }}" alt="LGU GCash QR" style="max-width:180px;border-radius:16px;box-shadow:0 4px 15px rgba(0,0,0,0.3);">
                            <div class="small fw-600 mt-2" style="font-size:.78rem;color:#e2e8f0;">Scan LGU QR or enter GCash Mobile No below</div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.8rem;color:#f1f5f9;">GCash Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control form-control-custom" placeholder="e.g. 09171234567">
                    </div>
                </div>

                {{-- Maya Details --}}
                <div id="details_maya" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3" style="color:#34d399;">
                        <i class="bi bi-qr-code-scan fs-5"></i>
                        <span class="fw-700" style="font-size:.95rem;color:#6ee7b7;">Maya Payment Details</span>
                    </div>
                    @if($lgu?->maya_qr_path)
                        <div class="text-center mb-3">
                            <img src="{{ Storage::url($lgu->maya_qr_path) }}" alt="LGU Maya QR" style="max-width:180px;border-radius:16px;box-shadow:0 4px 15px rgba(0,0,0,0.3);">
                            <div class="small fw-600 mt-2" style="font-size:.78rem;color:#e2e8f0;">Scan LGU Maya QR</div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.8rem;color:#f1f5f9;">Maya Account / Mobile Number</label>
                        <input type="text" name="maya_number" class="form-control form-control-custom" placeholder="e.g. 09181234567">
                    </div>
                </div>

                {{-- Card Details --}}
                <div id="details_card" style="display:none;">
                    <div class="d-flex align-items-center gap-2 mb-3" style="color:#c084fc;">
                        <i class="bi bi-credit-card-2-front-fill fs-5"></i>
                        <span class="fw-700" style="font-size:.95rem;color:#e9d5ff;">Credit / Debit Card Details</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.8rem;color:#f1f5f9;">Card Number</label>
                        <input type="text" name="card_number" class="form-control form-control-custom" placeholder="4532 •••• •••• 8901">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-700" style="font-size:.8rem;color:#f1f5f9;">Expiry (MM/YY)</label>
                            <input type="text" placeholder="12/28" class="form-control form-control-custom">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-700" style="font-size:.8rem;color:#f1f5f9;">CVV</label>
                            <input type="password" placeholder="•••" maxlength="4" class="form-control form-control-custom">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Action --}}
            <button type="submit" class="btn btn-confirm w-100 py-3">
                <i class="bi bi-lock-fill me-2"></i> Confirm & Pay ₱{{ number_format($balanceRemaining, 2) }}
            </button>
            <div class="text-center mt-2 fw-600" style="font-size:.78rem;color:#cbd5e1;">
                By confirming, your payment will be automatically recorded in the TVIRS database.
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
        function selectMethod(method) {
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
        }
    </script>
</body>
</html>
