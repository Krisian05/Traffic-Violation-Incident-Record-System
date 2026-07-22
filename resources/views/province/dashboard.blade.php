@extends('layouts.app')

@section('title', 'Provincial Command Dashboard')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-rotate@0.2.1/dist/leaflet-rotate.css" />
<style>
    #provinceMap {
        height: 360px;
        width: 100%;
        border-radius: 12px;
        z-index: 1;
    }
    .hotspot-pulse-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hotspot-pulse-ring {
        width: 24px;
        height: 24px;
        background: rgba(220, 38, 38, 0.95);
        border: 2px solid #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.8);
        animation: hotspotPulseAnim 1.6s infinite ease-in-out;
    }
    @keyframes hotspotPulseAnim {
        0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.85); }
        70% { transform: scale(1.35); box-shadow: 0 0 0 14px rgba(220, 38, 38, 0); }
        100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }
    .gis-legend {
        background: rgba(255, 255, 255, 0.95);
        padding: 8px 12px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        font-size: 0.74rem;
        line-height: 1.5;
    }
    .map-rotate-toolbar {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.92);
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        padding: 4px;
        display: flex;
        gap: 4px;
    }
    .map-rotate-btn {
        border: none;
        background: #f8fafc;
        color: #1e293b;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .map-rotate-btn:hover {
        background: #1d4ed8;
        color: #fff;
    }
