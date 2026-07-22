@extends('layouts.app')

@section('title', 'Provincial Command Dashboard')

@section('content')
<div class="container-fluid px-4 py-3">
    {{-- Executive Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                 style="width:52px;height:52px;background:linear-gradient(135deg,#1e3a8a,#1d4ed8);color:#fff;flex-shrink:0;">
                <i class="bi bi-diagram-3-fill" style="font-size:1.4rem;"></i>
            </div>
            <div>
                <div class="text-uppercase fw-bold text-primary" style="font-size:.68rem;letter-spacing:.08em;">Province of Cebu • Oversight Platform</div>
                <h3 class="mb-0 fw-extrabold text-dark">Provincial Command Dashboard</h3>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="{{ route('province.dashboard') }}" class="d-flex align-items-center gap-2">
                <select name="lgu_id" class="form-select form-select-sm shadow-sm rounded-3 fw-semibold border-secondary-subtle" onchange="this.form.submit()">
                    <option value="">All LGUs in Cebu</option>
                    @foreach($lgus as $lgu)
                        <option value="{{ $lgu->id }}" {{ $selectedLguId == $lgu->id ? 'selected' : '' }}>{{ $lgu->name }}</option>
                    @endforeach
                </select>

                <select name="year" class="form-select form-select-sm shadow-sm rounded-3 fw-semibold border-secondary-subtle" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    {{-- Key Performance Indicator Cards --}}
    <div class="row g-3 mb-4">
        <!-- Total Violations -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;border-left:4px solid #1d4ed8 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.05em;">Total Citations</span>
                        <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">{{ number_format($totalViolations) }}</div>
                    <div class="text-muted" style="font-size:.75rem;">
                        <span class="badge bg-primary-subtle text-primary fw-bold">All-Time: {{ number_format($totalViolationsAllTime) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Revenue Collected -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;border-left:4px solid #16a34a !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.05em;">Revenue Collected</span>
                        <div class="rounded-3 p-2 bg-success-subtle text-success">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">₱{{ number_format($totalRevenueCollected, 2) }}</div>
                    <div class="text-muted" style="font-size:.75rem;">Settled fine payments in {{ $year }}</div>
                </div>
            </div>
        </div>

        <!-- Outstanding Fines -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;border-left:4px solid #dc2626 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.05em;">Outstanding Penalties</span>
                        <div class="rounded-3 p-2 bg-danger-subtle text-danger">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-extrabold text-dark mb-1">₱{{ number_format($totalUncollectedFines, 2) }}</div>
                    <div class="text-muted" style="font-size:.75rem;">Pending & overdue balances</div>
                </div>
            </div>
        </div>

        <!-- Active Officers & Motorists -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;border-left:4px solid #8b5cf6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size:.7rem;letter-spacing:.05em;">Deployment & Database</span>
                        <div class="rounded-3 p-2 bg-purple-subtle text-purple" style="background:#f3e8ff;color:#9333ea;">
                            <i class="bi bi-shield-check fs-5"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <span class="fs-2 fw-extrabold text-dark">{{ number_format($totalActiveOfficers) }}</span>
                        <span class="text-muted" style="font-size:.8rem;">Officers</span>
                        <span class="mx-1 text-muted">•</span>
                        <span class="fs-4 fw-bold text-dark">{{ number_format($totalViolators) }}</span>
                        <span class="text-muted" style="font-size:.8rem;">Motorists</span>
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">Active enforcement personnel</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Trends & Distribution Row --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Provincial Violation Trends ({{ $year }})</h6>
                        <span class="text-muted" style="font-size:.78rem;">Monthly citation distribution across participating LGUs</span>
                    </div>
                </div>
                <div class="card-body px-3 pb-3 pt-0" style="height:310px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark">Violation Classification Split</h6>
                    <span class="text-muted" style="font-size:.78rem;">Top citation categories across Cebu</span>
                </div>
                <div class="card-body px-3 pb-3 pt-0 d-flex align-items-center justify-content-center" style="height:310px;">
                    @if(empty($categoryData))
                        <div class="text-muted style-italic" style="font-size:.85rem;">No category data for {{ $year }}.</div>
                    @else
                        <div style="width:220px;height:220px;position:relative;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Hotspots & Comparative Analysis Row --}}
    <div class="row g-4 mb-4">
        {{-- Provincial Hotspots --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i> High Incident Hotspots</h6>
                    <span class="text-muted" style="font-size:.78rem;">Top enforcement locations in Cebu</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($provincialHotspots as $idx => $spot)
                            <div class="list-group-item px-3 py-2.5 d-flex align-items-center justify-content-between border-bottom-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger text-white rounded-circle" style="width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;">{{ $idx + 1 }}</span>
                                    <span class="fw-semibold text-dark" style="font-size:.84rem;">{{ $spot->location }}</span>
                                </div>
                                <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill" style="font-size:.75rem;">{{ number_format($spot->total) }} tickets</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted" style="font-size:.85rem;">No location hotspot data recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- LGU Comparative Table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Comparative LGU Performance Matrix</h6>
                        <span class="text-muted" style="font-size:.78rem;">Enforcement volume and settlement rates across municipalities</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                        <thead class="bg-light text-muted" style="font-size:.74rem;text-transform:uppercase;letter-spacing:.04em;">
                            <tr>
                                <th class="ps-3" style="width:60px;">Rank</th>
                                <th>LGU / Municipality</th>
                                <th class="text-center">Total Tickets</th>
                                <th class="text-center">Settled</th>
                                <th class="text-center">Pending / Overdue</th>
                                <th class="text-center">Settlement Rate</th>
                                <th style="width:200px;">Volume Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $maxViolations = $municipalityStats->max('total_violations') ?: 1;
                            @endphp
                            @forelse($municipalityStats as $index => $stat)
                            <tr>
                                <td class="ps-3 fw-bold text-secondary">#{{ $index + 1 }}</td>
                                <td class="fw-bold text-dark">
                                    {{ $stat->municipality_name }}
                                    @if($stat->code)<span class="text-muted ms-1" style="font-size:.72rem;">({{ $stat->code }})</span>@endif
                                </td>
                                <td class="text-center fw-bold text-dark">{{ number_format($stat->total_violations) }}</td>
                                <td class="text-center fw-bold text-success">{{ number_format($stat->settled_violations) }}</td>
                                <td class="text-center fw-bold text-danger">{{ number_format($stat->total_violations - $stat->settled_violations) }}</td>
                                <td class="text-center">
                                    <span class="badge px-2.5 py-1 rounded-pill fw-bold" style="background:{{ $stat->settled_rate >= 70 ? '#d1fae5;color:#065f46;' : ($stat->settled_rate >= 40 ? '#fef3c7;color:#92400e;' : '#fee2e2;color:#991b1b;') }};font-size:0.75rem;">
                                        {{ $stat->settled_rate }}%
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $percentage = round(($stat->total_violations / $maxViolations) * 100);
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;border-radius:3px;background-color:#f1f5f9;">
                                            <div class="progress-bar" style="width:{{ $percentage }}%;background-color:#1d4ed8;border-radius:3px;"></div>
                                        </div>
                                        <span class="text-muted fw-bold" style="font-size:.72rem;width:28px;text-align:right;">{{ $percentage }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No LGU records available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendChart');
    if(ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Violations',
                    data: @json($chartData),
                    borderColor: '#1d4ed8',
                    backgroundColor: 'rgba(29,78,216,0.06)',
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#1d4ed8',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const catCtx = document.getElementById('categoryChart');
    if(catCtx) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: @json($categoryLabels),
                datasets: [{
                    data: @json($categoryData),
                    backgroundColor: ['#1d4ed8', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
            }
        });
    }
});
</script>
@endpush
@endsection
