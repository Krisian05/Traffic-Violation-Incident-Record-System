<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Express Cashier Deposit - Ticket {{ $violation->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --gold: #fbbf24;
            --gold-dark: #d97706;
            --bg-casino: radial-gradient(circle at top, #1e1b4b 0%, #0f172a 45%, #020617 100%);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-casino);
            min-height: 100vh;
            color: #ffffff;
            padding-bottom: 2.5rem;
        }
        .casino-card {
            background: rgba(15, 23, 42, 0.78);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }
        .cashier-header {
            background: rgba(251, 191, 36, 0.08);
            border-bottom: 1px solid rgba(251, 191, 36, 0.2);
            padding: 1.25rem 1.5rem;
            border-radius: 28px 28px 0 0;
        }
        .gold-pill {
            background: linear-gradient(135deg, #fbbf24, #d97706);
            color: #0f172a;
            font-weight: 900;
            padding: .4rem .9rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
            letter-spacing: .05em;
        }
        .channel-card {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all .2s ease;
        }
        .channel-card:hover {
            border-color: #fbbf24;
            background: rgba(255, 255, 255, 0.1);
        }
        .channel-card.active {
            border-color: #fbbf24;
            background: rgba(251, 191, 36, 0.12);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.25), 0 10px 25px rgba(0, 0, 0, 0.4);
        }
        .btn-deposit-gold {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
            border: none;
            color: #0f172a;
            font-size: 1.2rem;
            font-weight: 900;
            border-radius: 20px;
            padding: 1.15rem;
            box-shadow: 0 8px 30px rgba(251, 191, 36, 0.5);
            transition: transform .15s ease, box-shadow .15s ease;
            letter-spacing: .02em;
        }
        .btn-deposit-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 38px rgba(251, 191, 36, 0.7);
            color: #0f172a;
        }
    </style>
</head>
<body>

    <div class="container py-4" style="max-width: 560px;">
        {{-- Navigation Bar --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('online-payment.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-3" style="font-size:.84rem;font-weight:600;">
                <i class="bi bi-arrow-left me-1"></i> Back to Search
            </a>
            <span class="gold-pill text-uppercase" style="font-size:.72rem;">
                <i class="bi bi-shield-check me-1"></i> 24/7 VIP CASHIER GATEWAY
            </span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-4 mb-3 border-0 shadow" style="background:rgba(220,38,38,0.9);color:#fff;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Casino Express Cashier Card --}}
        <div class="casino-card mb-4 overflow-hidden">
            <div class="cashier-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,#fbbf24,#d97706);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(251,191,36,0.4);">
                        <i class="bi bi-wallet2 text-dark fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-900 text-white mb-0" style="font-size:1.05rem;">{{ $lgu?->name ?: 'Balamban' }} Online Cashier</h6>
                        <small class="text-slate-300" style="font-size:.78rem;color:#cbd5e1;">Express Government Settlement Portal</small>
                    </div>
                </div>
                <span class="badge bg-success text-white font-monospace px-3 py-2 rounded-pill fw-800" style="font-size:.72rem;">INSTANT</span>
            </div>

            <div class="p-4">
                {{-- Order Summary --}}
                <div class="pb-3 mb-3 border-bottom border-secondary d-flex align-items-center justify-content-between" style="border-color:rgba(255,255,255,0.15) !important;">
                    <div>
                        <span class="text-uppercase fw-800" style="font-size:.7rem;letter-spacing:.08em;color:#fbbf24;">Citation Ticket Number</span>
                        <h5 class="fw-900 text-white mb-0">{{ $violation->ticket_number }}</h5>
                    </div>
                    @if($violation->isSettled())
                        <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-800"><i class="bi bi-check-circle-fill me-1"></i>Settled</span>
                    @elseif($isOverdue)
                        <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-800"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-800"><i class="bi bi-clock-history me-1"></i>Pending</span>
                    @endif
                </div>

                {{-- Motorist Details --}}
                <div class="row g-3 mb-4" style="font-size:.9rem;">
                    <div class="col-6">
                        <span class="d-block fw-600" style="font-size:.75rem;color:#94a3b8;">Motorist Name</span>
                        <strong class="text-white fs-6">{{ $violation->violator?->full_name ?: '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="d-block fw-600" style="font-size:.75rem;color:#94a3b8;">Driver License #</span>
                        <strong class="text-white fs-6">{{ $violation->violator?->license_number ?: 'N/A' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="d-block fw-600" style="font-size:.75rem;color:#94a3b8;">Violation Type</span>
                        <strong class="text-white fs-6">{{ $violation->violationType?->name }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="d-block fw-600" style="font-size:.75rem;color:#94a3b8;">Vehicle Plate #</span>
                        <strong class="text-white fs-6">{{ $violation->vehicle_plate ?: ($violation->vehicle?->plate_number ?: 'N/A') }}</strong>
                    </div>
                </div>

                {{-- High Impact Deposit Amount Card --}}
                <div class="p-3 rounded-4" style="background: rgba(0,0,0,0.45); border: 1px dashed rgba(251,191,36,0.4);">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.88rem;color:#cbd5e1;font-weight:500;">
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
                        <span>Cashier Processing Fee</span>
                        <span class="fw-700">₱0.00 (FREE)</span>
                    </div>
                    <hr style="border-color: rgba(255,255,255,0.2);" class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-900 text-white" style="font-size:1.05rem;">TOTAL DEPOSIT DUE</span>
                        <span class="fw-900 text-warning fs-2" style="color:#fbbf24 !important;">₱{{ number_format($balanceRemaining, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Channel Selector --}}
        @if($balanceRemaining > 0 && !$violation->isSettled())
        <form action="{{ route('online-payment.process', $violation) }}" method="POST" id="cashierForm">
            @csrf
            <input type="hidden" name="payment_method" id="selected_method" value="gcash">

            <h6 class="fw-900 text-white mb-3" style="font-size:.95rem;letter-spacing:.05em;">SELECT INSTANT DEPOSIT METHOD</h6>

            <div class="d-flex flex-column gap-3 mb-4">
                {{-- GCash Channel --}}
                <div class="channel-card active d-flex align-items-center justify-content-between" id="card_gcash" onclick="selectChannel('gcash')">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:46px;height:46px;border-radius:14px;background:#005ce6;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.3rem;box-shadow:0 6px 16px rgba(0,92,230,0.5);">
                            G
                        </div>
                        <div>
                            <strong class="d-block text-white" style="font-size:.95rem;">GCash Express Deposit</strong>
                            <small style="font-size:.76rem;color:#94a3b8;"><i class="bi bi-lightning-fill text-warning me-1"></i>Instant wallet deposit & QR Scanner</small>
                        </div>
                    </div>
                    <i class="bi bi-check-circle-fill text-warning fs-3" id="icon_gcash"></i>
                </div>

                {{-- Maya Channel --}}
                <div class="channel-card d-flex align-items-center justify-content-between" id="card_maya" onclick="selectChannel('maya')">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:46px;height:46px;border-radius:14px;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.3rem;box-shadow:0 6px 16px rgba(16,185,129,0.5);">
                            M
                        </div>
                        <div>
                            <strong class="d-block text-white" style="font-size:.95rem;">Maya Express Wallet</strong>
                            <small style="font-size:.76rem;color:#94a3b8;"><i class="bi bi-shield-fill-check text-success me-1"></i>PayMaya Zero Fee Gateway</small>
                        </div>
                    </div>
                    <i class="bi bi-circle text-slate-500 fs-3" id="icon_maya"></i>
                </div>

                {{-- Credit / Debit Card --}}
                <div class="channel-card d-flex align-items-center justify-content-between" id="card_card" onclick="selectChannel('card')">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:46px;height:46px;border-radius:14px;background:#8b5cf6;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.3rem;box-shadow:0 6px 16px rgba(139,92,246,0.5);">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                        <div>
                            <strong class="d-block text-white" style="font-size:.95rem;">VIP Credit / Debit Card</strong>
                            <small style="font-size:.76rem;color:#94a3b8;"><i class="bi bi-lock-fill text-purple me-1"></i>Visa, Mastercard, 3D Secure</small>
                        </div>
                    </div>
                    <i class="bi bi-circle text-slate-500 fs-3" id="icon_card"></i>
                </div>
            </div>

            {{-- Big Deposit Action --}}
            <button type="submit" class="btn btn-deposit-gold w-100 mb-2">
                <i class="bi bi-lightning-charge-fill me-2"></i> PROCEED TO DEPOSIT ₱{{ number_format($balanceRemaining, 2) }} ⚡
            </button>
            <div class="text-center fw-600" style="font-size:.78rem;color:#cbd5e1;">
                <i class="bi bi-shield-lock-fill text-warning me-1"></i> Cashier engine holds transaction until money is verified received.
            </div>
        </form>
        @else
            <div class="casino-card p-4 text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size:3.5rem;"></i>
                <h5 class="fw-900 text-white mt-2">Citation Fully Settled</h5>
                <p style="font-size:.9rem;color:#cbd5e1;">This citation ticket has no outstanding balance.</p>
                @if($violation->latestPayment)
                    <a href="{{ route('online-payment.receipt', $violation->latestPayment) }}" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-900">
                        <i class="bi bi-receipt me-1"></i> View Official Receipt
                    </a>
                @endif
            </div>
        @endif
    </div>

    <script>
        function selectChannel(method) {
            document.getElementById('selected_method').value = method;
            ['gcash', 'maya', 'card'].forEach(m => {
                const card = document.getElementById('card_' + m);
                const icon = document.getElementById('icon_' + m);
                if (m === method) {
                    card.classList.add('active');
                    icon.className = 'bi bi-check-circle-fill text-warning fs-3';
                } else {
                    card.classList.remove('active');
                    icon.className = 'bi bi-circle text-slate-500 fs-3';
                }
            });
        }
    </script>
</body>
</html>
