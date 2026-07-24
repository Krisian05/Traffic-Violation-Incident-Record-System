@extends('layouts.app')

@section('title', 'Database Backup Snapshots')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.technical.index') }}">Technical Administration</a></li>
    <li class="breadcrumb-item active">Database Backups</li>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #292524;"><i class="bi bi-database-fill-check text-success me-2"></i>Database Snapshot & Export Backups</h4>
            <p class="text-muted small mb-0">Create on-demand database snapshots, download structured export archives, and manage retention.</p>
        </div>
        <div>
            <form method="POST" action="{{ route('admin.technical.backups.create') }}">
                @csrf
                <button type="submit" class="btn btn-success text-white btn-sm rounded-3 fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Generate On-Demand Backup Snapshot
                </button>
            </form>
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

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h6 class="fw-bold mb-0"><i class="bi bi-archive me-2 text-primary"></i>Available Database Backups ({{ count($backups) }})</h6>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th>Backup Filename</th>
                            <th>File Size</th>
                            <th>Created Timestamp</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark font-monospace"><i class="bi bi-file-earmark-zip me-2 text-success"></i>{{ $backup['filename'] }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $backup['size_mb'] }} MB</span>
                                </td>
                                <td class="small text-muted">
                                    {{ $backup['created_at'] }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.technical.backups.download', $backup['filename']) }}" class="btn btn-outline-primary btn-sm rounded-2 me-1">
                                        <i class="bi bi-download me-1"></i> Download
                                    </a>
                                    <form method="POST" action="{{ route('admin.technical.backups.destroy', $backup['filename']) }}" class="d-inline" onsubmit="return confirm('Delete this backup snapshot?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-2">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="bi bi-database fs-1 d-block mb-2 text-secondary"></i>
                                    No database backup snapshots found. Click "Generate On-Demand Backup Snapshot" above to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
