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
                <div style="font-size:.8rem;color:#94a3b8;font-weight:500;">Recorded in {{ $year }} across all LGUs</div>
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
                <div style="font-size:1.05rem;font-weight:700;color:#1e293b;">LGU Breakdown</div>
                <div style="font-size:.85rem;color:#64748b;">Top municipalities by violation count</div>
            </div>
            <div class="card-body p-0">
                @if($municipalityStats->isEmpty())
                <div class="p-4 text-center text-muted" style="font-size:.9rem;font-style:italic;">No data available.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.88rem;">
                        <tbody>
                            @foreach($municipalityStats as $stat)
                            <tr>
                                <td style="padding:.75rem 1.5rem;">
                                    <div style="font-weight:700;color:#1e293b;">{{ $stat->municipality_name }}</div>
                                    <div style="font-size:.75rem;color:#64748b;">{{ $stat->settled_rate }}% settlement rate</div>
                                </td>
                                <td style="padding:.75rem 1.5rem;text-align:right;">
                                    <span class="badge" style="background:#f1f5f9;color:#334155;font-size:.85rem;">{{ number_format($stat->total_violations) }}</span>
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
    if(!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Violations',
                data: @json($chartData),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
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
});
</script>
@endpush
@endsection
