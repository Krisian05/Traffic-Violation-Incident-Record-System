@extends('layouts.app')

@section('title', 'Application Logs')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.technical.index') }}">Technical Administration</a></li>
    <li class="breadcrumb-item active">Application Logs</li>
@endsection

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #292524;"><i class="bi bi-file-earmark-code-fill text-info me-2"></i>Application System Logs (`laravel.log`)</h4>
            <p class="text-muted small mb-0">Inspect real-time application log stream, filter by severity level, search stack trace lines, clear or download log files.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.technical.logs.download') }}" class="btn btn-outline-primary btn-sm rounded-3">
                <i class="bi bi-download me-1"></i> Download Log File ({{ $logSizeMb }} MB)
            </a>
            <form method="POST" action="{{ route('admin.technical.logs.clear') }}" onsubmit="return confirm('Clear application log file contents?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                    <i class="bi bi-trash me-1"></i> Clear Log
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

    <!-- Log Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.technical.logs.index') }}" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <select name="level" class="form-select">
                        <option value="">All Log Levels</option>
                        <option value="error" {{ $level === 'error' ? 'selected' : '' }}>ERROR</option>
                        <option value="warning" {{ $level === 'warning' ? 'selected' : '' }}>WARNING</option>
                        <option value="info" {{ $level === 'info' ? 'selected' : '' }}>INFO</option>
                        <option value="debug" {{ $level === 'debug' ? 'selected' : '' }}>DEBUG</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search log entries or keywords..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-info text-white fw-bold w-100 rounded-3">
                        <i class="bi bi-filter me-1"></i> Filter Logs
                    </button>
                    <a href="{{ route('admin.technical.logs.index') }}" class="btn btn-outline-secondary rounded-3">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Log Content Box -->
    <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
        <div class="card-header bg-dark border-secondary pt-3 px-4 pb-2 d-flex justify-content-between align-items-center">
            <span class="small text-muted font-monospace"><i class="bi bi-terminal-fill text-success me-2"></i>storage/logs/laravel.log</span>
            <span class="badge bg-secondary small font-monospace">{{ count($logLines) }} entries shown</span>
        </div>
        <div class="card-body p-4 font-monospace small" style="max-height: 600px; overflow-y: auto; background-color: #0d1117; color: #c9d1d9; border-radius: 0 0 16px 16px;">
            @forelse($logLines as $line)
                <div class="py-1 border-bottom border-secondary border-opacity-25 text-wrap">
                    @if(str_contains(strtolower($line), '.error:') || str_contains(strtolower($line), 'local.error'))
                        <span class="text-danger fw-bold">[ERROR]</span>
                    @elseif(str_contains(strtolower($line), '.warning:') || str_contains(strtolower($line), 'local.warning'))
                        <span class="text-warning fw-bold">[WARNING]</span>
                    @elseif(str_contains(strtolower($line), '.info:') || str_contains(strtolower($line), 'local.info'))
                        <span class="text-info fw-bold">[INFO]</span>
                    @else
                        <span class="text-success">[LOG]</span>
                    @endif
                    <span class="ms-2" style="color: #e6edf3;">{{ $line }}</span>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-file-earmark-check fs-1 d-block mb-2 text-secondary"></i>
                    No matching log entries found.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
