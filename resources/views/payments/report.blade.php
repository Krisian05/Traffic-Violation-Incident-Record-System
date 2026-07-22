@extends('layouts.app')

@section('title', 'Collection Reports')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                 style="width:52px;height:52px;background:linear-gradient(135deg,#15803d,#166534);color:#fff;flex-shrink:0;">
                <i class="bi bi-cash-coin" style="font-size:1.4rem;"></i>
            </div>
            <div>
                <div class="text-uppercase fw-bold text-success" style="font-size:.68rem;letter-spacing:.08em;">Payment Monitoring</div>
                <h3 class="mb-0 fw-extrabold text-dark">Collection Reports &amp; Reconciliation</h3>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="{{ route('payments.report') }}" class="d-flex align-items-center gap-2">
                @if(!Auth::user()->isTreasurer())
                <select name="lgu_id" class="form-select form-select-sm shadow-sm rounded-3 fw-semibold border-secondary-subtle" onchange="this.form.submit()">
                    <option value="">All LGUs</option>
                    @foreach($lgus as $lgu)
                        <option value="{{ $lgu->id }}" {{ (string) $selectedLguId === (string) $lgu->id ? 'selected' : '' }}>{{ $lgu->name }}</option>
                    @endforeach
                </select>
                @endif
                <select name="year" class="form-select form-select-sm shadow-sm rounded-3 fw-semibold border-secondary-subtle" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    @if(Auth::user()->isTreasurer())
    <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius:12px;font-size:.85rem;">
        <i class="bi bi-info-circle-fill me-1"></i> Showing collections for your assigned LGU only.
    </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;border-left:4px solid #16a34a !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.05em;">Revenue Collected</span>
                        <div class="rounded-3 p-2 bg-success-subtle text-success"><i class="bi bi-cash-stack fs-5"></i></div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">₱{{ number_format($paidAmount, 2) }}</div>
                    <div class="text-muted" style="font-size:.75rem;">All payments recorded in {{ $year }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;border-left:4px solid #dc2626 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.05em;">Outstanding Balance</span>
                        <div class="rounded-3 p-2 bg-danger-subtle text-danger"><i class="bi bi-clock-history fs-5"></i></div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">₱{{ number_format($unpaidAmount, 2) }}</div>
                    <div class="text-muted" style="font-size:.75rem;">Pending + partial balances, incl. late penalties</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;border-left:4px solid #1d4ed8 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.05em;">Citation Status Mix</span>
                        <div class="rounded-3 p-2 bg-primary-subtle text-primary"><i class="bi bi-pie-chart-fill fs-5"></i></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2" style="font-size:.78rem;">
                        <span class="badge bg-success-subtle text-success fw-bold">Settled {{ $statusCounts['settled'] ?? 0 }}</span>
                        <span class="badge bg-warning-subtle text-warning-emphasis fw-bold">Pending {{ $statusCounts['pending'] ?? 0 }}</span>
                        <span class="badge fw-bold" style="background:#fff7ed;color:#c2410c;">Partial {{ $statusCounts['partial'] ?? 0 }}</span>
                        <span class="badge bg-secondary-subtle text-secondary fw-bold">Contested {{ $statusCounts['contested'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-3" style="font-size:.85rem;">Daily Collection — Last 30 Days</h6>
                    <canvas id="dailyChart" height="90"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-3" style="font-size:.85rem;">Monthly Collection — {{ $year }}</h6>
                    <canvas id="monthlyChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Annual Summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-3 px-3">
                    <h6 class="fw-bold text-dark mb-0" style="font-size:.85rem;">Annual Summary (Last 5 Years)</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
                        <tbody>
                            @forelse($annualCollection as $y => $total)
                                <tr>
                                    <td class="ps-3 fw-600">{{ $y }}</td>
                                    <td class="text-end pe-3 fw-700 text-success">₱{{ number_format($total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td class="ps-3 py-3 text-muted">No collection history yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- LGU Performance Ranking --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-3 px-3">
                    <h6 class="fw-bold text-dark mb-0" style="font-size:.85rem;">LGU Collection Performance — {{ $year }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
                            <thead style="background:#faf9f6;">
                                <tr>
                                    <th class="ps-3">LGU</th>
                                    <th class="text-center">Citations</th>
                                    <th class="text-center">Settled</th>
                                    <th class="text-center">Rate</th>
                                    <th class="text-end pe-3">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lguPerformance as $row)
                                <tr>
                                    <td class="ps-3 fw-700">{{ $row->name }} <span class="text-muted">({{ $row->code }})</span></td>
                                    <td class="text-center">{{ $row->total }}</td>
                                    <td class="text-center">{{ $row->settled }}</td>
                                    <td class="text-center">{{ $row->settled_rate }}%</td>
                                    <td class="text-end pe-3 fw-700 text-success">₱{{ number_format($row->revenue, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="py-3 text-center text-muted">No data for this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reconciliation Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-3 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h6 class="fw-bold text-dark mb-0" style="font-size:.85rem;">Payment Reconciliation</h6>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('payments.report.export', request()->query()) }}" class="btn btn-sm btn-outline-success fw-600">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="card-body p-3">
            <form method="GET" action="{{ route('payments.report') }}" class="row g-2 mb-3">
                <input type="hidden" name="year" value="{{ $year }}">
                @if(!Auth::user()->isTreasurer())<input type="hidden" name="lgu_id" value="{{ $selectedLguId }}">@endif
                <div class="col-md-3">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To">
                </div>
                <div class="col-md-2">
                    <select name="method" class="form-select form-select-sm">
                        <option value="">Any Method</option>
                        @foreach(['cash'=>'Cash','gcash'=>'GCash','maya'=>'Maya','bank'=>'Bank Transfer','other'=>'Other'] as $val => $label)
                            <option value="{{ $val }}" {{ request('method') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="or_number" class="form-control form-control-sm" value="{{ request('or_number') }}" placeholder="Search OR Number">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel-fill"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.82rem;">
                    <thead style="background:#faf9f6;">
                        <tr>
                            <th class="ps-3">OR Number</th>
                            <th>Ticket</th>
                            <th>Violator</th>
                            <th>LGU</th>
                            <th class="text-end">Amount</th>
                            <th>Method</th>
                            <th>Collector</th>
                            <th>Date Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td class="ps-3 fw-700" style="font-family:ui-monospace,monospace;">{{ $p->or_number }}</td>
                            <td>
                                @if($p->violation)
                                    <a href="{{ route('violations.show', $p->violation) }}">{{ $p->violation->ticket_number ?: '#' . $p->violation->id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $p->violation?->violator?->full_name ?? '—' }}</td>
                            <td>{{ $p->violation?->lgu?->name ?? '—' }}</td>
                            <td class="text-end fw-700 text-success">₱{{ number_format($p->amount_paid, 2) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst($p->payment_method) }}</span></td>
                            <td>{{ $p->collector?->name ?? $p->cashier_name }}</td>
                            <td>{{ $p->paid_at->format('M d, Y g:i A') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="py-4 text-center text-muted">No payments match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $payments->links() }}</div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: @json($dailyLabels),
            datasets: [{
                label: 'Collected (₱)',
                data: @json($dailyData),
                borderColor: '#15803d',
                backgroundColor: 'rgba(21,128,61,.1)',
                fill: true,
                tension: .3,
                pointRadius: 2,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: @json($monthlyLabels),
            datasets: [{
                label: 'Collected (₱)',
                data: @json($monthlyData),
                backgroundColor: '#1d4ed8',
                borderRadius: 4,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
