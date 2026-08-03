<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pay Citation Online - TVIRS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1d4ed8;
            --primary-dark: #1e40af;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e3a8a 100%);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            color: #ffffff;
            display: flex;
            flex-direction: column;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }
        .search-input {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #ffffff;
            border-radius: 14px;
            padding: .85rem 1.1rem;
            font-size: 1rem;
            font-weight: 500;
            transition: all .2s ease;
        }
        .search-input:focus {
            background: rgba(255, 255, 255, 0.28);
            border-color: #93c5fd;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.35);
        }
        .search-input::placeholder {
            color: #cbd5e1;
            opacity: 0.95;
        }
        .btn-pay {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            color: #ffffff;
            font-weight: 700;
            border-radius: 14px;
            padding: .85rem 1.5rem;
            box-shadow: 0 4px 18px rgba(37, 99, 235, 0.45);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.6);
            color: #ffffff;
        }
        .result-item {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            transition: background .2s ease;
        }
        .result-item:hover { background: rgba(255, 255, 255, 0.15); }
        .badge-status {
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .35rem .75rem;
            border-radius: 50px;
        }
    </style>
</head>
<body>

    <div class="container my-auto py-5" style="max-width: 580px;">
        {{-- Header --}}
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:68px;height:68px;border-radius:20px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);box-shadow:0 10px 25px rgba(29,78,216,0.5);">
                <i class="bi bi-shield-check" style="font-size: 2rem; color: #fff;"></i>
            </div>
            <h2 class="fw-800 tracking-tight text-white mb-1">Traffic Citation Online Payment</h2>
            <p style="font-size: .95rem; color: #e2e8f0; font-weight: 500;">
                Pay your traffic violation fine quickly and securely via GCash, Maya, or Card.
            </p>
        </div>

        {{-- Search Form Card --}}
        <div class="glass-card p-4 mb-4">
            <form action="{{ route('online-payment.search') }}" method="POST">
                @csrf
                <label class="form-label fw-700 text-uppercase tracking-wider mb-2" style="font-size:.82rem;letter-spacing:.06em;color:#f1f5f9;">
                    SEARCH YOUR CITATION TICKET
                </label>
                <div class="input-group mb-3">
                    <input type="text" name="search" class="form-control search-input"
                           placeholder="Enter Ticket # (e.g. CEB-2026-00001), License #, or Plate #"
                           value="{{ $searchQuery }}" required autofocus>
                    <button class="btn btn-pay px-4" type="submit">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
                <div class="d-flex align-items-center justify-content-between" style="font-size:.82rem;color:#e2e8f0;font-weight:600;">
                    <span><i class="bi bi-qr-code-scan me-1" style="color:#60a5fa;"></i> Or scan your printed ticket QR code</span>
                    <span><i class="bi bi-lock-fill me-1" style="color:#4ade80;"></i> 256-bit Encrypted</span>
                </div>
            </form>
        </div>

        {{-- Search Results --}}
        @if(!empty($searchQuery))
        <div class="glass-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-700 text-white mb-0" style="font-size:.92rem;">Search Results for "{{ $searchQuery }}"</h6>
                <span class="badge bg-primary px-2 py-1" style="font-size:.75rem;">{{ $violations->count() }} Found</span>
            </div>

            @forelse($violations as $v)
                <div class="result-item p-3 mb-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-800 text-white" style="font-size: 1.05rem;">{{ $v->ticket_number }}</span>
                            @if($v->isSettled())
                                <span class="badge badge-status bg-success text-white"><i class="bi bi-check-circle-fill me-1"></i>Settled</span>
                            @elseif($v->isOverdue())
                                <span class="badge badge-status bg-danger text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>
                            @else
                                <span class="badge badge-status bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>
                            @endif
                        </div>
                        <div style="font-size:.88rem;color:#f1f5f9;font-weight:500;">
                            <strong>Motorist:</strong> {{ $v->violator?->full_name ?: 'Unknown' }}
                            @if($v->vehicle_plate_number)
                                · <i class="bi bi-car-front-fill me-1" style="color:#60a5fa;"></i>{{ $v->vehicle_plate_number }}
                            @endif
                        </div>
                        <div style="font-size:.8rem;color:#cbd5e1;" class="mt-1">
                            {{ $v->violationType?->name }} · {{ $v->date_of_violation?->format('M d, Y') }}
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="fw-800 text-warning mb-2" style="font-size:1.15rem;">
                            ₱{{ number_format($v->balanceRemaining(), 2) }}
                        </div>
                        @if($v->isSettled())
                            @if($v->latestPayment)
                                <a href="{{ route('online-payment.receipt', $v->latestPayment) }}" class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size:.8rem;font-weight:600;">
                                    <i class="bi bi-receipt me-1"></i> View Receipt
                                </a>
                            @endif
                        @else
                            <a href="{{ route('online-payment.checkout', $v->ticket_number) }}" class="btn btn-sm btn-pay rounded-pill px-3 py-1" style="font-size:.84rem;">
                                Pay Now <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color: #e2e8f0;">
                    <i class="bi bi-search" style="font-size: 2.5rem; opacity: .7; color:#60a5fa;"></i>
                    <div class="mt-2 font-weight-bold" style="font-size:1rem;color:#fff;">No citations found matching "{{ $searchQuery }}"</div>
                    <div class="small mt-1" style="color:#cbd5e1;">Please double-check your Ticket Number, License Number, or Plate Number.</div>
                </div>
            @endforelse
        </div>
        @endif

        {{-- Footer --}}
        <div class="text-center mt-4" style="font-size:.85rem;color:#e2e8f0;font-weight:500;">
            Traffic Violation & Incident Record System (TVIRS) &copy; {{ date('Y') }}
            <br>
            <a href="{{ route('home') }}" class="text-decoration-none fw-700" style="color:#60a5fa;">Back to Main System</a>
        </div>
    </div>

</body>
</html>
