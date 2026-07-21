@extends('layouts.app')

@section('title', 'Province Dashboard')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:45px;height:45px;background:linear-gradient(135deg,#1e40af,#3b82f6);flex-shrink:0;">
        <i class="bi bi-diagram-3-fill text-white" style="font-size:1.1rem;"></i>
    </div>
    <div>
        <h4 class="mb-0 fw-700" style="color:#1c1917;">Province Dashboard</h4>
        <div style="font-size:.85rem;color:#78716c;">Overview across all LGUs and municipalities</div>
    </div>
    
    <div class="ms-auto d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('province.dashboard') }}" class="d-flex align-items-center gap-2">
            <select name="year" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="border-radius:8px;font-weight:600;color:#44403c;border-color:#e7e5e4;">
                @for($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Total Violations -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;background:#fff;border-bottom:4px solid #3b82f6 !important;">
            <div class="card-body p-4 position-relative overflow-hidden">
                <div class="position-absolute" style="top:-15px;right:-15px;opacity:0.04;transform:rotate(-15deg);">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:7rem;color:#3b82f6;"></i>
                </div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#eff6ff;">
                        <i class="bi bi-exclamation-triangle-fill" style="color:#3b82f6;font-size:1.1rem;"></i>
                    </div>
                    <div style="font-size:.85rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Total Violations</div>
                </div>
                <div style="font-size:2.2rem;font-weight:800;color:#1e293b;">{{ number_format($totalViolations) }}</div>
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:.8rem;color:#94a3b8;font-weight:500;">Recorded in {{ $year }}</span>
                    <span class="badge" style="background:#eff6ff;color:#1e40af;font-size:.7rem;font-weight:700;border:1px solid #bfdbfe;">All-Time: {{ number_format($totalViolationsAllTime) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Violators -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;background:#fff;border-bottom:4px solid #8b5cf6 !important;">
            <div class="card-body p-4 position-relative overflow-hidden">
                <div class="position-absolute" style="top:-15px;right:-15px;opacity:0.04;transform:rotate(-15deg);">
                    <i class="bi bi-people-fill" style="font-size:7rem;color:#8b5cf6;"></i>
                </div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#f5f3ff;">
                        <i class="bi bi-people-fill" style="color:#8b5cf6;font-size:1.1rem;"></i>
                    </div>
                    <div style="font-size:.85rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Registered Motorists</div>
                </div>
                <div style="font-size:2.2rem;font-weight:800;color:#1e293b;">{{ number_format($totalViolators) }}</div>
                <div style="font-size:.8rem;color:#94a3b8;font-weight:500;">All-time registered violators</div>
            </div>
        </div>
    </div>

    <!-- Active Officers -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;background:#fff;border-bottom:4px solid #10b981 !important;">
            <div class="card-body p-4 position-relative overflow-hidden">
                <div class="position-absolute" style="top:-15px;right:-15px;opacity:0.04;transform:rotate(-15deg);">
                    <i class="bi bi-person-badge-fill" style="font-size:7rem;color:#10b981;"></i>
                </div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#ecfdf5;">
                        <i class="bi bi-person-badge-fill" style="color:#10b981;font-size:1.1rem;"></i>
                    </div>
                    <div style="font-size:.85rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Active Officers</div>
                </div>
                <div style="font-size:2.2rem;font-weight:800;color:#1e293b;">{{ number_format($totalActiveOfficers) }}</div>
                <div style="font-size:.8rem;color:#94a3b8;font-weight:500;">Traffic officers & operators</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">
            <div class="card-header border-0 bg-white" style="padding:1.25rem 1.5rem;">
                <div style="font-size:1.05rem;font-weight:700;color:#1e293b;">Violation Trends ({{ $year }})</div>
                <div style="font-size:.85rem;color:#64748b;">Monthly distribution across the province</div>
            </div>
            <div class="card-body px-4 pb-4 pt-0" style="height:320px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">
            <div class="card-header border-0 bg-white" style="padding:1.25rem 1.5rem;">
                <div style="font-size:1.05rem;font-weight:700;color:#1e293b;">Violation Category Distribution</div>
                <div style="font-size:.85rem;color:#64748b;">Top citation types across all LGUs</div>
            </div>
            <div class="card-body px-4 pb-4 pt-0 d-flex align-items-center justify-content-center" style="height:320px;">
                @if(empty($categoryData))
                    <div class="text-muted" style="font-size:.85rem;font-style:italic;">No data available for {{ $year }}.</div>
                @else
                    <div style="width:230px;height:230px;position:relative;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">
            <div class="card-header border-0 bg-white" style="padding:1.25rem 1.5rem;">
                <div style="font-size:1.05rem;font-weight:700;color:#1e293b;">Comparative LGU Performance Analysis</div>
                <div style="font-size:.85rem;color:#64748b;">Enforcement activity and settlement ratios by municipality for {{ $year }}</div>
            </div>
            <div class="card-body p-0">
                @if($municipalityStats->isEmpty())
                    <div class="p-4 text-center text-muted" style="font-size:.9rem;font-style:italic;">No data available.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.9rem;">
                            <thead class="table-light text-uppercase" style="font-size:.75rem;font-weight:700;letter-spacing:.04em;color:#475569;">
                                <tr>
                                    <th style="padding:1rem 1.5rem;width:80px;">Rank</th>
                                    <th style="padding:1rem 1.5rem;">Municipality</th>
                                    <th style="padding:1rem 1.5rem;text-align:center;">Total Citations</th>
                                    <th style="padding:1rem 1.5rem;text-align:center;">Settled</th>
                                    <th style="padding:1rem 1.5rem;text-align:center;">Pending / Overdue</th>
                                    <th style="padding:1rem 1.5rem;text-align:center;">Settlement Rate</th>
                                    <th style="padding:1rem 1.5rem;width:250px;">Enforcement Volume Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $maxViolations = $municipalityStats->max('total_violations') ?: 1;
                                @endphp
                                @foreach($municipalityStats as $index => $stat)
                                <tr>
                                    <td style="padding:1rem 1.5rem;font-weight:700;color:#64748b;">
                                        #{{ $index + 1 }}
                                    </td>
                                    <td style="padding:1rem 1.5rem;font-weight:700;color:#1e293b;">
                                        {{ $stat->municipality_name }}
                                    </td>
                                    <td style="padding:1rem 1.5rem;text-align:center;font-weight:600;color:#1e293b;">
                                        {{ number_format($stat->total_violations) }}
                                    </td>
                                    <td style="padding:1rem 1.5rem;text-align:center;color:#16a34a;font-weight:600;">
                                        {{ number_format($stat->settled_violations) }}
                                    </td>
                                    <td style="padding:1rem 1.5rem;text-align:center;color:#dc2626;font-weight:600;">
                                        {{ number_format($stat->total_violations - $stat->settled_violations) }}
                                    </td>
                                    <td style="padding:1rem 1.5rem;text-align:center;">
                                        <span class="badge" style="background:{{ $stat->settled_rate >= 70 ? '#d1fae5;color:#065f46;' : ($stat->settled_rate >= 40 ? '#fef3c7;color:#92400e;' : '#fee2e2;color:#991b1b;') }};padding:.35rem .65rem;border-radius:6px;font-size:0.8rem;">
                                            {{ $stat->settled_rate }}%
                                         </span>
                                    </td>
                                    <td style="padding:1rem 1.5rem;">
                                        @php
                                            $percentage = round(($stat->total_violations / $maxViolations) * 100);
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                             <div class="progress flex-grow-1" style="height:6px;border-radius:3px;background-color:#f1f5f9;">
                                                 <div class="progress-bar" style="width:{{ $percentage }}%;background-color:#3b82f6;border-radius:3px;"></div>
                                             </div>
                                             <span style="font-size:.75rem;font-weight:600;color:#64748b;width:30px;text-align:right;">{{ $percentage }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
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
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.06)',
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', borderDash: [4, 4] },
                        ticks: { precision: 0, font: { size: 11, family: "'Inter', sans-serif" } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, family: "'Inter', sans-serif" } }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
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
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '68%'
            }
        });
    }
});
</script>
@endpush
@endsection
