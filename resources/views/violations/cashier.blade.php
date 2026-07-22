@extends('layouts.app')
@section('title', 'Cashier Portal')

@section('content')
<div class="container-fluid py-4" style="max-width: 1000px; margin: 0 auto;">
    
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-800 text-stone-900 mb-1" style="color: #1c1917; font-family: 'Instrument Sans', sans-serif;">Cashier Payment Portal</h4>
            <p class="text-muted mb-0" style="font-size: .85rem;">Scan QR code or enter citation ticket number to collect payment.</p>
        </div>
        <div class="badge" style="background-color: #dcfce7; color: #15803d; font-size: .85rem; font-weight: 700; padding: .5rem 1rem; border-radius: 9999px;">
            <i class="bi bi-wallet2 me-1"></i> Cashier Session Active
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 12px; background-color: #f0fdf4; color: #15803d;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 12px; background-color: #fef2f2; color: #b91c1c;">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Search Ticket Card --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; overflow: hidden; border: 1px solid #e7e2db;">
        <div class="card-body p-4" style="background-color: #fff;">
            <form method="GET" action="{{ route('violations.cashier') }}">
                <label class="form-label fw-700" style="font-size: .9rem; color: #44403c;">Scan or Enter Ticket Number</label>
                <div class="input-group input-group-lg" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <span class="input-group-text border-end-0" style="background-color: #fcfbf9; border-color: #d6d3d1;">
                        <i class="bi bi-qr-code-scan text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 border-end-0" 
                           placeholder="Scan QR / Enter Ticket (e.g., TVIRS-CEB-BAL-...)" 
                           value="{{ $search }}" style="background-color: #fcfbf9; border-color: #d6d3d1; font-family: ui-monospace, monospace; font-weight: 600;" required autofocus>
                    <button type="submit" class="btn fw-700 text-white" style="background: linear-gradient(135deg, #1d4ed8, #1e40af); border-color: #1d4ed8; padding-left: 2rem; padding-right: 2rem;">
                        <i class="bi bi-search me-1"></i> Retrieve Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($search)
        @if($violation)
            {{-- Ticket Found --}}
            <div class="row g-4">
                
                {{-- Left Side: Ticket Details --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; overflow: hidden; border: 1px solid #e7e2db;">
                        <div class="card-header border-0 p-4 d-flex align-items-center justify-content-between" style="background-color: #faf9f6; border-bottom: 1px solid #e7e2db !important;">
                            <div>
                                <span class="text-muted uppercase fw-700" style="font-size: .75rem; letter-spacing: .05em; display: block;">TICKET DETAILS</span>
                                <h5 class="fw-800 mb-0" style="color: #1c1917;">{{ $violation->ticket_number ?: '#' . $violation->id }}</h5>
                            </div>
                            <div>
                                @php
                                    $isOverdue = $violation->isOverdue();
                                    $displayStatus = $isOverdue ? 'overdue' : $violation->status;
                                    $statusPillCls = [
                                        'overdue' => 'background-color: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5;',
                                        'pending' => 'background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                                        'partial' => 'background-color: #fff7ed; color: #c2410c; border: 1px solid #fdba74;',
                                        'settled' => 'background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;',
                                    ][$displayStatus] ?? 'background-color: #f5f5f4; color: #57534e;';
                                @endphp
                                <span class="badge px-3 py-2 fw-700" style="border-radius: 20px; font-size: .8rem; {{ $statusPillCls }}">
                                    {{ ucfirst($displayStatus) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4" style="background-color: #fff;">
                            
                            {{-- Fine Summary --}}
                            <div class="p-3 mb-4" style="background-color: #fef2f2; border-radius: 12px; border: 1px solid #fca5a5;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted fw-600" style="font-size: .75rem; display: block;">{{ $violation->status === 'partial' ? 'BALANCE DUE' : 'TOTAL AMOUNT DUE' }}</span>
                                        <strong style="font-size: 1.5rem; color: #b91c1c;">₱{{ number_format($violation->balanceRemaining(), 2) }}</strong>
                                    </div>
                                    <i class="bi bi-cash-coin text-danger fs-1"></i>
                                </div>
                                <div class="mt-2 pt-2" style="border-top: 1px dashed #fca5a5; font-size: .78rem; color: #7f1d1d;">
                                    Base fine: ₱{{ number_format($violation->violationType->fine_amount, 2) }}
                                    @if($violation->isOverdue() && $violation->latePenaltyAmount() > 0)
                                        <span class="ms-1">+ Late penalty: ₱{{ number_format($violation->latePenaltyAmount(), 2) }}</span>
                                    @endif
                                    @if($violation->totalAmountPaid() > 0)
                                        <span class="ms-1">· Already paid: ₱{{ number_format($violation->totalAmountPaid(), 2) }}</span>
                                    @endif
                                </div>
                            </div>

                            <table class="table table-borderless align-middle mb-0" style="font-size: .88rem;">
                                <tbody>
                                    <tr style="border-bottom: 1px dashed #f5f0e8;">
                                        <td class="text-muted py-2" style="width: 40%;">Violator</td>
                                        <td class="fw-700 text-stone-900 py-2">{{ $violation->violator?->full_name }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px dashed #f5f0e8;">
                                        <td class="text-muted py-2">License Number</td>
                                        <td class="fw-600 py-2" style="font-family: ui-monospace, monospace;">{{ $violation->violator?->license_number ?: 'Not on file' }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px dashed #f5f0e8;">
                                        <td class="text-muted py-2">Violation Type</td>
                                        <td class="fw-600 py-2">{{ $violation->violationType?->name }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px dashed #f5f0e8;">
                                        <td class="text-muted py-2">Date &amp; Time</td>
                                        <td class="fw-600 py-2">{{ $violation->date_of_violation->format('M d, Y') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px dashed #f5f0e8;">
                                        <td class="text-muted py-2">Location</td>
                                        <td class="fw-600 py-2">{{ $violation->location ?: '—' }}</td>
                                    </tr>
                                    @if($violation->vehicle_plate || $violation->vehicle?->plate_number)
                                    <tr style="border-bottom: 1px dashed #f5f0e8;">
                                        <td class="text-muted py-2">Vehicle Plate</td>
                                        <td class="fw-600 py-2" style="font-family: ui-monospace, monospace;">{{ $violation->vehicle?->plate_number ?? $violation->vehicle_plate }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Settle Action or Receipt Details --}}
                <div class="col-md-6">
                    @if($violation->payments->isNotEmpty())
                        {{-- Payment History --}}
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px; overflow: hidden; border: 1px solid #e7e2db;">
                            <div class="card-header border-0 p-3" style="background-color: #faf9f6; border-bottom: 1px solid #e7e2db !important;">
                                <span class="text-muted uppercase fw-700" style="font-size: .72rem; letter-spacing: .05em;">PAYMENT HISTORY</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm align-middle mb-0" style="font-size: .8rem;">
                                    <tbody>
                                        @foreach($violation->payments->sortByDesc('paid_at') as $p)
                                        <tr>
                                            <td class="ps-3 py-2 fw-700" style="font-family: ui-monospace, monospace;">{{ $p->or_number }}</td>
                                            <td class="py-2 text-success fw-700">₱{{ number_format($p->amount_paid, 2) }}</td>
                                            <td class="py-2 text-muted">{{ $p->paid_at?->format('M d, Y g:i A') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    @if($violation->status === 'settled')
                        {{-- Settle Details (Paid State) --}}
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; overflow: hidden; border: 1px solid #e7e2db;">
                            <div class="card-header border-0 p-4" style="background-color: #f0fdf4; border-bottom: 1px solid #bbf7d0 !important;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-patch-check-fill text-success fs-4"></i>
                                    <div>
                                        <h5 class="fw-800 text-success mb-0">Receipt Details</h5>
                                        <small style="color: #16a34a;">This citation has been successfully settled.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4" style="background-color: #fff;">
                                <table class="table table-borderless align-middle mb-4" style="font-size: .88rem;">
                                    <tbody>
                                        <tr style="border-bottom: 1px dashed #f0fdf4;">
                                            <td class="text-muted py-2" style="width: 40%;">OR Number</td>
                                            <td class="fw-700 text-stone-900 py-2" style="font-family: ui-monospace, monospace;">{{ $violation->or_number }}</td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #f0fdf4;">
                                            <td class="text-muted py-2">Payment Method</td>
                                            <td class="fw-700 py-2">
                                                @php
                                                    $pmDetails = [
                                                        'cash'  => ['label'=>'Cash',         'icon'=>'bi-cash-stack',      'cls'=>'bg-success text-white'],
                                                        'gcash' => ['label'=>'GCash',        'icon'=>'bi-phone-fill',       'cls'=>'bg-primary text-white'],
                                                        'maya'  => ['label'=>'Maya',         'icon'=>'bi-credit-card-fill', 'cls'=>'bg-purple text-white'],
                                                        'bank'  => ['label'=>'Bank Transfer','icon'=>'bi-bank2',            'cls'=>'bg-warning text-dark'],
                                                        'other' => ['label'=>'Other',        'icon'=>'bi-three-dots',       'cls'=>'bg-secondary text-white'],
                                                    ][$violation->payment_method] ?? ['label'=>ucfirst($violation->payment_method ?: 'Cash'), 'icon'=>'bi-credit-card', 'cls'=>'bg-light text-dark'];
                                                @endphp
                                                <span class="badge {{ $pmDetails['cls'] }} px-2.5 py-1" style="font-size: .75rem;">
                                                    <i class="{{ $pmDetails['icon'] }} me-1"></i> {{ $pmDetails['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #f0fdf4;">
                                            <td class="text-muted py-2">Cashier Name</td>
                                            <td class="fw-600 py-2">{{ $violation->cashier_name }}</td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #f0fdf4;">
                                            <td class="text-muted py-2">Date Paid</td>
                                            <td class="fw-600 py-2">{{ $violation->settled_at ? $violation->settled_at->format('M d, Y  g:i A') : '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                @if($violation->receipt_photo)
                                    <div class="text-center p-2 border" style="border-radius: 12px; background-color: #faf9f6;">
                                        <span class="text-muted d-block mb-2 fw-700" style="font-size: .72rem; letter-spacing: .05em;">ATTACHED RECEIPT</span>
                                        <a href="{{ uploaded_file_url($violation->receipt_photo) }}" target="_blank">
                                            <img src="{{ uploaded_file_url($violation->receipt_photo) }}" alt="Receipt" 
                                                 style="max-width: 100%; max-height: 180px; border-radius: 8px; object-fit: contain; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                                        </a>
                                    </div>
                                @endif
                                
                                <div class="mt-4 text-center">
                                    <a href="{{ route('violations.print', $violation) }}" target="_blank" class="btn btn-outline-success fw-700 w-100" style="border-radius: 10px;">
                                        <i class="bi bi-printer me-1"></i> Print Violation Record / Invoice
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Settlement Form --}}
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; overflow: hidden; border: 1px solid #e7e2db;">
                            <div class="card-header border-0 p-4" style="background-color: #faf9f6; border-bottom: 1px solid #e7e2db !important;">
                                <span class="text-muted uppercase fw-700" style="font-size: .75rem; letter-spacing: .05em; display: block;">ACTION</span>
                                <h5 class="fw-800 mb-0" style="color: #1c1917;">Collect Payment</h5>
                            </div>
                            <div class="card-body p-4" style="background-color: #fff;">
                                <form action="{{ route('violations.settle', $violation) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-700" style="font-size: .82rem;">Official Receipt (OR) Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-hash text-muted"></i></span>
                                            <input type="text" name="or_number" class="form-control" placeholder="e.g., OR-891047" required maxlength="50" style="font-family: ui-monospace, monospace;">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-700" style="font-size: .82rem;">Cashier Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-badge text-muted"></i></span>
                                            <input type="text" name="cashier_name" class="form-control" placeholder="Cashier's full name" value="{{ Auth::user()->name }}" required maxlength="150">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-700" style="font-size: .82rem;">Payment Method <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach([
                                                'cash'  => ['label'=>'Cash',         'icon'=>'bi-cash-stack',      'color'=>'#15803d'],
                                                'gcash' => ['label'=>'GCash',        'icon'=>'bi-phone-fill',       'color'=>'#0069f5'],
                                                'maya'  => ['label'=>'Maya',         'icon'=>'bi-credit-card-fill', 'color'=>'#6d28d9'],
                                                'bank'  => ['label'=>'Bank Transfer','icon'=>'bi-bank2',            'color'=>'#b45309'],
                                                'other' => ['label'=>'Other',        'icon'=>'bi-three-dots',       'color'=>'#475569'],
                                            ] as $val => $opt)
                                            <label style="cursor:pointer;">
                                                <input type="radio" name="payment_method" value="{{ $val }}" class="d-none settle-pm-radio" {{ $val === 'cash' ? 'checked' : '' }}>
                                                <span class="settle-pm-pill" data-color="{{ $opt['color'] }}"
                                                    style="display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .75rem;border-radius:20px;font-size:.78rem;font-weight:700;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;transition:all .15s;user-select:none;">
                                                    <i class="bi {{ $opt['icon'] }}"></i> {{ $opt['label'] }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-700" style="font-size: .82rem;">Amount Received <span class="text-muted" style="font-weight: 400;">(optional — leave blank to collect the full balance)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" name="amount_paid" class="form-control" step="0.01" min="0.01"
                                                   max="{{ $violation->balanceRemaining() }}"
                                                   placeholder="Full balance: {{ number_format($violation->balanceRemaining(), 2) }}">
                                        </div>
                                        <small style="font-size: .72rem; color: #a8a29e;">Enter a smaller amount to record a partial payment.</small>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-700" style="font-size: .82rem;">Receipt Image <span class="text-muted" style="font-weight: 400;">(optional)</span></label>
                                        <input type="file" name="receipt_photo" id="cashier_receipt_photo" class="form-control" accept="image/*">
                                        <div id="receiptPreviewWrap" class="d-none mt-3 text-center">
                                            <img id="receiptPreview" src="" alt="Receipt Preview" style="max-width: 100%; max-height: 150px; border-radius: 8px; object-fit: contain; border: 1px solid #d6d3d1;">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn text-white fw-700 w-100 py-2.5" style="background: linear-gradient(135deg, #15803d, #166534); border: none; border-radius: 10px;">
                                        <i class="bi bi-check2-circle me-1"></i> Record Payment
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        @else
            {{-- Ticket Not Found --}}
            <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 16px; border: 1px solid #e7e2db;">
                <div class="card-body">
                    <i class="bi bi-search-heart text-muted display-4 mb-3 d-block"></i>
                    <h5 class="fw-800 text-stone-900 mb-2">No Ticket Found</h5>
                    <p class="text-muted mb-0" style="max-width: 400px; margin: 0 auto; font-size: .88rem;">We couldn't find a violation ticket matching "<strong>{{ $search }}</strong>". Please verify the spelling or search using another Ticket ID.</p>
                </div>
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 16px; border: 1px solid #e7e2db;">
            <div class="card-body">
                <i class="bi bi-qr-code text-muted display-3 mb-3 d-block"></i>
                <h5 class="fw-800 text-stone-900 mb-2">Ready to Scan</h5>
                <p class="text-muted mb-0" style="max-width: 400px; margin: 0 auto; font-size: .88rem;">Enter a Ticket ID above or scan the QR code printed on the citation ticket to begin collecting payments.</p>
            </div>
        </div>
    @endif

    {{-- Pending Tickets List --}}
    @if(isset($pendingTickets) && $pendingTickets->count() > 0)
        <div class="mt-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-clock-history text-muted fs-5"></i>
                <h5 class="fw-800 text-stone-900 mb-0" style="font-family: 'Instrument Sans', sans-serif;">Unpaid / Pending Citation Tickets</h5>
            </div>
            <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; border: 1px solid #e7e2db;">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size: .88rem;">
                        <thead style="background-color: #faf9f6; border-bottom: 1px solid #e7e2db;">
                            <tr>
                                <th class="p-3 fw-700 text-muted" style="width: 25%;">Ticket Number</th>
                                <th class="p-3 fw-700 text-muted" style="width: 30%;">Violator</th>
                                <th class="p-3 fw-700 text-muted" style="width: 25%;">Violation Type</th>
                                <th class="p-3 fw-700 text-muted" style="width: 10%;">Fine</th>
                                <th class="p-3 fw-700 text-muted text-center" style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingTickets as $ticket)
                                <tr style="border-bottom: 1px solid #f5f0e8;">
                                    <td class="p-3 fw-700" style="font-family: ui-monospace, monospace;">{{ $ticket->ticket_number }}</td>
                                    <td class="p-3 fw-600 text-stone-900">{{ $ticket->violator?->full_name }}</td>
                                    <td class="p-3 text-muted">{{ $ticket->violationType?->name }}</td>
                                    <td class="p-3 fw-700 text-danger">₱{{ number_format($ticket->violationType?->fine_amount, 2) }}</td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('violations.cashier', ['search' => $ticket->ticket_number]) }}" 
                                           class="btn btn-sm text-white fw-700" 
                                           style="background: linear-gradient(135deg, #1d4ed8, #1e40af); border-radius: 8px; font-size: .75rem; padding: .35rem .75rem;">
                                            <i class="bi bi-wallet2 me-1"></i> Process
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    function updatePaymentPills() {
        document.querySelectorAll('.settle-pm-radio').forEach(function(radio) {
            var pill = radio.nextElementSibling;
            var color = pill.dataset.color;
            if (radio.checked) {
                pill.style.background = color;
                pill.style.borderColor = color;
                pill.style.color = '#fff';
            } else {
                pill.style.background = '#fff';
                pill.style.borderColor = '#e2e8f0';
                pill.style.color = '#64748b';
            }
        });
    }

    document.querySelectorAll('.settle-pm-radio').forEach(function(radio) {
        radio.addEventListener('change', updatePaymentPills);
    });

    updatePaymentPills();

    var imgInput = document.getElementById('cashier_receipt_photo');
    if (imgInput) {
        imgInput.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('receiptPreview').src = e.target.result;
                document.getElementById('receiptPreviewWrap').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endpush
@endsection
