@extends('layouts.app')
@section('title', 'Cashier')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Cashier</li>
@endsection

@section('content')
<div class="container-fluid py-4" style="max-width: 1000px; margin: 0 auto;">
    
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div style="flex: 1 1 auto; max-width: 760px; min-width: 0;">
            <h4 class="fw-800 text-stone-900 mb-1" style="color: #1c1917; font-family: 'Instrument Sans', sans-serif;">Cashier Payment</h4>
            <p class="text-muted mb-0" style="font-size: .85rem; line-height: 1.5;">Enter citation ticket number, violator name, license, or plate number to collect payment.</p>
        </div>
        <div class="flex-shrink-0">
            <span class="badge d-inline-flex align-items-center gap-1.5 shadow-sm" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: .85rem; font-weight: 700; padding: .55rem 1.1rem; border-radius: 9999px; white-space: nowrap;">
                <i class="bi bi-wallet2"></i> <span>Cashier Session Active</span>
            </span>
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
                <label class="form-label fw-700" style="font-size: .9rem; color: #44403c;">Search Violation</label>
                <div class="input-group input-group-lg" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <input type="text" id="cashierSearchInput" name="search" class="form-control border-end-0" 
                           placeholder="Ticket # / Violator Name / License # / Plate #" 
                           value="{{ $search }}" style="background-color: #fcfbf9; border-color: #d6d3d1; font-family: ui-monospace, monospace; font-weight: 600;" autofocus autocomplete="off">
                    <button type="submit" class="btn fw-700 text-white" style="background: linear-gradient(135deg, #1d4ed8, #1e40af); border-color: #1d4ed8; padding-left: 2rem; padding-right: 2rem;">
                        <i class="bi bi-search me-1"></i> Retrieve Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="searchResultsArea">
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
                            <div class="card-header border-0 p-3 d-flex justify-content-between align-items-center" style="background-color: #faf9f6; border-bottom: 1px solid #e7e2db !important;">
                                <span class="text-muted uppercase fw-700" style="font-size: .72rem; letter-spacing: .05em;">PAYMENT HISTORY</span>
                                <span class="badge bg-light text-dark">{{ $violation->payments->count() }} transaction(s)</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm align-middle mb-0" style="font-size: .8rem;">
                                    <tbody>
                                        @foreach($violation->payments->sortByDesc('paid_at') as $p)
                                        <tr style="{{ $p->isVoided() ? 'text-decoration: line-through; opacity: 0.6; background-color: #fef2f2;' : '' }}">
                                            <td class="ps-3 py-2 fw-700" style="font-family: ui-monospace, monospace;">
                                                {{ $p->or_number }}
                                                @if($p->isVoided())
                                                    <span class="badge bg-danger text-white ms-1" style="font-size: 0.65rem; text-decoration: none;">VOID</span>
                                                @endif
                                            </td>
                                            <td class="py-2 {{ $p->isVoided() ? 'text-muted' : 'text-success' }} fw-700">₱{{ number_format($p->amount_paid, 2) }}</td>
                                            <td class="py-2 text-muted" style="font-size: .75rem;">{{ $p->paid_at?->format('M d, Y g:i A') }}</td>
                                            <td class="pe-3 py-2 text-end">
                                                @if(!$p->isVoided())
                                                    <a href="{{ route('payments.receipt', [$violation, $p]) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-1.5" title="Print Receipt" style="font-size: 0.7rem;">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                    @can('void', $p)
                                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1.5 ms-1" title="Void Payment" style="font-size: 0.7rem;"
                                                                onclick="openVoidModal('{{ route('payments.void', $p) }}', '{{ $p->or_number }}')">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    @endcan
                                                @endif
                                            </td>
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
                                                <span class="badge bg-success text-white px-2.5 py-1" style="font-size: .75rem;">
                                                    <i class="bi bi-credit-card me-1"></i> {{ ucfirst($violation->payment_method ?: 'Cash') }}
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
                                
                                <div class="mt-4 d-flex gap-2">
                                    <a href="{{ route('violations.print', $violation) }}" target="_blank" class="btn btn-outline-success fw-700 flex-grow-1" style="border-radius: 10px;">
                                        <i class="bi bi-printer me-1"></i> Print Violation Record
                                    </a>
                                    @if($violation->latestPayment)
                                        <a href="{{ route('payments.receipt', [$violation, $violation->latestPayment]) }}" target="_blank" class="btn btn-success fw-700 flex-grow-1" style="border-radius: 10px;">
                                            <i class="bi bi-receipt me-1"></i> Print Receipt
                                        </a>
                                    @endif
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
                                
                                {{-- #16 Line-Item Penalty Breakdown --}}
                                <div class="p-3 mb-3 bg-light rounded-3 border" style="font-size: 0.82rem;">
                                    <div class="fw-700 text-muted mb-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Payment Breakdown</div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Base Fine:</span>
                                        <strong>₱{{ number_format($violation->violationType->fine_amount, 2) }}</strong>
                                    </div>
                                    @if($violation->isOverdue() && $violation->latePenaltyAmount() > 0)
                                        <div class="d-flex justify-content-between mb-1 text-danger">
                                            <span>Late Penalty:</span>
                                            <strong>+ ₱{{ number_format($violation->latePenaltyAmount(), 2) }}</strong>
                                        </div>
                                    @endif
                                    @if($violation->totalAmountPaid() > 0)
                                        <div class="d-flex justify-content-between mb-1 text-success">
                                            <span>Previous Payments:</span>
                                            <strong>- ₱{{ number_format($violation->totalAmountPaid(), 2) }}</strong>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between pt-2 border-top fw-800 text-stone-900" style="font-size: 0.95rem;">
                                        <span>Balance Due:</span>
                                        <span class="text-danger">₱{{ number_format($violation->balanceRemaining(), 2) }}</span>
                                    </div>
                                </div>

                                <form id="settlementForm" action="{{ route('violations.settle', $violation) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-700" style="font-size: .82rem;">Official Receipt (OR) Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-hash text-muted"></i></span>
                                            <input type="text" name="or_number" id="input_or_number" class="form-control"
                                                   value="{{ old('or_number', $violation->suggestOrNumber()) }}"
                                                   placeholder="e.g., OR-891047" required maxlength="50" style="font-family: ui-monospace, monospace;">
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
                                        <label class="form-label fw-700" style="font-size: .82rem;">Amount Received <span class="text-muted" style="font-weight: 400;">(optional — leave blank to collect full balance)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" name="amount_paid" id="input_amount_paid" class="form-control" step="0.01" min="0.01"
                                                   max="{{ $violation->balanceRemaining() }}"
                                                   placeholder="Full balance: {{ number_format($violation->balanceRemaining(), 2) }}">
                                        </div>
                                    </div>

                                    {{-- #10 Cash Change Calculator --}}
                                    <div id="cashCalcBox" class="p-3 mb-3 bg-emerald-50 rounded-3 border border-emerald-200" style="background-color: #f0fdf4; border-color: #bbf7d0;">
                                        <label class="form-label fw-700 text-emerald-900" style="font-size: .82rem; color: #166534;">Cash Tendered Calculator</label>
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-white">Cash Tendered ₱</span>
                                            <input type="number" id="cashTenderedInput" class="form-control" step="0.01" min="0" placeholder="e.g., 1000">
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center" style="font-size: 0.88rem;">
                                            <span class="fw-600 text-emerald-800" style="color: #166534;">Change Due:</span>
                                            <strong id="changeDueDisplay" class="fs-5 text-success">₱0.00</strong>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-700" style="font-size: .82rem;">Receipt Image <span class="text-muted" style="font-weight: 400;">(optional)</span></label>
                                        <input type="file" name="receipt_photo" id="cashier_receipt_photo" class="form-control" accept="image/*">
                                        <div id="receiptPreviewWrap" class="d-none mt-3 text-center">
                                            <img id="receiptPreview" src="" alt="Receipt Preview" style="max-width: 100%; max-height: 150px; border-radius: 8px; object-fit: contain; border: 1px solid #d6d3d1;">
                                        </div>
                                    </div>

                                    {{-- #3 Trigger Confirmation Modal --}}
                                    <button type="button" onclick="showConfirmModal()" class="btn text-white fw-700 w-100 py-2.5" style="background: linear-gradient(135deg, #15803d, #166534); border: none; border-radius: 10px;">
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
                    <h5 class="fw-800 text-stone-900 mb-2">No Violation Found</h5>
                    <p class="text-muted mb-0" style="max-width: 400px; margin: 0 auto; font-size: .88rem;">We couldn't find a record matching "<strong>{{ $search }}</strong>". Please verify the Ticket #, Violator Name, License #, or Plate #.</p>
                </div>
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 16px; border: 1px solid #e7e2db;">
            <div class="card-body">
                <i class="bi bi-search text-muted display-3 mb-3 d-block"></i>
                <h5 class="fw-800 text-stone-900 mb-2">Ready to Process Payment</h5>
                <p class="text-muted mb-0" style="max-width: 400px; margin: 0 auto; font-size: .88rem;">Enter a Ticket ID or search by Violator Name / License # / Plate # above to begin collecting payments.</p>
            </div>
        </div>
    @endif
    </div>{{-- /#searchResultsArea --}}

    {{-- Pending Tickets List --}}
    @if(isset($pendingTickets) && $pendingTickets->count() > 0)
        <div class="mt-5">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; border: 1.5px solid #fde68a !important; background: #fffbeb;">
                <div class="card-header border-0 px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%); border-bottom: 1.5px solid #fde68a !important;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle" style="width:34px;height:34px;border:1px solid #fcd34d;color:#d97706;">
                            <i class="bi bi-clock-history"></i>
                        </span>
                        <div>
                            <h5 class="fw-800 mb-0" style="font-family: 'Instrument Sans', sans-serif; color: #92400e; font-size: .95rem;">Unpaid / Pending Citation Tickets</h5>
                            <div style="font-size:.72rem; color:#b45309;">Motorist citations awaiting cashier payment or settlement</div>
                        </div>
                    </div>
                    <span class="badge fw-700 font-monospace" style="background:#fef3c7; color:#92400e; border:1px solid #fcd34d; padding:.4rem .75rem; border-radius:8px;">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $pendingTickets->total() }} Total Pending
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size: .88rem; background: #fff;">
                        <thead style="background-color: #faf9f6; border-bottom: 1px solid #e7e2db;">
                            <tr>
                                <th class="p-3 fw-700 text-muted" style="width: 25%;">Ticket Number</th>
                                <th class="p-3 fw-700 text-muted" style="width: 25%;">Violator</th>
                                <th class="p-3 fw-700 text-muted" style="width: 20%;">Violation Type</th>
                                <th class="p-3 fw-700 text-muted" style="width: 10%;">Balance Due</th>
                                <th class="p-3 fw-700 text-muted" style="width: 10%;">Status</th>
                                <th class="p-3 fw-700 text-muted text-center" style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingTickets as $ticket)
                                <tr style="border-bottom: 1px solid #f5f0e8;">
                                    <td class="p-3 fw-700" style="font-family: ui-monospace, monospace;">{{ $ticket->ticket_number }}</td>
                                    <td class="p-3 fw-600 text-stone-900">{{ $ticket->violator?->full_name }}</td>
                                    <td class="p-3 text-muted">{{ $ticket->violationType?->name }}</td>
                                    <td class="p-3 fw-700 text-danger">₱{{ number_format($ticket->balanceRemaining(), 2) }}</td>
                                    <td class="p-3">
                                        @if($ticket->isOverdue())
                                            <span class="badge" style="background:#fef2f2;color:#b91c1c;">Overdue</span>
                                        @elseif($ticket->status === 'partial')
                                            <span class="badge" style="background:#fff7ed;color:#c2410c;">Partial</span>
                                        @else
                                            <span class="badge" style="background:#fffbeb;color:#b45309;">Pending</span>
                                        @endif
                                    </td>
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
                {{-- #13/#21 Pagination Links --}}
                <div class="p-3 border-top bg-light">
                    {{ $pendingTickets->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    @endif

</div>

{{-- #3 Confirmation Modal --}}
<div class="modal fade" id="paymentConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-success text-white border-0 p-4">
                <h5 class="modal-title fw-800"><i class="bi bi-shield-check me-2"></i> Confirm Payment Record</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3" style="font-size: 0.88rem;">Please double-check payment details before recording. This transaction will create an official payment entry.</p>
                <div class="p-3 bg-light rounded-3 border" style="font-size: 0.88rem;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">OR Number:</span>
                        <strong id="modal_or_number" class="font-monospace text-dark">—</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Method:</span>
                        <strong id="modal_method" class="text-capitalize">—</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Amount to Collect:</span>
                        <strong id="modal_amount" class="text-success fs-5">₱0.00</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light fw-700" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="submitForm()" class="btn btn-success fw-700 px-4" style="border-radius: 8px;">
                    <i class="bi bi-check-circle me-1"></i> Confirm &amp; Submit
                </button>
            </div>
        </div>
    </div>
</div>

{{-- #8 Void Payment Modal --}}
<div class="modal fade" id="voidPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="voidPaymentForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-danger text-white border-0 p-4">
                    <h5 class="modal-title fw-800"><i class="bi bi-exclamation-triangle me-2"></i> Void Payment OR#<span id="voidOrDisplay"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3" style="font-size: 0.88rem;">Voiding this payment will subtract the amount from the violation's total paid balance. This action will be logged.</p>
                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size: 0.82rem;">Reason for Voiding <span class="text-danger">*</span></label>
                        <textarea name="void_reason" class="form-control" rows="3" placeholder="Specify why this payment is being voided (e.g. duplicate entry, check bounced, wrong ticket)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-700 px-4" style="border-radius: 8px;">
                        <i class="bi bi-x-circle me-1"></i> Void Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const fullBalance = {{ $violation ? $violation->balanceRemaining() : 0 }};

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
        calculateChange();
    }

    // #10 Cash Change Calculator JS
    function calculateChange() {
        const method = document.querySelector('.settle-pm-radio:checked')?.value;
        const calcBox = document.getElementById('cashCalcBox');
        if (method === 'cash') {
            if (calcBox) calcBox.style.display = 'block';
            const amountInput = document.getElementById('input_amount_paid');
            const amountToPay = parseFloat(amountInput?.value) || fullBalance;
            const tendered = parseFloat(document.getElementById('cashTenderedInput')?.value) || 0;
            const change = Math.max(0, tendered - amountToPay);
            document.getElementById('changeDueDisplay').innerText = '₱' + change.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            if (calcBox) calcBox.style.display = 'none';
        }
    }

    document.querySelectorAll('.settle-pm-radio').forEach(function(radio) {
        radio.addEventListener('change', updatePaymentPills);
    });

    document.getElementById('input_amount_paid')?.addEventListener('input', calculateChange);
    document.getElementById('cashTenderedInput')?.addEventListener('input', calculateChange);

    updatePaymentPills();

    // #3 Show Confirmation Modal
    function showConfirmModal() {
        const orInput = document.getElementById('input_or_number');
        if (!orInput.checkValidity()) {
            orInput.reportValidity();
            return;
        }

        const method = document.querySelector('.settle-pm-radio:checked')?.value || 'Cash';
        const amountInput = document.getElementById('input_amount_paid');
        const amount = parseFloat(amountInput?.value) || fullBalance;

        document.getElementById('modal_or_number').innerText = orInput.value;
        document.getElementById('modal_method').innerText = method;
        document.getElementById('modal_amount').innerText = '₱' + amount.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const modal = new bootstrap.Modal(document.getElementById('paymentConfirmModal'));
        modal.show();
    }

    function submitForm() {
        document.getElementById('settlementForm').submit();
    }

    // #8 Open Void Modal
    function openVoidModal(actionUrl, orNumber) {
        document.getElementById('voidPaymentForm').action = actionUrl;
        document.getElementById('voidOrDisplay').innerText = orNumber;
        const modal = new bootstrap.Modal(document.getElementById('voidPaymentModal'));
        modal.show();
    }

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

    // ── Live Search (debounced) ────────────────────────────────────────────────
    (function () {
        const searchInput  = document.getElementById('cashierSearchInput');
        const resultsArea  = document.getElementById('searchResultsArea');
        const cashierUrl   = '{{ route("violations.cashier") }}';
        let debounceTimer  = null;
        let currentRequest = null;

        if (!searchInput) return;

        const fmt = (n) => Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const statusStyles = {
            overdue: 'background-color:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;',
            pending: 'background-color:#fffbeb;color:#b45309;border:1px solid #fde68a;',
            partial: 'background-color:#fff7ed;color:#c2410c;border:1px solid #fdba74;',
            settled: 'background-color:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;',
        };

        function renderLoading() {
            resultsArea.innerHTML = `
                <div class="card border-0 shadow-sm text-center py-4" style="border-radius:16px;border:1px solid #e7e2db;">
                    <div class="card-body">
                        <div class="spinner-border text-primary mb-3" role="status" style="width:2rem;height:2rem;"></div>
                        <p class="text-muted mb-0" style="font-size:.88rem;">Searching…</p>
                    </div>
                </div>`;
        }

        function renderEmpty() {
            resultsArea.innerHTML = `
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius:16px;border:1px solid #e7e2db;">
                    <div class="card-body">
                        <i class="bi bi-search text-muted display-3 mb-3 d-block"></i>
                        <h5 class="fw-800 text-stone-900 mb-2">Ready to Process Payment</h5>
                        <p class="text-muted mb-0" style="max-width:400px;margin:0 auto;font-size:.88rem;">Enter a Ticket ID or search by Violator Name / License # / Plate # above to begin collecting payments.</p>
                    </div>
                </div>`;
        }

        function renderNotFound(search) {
            resultsArea.innerHTML = `
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius:16px;border:1px solid #e7e2db;">
                    <div class="card-body">
                        <i class="bi bi-search-heart text-muted display-4 mb-3 d-block"></i>
                        <h5 class="fw-800 text-stone-900 mb-2">No Violation Found</h5>
                        <p class="text-muted mb-0" style="max-width:400px;margin:0 auto;font-size:.88rem;">We couldn't find a record matching "<strong>${search}</strong>". Please verify the Ticket #, Violator Name, License #, or Plate #.</p>
                    </div>
                </div>`;
        }

        function renderFound(d) {
            const stStyle = statusStyles[d.display_status] || 'background-color:#f5f5f4;color:#57534e;';
            const statusLabel = d.display_status.charAt(0).toUpperCase() + d.display_status.slice(1);
            const plateRow = d.plate ? `<tr style="border-bottom:1px dashed #f5f0e8;"><td class="text-muted py-2">Vehicle Plate</td><td class="fw-600 py-2" style="font-family:ui-monospace,monospace;">${d.plate}</td></tr>` : '';
            const locationRow = d.location ? `<tr style="border-bottom:1px dashed #f5f0e8;"><td class="text-muted py-2">Location</td><td class="fw-600 py-2">${d.location}</td></tr>` : '';
            const latePenRow = d.late_penalty > 0 ? `<span class="ms-1">+ Late penalty: ₱${fmt(d.late_penalty)}</span>` : '';
            const paidRow    = d.total_paid > 0  ? `<span class="ms-1">· Already paid: ₱${fmt(d.total_paid)}</span>`   : '';
            const amountLabel = d.status === 'partial' ? 'BALANCE DUE' : 'TOTAL AMOUNT DUE';

            let actionHtml = '';
            if (d.status === 'settled') {
                actionHtml = `
                    <div class="card border-0 shadow-sm h-100" style="border-radius:16px;overflow:hidden;border:1px solid #e7e2db;">
                        <div class="card-header border-0 p-4" style="background-color:#f0fdf4;border-bottom:1px solid #bbf7d0 !important;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-patch-check-fill text-success fs-4"></i>
                                <div>
                                    <h5 class="fw-800 text-success mb-0">Settled</h5>
                                    <small style="color:#16a34a;">This citation has been successfully settled.</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <a href="${d.cashier_url}" class="btn btn-outline-success fw-700 w-100 mb-2" style="border-radius:10px;"><i class="bi bi-eye me-1"></i> View Full Details</a>
                            <a href="${d.print_url}" target="_blank" class="btn btn-outline-secondary fw-700 w-100" style="border-radius:10px;"><i class="bi bi-printer me-1"></i> Print Violation Record</a>
                        </div>
                    </div>`;
            } else {
                actionHtml = `
                    <div class="card border-0 shadow-sm h-100" style="border-radius:16px;overflow:hidden;border:1px solid #e7e2db;">
                        <div class="card-header border-0 p-4" style="background-color:#faf9f6;border-bottom:1px solid #e7e2db !important;">
                            <span class="text-muted text-uppercase fw-700" style="font-size:.75rem;letter-spacing:.05em;display:block;">ACTION</span>
                            <h5 class="fw-800 mb-0" style="color:#1c1917;">Collect Payment</h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            <p class="text-muted mb-3" style="font-size:.88rem;">Balance Due: <strong class="text-danger fs-5">₱${fmt(d.balance)}</strong></p>
                            <a href="${d.cashier_url}" class="btn fw-700 text-white w-100 py-2" style="background:linear-gradient(135deg,#15803d,#166534);border:none;border-radius:10px;">
                                <i class="bi bi-check2-circle me-1"></i> Open Payment Form
                            </a>
                        </div>
                    </div>`;
            }

            resultsArea.innerHTML = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;overflow:hidden;border:1px solid #e7e2db;">
                            <div class="card-header border-0 p-4 d-flex align-items-center justify-content-between" style="background-color:#faf9f6;border-bottom:1px solid #e7e2db !important;">
                                <div>
                                    <span class="text-muted text-uppercase fw-700" style="font-size:.75rem;letter-spacing:.05em;display:block;">TICKET DETAILS</span>
                                    <h5 class="fw-800 mb-0" style="color:#1c1917;">${d.ticket_number}</h5>
                                </div>
                                <span class="badge px-3 py-2 fw-700" style="border-radius:20px;font-size:.8rem;${stStyle}">${statusLabel}</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="p-3 mb-4" style="background-color:#fef2f2;border-radius:12px;border:1px solid #fca5a5;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="text-muted fw-600" style="font-size:.75rem;display:block;">${amountLabel}</span>
                                            <strong style="font-size:1.5rem;color:#b91c1c;">₱${fmt(d.balance)}</strong>
                                        </div>
                                        <i class="bi bi-cash-coin text-danger fs-1"></i>
                                    </div>
                                    <div class="mt-2 pt-2" style="border-top:1px dashed #fca5a5;font-size:.78rem;color:#7f1d1d;">
                                        Base fine: ₱${fmt(d.fine_amount)} ${latePenRow} ${paidRow}
                                    </div>
                                </div>
                                <table class="table table-borderless align-middle mb-0" style="font-size:.88rem;">
                                    <tbody>
                                        <tr style="border-bottom:1px dashed #f5f0e8;"><td class="text-muted py-2" style="width:40%;">Violator</td><td class="fw-700 text-stone-900 py-2">${d.violator_name || '—'}</td></tr>
                                        <tr style="border-bottom:1px dashed #f5f0e8;"><td class="text-muted py-2">License Number</td><td class="fw-600 py-2" style="font-family:ui-monospace,monospace;">${d.license_number || 'Not on file'}</td></tr>
                                        <tr style="border-bottom:1px dashed #f5f0e8;"><td class="text-muted py-2">Violation Type</td><td class="fw-600 py-2">${d.violation_type || '—'}</td></tr>
                                        <tr style="border-bottom:1px dashed #f5f0e8;"><td class="text-muted py-2">Date</td><td class="fw-600 py-2">${d.date}</td></tr>
                                        ${locationRow}
                                        ${plateRow}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">${actionHtml}</div>
                </div>`;
        }

        function doSearch(query) {
            if (currentRequest) currentRequest.abort();

            if (!query) {
                renderEmpty();
                return;
            }

            renderLoading();

            const controller = new AbortController();
            currentRequest = controller;

            fetch(`${cashierUrl}?search=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: controller.signal
            })
            .then(r => r.json())
            .then(data => {
                currentRequest = null;
                if (data.empty) { renderEmpty(); return; }
                if (!data.found) { renderNotFound(query); return; }
                renderFound(data);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                currentRequest = null;
            });
        }

        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => doSearch(query), 350);
        });
    })();

</script>
@endpush
@endsection
