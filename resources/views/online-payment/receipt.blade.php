<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Official Receipt #{{ $payment->or_number }} - TVIRS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            padding-bottom: 2rem;
        }
        .receipt-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff;
            padding: 2rem 1.5rem;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .receipt-card { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

    <div class="container py-4" style="max-width: 520px;">
        {{-- Navigation Actions --}}
        <div class="d-flex align-items-center justify-content-between mb-3 no-print">
            <a href="{{ route('online-payment.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-search me-1"></i> Search Another Ticket
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-printer-fill me-1"></i> Print / Save PDF
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-4 mb-3 no-print">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Digital Official Receipt Card --}}
        <div class="receipt-card">
            <div class="receipt-header">
                <div class="d-inline-flex align-items-center justify-content-center mb-2"
                     style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.2);">
                    <i class="bi bi-check-lg" style="font-size: 2rem; color: #fff;"></i>
                </div>
                <h5 class="fw-800 text-uppercase tracking-wider mb-1" style="letter-spacing:.05em;">Official Payment Receipt</h5>
                <div class="small opacity-90">{{ $lgu?->name ?: 'Republic of the Philippines' }} · Traffic Enforcement Division</div>
            </div>

            <div class="p-4">
                {{-- OR Number & Date --}}
                <div class="text-center pb-3 mb-3 border-bottom">
                    <span class="text-muted text-uppercase fw-700" style="font-size:.68rem;letter-spacing:.08em;color:#64748b;">Official Receipt Number</span>
                    <h3 class="fw-800 text-success mb-1" style="letter-spacing:-.02em;">{{ $payment->or_number }}</h3>
                    <div style="font-size:.78rem;color:#64748b;">
                        <i class="bi bi-calendar-check me-1"></i>{{ $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}
                    </div>
                </div>

                {{-- Citation & Violator Details --}}
                <div class="row g-3 mb-3" style="font-size:.85rem;">
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:.72rem;">Citation Ticket #</span>
                        <strong class="text-dark">{{ $violation->ticket_number }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:.72rem;">Violator Name</span>
                        <strong class="text-dark">{{ $violation->violator?->full_name ?: '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:.72rem;">Driver License #</span>
                        <strong class="text-dark">{{ $violation->violator?->license_number ?: 'N/A' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block" style="font-size:.72rem;">Vehicle Plate</span>
                        <strong class="text-dark">{{ $violation->vehicle_plate_number ?: 'N/A' }}</strong>
                    </div>
                    <div class="col-12">
                        <span class="text-muted d-block" style="font-size:.72rem;">Violation Type</span>
                        <strong class="text-dark">{{ $violation->violationType?->name }}</strong>
                    </div>
                </div>

                {{-- Payment Financials --}}
                <div class="p-3 rounded-4 bg-light border mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.84rem;">
                        <span class="text-muted">Payment Channel</span>
                        <strong class="text-dark text-capitalize">{{ $payment->payment_method }} (Online)</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:.84rem;">
                        <span class="text-muted">Collector / Gateway</span>
                        <strong class="text-dark">{{ $payment->cashier_name }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-700 text-dark">Amount Paid</span>
                        <span class="fw-800 text-success fs-5">₱{{ number_format($payment->amount_paid, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center" style="font-size:.82rem;">
                        <span class="text-muted">Remaining Balance Owed</span>
                        <span class="fw-700 text-slate-600">₱{{ number_format($violation->balanceRemaining(), 2) }}</span>
                    </div>
                </div>

                <div class="text-center pt-2" style="font-size:.75rem;color:#94a3b8;">
                    <i class="bi bi-patch-check-fill text-success me-1"></i> This is an officially generated electronic receipt. Valid for official records.
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-4 no-print" style="font-size:.78rem;color:#64748b;">
            <a href="{{ route('home') }}" class="btn btn-link text-decoration-none text-muted btn-sm">Return to System Homepage</a>
        </div>
    </div>

</body>
</html>
