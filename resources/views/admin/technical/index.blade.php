@extends('layouts.app')

@section('title', 'Technical Administration')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Technical Administration</li>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #292524;"><i class="bi bi-tools text-warning me-2"></i>Technical Administration Console</h4>
            <p class="text-muted small mb-0">Manage system maintenance mode, application logs, database snapshot backups, and artisan system commands.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Maintenance Mode Toggle -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger-subtle text-danger p-2 rounded-3"><i class="bi bi-slash-circle fs-5"></i></span>
                        <div>
                            <h6 class="fw-bold mb-0">System Maintenance Mode</h6>
                            <span class="text-muted small">Control public access during scheduled upgrades</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.technical.maintenance.toggle') }}">
                        @csrf
                        <div class="form-check form-switch mb-3 p-3 bg-light rounded-3">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" value="1" {{ $maintenanceMode ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-danger" for="maintenance_mode">Enable System Maintenance Mode</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Public Notice Message</label>
                            <textarea name="maintenance_message" class="form-control @error('maintenance_message') is-invalid @enderror" rows="3" required>{{ old('maintenance_message', $maintenanceMessage) }}</textarea>
                            @error('maintenance_message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-warning text-dark fw-bold w-100 py-2 rounded-3">
                            <i class="bi bi-save me-1"></i> Update Maintenance State
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Artisan Cache & Utility Runner -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary p-2 rounded-3"><i class="bi bi-terminal fs-5"></i></span>
                        <div>
                            <h6 class="fw-bold mb-0">Artisan Utility Operations</h6>
                            <span class="text-muted small">Safely trigger administrative framework utilities</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('admin.technical.artisan') }}">
                            @csrf
                            <input type="hidden" name="command" value="cache:clear">
                            <button type="submit" class="btn btn-outline-primary w-100 text-start d-flex align-items-center justify-content-between p-3 rounded-3">
                                <div>
                                    <div class="fw-bold">Clear Application Cache</div>
                                    <div class="small text-muted">Flushes cached system data & settings</div>
                                </div>
                                <i class="bi bi-play-circle fs-5"></i>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.technical.artisan') }}">
                            @csrf
                            <input type="hidden" name="command" value="route:clear">
                            <button type="submit" class="btn btn-outline-primary w-100 text-start d-flex align-items-center justify-content-between p-3 rounded-3">
                                <div>
                                    <div class="fw-bold">Clear Route & View Cache</div>
                                    <div class="small text-muted">Recompiles route registrations and blade views</div>
                                </div>
                                <i class="bi bi-play-circle fs-5"></i>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.technical.artisan') }}">
                            @csrf
                            <input type="hidden" name="command" value="storage:link">
                            <button type="submit" class="btn btn-outline-secondary w-100 text-start d-flex align-items-center justify-content-between p-3 rounded-3">
                                <div>
                                    <div class="fw-bold">Verify Storage Symlink</div>
                                    <div class="small text-muted">Links public storage directory for media assets</div>
                                </div>
                                <i class="bi bi-play-circle fs-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Module Cards -->
    <div class="row g-4">
        <!-- Log Viewer Module -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-info-subtle text-info rounded-3"><i class="bi bi-file-earmark-text fs-3"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Application Logs (`laravel.log`)</h6>
                            <span class="text-muted small">File Size: <strong>{{ $logSizeMb }} MB</strong></span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.technical.logs.index') }}" class="btn btn-info text-white btn-sm rounded-3">
                            <i class="bi bi-eye me-1"></i> View Logs
                        </a>
                        <a href="{{ route('admin.technical.logs.download') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Backup Module -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-success-subtle text-success rounded-3"><i class="bi bi-database-check fs-3"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Database Backup & Export Snapshots</h6>
                            <span class="text-muted small">Existing Snapshots: <strong>{{ $backupsCount }} Files</strong></span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.technical.backups.index') }}" class="btn btn-success text-white btn-sm rounded-3">
                            <i class="bi bi-database me-1"></i> Manage Backups
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
