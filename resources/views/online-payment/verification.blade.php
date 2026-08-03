<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Awaiting Payment - Ticket {{ $violation->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
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
        .status-badge-pulsing {
            background: rgba(245, 158, 11, 0.2);
            border: 1px solid #f59e0b;
            color: #fbbf24;
            padding: .5rem 1.25rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: .9rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            animation: pulse-amber 1.8s infinite;
        }
        @keyframes pulse-amber {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 12px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }
        .timer-box {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: .75rem 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-weight: 800;
            font-size: 1.2rem;
            color: #60a5fa;
        }
        .btn-confirm-received {
            background: linear-gradient(135deg, #16a34a, #15803d);
            border: none;
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 800;
            border-radius: 18px;
            padding: 1.1rem;
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.5);
            transition: all .15s ease;
        }
        .btn-confirm-received:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(22, 163, 74, 0.65);
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="container py-4" style="max-width: 520px;">
        {{-- Top Bar --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('online-payment.checkout', $violation->ticket_number) }}" class="btn btn-outline-light btn-sm rounded-pill px-3" style="font-size:.84rem;font-weight:600;">
                <i class="bi bi-arrow-left me-1"></i> Back to Checkout
            </a>
            <span class="badge bg-primary px-3 py-2 rounded-pill" style="font-size:.78rem;font-weight:700;">
                <i class="bi bi-shop me-1"></i> Official Merchant Gateway
            </span>
        </div>

        {{-- Merchant Money Verification Gateway Card --}}
        <div class="glass-card p-4 text-center mb-4">
            <div class="mb-3">
                <span class="status-badge-pulsing" id="statusBadge">
                    <span class="spinner-grow spinner-grow-sm text-warning" role="status"></span>
                    <span id="statusText">AWAITING MONEY RECEIVED...</span>
                </span>
            </div>

            <h4 class="fw-800 text-white mb-1">Verify Merchant Payment</h4>
            <p style="font-size:.9rem;color:#cbd5e1;max-width:400px;" class="mx-auto mb-3">
                System is waiting for payment confirmation from <strong class="text-white text-uppercase">{{ $paymentMethod }}</strong>. Citation will not be marked settled until funds are verified.
            </p>

            {{-- Timer --}}
            <div class="timer-box mb-4">
                <i class="bi bi-clock-history text-info"></i>
                <span id="countdownTimer">14:59</span>
                <small class="text-slate-400" style="font-size:.7rem;color:#94a3b8;">Time Remaining</small>
            </div>

            {{-- Merchant Order Details Table --}}
            <div class="p-3 rounded-4 text-start mb-4" style="background: rgba(0,0,0,0.35); border: 1px dashed rgba(255,255,255,0.2);">
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;color:#cbd5e1;">
                    <span>Merchant Biller</span>
                    <strong class="text-white">{{ $lgu?->name ?: 'Balamban' }} Traffic Treasury</strong>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;color:#cbd5e1;">
                    <span>Transaction Reference</span>
                    <strong class="text-info font-monospace">{{ $transactionRef }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;color:#cbd5e1;">
                    <span>Citation Ticket #</span>
                    <strong class="text-white">{{ $violation->ticket_number }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;color:#cbd5e1;">
                    <span>Violator / Motorist</span>
                    <strong class="text-white">{{ $violation->violator?->full_name ?: '—' }}</strong>
                </div>
                <hr style="border-color: rgba(255,255,255,0.2);" class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-700 text-white" style="font-size:.95rem;">Amount Due to Receive</span>
                    <span class="fw-900 text-warning fs-4">₱{{ number_format($balanceRemaining, 2) }}</span>
                </div>
            </div>

            {{-- Dynamic QR or Gateway Instructions --}}
            @if($paymentMethod === 'gcash' && $lgu?->gcash_qr_path)
                <div class="mb-4">
                    <img src="{{ Storage::url($lgu->gcash_qr_path) }}" alt="LGU GCash QR" style="max-width:180px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.4);">
                    <div class="small fw-600 mt-2" style="font-size:.78rem;color:#e2e8f0;">Scan with GCash App to complete payment</div>
                </div>
            @elseif($paymentMethod === 'maya' && $lgu?->maya_qr_path)
                <div class="mb-4">
                    <img src="{{ Storage::url($lgu->maya_qr_path) }}" alt="LGU Maya QR" style="max-width:180px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.4);">
                    <div class="small fw-600 mt-2" style="font-size:.78rem;color:#e2e8f0;">Scan with Maya App to complete payment</div>
                </div>
            @endif

            {{-- Confirm Money Received Form --}}
            <form action="{{ route('online-payment.confirm-received', ['violation' => $violation->id, 'ref' => $transactionRef]) }}" method="POST" id="confirmForm">
                @csrf
                <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">
                
                <button type="button" class="btn btn-confirm-received w-100 mb-2" onclick="confirmMoneyReceived()">
                    <i class="bi bi-check-circle-fill me-2"></i> Confirm Money Received & Issue Receipt
                </button>
            </form>
            <div class="text-muted small fw-500" style="font-size:.75rem;color:#94a3b8;">
                <i class="bi bi-shield-lock-fill text-success me-1"></i> TVIRS Merchant Engine locks ticket settlement until money is verified.
            </div>
        </div>
    </div>

    <script>
        // 15-Minute Countdown Timer
        let secondsLeft = 15 * 60;
        const timerEl = document.getElementById('countdownTimer');

        setInterval(() => {
            if (secondsLeft <= 0) return;
            secondsLeft--;
            const mins = Math.floor(secondsLeft / 60).toString().padStart(2, '0');
            const secs = (secondsLeft % 60).toString().padStart(2, '0');
            timerEl.innerText = `${mins}:${secs}`;
        }, 1000);

        // Auto Poll status every 4 seconds
        setInterval(() => {
            fetch("{{ route('online-payment.status', ['violation' => $violation->id, 'ref' => $transactionRef]) }}")
                .then(res => res.json())
                .then(data => {
                    if (data.settled && data.receipt_url) {
                        window.location.href = data.receipt_url;
                    }
                }).catch(e => console.log(e));
        }, 4000);

        function confirmMoneyReceived() {
            const btn = document.querySelector('.btn-confirm-received');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying & Recording Settlement...';

            document.getElementById('statusText').innerText = 'MONEY RECEIVED & VERIFYING...';

            setTimeout(() => {
                document.getElementById('confirmForm').submit();
            }, 800);
        }
    </script>
</body>
</html>
