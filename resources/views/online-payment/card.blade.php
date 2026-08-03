<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Card Payment Portal - Ticket {{ $violation->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .card-navbar {
            background: #1e1b4b;
            color: #fff;
            padding: 1.25rem 1rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(30,27,75,0.3);
        }
        .card-box {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .btn-pay-card {
            background: #1e1b4b;
            color: #fff;
            font-weight: 800;
            border: none;
            border-radius: 16px;
            padding: 1rem;
            font-size: 1.1rem;
            box-shadow: 0 6px 20px rgba(30,27,75,0.35);
            transition: all .15s ease;
        }
        .btn-pay-card:hover {
            background: #0f172a;
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    {{-- Official Visa / Mastercard 3D Secure Navbar --}}
    <div class="card-navbar">
        <div class="d-flex align-items-center justify-content-between container" style="max-width: 480px;">
            <a href="{{ route('online-payment.checkout', $violation->ticket_number) }}" class="text-white text-decoration-none small fw-700">
                <i class="bi bi-chevron-left me-1"></i> Cancel
            </a>
            <div class="fw-900 fs-4 tracking-tight"><i class="bi bi-shield-lock-fill text-warning me-2"></i>3D SECURE</div>
            <span class="badge bg-white text-dark fw-800 rounded-pill px-3 py-2">
                VISA / MASTER
            </span>
        </div>
    </div>

    <div class="container my-auto py-4" style="max-width: 480px;">
        <div class="card-box p-4 mb-4">
            <div class="text-center pb-3 border-bottom mb-3">
                <span class="text-uppercase text-muted fw-700" style="font-size:.7rem;letter-spacing:.08em;">Government Merchant</span>
                <h5 class="fw-800 text-dark mb-1">TVIRS — {{ $lgu?->name ?: 'Traffic Enforcement' }}</h5>
                <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill" style="font-size:.78rem;">
                    Ticket #{{ $violation->ticket_number }}
                </span>
            </div>

            <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-light border mb-4">
                <div>
                    <span class="text-muted d-block small fw-700">Card Charge Amount</span>
                    <strong class="text-dark" style="font-size:.85rem;">Motorist: {{ $violation->violator?->full_name ?: 'Citation Fine' }}</strong>
                </div>
                <span class="fw-900 text-purple fs-3" style="color:#7c3aed !important;">₱{{ number_format($balanceRemaining, 2) }}</span>
            </div>

            {{-- Card Information Form --}}
            <form action="{{ route('online-payment.verify', ['ticket' => $violation->ticket_number, 'ref' => 'TXN-' . now()->format('Ymd') . '-' . strtoupper(Illuminate\Support\Str::random(6))]) }}" method="GET" id="cardForm">
                <input type="hidden" name="method" value="card">

                <div id="card_step_input">
                    <div class="mb-3">
                        <label class="form-label fw-700 small text-secondary">Cardholder Name</label>
                        <input type="text" class="form-control form-control-lg fw-700" value="{{ $violation->violator?->full_name ?: 'Juan Dela Cruz' }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-700 small text-secondary">Card Number</label>
                        <input type="text" class="form-control form-control-lg fw-700" value="4532 8901 2345 6789" required>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label fw-700 small text-secondary">Expiry (MM/YY)</label>
                            <input type="text" class="form-control form-control-lg" value="12/28" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-700 small text-secondary">CVV</label>
                            <input type="password" class="form-control form-control-lg" value="123" maxlength="4" required>
                        </div>
                    </div>

                    <button type="button" class="btn btn-pay-card w-100" onclick="submitCardPayment()">
                        Pay ₱{{ number_format($balanceRemaining, 2) }}
                    </button>
                </div>

                <div id="card_step_processing" style="display:none;" class="text-center py-4">
                    <div class="spinner-border text-dark mb-3" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
                    <h5 class="fw-800 text-dark mb-1">Authenticating 3D-Secure...</h5>
                    <p class="text-muted small mb-0">Communicating with card issuer bank. Please wait...</p>
                </div>
            </form>
        </div>

        <div class="text-center small text-muted">
            <i class="bi bi-shield-check text-success me-1"></i> Visa & Mastercard 3D-Secure PCI-DSS Compliant Gateway
        </div>
    </div>

    <script>
        function submitCardPayment() {
            document.getElementById('card_step_input').style.display = 'none';
            document.getElementById('card_step_processing').style.display = 'block';

            setTimeout(() => {
                document.getElementById('cardForm').submit();
            }, 1200);
        }
    </script>
</body>
</html>
