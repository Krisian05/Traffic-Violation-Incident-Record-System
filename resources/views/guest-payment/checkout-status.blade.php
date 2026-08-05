<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Payment Verification — {{ $violation->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding-bottom: 2.5rem; }
        .card { border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .spinner-pulse { width: 3.5rem; height: 3.5rem; }
    </style>
</head>
<body>
<div class="container py-5" style="max-width: 480px;">

    <div class="text-center mb-4">
        <div class="fw-800 text-uppercase text-muted" style="font-size: .75rem; letter-spacing: .05em;">Traffic Citation Payment</div>
        <h5 class="fw-800 mb-0">{{ $violation->ticket_number }}</h5>
    </div>

    <!-- PENDING / PROCESSING STATE -->
    <div id="status-processing" class="card shadow-sm text-center p-4 mb-3 {{ $violation->isSettled() ? 'd-none' : '' }}">
        <div class="card-body py-4">
            <div class="spinner-border text-primary spinner-pulse mb-3" role="status">
                <span class="visually-hidden">Verifying...</span>
            </div>
            <h5 class="fw-800 mb-2">Verifying Online Payment</h5>
            <p class="text-muted mb-3" style="font-size: .88rem;">
                We are confirming your payment with PayMongo. This page will update automatically once verified.
            </p>
            <div class="small text-muted fst-italic">
                <i class="bi bi-arrow-repeat me-1"></i> Checking status in real-time...
            </div>
        </div>
    </div>

    <!-- SETTLED / SUCCESS STATE -->
    <div id="status-success" class="card shadow-sm p-4 mb-3 {{ $violation->isSettled() ? '' : 'd-none' }}" style="background-color: #f0fdf4; border-color: #bbf7d0;">
        <div class="card-body text-center p-2">
            <i class="bi bi-patch-check-fill text-success" style="font-size: 3.5rem;"></i>
            <h4 class="fw-800 text-success mt-2 mb-1">Payment Confirmed!</h4>
            <p class="text-muted mb-4" style="font-size: .88rem;">Your traffic citation has been officially settled.</p>

            <div class="bg-white rounded-3 p-3 border text-start mb-4">
                <table class="table table-borderless align-middle mb-0" style="font-size: .88rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted py-1" style="width: 42%;">Ticket Number</td>
                            <td class="fw-700 py-1" style="font-family: ui-monospace, monospace;">{{ $violation->ticket_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Official Receipt (OR)</td>
                            <td class="fw-700 text-success py-1" style="font-family: ui-monospace, monospace;" id="or-number-display">{{ $violation->or_number ?? 'Generating...' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Payment Method</td>
                            <td class="fw-600 py-1" id="payment-method-display">{{ strtoupper($violation->payment_method ?: 'PAYMONGO') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1">Date Paid</td>
                            <td class="fw-600 py-1" id="paid-at-display">{{ $violation->settled_at?->format('M d, Y g:i A') ?? now()->format('M d, Y g:i A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <a href="{{ route('guest-payment.show', $violation->public_payment_token) }}" class="btn btn-outline-success fw-700 w-100 py-2">
                <i class="bi bi-arrow-left me-1"></i> Return to Citation Summary
            </a>
        </div>
    </div>

</div>

@if(!$violation->isSettled() && $sessionRef)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = @json($violation->public_payment_token);
    const sessionRef = @json($sessionRef);
    const checkUrl = "{{ route('guest-payment.check-session-status', ['token' => $violation->public_payment_token, 'sessionRef' => $sessionRef]) }}";

    let polls = 0;
    const maxPolls = 60; // Poll for up to 3 minutes (60 x 3s)

    const pollInterval = setInterval(function() {
        polls++;
        if (polls > maxPolls) {
            clearInterval(pollInterval);
            return;
        }

        fetch(checkUrl)
            .then(res => res.json())
            .then(data => {
                if (data.is_settled) {
                    clearInterval(pollInterval);
                    document.getElementById('status-processing').classList.add('d-none');
                    document.getElementById('status-success').classList.remove('d-none');
                    if (data.or_number) {
                        document.getElementById('or-number-display').innerText = data.or_number;
                    }
                    if (data.paid_at) {
                        document.getElementById('paid-at-display').innerText = data.paid_at;
                    }
                }
            })
            .catch(err => console.error('Polling error:', err));
    }, 3000);
});
</script>
@endif
</body>
</html>
