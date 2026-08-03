<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>VIP Online Cashier & Payment Portal - TVIRS</title>
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
            display: flex;
            flex-direction: column;
        }
        .casino-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.25);
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        .gold-badge {
            background: linear-gradient(135deg, #fbbf24, #d97706);
            color: #0f172a;
            font-weight: 900;
            padding: .4rem .9rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
            letter-spacing: .05em;
        }
        .search-input-casino {
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(251, 191, 36, 0.3);
            color: #ffffff;
            border-radius: 16px;
            padding: .9rem 1.2rem;
            font-size: 1.05rem;
            font-weight: 600;
            transition: all .2s ease;
        }
        .search-input-casino:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #fbbf24;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.25);
        }
        .search-input-casino::placeholder {
            color: #94a3b8;
        }
        .btn-casino-gold {
            background: linear-gradient(135deg, #fbbf24, #d97706);
            border: none;
            color: #0f172a;
            font-weight: 900;
            border-radius: 16px;
            padding: .9rem 1.75rem;
            font-size: 1rem;
            box-shadow: 0 6px 22px rgba(251, 191, 36, 0.45);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-casino-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.65);
            color: #0f172a;
        }
        .result-item-casino {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            transition: all .2s ease;
        }
        .result-item-casino:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(251, 191, 36, 0.4);
        }
    </style>
</head>
<body>

    <div class="container my-auto py-5" style="max-width: 600px;">
        {{-- Header --}}
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:76px;height:76px;border-radius:24px;background:linear-gradient(135deg,#fbbf24,#d97706);box-shadow:0 12px 30px rgba(251,191,36,0.5);">
                <i class="bi bi-currency-exchange" style="font-size: 2.2rem; color: #0f172a;"></i>
            </div>
            <div class="mb-2">
                <span class="gold-badge text-uppercase" style="font-size:.72rem;"><i class="bi bi-lightning-charge-fill me-1"></i> EXPRESS VIP CASHIER PORTAL</span>
            </div>
            <h2 class="fw-900 tracking-tight text-white mb-1">Traffic Citation Online Payment</h2>
            <p style="font-size: .95rem; color: #cbd5e1; font-weight: 500;">
                Instant online settlement via GCash, Maya, QR Ph, or Credit/Debit Card.
            </p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-4 mb-3 border-0 shadow" style="background: rgba(220, 38, 38, 0.9); color: #fff;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Search Form Card --}}
        <div class="casino-card p-4 mb-4">
            <form action="{{ route('online-payment.search') }}" method="POST">
                @csrf
                <label class="form-label fw-800 text-uppercase tracking-wider mb-2" style="font-size:.82rem;letter-spacing:.08em;color:#fbbf24;">
                    <i class="bi bi-search me-1"></i> FIND CITATION TICKET
                </label>
                <div class="input-group mb-3">
                    <input type="text" name="search" class="form-control search-input-casino"
                           placeholder="Ticket # (e.g. CEB-2026-00001), License #, or Plate #"
                           value="{{ $searchQuery }}" required autofocus>
                    <button class="btn btn-casino-gold px-4" type="submit">
                        Search <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center justify-content-between" style="font-size:.82rem;color:#cbd5e1;font-weight:600;">
                    <span><i class="bi bi-qr-code-scan me-1" style="color:#fbbf24;"></i> Scan printed ticket QR</span>
                    <span><i class="bi bi-shield-lock-fill me-1" style="color:#34d399;"></i> 256-Bit Encrypted</span>
                </div>
            </form>
        </div>

        {{-- Search Results --}}
        @if(!empty($searchQuery))
        <div class="casino-card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-800 text-white mb-0" style="font-size:.95rem;">Search Results for "{{ $searchQuery }}"</h6>
                <span class="badge bg-warning text-dark font-monospace fw-800 px-3 py-1 rounded-pill" style="font-size:.78rem;">{{ $violations->count() }} Found</span>
            </div>

            @forelse($violations as $v)
                <div class="result-item-casino p-3 mb-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-900 text-white" style="font-size: 1.1rem;">{{ $v->ticket_number }}</span>
                            @if($v->isSettled())
                                <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-800" style="font-size:.7rem;"><i class="bi bi-check-circle-fill me-1"></i>Settled</span>
                            @elseif($v->isOverdue())
                                <span class="badge bg-danger text-white px-3 py-1 rounded-pill fw-800" style="font-size:.7rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-800" style="font-size:.7rem;"><i class="bi bi-clock-history me-1"></i>Pending</span>
                            @endif
                        </div>
                        <div style="font-size:.88rem;color:#f1f5f9;font-weight:500;">
                            <strong>Motorist:</strong> {{ $v->violator?->full_name ?: 'Unknown' }}
                            @php $plateNum = $v->vehicle_plate ?: $v->vehicle?->plate_number; @endphp
                            @if($plateNum)
                                · <i class="bi bi-car-front-fill me-1" style="color:#fbbf24;"></i>{{ $plateNum }}
                            @endif
                        </div>
                        <div style="font-size:.8rem;color:#cbd5e1;" class="mt-1">
                            {{ $v->violationType?->name }} · {{ $v->date_of_violation?->format('M d, Y') }}
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="fw-900 text-warning mb-2" style="font-size:1.2rem;">
                            ₱{{ number_format($v->balanceRemaining(), 2) }}
                        </div>
                        @if($v->isSettled())
                            @if($v->latestPayment)
                                <a href="{{ route('online-payment.receipt', $v->latestPayment) }}" class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size:.8rem;font-weight:700;">
                                    <i class="bi bi-receipt me-1"></i> Receipt
                                </a>
                            @endif
                        @else
                            <a href="{{ route('online-payment.checkout', $v->ticket_number) }}" class="btn btn-sm btn-casino-gold rounded-pill px-3 py-1" style="font-size:.84rem;">
                                Deposit & Pay <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4" style="color: #cbd5e1;">
                    <i class="bi bi-search" style="font-size: 2.5rem; opacity: .7; color:#fbbf24;"></i>
                    <div class="mt-2 font-weight-bold" style="font-size:1rem;color:#fff;">No citations found matching "{{ $searchQuery }}"</div>
                    <div class="small mt-1" style="color:#94a3b8;">Please check your Ticket Number, License Number, or Plate Number.</div>
                </div>
            @endforelse
        </div>
        @endif

        {{-- Footer --}}
        <div class="text-center mt-4" style="font-size:.85rem;color:#cbd5e1;font-weight:500;">
            Traffic Violation Record System (TVIRS) &copy; {{ date('Y') }}
            <br>
            <a href="{{ route('home') }}" class="text-decoration-none fw-700" style="color:#fbbf24;">Back to System</a>
        </div>
    </div>

</body>
</html>
