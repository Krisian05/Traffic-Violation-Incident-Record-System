@extends('layouts.app')
@section('title', 'Sync Offline Records')

@section('content')
<div class="container py-4" style="max-width:800px;">
    <div class="p-4 mb-4 rounded-3 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-left: 6px solid #3b82f6;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-arrow-repeat display-5 text-info"></i>
                <div>
                    <h4 class="fw-700 mb-1">Offline Record Synchronization</h4>
                    <p class="mb-0 text-white-50" style="font-size:.88rem;">Monitor and process field citations &amp; incident reports created while offline.</p>
                </div>
            </div>
            <button class="btn btn-primary fw-600 px-4" id="btnSyncNow" onclick="window.syncAllOfflineRecords ? window.syncAllOfflineRecords() : location.reload();">
                <i class="bi bi-cloud-upload me-1"></i> Sync Now
            </button>
        </div>
    </div>

    {{-- Sync Status Card --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h3 class="fw-700 text-primary mb-1" id="offlineViolationsCount">0</h3>
                <div class="text-muted" style="font-size:.85rem;">Pending Offline Citations</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h3 class="fw-700 text-warning mb-1" id="offlineIncidentsCount">0</h3>
                <div class="text-muted" style="font-size:.85rem;">Pending Offline Incidents</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <h3 class="fw-700 text-success mb-1" id="syncStatusBadge"><i class="bi bi-wifi"></i> Online</h3>
                <div class="text-muted" style="font-size:.85rem;">Network Connectivity</div>
            </div>
        </div>
    </div>

    {{-- Queue Detail --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-700 text-dark mb-0"><i class="bi bi-list-task me-2"></i>Pending Upload Queue</h6>
            <span class="badge bg-light text-dark" style="font-size:.78rem;">Auto-Sync Active</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:.88rem;">
                    <thead class="bg-light">
                        <tr>
                            <th>Type</th>
                            <th>Reference Key</th>
                            <th>Created Timestamp</th>
                            <th>Sync Status</th>
                        </tr>
                    </thead>
                    <tbody id="offlineQueueTable">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No pending offline transactions in queue. All records are fully synchronized.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