</style>
@endpush

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
                <div class="text-uppercase fw-bold text-primary" style="font-size:.68rem;letter-spacing:.08em;">Province of Cebu • Provincial Command Hub</div>
                <h3 class="mb-0 fw-extrabold text-dark">Provincial Traffic Intelligence & Command Dashboard</h3>
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

    {{-- Section 1 & 3: Province-Wide Statistics & Collection Performance KPI Cards --}}
    <div class="row g-3 mb-4">
        <!-- Total Citations -->
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

        <!-- Revenue Collected -->
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
                    <div class="text-muted" style="font-size:.75rem;">Settled fine revenue in {{ $year }}</div>
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

        <!-- Active Officers & Database -->
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

    {{-- Section 7: Monthly Enforcement Trend Analysis & Category Distribution --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Monthly Enforcement Trend Analysis ({{ $year }})</h6>
                        <span class="text-muted" style="font-size:.78rem;">Periodic enforcement activity trends across the province</span>
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
                    <h6 class="mb-0 fw-bold text-dark">Violation Category Split</h6>
                    <span class="text-muted" style="font-size:.78rem;">Distribution of top ordinance categories</span>
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

    {{-- Section 4: Combined 360° Rotatable GIS Heatmap (Violations & Incidents Hotspots) --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-compass-fill text-danger me-1"></i> Province-Wide 360° Rotatable GIS Heatmap</h6>
                        <span class="text-muted" style="font-size:.78rem;">Rotatable interactive map showing precise violation & incident hotspot markers</span>
                    </div>
                    <span class="badge bg-danger-subtle text-danger fw-bold" style="font-size:.75rem;">{{ count($mapPoints) }} Mapped GIS Points</span>
                </div>
                <div class="card-body p-3 position-relative">
                    <div id="provinceMap"></div>
                    <div class="map-rotate-toolbar">
                        <button type="button" class="map-rotate-btn" id="btnRotateLeft" title="Rotate Counter-Clockwise"><i class="bi bi-arrow-counterclockwise"></i> -45°</button>
                        <button type="button" class="map-rotate-btn" id="btnRotateReset" title="Reset North"><i class="bi bi-compass"></i> North</button>
                        <button type="button" class="map-rotate-btn" id="btnRotateRight" title="Rotate Clockwise"><i class="bi bi-arrow-clockwise"></i> +45°</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-fire text-danger me-1"></i> High-Incidence Violation & Incident Hotspots</h6>
                    <span class="text-muted" style="font-size:.78rem;">Concentrated violation and incident areas requiring targeted officer deployment</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($provincialHotspots as $idx => $spot)
                            <div class="list-group-item px-3 py-2.5 d-flex align-items-center justify-content-between border-bottom-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger text-white rounded-circle" style="width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;">{{ $idx + 1 }}</span>
                                    <span class="fw-semibold text-dark" style="font-size:.84rem;">{{ $spot->location }}</span>
                                </div>
                                <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill" style="font-size:.75rem;">{{ number_format($spot->total) }} incidents & citations</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted" style="font-size:.85rem;">No hotspot locations recorded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 5 & 6: Repeat Offender Intelligence & Officer Productivity --}}
    <div class="row g-4 mb-4">
        {{-- Repeat Offender Monitoring --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-exclamation text-warning me-1"></i> Repeat Offender Intelligence</h6>
                        <span class="text-muted" style="font-size:.78rem;">Recurring violators across the province</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.84rem;">
                        <thead class="bg-light text-muted" style="font-size:.72rem;text-transform:uppercase;">
                            <tr>
                                <th class="ps-3">Violator Name</th>
                                <th>License No</th>
                                <th class="text-center">Total Citations</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($repeatOffenders as $offender)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">{{ $offender->full_name }}</td>
                                    <td class="text-muted">{{ $offender->license_number ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning-subtle text-warning-emphasis fw-bold rounded-pill" style="font-size:.75rem;">
                                            {{ $offender->violations_count }} violations
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('violators.show', $offender->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-0" style="font-size:.75rem;">View Record</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted" style="font-size:.85rem;">No repeat offenders recorded for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Officer Productivity --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-award-fill text-success me-1"></i> Officer & Unit Productivity</h6>
                        <span class="text-muted" style="font-size:.78rem;">Top performing enforcement officers</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.84rem;">
                        <thead class="bg-light text-muted" style="font-size:.72rem;text-transform:uppercase;">
                            <tr>
                                <th class="ps-3">Officer Name</th>
                                <th>Role</th>
                                <th class="text-center">Issued</th>
                                <th class="text-center">Settled Ratio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topOfficers as $officer)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">{{ $officer->name }}</td>
                                    <td class="text-muted">{{ ucfirst(str_replace('_', ' ', $officer->role)) }}</td>
                                    <td class="text-center fw-bold text-dark">{{ number_format($officer->total_issued) }}</td>
                                    <td class="text-center">
                                        @php
                                            $ratio = $officer->total_issued > 0 ? round(($officer->total_settled / $officer->total_issued) * 100) : 0;
                                        @endphp
                                        <span class="badge bg-success-subtle text-success fw-bold rounded-pill" style="font-size:.75rem;">{{ $ratio }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted" style="font-size:.85rem;">No officer productivity data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 2: LGU Enforcement Performance Ranking --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#fff;">
                <div class="card-header bg-white p-3 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Comparative LGU Performance Matrix</h6>
                        <span class="text-muted" style="font-size:.78rem;">Comparative analytics and compliance monitoring across municipalities in Cebu</span>
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
                                <th class="text-center">Compliance & Settlement Rate</th>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-rotate@0.2.1/dist/leaflet-rotate.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Trend Line Chart ──
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

    // ── Category Split Doughnut Chart ──
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

    // ── 360° Rotatable GIS Leaflet Map ──
    const mapEl = document.getElementById('provinceMap');
    if (mapEl) {
        let currentBearing = 0;
        const mapOptions = {
            center: [10.3157, 123.8854],
            zoom: 9,
            rotate: true,
            touchRotate: true,
            rotateControl: false,
            bearing: 0
        };

        const map = L.map('provinceMap', mapOptions);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Rotation Controls
        const btnLeft = document.getElementById('btnRotateLeft');
        const btnRight = document.getElementById('btnRotateRight');
        const btnReset = document.getElementById('btnRotateReset');

        if (btnLeft) {
            btnLeft.addEventListener('click', function() {
                currentBearing = (currentBearing - 45 + 360) % 360;
                if (typeof map.setBearing === 'function') map.setBearing(currentBearing);
            });
        }

        if (btnRight) {
            btnRight.addEventListener('click', function() {
                currentBearing = (currentBearing + 45) % 360;
                if (typeof map.setBearing === 'function') map.setBearing(currentBearing);
            });
        }

        if (btnReset) {
            btnReset.addEventListener('click', function() {
                currentBearing = 0;
                if (typeof map.setBearing === 'function') map.setBearing(0);
            });
        }

        // Add GIS Legend Control
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function() {
            const div = L.DomUtil.create('div', 'gis-legend');
            div.innerHTML = `
                <div class="fw-bold mb-1" style="font-size:0.72rem;color:#0f172a;">GIS Map Legend</div>
                <div class="d-flex align-items-center gap-1.5"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#dc2626;box-shadow:0 0 4px #dc2626;"></span> <span style="font-size:0.68rem;">🔴 High-Incidence Hotspot</span></div>
                <div class="d-flex align-items-center gap-1.5"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#1d4ed8;"></span> <span style="font-size:0.68rem;">🔵 Traffic Violation Point</span></div>
                <div class="d-flex align-items-center gap-1.5"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#f97316;"></span> <span style="font-size:0.68rem;">🟠 Traffic Incident Point</span></div>
                <div class="mt-1 text-muted" style="font-size:0.62rem;">Shift+Drag or Right-Click Drag to Rotate</div>
            `;
            return div;
        };
        legend.addTo(map);

        const points = @json($mapPoints);
        if (points && points.length > 0) {
            const bounds = [];
            
            // Group coordinates to identify hotspot concentration
            const locationCounts = {};
            points.forEach(function(pt) {
                if (pt.gps_lat && pt.gps_lng) {
                    const key = pt.gps_lat.toFixed(4) + ',' + pt.gps_lng.toFixed(4);
                    locationCounts[key] = (locationCounts[key] || 0) + 1;
                }
            });

            const hotspotPulseIcon = L.divIcon({
                className: 'hotspot-pulse-container',
                html: '<div class="hotspot-pulse-ring"></div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            points.forEach(function(pt) {
                if (pt.gps_lat && pt.gps_lng) {
                    const lat = parseFloat(pt.gps_lat);
                    const lng = parseFloat(pt.gps_lng);
                    bounds.push([lat, lng]);

                    const key = lat.toFixed(4) + ',' + lng.toFixed(4);
                    const isHotspot = (locationCounts[key] && locationCounts[key] >= 2);

                    if (isHotspot) {
                        // Render glowing Red Dot Pulsing Hotspot Marker at exact location
                        L.marker([lat, lng], { icon: hotspotPulseIcon }).addTo(map)
                        .bindPopup('<div class="p-1"><span class="badge bg-danger mb-1">🔴 HIGH INCIDENT HOTSPOT AREA</span><br><b>' + pt.title + '</b><br>' + (pt.location || 'Cebu') + '<br><small class="text-muted">' + (pt.lgu ? pt.lgu + ' • ' : '') + pt.date + '</small></div>');
                    } else if (pt.category === 'incident') {
                        // Render Traffic Incident Marker (Orange) at exact location
                        L.circleMarker([lat, lng], {
                            radius: 7,
                            fillColor: '#f97316',
                            color: '#c2410c',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.85
                        }).addTo(map)
                        .bindPopup('<div class="p-1"><span class="badge bg-warning text-dark mb-1">🟠 Traffic Incident</span><br><b>' + pt.title + '</b><br>' + (pt.location || 'Cebu') + '<br><small class="text-muted">' + (pt.lgu ? pt.lgu + ' • ' : '') + pt.date + '</small></div>');
                    } else {
                        // Render Traffic Violation Marker (Red) at exact location
                        L.circleMarker([lat, lng], {
                            radius: 7,
                            fillColor: '#dc2626',
                            color: '#991b1b',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.85
                        }).addTo(map)
                        .bindPopup('<div class="p-1"><span class="badge bg-danger mb-1">🔴 Traffic Violation</span><br><b>' + pt.title + '</b><br>' + (pt.location || 'Cebu') + '<br><small class="text-muted">' + (pt.lgu ? pt.lgu + ' • ' : '') + pt.date + '</small></div>');
                    }
                }
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }
        }
    }
});
</script>
@endpush
@endsection
