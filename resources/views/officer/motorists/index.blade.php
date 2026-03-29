@extends('layouts.mobile')
@section('title', 'Motorists')

@section('content')

{{-- ── Search ── --}}
<form method="GET" action="{{ route('officer.motorists.index') }}" class="mb-3">
    <div class="input-group" style="border-radius:12px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.07);">
        <span class="input-group-text" style="background:#fff;border-color:#e2e8f0;border-right:none;padding-left:.875rem;">
            <i class="ph ph-magnifying-glass" style="color:#94a3b8;"></i>
        </span>
        <input type="text" name="search" value="{{ $search }}"
               class="form-control mob-input"
               style="border-left:none;border-color:#e2e8f0;"
               placeholder="Name or license number...">
        <button class="btn" type="submit"
                style="background:linear-gradient(135deg,#1d4ed8,#1e40af);color:#fff;border:none;padding:0 1rem;font-weight:700;font-size:.85rem;min-height:44px;">
            Search
        </button>
    </div>
</form>

{{-- ── List ── --}}
@if($violators->isEmpty())
    <div class="mob-card">
        <div class="mob-empty">
            <i class="ph ph-users mob-empty-icon"></i>
            <div class="mob-empty-text">No motorists found</div>
            @if($search)
            <div class="mob-empty-sub">
                <a href="{{ route('officer.motorists.index') }}" style="color:#1d4ed8;font-weight:600;">Clear search</a>
            </div>
            @endif
        </div>
    </div>
@else
    <div class="mob-card mb-3">
        @foreach($violators as $v)
        <a href="{{ route('officer.motorists.show', $v) }}" class="mob-list-item">
            <div style="width:42px;height:42px;border-radius:13px;background:linear-gradient(135deg,#1d4ed8,#1e40af);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:.875rem;font-size:.95rem;font-weight:800;color:#fff;box-shadow:0 2px 8px rgba(29,78,216,.2);">
                {{ strtoupper(substr($v->first_name, 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.9rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $v->last_name }}, {{ $v->first_name }}
                    @if($v->middle_name) {{ substr($v->middle_name,0,1) }}. @endif
                </div>
                <div style="font-size:.72rem;color:#94a3b8;margin-top:.05rem;">
                    @if($v->license_number)
                        <i class="ph ph-identification-badge me-1"></i>{{ $v->license_number }}
                    @else
                        <span style="color:#c0cad8;">No license on file</span>
                    @endif
                    <span style="margin:0 .3rem;color:#dde1e7;">·</span>
                    {{ $v->violations_count }} violation{{ $v->violations_count !== 1 ? 's' : '' }}
                </div>
                @if($v->violations_count >= 3)
                    <span style="display:inline-flex;align-items:center;gap:.2rem;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;border-radius:20px;font-size:.6rem;font-weight:700;padding:.1rem .45rem;margin-top:.25rem;">
                        <i class="ph-fill ph-fire" style="font-size:.65rem;"></i> Recidivist
                    </span>
                @elseif($v->violations_count == 2)
                    <span style="display:inline-flex;align-items:center;gap:.2rem;background:#fffbeb;color:#92400e;border:1px solid #fcd34d;border-radius:20px;font-size:.6rem;font-weight:700;padding:.1rem .45rem;margin-top:.25rem;">
                        <i class="ph-fill ph-shield-warning" style="font-size:.65rem;"></i> Repeat Offender
                    </span>
                @endif
            </div>
            <i class="ph ph-caret-right" style="color:#d6d3d1;font-size:.8rem;flex-shrink:0;margin-left:.5rem;"></i>
        </a>
        @endforeach
    </div>

    @if($violators->hasPages())
    <div class="d-flex justify-content-center mb-4">
        {{ $violators->links() }}
    </div>
    @endif
@endif

{{-- FAB --}}
<a href="{{ route('officer.motorists.create') }}" class="mob-fab" title="Add Motorist">
    <i class="ph-bold ph-plus"></i>
</a>

@endsection
