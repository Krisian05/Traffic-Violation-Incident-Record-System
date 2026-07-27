@extends('layouts.app')

@section('title', 'Collection Reports & Payment Analytics')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payment Reports</li>
@endsection

@section('content')
<style>
    .pm-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 1rem !important;
    }
    .pm-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important;
    }
    .pm-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
    .pm-badge-method {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.3em 0.65em;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .pm-filter-input {
        font-size: 0.85rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
    }
    .pm-filter-input:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
    }
    .pm-table th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
    }
    .pm-table td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
    }
</style>

<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                 style="width:54px;height:54px;background:linear-gradient(135deg,#059669,#047857);color:#fff;flex-shrink:0;">
                <i class="bi bi-cash-coin" style="font-size:1.5rem;"></i>
            </div>
            <div>
                <div class="text-uppercase fw-bold text-success" style="font-size:.7rem;letter-spacing:.09em;">Financial Reporting</div>
                <h3 class="mb-0 fw-extrabold text-dark" style="letter-spacing:-0.02em;">Payment &amp; Collection Reports</h3>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="{{ route('payments.report') }}" class="d-flex align-items-center gap-2">
                @if(!Auth::user()->isTreasurer() && !Auth::user()->isCashier())
                <select name="lgu_id" class="form-select form-select-sm shadow-sm pm-filter-input fw-semibold" onchange="this.form.submit()">
                    <option value="">All Municipalities / LGUs</option>
                    @foreach($lgus as $lgu)
                        <option value="{{ $lgu->id }}" {{ (string) $selectedLguId === (string) $lgu->id ? 'selected' : '' }}>{{ $lgu->name }}</option>
                    @endforeach
                </select>
                @endif
                <select name="year" class="form-select form-select-sm shadow-sm pm-filter-input fw-semibold" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                    @endfor
                </select>
            </form>

            <a href="{{ route('payments.report.export', request()->query()) }}" class="btn btn-sm btn-success shadow-sm fw-bold px-3 py-1-5 rounded-3 d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
            </a>
        </div>
    </div>

    @if(Auth::user()->isTreasurer() || Auth::user()->isCashier())
    <div class="alert alert-emerald border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between" style="background:#ecfdf5;color:#047857;border-radius:12px;font-size:.875rem;">
        <div>
            <i class="bi bi-shield-check me-2 fs-5 align-middle"></i>
            <span>Showing payment collection records for <strong>{{ Auth::user()->lgu?->name ?? 'assigned municipality' }}</strong> only.</span>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 text-uppercase fw-bold" style="font-size:0.68rem;">Cashier Scoped</span>
    </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        {{-- Revenue Collected --}}
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm pm-card h-100 overflow-hidden" style="background:linear-gradient(135deg,#ffffff 0%,#f0fdf4 100%);border-left:5px solid #10b981 !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.06em;">Total Revenue Collected</span>
                        <div class="pm-stat-icon bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">₱{{ number_format($paidAmount, 2) }}</div>
                    <div class="d-flex align-items-center gap-1 text-muted" style="font-size:.75rem;">
                        <i class="bi bi-calendar-check text-success"></i>
                        <span>Payments settled in {{ $year }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Outstanding Balance --}}
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm pm-card h-100 overflow-hidden" style="background:linear-gradient(135deg,#ffffff 0%,#fef2f2 100%);border-left:5px solid #ef4444 !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.06em;">Outstanding Receivables</span>
                        <div class="pm-stat-icon bg-danger-subtle text-danger"><i class="bi bi-clock-history"></i></div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">₱{{ number_format($unpaidAmount, 2) }}</div>
                    <div class="d-flex align-items-center gap-1 text-muted" style="font-size:.75rem;">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                        <span>Pending &amp; partial balances (incl. penalties)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Citation Status Breakdown --}}
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm pm-card h-100 overflow-hidden" style="background:linear-gradient(135deg,#ffffff 0%,#eff6ff 100%);border-left:5px solid #3b82f6 !important;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.06em;">Citation Payment Status</span>
                        <div class="pm-stat-icon bg-primary-subtle text-primary"><i class="bi bi-pie-chart-fill"></i></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2" style="font-size:.8rem;">
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2.5 py-1.5 rounded-2">
                            <i class="bi bi-check-circle-fill me-1"></i> Settled: {{ $statusCounts['settled'] ?? 0 }}
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold px-2.5 py-1.5 rounded-2">
                            <i class="bi bi-hourglass-split me-1"></i> Pending: {{ $statusCounts['pending'] ?? 0 }}
                        </span>
                        <span class="badge fw-bold px-2.5 py-1.5 rounded-2" style="background:#fff7ed;color:#c2410c;border:1px solid #ffedd5;">
                            <i class="bi bi-pie-chart me-1"></i> Partial: {{ $statusCounts['partial'] ?? 0 }}
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-bold px-2.5 py-1.5 rounded-2">
                            <i class="bi bi-shield-exclamation me-1"></i> Contested: {{ $statusCounts['contested'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm pm-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up-arrow text-success me-1.5"></i> Daily Collections (Last 30 Days)</h6>
                        <span class="text-muted" style="font-size:0.75rem;">Daily revenue trend in Philippine Pesos</span>
                    </div>
                    <span class="badge bg-light text-muted border font-monospace">30-Day Window</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="dailyChart" height="110"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm pm-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line-fill text-primary me-1.5"></i> Monthly Breakdown ({{ $year }})</h6>
                        <span class="text-muted" style="font-size:0.75rem;">Collection volume by calendar month</span>
                    </div>
                    <span class="badge bg-light text-muted border font-monospace">{{ $year }}</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="monthlyChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance & Annual Summaries Row --}}
    <div class="row g-3 mb-4">
        {{-- Annual Collection History --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm pm-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3.5">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-range text-info me-1.5"></i> Annual Revenue History</h6>
                    <span class="text-muted" style="font-size:0.75rem;">5-year historical collection total</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3.5">Year</th>
                                <th class="text-end pe-3.5">Revenue Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($annualCollection as $y => $total)
                                <tr>
                                    <td class="ps-3.5 fw-bold text-dark"><i class="bi bi-calendar3 me-1 text-muted"></i> {{ $y }}</td>
                                    <td class="text-end pe-3.5 fw-bold text-success">₱{{ number_format($total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="ps-3 py-3 text-muted text-center">No historical collection records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- LGU Performance Ranking --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm pm-card h-100">
                <div class="card-header bg-white border-0 pt-3 px-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-trophy-fill text-warning me-1.5"></i> LGU Collection Performance ({{ $year }})</h6>
                        <span class="text-muted" style="font-size:0.75rem;">Settlement rates and total revenue per municipality</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.84rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3.5">Municipality / LGU</th>
                                    <th class="text-center">Total Issued</th>
                                    <th class="text-center">Settled</th>
                                    <th class="text-center">Settlement Rate</th>
                                    <th class="text-end pe-3.5">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lguPerformance as $row)
                                <tr>
                                    <td class="ps-3.5 fw-bold text-dark">
                                        <i class="bi bi-geo-alt-fill text-danger me-1" style="font-size:0.8rem;"></i>
                                        {{ $row->name }} <span class="text-muted fw-normal">({{ $row->code }})</span>
                                    </td>
                                    <td class="text-center fw-semibold">{{ number_format($row->total) }}</td>
                                    <td class="text-center fw-bold text-success">{{ number_format($row->settled) }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $row->settled_rate }}%"></div>
                                            </div>
                                            <span class="fw-bold" style="font-size:0.78rem;">{{ $row->settled_rate }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3.5 fw-extrabold text-success">₱{{ number_format($row->revenue, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="py-4 text-center text-muted">No collection performance data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Payment Reconciliation Section --}}
    <div class="card border-0 shadow-sm pm-card">
        <div class="card-header bg-white border-0 pt-3.5 px-3.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-receipt-cutoff text-success me-1.5"></i> Payment Reconciliation &amp; Transaction Audit Log</h6>
                <span class="text-muted" style="font-size:0.75rem;">Verified payments issued with Official Receipts (OR)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('payments.report.export', request()->query()) }}" class="btn btn-sm btn-outline-success fw-bold px-3 rounded-2">
                    <i class="bi bi-file-earmark-excel me-1"></i> Download Report (Excel)
                </a>
            </div>
        </div>
        <div class="card-body p-3.5">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('payments.report') }}" class="row g-2 mb-3 bg-light p-2.5 rounded-3 border">
                <input type="hidden" name="year" value="{{ $year }}">
                @if(!Auth::user()->isTreasurer() && !Auth::user()->isCashier())
                    <input type="hidden" name="lgu_id" value="{{ $selectedLguId }}">
                @endif
                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">FROM DATE</label>
                    <input type="date" name="date_from" class="form-control form-control-sm pm-filter-input" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">TO DATE</label>
                    <input type="date" name="date_to" class="form-control form-control-sm pm-filter-input" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">METHOD</label>
                    <select name="method" class="form-select form-select-sm pm-filter-input">
                        <option value="">All Payment Methods</option>
                        @foreach(['cash'=>'Cash','gcash'=>'GCash','maya'=>'Maya','bank'=>'Bank Transfer','other'=>'Other'] as $val => $label)
                            <option value="{{ $val }}" {{ request('method') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">SEARCH OR / TICKET</label>
                    <input type="text" name="or_number" class="form-control form-control-sm pm-filter-input" value="{{ request('or_number') }}" placeholder="OR # or search query…">
                </div>
                <div class="col-md-1 col-sm-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-success w-100 fw-bold shadow-sm" style="height:31px;" title="Apply Filter">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table pm-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3.5">Official Receipt (OR) #</th>
                            <th>Citation Ticket #</th>
                            <th>Violator Name</th>
                            <th>LGU / Municipality</th>
                            <th class="text-end">Amount Paid</th>
                            <th>Payment Method</th>
                            <th>Cashier / Collector</th>
                            <th>Transaction Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td class="ps-3.5 fw-bold text-dark font-monospace" style="font-size:0.88rem;color:#0f172a;">
                                <i class="bi bi-receipt text-success me-1"></i> {{ $p->or_number }}
                            </td>
                            <td class="fw-semibold">
                                @if($p->violation)
                                    <a href="{{ route('violations.show', $p->violation) }}" class="text-decoration-none text-primary fw-bold">
                                        {{ $p->violation->ticket_number ?: '#' . $p->violation->id }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-dark">{{ $p->violation?->violator?->full_name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $p->violation?->lgu?->name ?? '—' }}</span></td>
                            <td class="text-end fw-extrabold text-success" style="font-size:0.9rem;">₱{{ number_format($p->amount_paid, 2) }}</td>
                            <td>
                                @php
                                    $methodMap = [
                                        'cash' => ['bg' => '#ecfdf5', 'color' => '#047857', 'icon' => 'bi-cash-stack'],
                                        'gcash' => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'icon' => 'bi-phone'],
                                        'maya' => ['bg' => '#fdf4ff', 'color' => '#86198f', 'icon' => 'bi-wallet2'],
                                        'bank' => ['bg' => '#f8fafc', 'color' => '#334155', 'icon' => 'bi-bank'],
                                    ];
                                    $mInfo = $methodMap[strtolower($p->payment_method)] ?? ['bg' => '#f1f5f9', 'color' => '#475569', 'icon' => 'bi-credit-card'];
                                @endphp
                                <span class="pm-badge-method" style="background:{{ $mInfo['bg'] }};color:{{ $mInfo['color'] }};border:1px solid {{ $mInfo['bg'] }};">
                                    <i class="bi {{ $mInfo['icon'] }} me-1"></i> {{ ucfirst($p->payment_method) }}
                                </span>
                            </td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1 text-secondary" style="font-size:0.82rem;">
                                    <i class="bi bi-person-circle"></i> {{ $p->collector?->name ?? $p->cashier_name }}
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                <i class="bi bi-clock me-1"></i> {{ $p->paid_at->format('M d, Y g:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-5 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <span>No payment transactions match the selected filters.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3.5 px-2 d-flex justify-content-end">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Custom Peso formatter for Chart tooltips
    const formatPeso = (value) => '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Daily Collection Chart
    const dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx) {
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: @json($dailyLabels),
                datasets: [{
                    label: 'Daily Revenue',
                    data: @json($dailyData),
                    borderColor: '#10b981',
                    borderWidth: 2.5,
                    backgroundColor: (context) => {
                        const bg = context.chart.ctx.createLinearGradient(0, 0, 0, 200);
                        bg.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
                        bg.addColorStop(1, 'rgba(16, 185, 129, 0.00)');
                        return bg;
                    },
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ' Revenue: ' + formatPeso(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            color: '#64748b',
                            callback: (val) => '₱' + Number(val).toLocaleString()
                        }
                    }
                }
            }
        });
    }

    // Monthly Collection Chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: 'Monthly Revenue',
                    data: @json($monthlyData),
                    backgroundColor: '#3b82f6',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ' Revenue: ' + formatPeso(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            color: '#64748b',
                            callback: (val) => '₱' + Number(val).toLocaleString()
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
