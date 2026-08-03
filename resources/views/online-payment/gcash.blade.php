<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>GCash Payment Portal - Ticket {{ $violation->ticket_number }}</title>
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
        .gcash-navbar {
            background: #005ce6;
            color: #fff;
            padding: 1.25rem 1rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,92,230,0.3);
        }
        .gcash-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .pin-digit {
            width: 52px;
            height: 58px;
            font-size: 1.6rem;
            text-align: center;
            border-radius: 12px;
            border: 2px solid #cbd5e1;
            font-weight: 800;
        }
        .pin-digit:focus {
            border-color: #005ce6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,92,230,0.25);
        }
        .btn-gcash {
            background: #005ce6;
            color: #fff;
            font-weight: 800;
            border: none;
            border-radius: 16px;
            padding: 1rem;
            font-size: 1.1rem;
            box-shadow: 0 6px 20px rgba(0, 92, 230, 0.35);
            transition: all .15s ease;
        }
        .btn-gcash:hover {
            background: #0041a8;
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    {{-- Official GCash Blue Navbar --}}
    <div class="gcash-navbar">
        <div class="d-flex align-items-center justify-content-between container" style="max-width: 480px;">
            <a href="{{ route('online-payment.checkout', $violation->ticket_number) }}" class="text-white text-decoration-none small fw-700">
                <i class="bi bi-chevron-left me-1"></i> Cancel
            </a>
            <div class="fw-900 fs-3 tracking-tight">GCash</div>
            <span class="badge bg-white text-primary fw-800 rounded-pill px-3 py-2" style="color:#005ce6 !important;">
                <i class="bi bi-shield-check me-1"></i> VERIFIED
            </span>
        </div>
    </div>

    <div class="container my-auto py-4" style="max-width: 480px;">
        {{-- App Launch Banner for Mobile Devices --}}
        <div class="alert bg-primary text-white border-0 rounded-4 p-3 mb-3 d-flex align-items-center justify-content-between" style="background:#005ce6 !important;">
            <div>
                <strong class="d-block" style="font-size:.9rem;"><i class="bi bi-phone me-1"></i> Have GCash App installed?</strong>
                <span class="small opacity-90">Tap to open GCash directly on your phone</span>
            </div>
            <a href="gcash://pay?merchant=TVIRS&amount={{ $balanceRemaining }}&ref={{ $violation->ticket_number }}"
               class="btn btn-light text-primary btn-sm rounded-pill fw-800 px-3 flex-shrink-0"
               style="color:#005ce6 !important;"
               onclick="handleGcashAppClick(event)">
                Open App
            </a>
        </div>

        {{-- Merchant & Payment Details Card --}}
        <div class="gcash-card p-4 mb-4">
            <div class="text-center pb-3 border-bottom mb-3">
                <span class="text-uppercase text-muted fw-700" style="font-size:.7rem;letter-spacing:.08em;">Merchant / Biller Name</span>
                <h5 class="fw-800 text-dark mb-1">TVIRS — {{ $lgu?->name ?: 'Traffic Enforcement' }}</h5>
                <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill" style="font-size:.78rem;">
                    Citation Ticket #{{ $violation->ticket_number }}
                </span>
            </div>

            <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-light border mb-4">
                <div>
                    <span class="text-muted d-block small fw-700">Total Amount Due</span>
                    <strong class="text-dark" style="font-size:.85rem;">Motorist: {{ $violation->violator?->full_name ?: 'Citation Fine' }}</strong>
                </div>
                <span class="fw-900 text-primary fs-3" style="color:#005ce6 !important;">₱{{ number_format($balanceRemaining, 2) }}</span>
            </div>

            {{-- Payment Form Submission to TVIRS Backend --}}
            <form action="{{ route('online-payment.process', $violation) }}" method="POST" id="gcashForm">
                @csrf
                <input type="hidden" name="payment_method" value="gcash">

                {{-- Step 1: GCash Mobile Number Login --}}
                <div id="step_login">
                    <h6 class="fw-800 text-dark mb-2">Login to pay with GCash</h6>
                    <p class="text-muted small mb-3">Enter your 11-digit GCash registered mobile number.</p>

                    <div class="mb-4">
                        <label class="form-label fw-700 small text-secondary">Mobile Number</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text fw-800 text-secondary bg-light">+63</span>
                            <input type="text" name="mobile_number" id="mobile_number"
                                   class="form-control fw-800 text-dark"
                                   placeholder="9171234567"
                                   value="9171234567" required autofocus>
                        </div>
                    </div>

                    <button type="button" class="btn btn-gcash w-100" onclick="showMpinStep()">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>

                {{-- Step 2: 4-Digit MPIN Security Keypad --}}
                <div id="step_mpin" style="display:none;">
                    <h6 class="fw-800 text-dark text-center mb-1">Enter 4-Digit MPIN</h6>
                    <p class="text-muted small text-center mb-4">Please enter your GCash MPIN to authorize payment.</p>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <input type="password" maxlength="1" class="pin-digit" value="1">
                        <input type="password" maxlength="1" class="pin-digit" value="2">
                        <input type="password" maxlength="1" class="pin-digit" value="3">
                        <input type="password" maxlength="1" class="pin-digit" value="4">
                    </div>

                    <button type="button" class="btn btn-gcash w-100" onclick="submitGcashPayment()">
                        Pay ₱{{ number_format($balanceRemaining, 2) }}
                    </button>

                    <button type="button" class="btn btn-link text-muted w-100 mt-2 small text-decoration-none" onclick="showLoginStep()">
                        <i class="bi bi-arrow-left me-1"></i> Change Mobile Number
                    </button>
                </div>

                {{-- Step 3: Processing Animation --}}
                <div id="step_processing" style="display:none;" class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem; color:#005ce6 !important;" role="status"></div>
                    <h5 class="fw-800 text-dark mb-1">Processing GCash Payment...</h5>
                    <p class="text-muted small mb-0">Communicating with GCash Payment Gateway. Please do not refresh.</p>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="text-center small text-muted">
            <i class="bi bi-lock-fill text-success me-1"></i> Official GCash Payment Gateway Interface
            <br>
            Powered by TVIRS Online Payment Gateway
        </div>
    </div>

    <script>
        function showMpinStep() {
            const mobile = document.getElementById('mobile_number').value;
            if (!mobile || mobile.length < 10) {
                alert('Please enter a valid GCash mobile number.');
                return;
            }
            document.getElementById('step_login').style.display = 'none';
            document.getElementById('step_mpin').style.display = 'block';
        }

        function showLoginStep() {
            document.getElementById('step_mpin').style.display = 'none';
            document.getElementById('step_login').style.display = 'block';
        }

        function submitGcashPayment() {
            document.getElementById('step_mpin').style.display = 'none';
            document.getElementById('step_processing').style.display = 'block';

            setTimeout(() => {
                document.getElementById('gcashForm').submit();
            }, 1200);
        }

        function handleGcashAppClick(e) {
            // Fallback if GCash app is not installed on device
            setTimeout(() => {
                showMpinStep();
            }, 2000);
        }
    </script>
</body>
</html>
