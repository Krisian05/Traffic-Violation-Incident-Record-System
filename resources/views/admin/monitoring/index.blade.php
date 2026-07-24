@extends('layouts.app')

@section('title', 'Platform Monitoring Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Platform Monitoring</li>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #292524;"><i class="bi bi-activity text-success me-2"></i>Real-Time Platform & Infrastructure Monitoring</h4>
            <p class="text-muted small mb-0">Monitor database latency, server disk & memory usage, cache operations, active sessions, and key system performance metrics.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2" id="lastRefreshedBadge">
                <i class="bi bi-clock me-1 text-primary"></i> <span id="lastRefreshedTime">{{ $stats['formatted_time'] }}</span>
            </span>
            <button class="btn btn-outline-primary btn-sm rounded-3" id="refreshBtn" onclick="fetchPlatformStats()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Now
            </button>
        </div>
    </div>

    <!-- Analytics Top Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="text-muted small">Total LGUs</div>
                <div class="fs-3 fw-bold text-primary" id="statLgus">{{ $stats['analytics']['total_lgus'] }}</div>
                <div class="small text-muted">Jurisdictions</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="text-muted small">System Users</div>
                <div class="fs-3 fw-bold text-dark" id="statUsers">{{ $stats['analytics']['total_users'] }}</div>
                <div class="small text-muted">All active roles</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="text-muted small">Total Violations Recorded</div>
                <div class="fs-3 fw-bold text-danger" id="statViolations">{{ $stats['analytics']['total_violations'] }}</div>
                <div class="small text-muted">Platform-wide citations</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="text-muted small">Total Incidents</div>
                <div class="fs-3 fw-bold text-warning" id="statIncidents">{{ $stats['analytics']['total_incidents'] }}</div>
                <div class="small text-muted">Field incidents</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="text-muted small">Total Revenue Collections</div>
                <div class="fs-3 fw-bold text-success">₱<span id="statCollections">{{ $stats['analytics']['total_collections'] }}</span></div>
                <div class="small text-muted">Paid citations</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Database Health -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary p-2 rounded-3"><i class="bi bi-database fs-5"></i></span>
                        <div>
                            <h6 class="fw-bold mb-0">Database Server Connection</h6>
                            <span class="text-muted small">Primary database engine status</span>
                        </div>
                    </div>
                    <span class="badge {{ $stats['database']['status'] === 'ok' ? 'bg-success' : 'bg-danger' }} px-3 py-2 rounded-3" id="dbStatusBadge">
                        {{ strtoupper($stats['database']['status']) }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded-3">
                        <div>
                            <div class="text-muted small">Query Latency</div>
                            <div class="fs-4 fw-bold text-dark" id="dbLatency">{{ $stats['database']['latency_ms'] }} ms</div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Driver / Engine</div>
                            <div class="fw-bold text-primary" id="dbDriver">{{ strtoupper($stats['database']['driver']) }}</div>
                        </div>
                    </div>
                    <div class="text-muted small" id="dbMessage">{{ $stats['database']['message'] }}</div>
                </div>
            </div>
        </div>

        <!-- Storage & Disk Usage -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning-subtle text-warning p-2 rounded-3"><i class="bi bi-hdd-network fs-5"></i></span>
                        <div>
                            <h6 class="fw-bold mb-0">Storage & Disk Capacity</h6>
                            <span class="text-muted small">Local filesystem read/write & disk usage</span>
                        </div>
                    </div>
                    <span class="badge {{ $stats['storage']['status'] === 'ok' ? 'bg-success' : 'bg-warning' }} px-3 py-2 rounded-3" id="storageStatusBadge">
                        {{ strtoupper($stats['storage']['status']) }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold small">Disk Usage Percentage</span>
                            <span class="fw-bold small" id="diskUsedPercent">{{ $stats['storage']['used_percent'] }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" id="diskProgressBar" role="progressbar" style="width: {{ $stats['storage']['used_percent'] }}%"></div>
                        </div>
                    </div>
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded-3">
                                <div class="text-muted small">Total Capacity</div>
                                <div class="fw-bold" id="diskTotal">{{ $stats['storage']['total_gb'] }} GB</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded-3">
                                <div class="text-muted small">Free Available Space</div>
                                <div class="fw-bold text-success" id="diskFree">{{ $stats['storage']['free_gb'] }} GB</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memory & PHP Runtime -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-info-subtle text-info p-2 rounded-3"><i class="bi bi-memory fs-5"></i></span>
                        <div>
                            <h6 class="fw-bold mb-0">Memory & Runtime Environment</h6>
                            <span class="text-muted small">PHP process memory consumption & framework versions</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Current Allocated Memory</div>
                                <div class="fs-4 fw-bold text-info" id="memUsage">{{ $stats['memory']['usage_mb'] }} MB</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Peak Memory Limit</div>
                                <div class="fs-4 fw-bold text-dark" id="memPeak">{{ $stats['memory']['peak_mb'] }} MB</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small pt-2 border-top">
                        <span>Environment: <strong class="text-dark">{{ $stats['environment'] }}</strong></span>
                        <span>PHP: <strong class="text-dark">{{ $stats['php_version'] }}</strong></span>
                        <span>Laravel: <strong class="text-dark">{{ $stats['laravel_version'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services & Active Sessions -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger-subtle text-danger p-2 rounded-3"><i class="bi bi-shield-check fs-5"></i></span>
                        <div>
                            <h6 class="fw-bold mb-0">Services & Active Sessions</h6>
                            <span class="text-muted small">Cache store, smart scan OCR API, and active user sessions</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Cache Driver</div>
                                <div class="fw-bold text-dark" id="cacheDriver">{{ strtoupper($stats['cache']['driver']) }}</div>
                                <div class="small text-success"><i class="bi bi-check-circle-fill"></i> Read/Write OK</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">OCR Engine Driver</div>
                                <div class="fw-bold text-dark" id="ocrEngine">{{ strtoupper($stats['ocr']['engine']) }}</div>
                                <div class="small text-primary"><i class="bi bi-check-circle-fill"></i> Operational</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small">Active User Sessions</div>
                                    <div class="fs-4 fw-bold text-dark" id="activeSessionsCount">{{ $stats['analytics']['active_sessions'] }}</div>
                                </div>
                                <a href="{{ route('admin.security.sessions') }}" class="btn btn-outline-danger btn-sm rounded-3">
                                    Manage Sessions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function fetchPlatformStats() {
    const refreshBtn = document.getElementById('refreshBtn');
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Fetching...';

    fetch("{{ route('admin.monitoring.stats') }}")
        .then(response => response.json())
        .then(data => {
            document.getElementById('lastRefreshedTime').innerText = data.formatted_time;
            document.getElementById('statLgus').innerText = data.analytics.total_lgus;
            document.getElementById('statUsers').innerText = data.analytics.total_users;
            document.getElementById('statViolations').innerText = data.analytics.total_violations;
            document.getElementById('statIncidents').innerText = data.analytics.total_incidents;
            document.getElementById('statCollections').innerText = data.analytics.total_collections;

            document.getElementById('dbLatency').innerText = data.database.latency_ms + ' ms';
            document.getElementById('dbDriver').innerText = data.database.driver.toUpperCase();
            document.getElementById('dbMessage').innerText = data.database.message;

            document.getElementById('diskUsedPercent').innerText = data.storage.used_percent + '%';
            document.getElementById('diskProgressBar').style.width = data.storage.used_percent + '%';
            document.getElementById('diskTotal').innerText = data.storage.total_gb + ' GB';
            document.getElementById('diskFree').innerText = data.storage.free_gb + ' GB';

            document.getElementById('memUsage').innerText = data.memory.usage_mb + ' MB';
            document.getElementById('memPeak').innerText = data.memory.peak_mb + ' MB';

            document.getElementById('activeSessionsCount').innerText = data.analytics.active_sessions;
        })
        .catch(err => console.error("Error fetching platform stats:", err))
        .finally(() => {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Refresh Now';
        });
}

// Auto refresh every 30 seconds
setInterval(fetchPlatformStats, 30000);
</script>
@endpush
@endsection
