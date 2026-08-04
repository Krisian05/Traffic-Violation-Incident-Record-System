<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Payment Claim Status — {{ $violation->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f3ef; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .card { border: 1px solid #e7e2db; border-radius: 16px; }
    </style>
</head>
<body>
<div class="container py-5" style="max-width: 480px;">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm text-center">
        <div class="card-body p-5">
            @if($violation->isSettled())
                <i class="bi bi-patch-check-fill text-success display-4 mb-3 d-block"></i>
                <h5 class="fw-800 text-success mb-2">Payment Confirmed</h5>
                <p class="text-muted mb-4" style="font-size: .9rem;">Citation {{ $violation->ticket_number }} has been settled.</p>
            @elseif($violation->pendingPaymentClaim)
                <i class="bi bi-hourglass-split text-warning display-4 mb-3 d-block"></i>
                <h5 class="fw-800 mb-2">Awaiting Verification</h5>
                <p class="text-muted mb-4" style="font-size: .9rem;">
                    Your payment claim for citation {{ $violation->ticket_number }} (Ref: {{ $violation->pendingPaymentClaim->claimed_reference }}) has been received and is being manually verified by our staff against the actual GCash transaction. This isn't instant — please check back later.
                </p>
            @else
                <i class="bi bi-info-circle text-secondary display-4 mb-3 d-block"></i>
                <h5 class="fw-800 mb-2">No Active Claim</h5>
                <p class="text-muted mb-4" style="font-size: .9rem;">There's no payment claim currently pending for this citation.</p>
            @endif

            <a href="{{ route('guest-payment.show', $violation->public_payment_token) }}" class="btn btn-outline-secondary fw-700">
                <i class="bi bi-arrow-left me-1"></i> Back to Citation
            </a>
        </div>
    </div>
</div>
</body>
</html>
