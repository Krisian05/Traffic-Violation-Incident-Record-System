@extends('layouts.mobile')
@section('title', 'Dashboard')

@section('content')

<style>
/* ── Hero ── */
.db-hero {
    background: linear-gradient(160deg, #0f2167 0%, #1d4ed8 55%, #1e40af 100%);
    border-radius: 22px;
    padding: 1.3rem 1.25rem;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 36px rgba(15,33,103,.45);
}
.db-hero::before {
    content: '';
    position: absolute;
    top: -70px; right: -50px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
    pointer-events: none;
}
.db-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -25px;
    width: 140px; height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    pointer-events: none;
}

/* ── Status badge ── */
.db-on-duty {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    background: rgba(34,197,94,.18);
    border: 1px solid rgba(34,197,94,.3);
    border-radius: 20px;
    padding: .22rem .65rem;
    font-size: .6rem;
    font-weight: 800;
    color: #86efac;
    letter-spacing: .05em;
    text-transform: uppercase;
    flex-shrink: 0;
}
.db-on-duty-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #4ade80;
    animation: db-pulse 2s infinite;
}
@keyframes db-pulse {
    0%,100% { opacity:1; }
    50%      { opacity:.35; }
}

/* ── Stat glass tiles ── */
.db-stat-tile {
    background: rgba(255,255,255,.11);
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 13px;
    padding: .7rem .4rem;
    text-align: center;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.db-stat-tile--red {
    background: rgba(239,68,68,.22);
    border-color: rgba(239,68,68,.3);
}
.db-stat-num   { font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1; }
.db-stat-num--amber { color: #fbbf24; }
.db-stat-num--red   { color: #fca5a5; }
.db-stat-num--green { color: #86efac; }
.db-stat-lbl {
    font-size: .54rem; font-weight: 700;
    color: rgba(255,255,255,.55);
    text-transform: uppercase; letter-spacing: .06em;
    margin-top: .2rem;
}

/* ── Section heading with rule ── */
.db-section-heading {
    font-size: .6rem; font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase; letter-spacing: .1em;
    display: flex; align-items: center; gap: .5rem;
    margin-bottom: .65rem;
}
.db-section-heading::after { content:''; flex:1; height:1px; background:#e2e8f0; }

/* ── Access cards ── */
.db-access {
    background: #fff;
    border-radius: 16px;
    padding: 1rem 1.1rem;
    display: flex; align-items: center; gap: .9rem;
    text-decoration: none; color: inherit;
    margin-bottom: .65rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: 1px solid rgba(0,0,0,.045);
    position: relative; overflow: hidden;
    transition: transform .12s;
    -webkit-tap-highlight-color: transparent;
}
.db-access::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
}
.db-access--blue::before { background: linear-gradient(180deg,#1d4ed8,#1e40af); }
.db-access--red::before  { background: linear-gradient(180deg,#dc2626,#b91c1c); }
.db-access:active  { transform: scale(.98); color: inherit; }
.db-access:hover   { color: inherit; }

.db-access-icon {
    width: 50px; height: 50px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem; flex-shrink: 0;
}
.db-access-icon--blue {
    background: linear-gradient(135deg,#1d4ed8,#1e40af);
    color: #fff;
    box-shadow: 0 4px 14px rgba(29,78,216,.35);
}
.db-access-icon--red {
    background: linear-gradient(135deg,#dc2626,#b91c1c);
    color: #fff;
    box-shadow: 0 4px 14px rgba(220,38,38,.35);
}

.db-access-title {
    font-size: .93rem; font-weight: 800; color: #0f172a; margin-bottom: .2rem;
}
.db-access-badge {
    display: inline-block;
    font-size: .63rem; font-weight: 700;
    padding: .15rem .5rem; border-radius: 20px;
}
.db-access-badge--blue { background: #eff6ff; color: #1d4ed8; }
.db-access-badge--red  { background: #fff1f2; color: #dc2626; }

/* ── Action buttons (2-up grid) ── */
.db-action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; margin-bottom: 1rem; }
.db-action-btn {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: .45rem; padding: 1.1rem .75rem;
    border-radius: 16px; border: none;
    text-decoration: none; color: #fff;
    font-weight: 700; font-size: .8rem;
    cursor: pointer;
    transition: transform .12s, box-shadow .12s;
    -webkit-tap-highlight-color: transparent;
}
.db-action-btn i { font-size: 1.6rem; }
.db-action-btn--blue {
    background: linear-gradient(135deg,#1d4ed8,#1e40af);
    box-shadow: 0 5px 18px rgba(29,78,216,.38);
}
.db-action-btn--red {
    background: linear-gradient(135deg,#dc2626,#b91c1c);
    box-shadow: 0 5px 18px rgba(220,38,38,.38);
}
.db-action-btn:active { transform: scale(.97); color: #fff; }
.db-action-btn:hover  { color: #fff; }

/* ── Overdue alert ── */
.db-overdue {
    background: linear-gradient(135deg,#fff7ed,#fff);
    border: 1.5px solid #fed7aa;
    border-left: 4px solid #f97316;
    border-radius: 14px;
    padding: .85rem 1rem;
    margin-bottom: 1rem;
    display: flex; align-items: center; gap: .75rem;
    text-decoration: none;
    color: inherit;
    transition: transform .12s, box-shadow .12s;
    -webkit-tap-highlight-color: transparent;
}
.db-overdue:hover { color: inherit; box-shadow: 0 6px 18px rgba(249,115,22,.12); }
.db-overdue:active { transform: scale(.985); color: inherit; }
.db-overdue-icon {
    width: 40px; height: 40px;
    background: #fff7ed;
    border: 1.5px solid #fed7aa;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
</style>

{{-- ══════════════════════════════
     HERO
══════════════════════════════ --}}
<div class="db-hero">

    {{-- Top bar: PNP logo · name · On Duty --}}
    <div class="d-flex align-items-center gap-3 mb-3" style="position:relative;z-index:1;">
        <div style="width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
            <img src="{{ asset('images/PNP.png') }}" alt="PNP" style="width:34px;height:34px;object-fit:contain;">
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.58rem;font-weight:700;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.1rem;">Traffic Officer</div>
            <div style="font-size:1rem;font-weight:800;color:#fff;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Auth::user()->name }}</div>
        </div>
        <div class="db-on-duty">
            <span class="db-on-duty-dot"></span>
            On Duty
        </div>
    </div>

    {{-- Greeting + date --}}
    <div style="margin-bottom:1rem;position:relative;z-index:1;">
        @php $hr = now()->hour; $greeting = $hr < 12 ? 'Good Morning' : ($hr < 18 ? 'Good Afternoon' : 'Good Evening'); @endphp
        <div style="font-size:.78rem;color:rgba(255,255,255,.75);font-weight:500;margin-bottom:.15rem;">{{ $greeting }}, <span style="font-weight:800;">{{ Auth::user()->name }}</span></div>
        <div style="font-size:.65rem;color:rgba(255,255,255,.5);">{{ now()->format('l, F d, Y') }}</div>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;position:relative;z-index:1;">
        <div class="db-stat-tile">
            <div class="db-stat-num">{{ $motoristCount }}</div>
            <div class="db-stat-lbl">Motorists</div>
        </div>
        <div class="db-stat-tile">
            <div class="db-stat-num db-stat-num--amber">{{ $violationCount }}</div>
            <div class="db-stat-lbl">Violations</div>
        </div>
        <div class="db-stat-tile">
            <div class="db-stat-num db-stat-num--green">{{ $incidentCount }}</div>
            <div class="db-stat-lbl">Incidents</div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════
     OVERDUE ALERT
══════════════════════════════ --}}
@if($overdueCount > 0)
<a href="{{ route('officer.violations.index', ['status' => 'overdue']) }}" class="db-overdue" aria-label="View overdue violations">
    <div class="db-overdue-icon">
        <i class="ph-fill ph-clock-countdown" style="font-size:1.15rem;color:#f97316;"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:.875rem;font-weight:700;color:#9a3412;">{{ $overdueCount }} Overdue Violation{{ $overdueCount > 1 ? 's' : '' }}</div>
        <div style="font-size:.72rem;color:#c2410c;margin-top:.1rem;">Pending payment for more than 72 hours.</div>
    </div>
    <i class="ph ph-warning-circle" style="font-size:1.2rem;color:#f97316;flex-shrink:0;"></i>
</a>
@endif

{{-- ══════════════════════════════
     QUICK ACCESS
══════════════════════════════ --}}
<div class="db-section-heading">Quick Access</div>

<a href="{{ route('officer.motorists.index') }}" class="db-access db-access--blue">
    <div class="db-access-icon db-access-icon--blue">
        <i class="ph-fill ph-identification-card"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <div class="db-access-title">Traffic Violation</div>
        <span class="db-access-badge db-access-badge--blue">{{ $motoristCount }} registered record{{ $motoristCount !== 1 ? 's' : '' }}</span>
    </div>
    <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.9rem;flex-shrink:0;"></i>
</a>

<a href="{{ route('officer.incidents.index') }}" class="db-access db-access--red">
    <div class="db-access-icon db-access-icon--red">
        <i class="ph-fill ph-flag"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <div class="db-access-title">Traffic Incident</div>
        <span class="db-access-badge db-access-badge--red">{{ $openIncidentCount }} open case{{ $openIncidentCount !== 1 ? 's' : '' }}</span>
    </div>
    <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.9rem;flex-shrink:0;"></i>
</a>

{{-- ══════════════════════════════
     FIELD ACTIONS
══════════════════════════════ --}}
<div class="db-section-heading mt-1">Field Actions</div>

<div class="db-action-grid">
    <a href="{{ route('officer.motorists.create') }}" class="db-action-btn db-action-btn--blue">
        <i class="ph-fill ph-user-plus"></i>
        <span>New Motorist</span>
    </a>
    <a href="{{ route('officer.incidents.create') }}" class="db-action-btn db-action-btn--red">
        <i class="ph-fill ph-plus-circle"></i>
        <span>Record Incident</span>
    </a>
</div>

@endsection
