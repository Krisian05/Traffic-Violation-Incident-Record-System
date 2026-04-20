@extends('layouts.mobile')
@section('title', 'Violations')
@section('back_url', route('officer.dashboard'))

@push('styles')
@include('partials.motshow-styles')
<style>
.vio-search-shell {
    background: #fff;
    border: 1px solid rgba(15,23,42,.05);
    border-radius: 18px;
    padding: .75rem;
    margin-bottom: 1rem;
    box-shadow: 0 6px 20px rgba(15,23,42,.06);
}
.vio-search-bar {
    display: flex;
    align-items: center;
    gap: .65rem;
    background: #f8fafc;
    border: 1px solid #dbe5f1;
    border-radius: 16px;
    padding: .2rem .22rem .2rem .8rem;
}
.vio-search-icon {
    color: #64748b;
    font-size: 1rem;
    flex-shrink: 0;
}
.vio-search-input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    min-height: 44px;
    font-size: .92rem;
    padding: 0 !important;
}
.vio-search-input::placeholder {
    color: #94a3b8;
}
.vio-search-submit {
    border: none;
    border-radius: 14px;
    min-height: 44px;
    padding: 0 1rem;
    font-size: .84rem;
    font-weight: 800;
    color: #fff;
    background: linear-gradient(135deg,#dc2626,#b91c1c);
    box-shadow: 0 6px 16px rgba(220,38,38,.24);
    flex-shrink: 0;
}
.vio-filter-row {
    display: flex;
    gap: .6rem;
    margin-top: .75rem;
}
.vio-filter-select {
    min-height: 44px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #334155;
    padding: 0 .9rem;
    font-size: .84rem;
    font-weight: 700;
    box-shadow: 0 1px 3px rgba(15,23,42,.04);
    flex: 1;
}
.vio-filter-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .7rem;
    margin-top: .75rem;
    flex-wrap: wrap;
}
.vio-filter-pills {
    display: flex;
    gap: .4rem;
    flex-wrap: wrap;
}
.vio-pill {
    display: inline-flex;
    align-items: center;
    gap: .28rem;
    border-radius: 999px;
    padding: .22rem .62rem;
    font-size: .68rem;
    font-weight: 700;
}
.vio-pill--search {
    background: #fff1f2;
    color: #dc2626;
    border: 1px solid #fecdd3;
}
.vio-pill--status {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.vio-filter-meta {
    font-size: .72rem;
    color: #64748b;
    font-weight: 600;
}
.vio-list-card {
    display: flex;
    align-items: center;
    gap: .9rem;
    background: #fff;
    border-radius: 18px;
    padding: .95rem 1rem .95rem .95rem;
    text-decoration: none;
    color: inherit;
    margin-bottom: .72rem;
    border: 1px solid rgba(15,23,42,.045);
    box-shadow: 0 3px 14px rgba(15,23,42,.06);
    position: relative;
    overflow: hidden;
}
.vio-list-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
}
.vio-list-card--overdue::before { background: linear-gradient(180deg,#dc2626,#b91c1c); }
.vio-list-card--pending::before { background: linear-gradient(180deg,#f59e0b,#d97706); }
.vio-list-card--settled::before { background: linear-gradient(180deg,#16a34a,#15803d); }
.vio-list-card--contested::before { background: linear-gradient(180deg,#7c3aed,#6d28d9); }
.vio-list-icon {
    width: 46px;
    height: 46px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 5px 14px rgba(15,23,42,.14);
}
.vio-list-icon--overdue { background: linear-gradient(135deg,#dc2626,#b91c1c); }
.vio-list-icon--pending { background: linear-gradient(135deg,#f59e0b,#d97706); }
.vio-list-icon--settled { background: linear-gradient(135deg,#16a34a,#15803d); }
.vio-list-icon--contested { background: linear-gradient(135deg,#7c3aed,#6d28d9); }
.vio-list-title {
    font-size: .88rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
}
.vio-list-meta {
    font-size: .71rem;
    color: #64748b;
    margin-top: .14rem;
    line-height: 1.46;
}
.vio-list-submeta {
    font-size: .67rem;
    color: #94a3b8;
    margin-top: .2rem;
}
.vio-status-tag {
    display: inline-flex;
    align-items: center;
    gap: .24rem;
    border-radius: 999px;
    padding: .14rem .5rem;
    font-size: .58rem;
    font-weight: 800;
    margin-top: .34rem;
}
.vio-status-tag--overdue { background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; }
.vio-status-tag--pending { background: #fffbeb; color: #92400e; border: 1px solid #fcd34d; }
.vio-status-tag--settled { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
.vio-status-tag--contested { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
.vio-pagination-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}
</style>
@endpush

@section('content')
@php
    $statusDisplay = ['pending' => 'Pending', 'settled' => 'Settled', 'contested' => 'Contested'][(string) $status] ?? '';
@endphp

<div class="motshow-section">
    @if($status === 'pending') Pending Violations
    @elseif($status === 'settled') Settled Violations
    @elseif($status === 'contested') Contested Violations
    @else Violation Search
    @endif
</div>

<form method="GET" action="{{ route('officer.violations.index') }}" class="vio-search-shell">
    <div class="vio-search-bar">
        <i class="ph ph-magnifying-glass vio-search-icon"></i>
        <input
            type="text"
            name="search"
            value="{{ $search }}"
            class="form-control vio-search-input"
            placeholder="Name, ticket no., plate, or location..."
        >
        <button class="vio-search-submit" type="submit">Search</button>
    </div>

    <div class="vio-filter-row">
        <select name="status" class="vio-filter-select" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="settled" {{ $status === 'settled' ? 'selected' : '' }}>Settled</option>
            <option value="contested" {{ $status === 'contested' ? 'selected' : '' }}>Contested</option>
        </select>
    </div>

    <div class="vio-filter-summary">
        <div class="vio-filter-pills">
            @if($search)
                <span class="vio-pill vio-pill--search">
                    <i class="ph ph-magnifying-glass"></i>
                    {{ $search }}
                </span>
            @endif

            @if($statusDisplay)
                <span class="vio-pill vio-pill--status">
                    <i class="ph ph-funnel"></i>
                    {{ $statusDisplay }}
                </span>
            @endif

            @if($search || $status)
                <a href="{{ route('officer.violations.index') }}" class="vio-pill vio-pill--search" style="text-decoration:none;">
                    <i class="ph ph-x-circle"></i>
                    Clear
                </a>
            @endif
        </div>

        <div class="vio-filter-meta">
            {{ $violations->total() }} violation{{ $violations->total() !== 1 ? 's' : '' }} found
        </div>
    </div>
</form>

<div class="motshow-section">Violation Records</div>

@if($violations->isEmpty())
    <div class="mob-card">
        <div class="mob-empty">
            <i class="ph ph-warning-circle mob-empty-icon"></i>
            <div class="mob-empty-text">No violations found</div>
            <div class="mob-empty-sub">
                @if($search || $status)
                    <a href="{{ route('officer.violations.index') }}" style="color:#1d4ed8;font-weight:700;text-decoration:none;">Clear filters</a>
                @else
                    No violation records match this view yet.
                @endif
            </div>
        </div>
    </div>
@else
    @foreach($violations as $violation)
        @php
            $isOverdue = $violation->isOverdue();
            $displayStatus = $isOverdue ? 'overdue' : ($violation->status ?? 'pending');
            $variant = in_array($displayStatus, ['overdue', 'pending', 'settled', 'contested'], true) ? $displayStatus : 'pending';
            $statusLabel = [
                'overdue' => 'Overdue',
                'pending' => 'Pending',
                'settled' => 'Settled',
                'contested' => 'Contested',
            ][$variant];
            $icon = [
                'overdue' => 'ph-fill ph-warning-octagon',
                'pending' => 'ph-fill ph-clock',
                'settled' => 'ph-fill ph-check-circle',
                'contested' => 'ph-fill ph-scales',
            ][$variant];
            $violatorName = $violation->violator?->full_name ?? $violation->vehicle_owner_name ?? 'Unknown Motorist';
            $plate = $violation->vehicle?->plate_number ?? $violation->vehicle_plate;
        @endphp

        <a href="{{ route('officer.violations.show', $violation) }}" class="vio-list-card vio-list-card--{{ $variant }}">
            <div class="vio-list-icon vio-list-icon--{{ $variant }}">
                <i class="{{ $icon }}"></i>
            </div>

            <div style="flex:1;min-width:0;">
                <div class="vio-list-title">
                    {{ $violation->violationType?->name ?? 'Violation Record' }}
                </div>

                <div class="vio-list-meta">
                    <strong>{{ $violatorName }}</strong>
                    @if($violation->location)
                        <span style="margin:0 .34rem;color:#cbd5e1;">·</span>
                        {{ \Illuminate\Support\Str::limit($violation->location, 32) }}
                    @endif
                </div>

                <div class="vio-list-submeta">
                    @if($violation->ticket_number)
                        Ticket #{{ $violation->ticket_number }}
                    @else
                        No ticket number
                    @endif

                    @if($plate)
                        <span style="margin:0 .34rem;color:#cbd5e1;">·</span>
                        Plate {{ $plate }}
                    @endif

                    @if($violation->date_of_violation)
                        <span style="margin:0 .34rem;color:#cbd5e1;">·</span>
                        {{ $violation->date_of_violation->format('M d, Y') }}
                    @endif
                </div>

                <span class="vio-status-tag vio-status-tag--{{ $variant }}">
                    <i class="{{ $icon }}"></i>
                    {{ $statusLabel }}
                    @if($isOverdue)
                        <span>· beyond 72h window</span>
                    @endif
                </span>
            </div>

            <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.88rem;flex-shrink:0;"></i>
        </a>
    @endforeach

    @if($violations->hasPages())
        <div class="vio-pagination-wrap">
            {{ $violations->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
@endif

@endsection
