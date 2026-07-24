@extends('layouts.app')
@section('title', 'Audit Trail & Activity Logs')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Audit Trail</li>
@endsection

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.08em;">System Audit</div>
            <h4 class="fw-bold mb-0 text-dark">Audit Trail & Activity Logs</h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2 rounded-pill" style="font-size: 0.78rem;">
                <i class="bi bi-shield-check me-1"></i> Governance & Transparency
            </span>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background:#fff;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3 col-12">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;">Search Logs</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Action description, user...">
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;">Event Type</label>
                    <select name="event" class="form-select form-select-sm">
                        <option value="">All Events</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev }}" {{ $event === $ev ? 'selected' : '' }}>{{ ucfirst($ev) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;">Date From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-2 col-6">
                    <label class="form-label text-muted fw-bold mb-1" style="font-size:0.7rem;">Date To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>

                <div class="col-md-3 col-12 d-flex gap-2 align-self-end mt-2 mt-md-0">
                    <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3 flex-grow-1 fw-bold">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    @if($search || $event || $dateFrom || $dateTo || $causer)
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-3 fw-bold">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Audit Logs Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#fff;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="bg-light text-muted" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>User / Actor</th>
                        <th>Event</th>
                        <th>Subject Model</th>
                        <th>Description / Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 text-nowrap" style="font-size:0.8rem;">
                                <div class="fw-bold text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size:0.72rem;">{{ $log->created_at->format('h:i:s A') }}</div>
                            </td>
                            <td>
                                @if($log->causer)
                                    <div class="fw-bold text-dark">{{ $log->causer->name }}</div>
                                    <div class="text-muted" style="font-size:0.72rem;">{{ ucfirst(str_replace('_', ' ', $log->causer->role)) }}</div>
                                @else
                                    <span class="badge bg-secondary text-white">System / Visitor</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($log->event) {
                                        'created' => 'bg-success',
                                        'updated' => 'bg-info text-dark',
                                        'deleted' => 'bg-danger',
                                        'login'   => 'bg-primary',
                                        default   => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size:0.7rem;">
                                    {{ strtoupper($log->event ?? $log->log_name) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size:0.7rem;">
                                    {{ class_basename($log->subject_type ?? 'Record') }} #{{ $log->subject_id ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->description }}</div>
                                @if($log->properties && count($log->properties) > 0)
                                    <details class="mt-1" style="font-size:0.72rem;">
                                        <summary class="text-primary cursor-pointer">View Changes</summary>
                                        <pre class="bg-light p-2 rounded mt-1 mb-0 border" style="max-height:120px; overflow-y:auto;">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-x display-6 d-block mb-2 text-secondary"></i>
                                No audit log records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted" style="font-size:0.78rem;">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} audit entries
                </div>
                <nav>
                    <ul class="aud-pager">
                        <li>
                            @if($logs->onFirstPage())
                                <span class="aud-page aud-page-disabled"><i class="bi bi-chevron-left"></i></span>
                            @else
                                <a class="aud-page" href="{{ $logs->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
                            @endif
                        </li>
                        @php
                            $cur = $logs->currentPage();
                            $last = $logs->lastPage();
                            $pages = collect();
                            for ($p = 1; $p <= $last; $p++) {
                                if ($p === 1 || $p === $last || abs($p - $cur) <= 2) $pages->push($p);
                            }
                            $pages = $pages->unique()->sort()->values();
                        @endphp
                        @foreach($pages as $i => $p)
                            @if($i > 0 && $p - $pages[$i - 1] > 1)
                                <li><span class="aud-page aud-page-ellipsis">&hellip;</span></li>
                            @endif
                            <li>
                                @if($p === $cur)
                                    <span class="aud-page aud-page-active">{{ $p }}</span>
                                @else
                                    <a class="aud-page" href="{{ $logs->url($p) }}">{{ $p }}</a>
                                @endif
                            </li>
                        @endforeach
                        <li>
                            @if($logs->hasMorePages())
                                <a class="aud-page" href="{{ $logs->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                            @else
                                <span class="aud-page aud-page-disabled"><i class="bi bi-chevron-right"></i></span>
                            @endif
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    </div>
</div>

<style>
.aud-pager { display: flex; align-items: center; gap: .25rem; list-style: none; margin: 0; padding: 0; }
.aud-page {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 .55rem;
    border-radius: 8px;
    font-size: .78rem; font-weight: 600;
    border: 1.5px solid #e7dfd5;
    color: #57534e;
    background: #fff;
    text-decoration: none;
    transition: all .15s;
    cursor: pointer;
}
a.aud-page:hover { background: #fdf8f0; border-color: #dc2626; color: #dc2626; }
.aud-page-active { background: linear-gradient(135deg, #dc2626, #b91c1c); border-color: #dc2626; color: #fff; box-shadow: 0 2px 8px rgba(220,38,38,.3); cursor: default; }
.aud-page-disabled { color: #d6d3d1; border-color: #f0ebe3; background: #fafaf9; cursor: default; }
.aud-page-ellipsis { border-color: transparent; background: transparent; color: #a8a29e; cursor: default; font-size: .85rem; }
</style>
@endsection
