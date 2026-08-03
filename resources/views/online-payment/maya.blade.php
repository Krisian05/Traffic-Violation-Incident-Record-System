<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Maya Payment Portal - Ticket {{ $violation->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .maya-navbar {
            background: #10b981;
            color: #fff;
            padding: 1.25rem 1rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }
        .maya-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .btn-maya {
            background: #10b981;
            color: #fff;
            font-weight: 800;
            border: none;
            border-radius: 16px;
            padding: 1rem;
            font-size: 1.1rem;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
            transition: all .15s ease;
        }
        .btn-maya:hover {
            background: #059669;
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    {{-- Official Maya Green Navbar --}}
    <div class="maya-navbar">
        <div class="d-flex align-items-center justify-content-between container" style="max-width: 480px;">
            <a href="{{ route('online-payment.checkout', $violation->ticket_number) }}" class="text-white text-decoration-none small fw-700">
                <i class="bi bi-chevron-left me-1"></i> Cancel
            </a>
            <div class="fw-900 fs-3 tracking-tight">maya</div>
            <span class="badge bg-white text-success fw-800 rounded-pill px-3 py-2" style="color:#10b981 !important;">
                <i class="bi bi-shield-check me-1"></i> SECURE
            </span>
        </div>
    </div>

    <div class="container my-auto py-4" style="max-width: 480px;">
        {{-- App Launch Banner --}}
        <div class="alert bg-success text-white border-0 rounded-4 p-3 mb-3 d-flex align-items-center justify-content-between" style="background:#10b981 !important;">
            <div>
                <strong class="d-block" style="font-size:.9rem;"><i class="bi bi-phone me-1"></i> PayMaya / Maya App Installed?</strong>
                <span class="small opacity-90">Tap to authorize payment in Maya App</span>
            </div>
            <a href="paymaya://pay?merchant=TVIRS&amount={{ $balanceRemaining }}&ref={{ $violation->ticket_number }}"
               class="btn btn-light text-success btn-sm rounded-pill fw-800 px-3 flex-shrink-0"
               style="color:#10b981 !important;">
                Open App
            </a>
        </div>

        {{-- Merchant & Payment Details Card --}}
        <div class="maya-card p-4 mb-4">
            <div class="text-center pb-3 border-bottom mb-3">
                <span class="text-uppercase text-muted fw-700" style="font-size:.7rem;letter-spacing:.08em;">Merchant / Biller</span>
                <h5 class="fw-800 text-dark mb-1">TVIRS — {{ $lgu?->name ?: 'Traffic Enforcement' }}</h5>
                <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill" style="font-size:.78rem;">
                    Citation Ticket #{{ $violation->ticket_number }}
                </span>
            </div>

            <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-light border mb-4">
                <div>
                    <span class="text-muted d-block small fw-700">Total Payable</span>
                    <strong class="text-dark" style="font-size:.85rem;">Motorist: {{ $violation->violator?->full_name ?: 'Citation Fine' }}</strong>
                </div>
                <span class="fw-900 text-success fs-3" style="color:#10b981 !important;">₱{{ number_format($balanceRemaining, 2) }}</span>
            </div>

            {{-- Payment Form --}}
            <form action="{{ route('online-payment.verify', ['ticket' => $violation->ticket_number, 'ref' => 'TXN-' . now()->format('Ymd') . '-' . strtoupper(Illuminate\Support\Str::random(6))]) }}" method="GET" id="mayaForm">
                <input type="hidden" name="method" value="maya">

                <div id="maya_step_login">
                    <h6 class="fw-800 text-dark mb-2">Log in with Maya</h6>
                    <div class="mb-3">
                        <label class="form-label fw-700 small text-secondary">Mobile Number or Registered Email</label>
                        <input type="text" name="mobile_number" class="form-control form-control-lg fw-700" value="09181234567" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-700 small text-secondary">Password / Security PIN</label>
                        <input type="password" class="form-control form-control-lg" value="••••••••" required>
                    </div>

                    <button type="button" class="btn btn-maya w-100" onclick="submitMayaPayment()">
                        Authorize & Pay ₱{{ number_format($balanceRemaining, 2) }}
                    </button>
                </div>

                <div id="maya_step_processing" style="display:none;" class="text-center py-4">
                    <div class="spinner-border text-success mb-3" style="width: 3.5rem; height: 3.5rem; color:#10b981 !important;" role="status"></div>
                    <h5 class="fw-800 text-dark mb-1">Authorizing Maya Payment...</h5>
                    <p class="text-muted small mb-0">Verifying transaction with Maya Payment Gateway.</p>
                </div>
            </form>
        </div>

        <div class="text-center small text-muted">
            <i class="bi bi-lock-fill text-success me-1"></i> Official Maya Payment Gateway Interface
        </div>
    </div>

    <script>
        function submitMayaPayment() {
            document.getElementById('maya_step_login').style.display = 'none';
            document.getElementById('maya_step_processing').style.display = 'block';

            setTimeout(() => {
                document.getElementById('mayaForm').submit();
            }, 1200);
        }
    </script>
</body>
</html>
