@extends('layouts.mobile')
@section('title', $violator->last_name . ', ' . $violator->first_name)
@section('back_url', route('officer.motorists.index'))

@section('content')

@php
    $violationCount = $violator->violations->count();
    $vehicleCount = $violator->vehicles->count();
    $incidentCount = $incidents->count();
    $fullName = trim($violator->last_name . ', ' . $violator->first_name . ' ' . ($violator->middle_name ?? ''));
    $age = $violator->date_of_birth ? $violator->date_of_birth->age : null;
    $licenseExpired = $violator->license_expiry_date?->isPast() ?? false;
    $restrCodes = $violator->license_restriction
        ? array_filter(array_map('trim', explode(',', $violator->license_restriction)))
        : [];
    $restrDesc = [
        'A' => 'Motorcycle',
        'A1' => 'MC w/ Sidecar',
        'B' => 'Light Vehicle',
        'B1' => 'Light Vehicle (Prof.)',
        'B2' => 'Light Vehicle w/ Trailer',
        'C' => 'Medium/Heavy Truck',
        'D' => 'Bus',
        'BE' => 'Light + Heavy Trailer',
        'CE' => 'Large Truck + Trailer',
    ];

    $profileBadge = $violationCount >= 3
        ? ['label' => 'Recidivist', 'class' => 'motshow-status--danger', 'icon' => 'ph-fire']
        : ($violationCount === 2
            ? ['label' => 'Repeat Offender', 'class' => 'motshow-status--warn', 'icon' => 'ph-shield-warning']
            : ($violationCount === 1
                ? ['label' => '1st Violation', 'class' => 'motshow-status--info', 'icon' => 'ph-record']
                : ['label' => 'Clean Record', 'class' => 'motshow-status--safe', 'icon' => 'ph-shield-check']));
@endphp

<style>
.motshow-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #0f2167 0%, #1d4ed8 56%, #1e40af 100%);
    border-radius: 24px;
    padding: 1.15rem;
    margin-bottom: 1rem;
    box-shadow: 0 14px 36px rgba(15, 33, 103, .36);
}

.motshow-hero::before {
    content: '';
    position: absolute;
    top: -78px;
    right: -46px;
    width: 176px;
    height: 176px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}

.motshow-hero::after {
    content: '';
    position: absolute;
    left: -22px;
    bottom: -62px;
    width: 138px;
    height: 138px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}

.motshow-hero-inner {
    position: relative;
    z-index: 1;
}

.motshow-chip {
    display: inline-flex;
    align-items: center;
    gap: .34rem;
    padding: .22rem .62rem;
    border-radius: 999px;
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.2);
    color: rgba(255,255,255,.88);
    font-size: .6rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.motshow-avatar {
    width: 78px;
    height: 78px;
    border-radius: 22px;
    overflow: hidden;
    background: rgba(255,255,255,.18);
    border: 3px solid rgba(255,255,255,.24);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.9rem;
    font-weight: 800;
    box-shadow: 0 10px 22px rgba(0,0,0,.18);
}

.motshow-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.motshow-name {
    margin-top: .65rem;
    font-size: 1.12rem;
    font-weight: 800;
    line-height: 1.2;
    color: #fff;
}

.motshow-subtitle {
    margin-top: .2rem;
    color: rgba(255,255,255,.72);
    font-size: .73rem;
    line-height: 1.4;
}

.motshow-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .6rem;
}

.motshow-meta-chip {
    display: inline-flex;
    align-items: center;
    gap: .24rem;
    padding: .18rem .48rem;
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,255,255,.86);
    font-size: .62rem;
    font-weight: 700;
}

.motshow-status {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    padding: .16rem .55rem;
    border-radius: 999px;
    font-size: .6rem;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-top: .65rem;
}

.motshow-status--danger {
    background: rgba(239,68,68,.22);
    color: #fecaca;
    border: 1px solid rgba(252,165,165,.32);
}

.motshow-status--warn {
    background: rgba(251,191,36,.22);
    color: #fde68a;
    border: 1px solid rgba(252,211,77,.28);
}

.motshow-status--info {
    background: rgba(147,197,253,.18);
    color: #dbeafe;
    border: 1px solid rgba(147,197,253,.28);
}

.motshow-status--safe {
    background: rgba(74,222,128,.18);
    color: #bbf7d0;
    border: 1px solid rgba(74,222,128,.28);
}

.motshow-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .55rem;
    margin-top: 1rem;
}

.motshow-stat {
    text-align: center;
    padding: .78rem .42rem;
    border-radius: 15px;
    background: rgba(255,255,255,.11);
    border: 1px solid rgba(255,255,255,.16);
    backdrop-filter: blur(10px);
}

.motshow-stat-num {
    font-size: 1.3rem;
    line-height: 1;
    font-weight: 800;
    color: #fff;
}

.motshow-stat-label {
    margin-top: .22rem;
    font-size: .56rem;
    font-weight: 800;
    color: rgba(255,255,255,.6);
    text-transform: uppercase;
    letter-spacing: .08em;
}

.motshow-alert {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    border-radius: 16px;
    padding: .9rem 1rem;
    margin-bottom: .95rem;
    border: 1px solid transparent;
}

.motshow-alert-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.05rem;
}

.motshow-alert--danger {
    background: linear-gradient(135deg, #fef2f2, #fff);
    border-color: #fecaca;
}

.motshow-alert--danger .motshow-alert-icon {
    background: #fee2e2;
    color: #dc2626;
}

.motshow-alert--amber {
    background: linear-gradient(135deg, #fffbeb, #fff);
    border-color: #fde68a;
}

.motshow-alert--amber .motshow-alert-icon {
    background: #fef3c7;
    color: #d97706;
}

.motshow-alert-title {
    font-size: .9rem;
    font-weight: 800;
    color: #0f172a;
}

.motshow-alert-text {
    margin-top: .16rem;
    font-size: .74rem;
    line-height: 1.45;
    color: #64748b;
}

.motshow-action-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .55rem;
    margin-bottom: 1rem;
}

.motshow-action {
    min-height: 90px;
    border-radius: 18px;
    padding: .82rem .46rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .34rem;
    text-align: center;
    text-decoration: none;
    border: 1px solid transparent;
    font-size: .72rem;
    font-weight: 800;
}

.motshow-action i {
    font-size: 1.32rem;
}

.motshow-action--danger {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff;
    box-shadow: 0 8px 18px rgba(220,38,38,.24);
}

.motshow-action--blue {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    color: #fff;
    box-shadow: 0 8px 18px rgba(29,78,216,.24);
}

.motshow-action--ghost {
    background: #fff;
    color: #334155;
    border-color: #e2e8f0;
}

.motshow-section {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .65rem;
    font-size: .6rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .1em;
}

.motshow-section::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
}

.motshow-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid rgba(15,23,42,.05);
    box-shadow: 0 3px 16px rgba(15,23,42,.06);
    overflow: hidden;
    margin-bottom: .9rem;
}

.motshow-card-head {
    padding: .88rem 1rem .32rem;
    font-size: .64rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.motshow-card-body {
    padding: 0 1rem 1rem;
}

.motshow-feature-box {
    background: linear-gradient(135deg, #eff6ff, #f8fbff);
    border: 1px solid #dbeafe;
    border-radius: 16px;
    padding: .9rem;
}

.motshow-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .9rem .8rem;
}

.motshow-info-full {
    grid-column: 1 / -1;
}

.motshow-label {
    margin-bottom: .18rem;
    font-size: .63rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.motshow-value {
    font-size: .88rem;
    line-height: 1.38;
    font-weight: 700;
    color: #0f172a;
}

.motshow-value--soft {
    font-weight: 600;
    color: #334155;
}

.motshow-license-code {
    display: inline-block;
    margin: .08rem .16rem 0 0;
    padding: .09rem .38rem;
    border-radius: 8px;
    background: #fef3c7;
    color: #92400e;
    font-size: .68rem;
    font-weight: 800;
}

.motshow-license-flag {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    margin-top: .36rem;
    padding: .12rem .44rem;
    border-radius: 999px;
    font-size: .58rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
}

.motshow-license-flag--danger {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
}

.motshow-license-flag--safe {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #86efac;
}

.motshow-photo-main {
    width: 100%;
    max-height: 280px;
    object-fit: contain;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(15,23,42,.1);
}

.motshow-list {
    display: flex;
    flex-direction: column;
    gap: .7rem;
}

.motshow-item {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    padding: .92rem;
    background: #fff;
    border: 1px solid rgba(15,23,42,.05);
    border-radius: 16px;
    color: inherit;
    text-decoration: none;
    box-shadow: 0 2px 10px rgba(15,23,42,.04);
}

.motshow-item--static {
    cursor: default;
}

.motshow-item-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    box-shadow: 0 6px 16px rgba(15,23,42,.12);
}

.motshow-item-icon--danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.motshow-item-icon--blue {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.motshow-item-icon--slate {
    background: linear-gradient(135deg, #64748b, #475569);
}

.motshow-item-title {
    font-size: .88rem;
    line-height: 1.3;
    font-weight: 800;
    color: #0f172a;
}

.motshow-item-meta {
    margin-top: .14rem;
    font-size: .71rem;
    line-height: 1.46;
    color: #64748b;
}

.motshow-item-submeta {
    margin-top: .2rem;
    font-size: .67rem;
    line-height: 1.45;
    color: #94a3b8;
}

.motshow-tag {
    display: inline-flex;
    align-items: center;
    gap: .22rem;
    padding: .12rem .44rem;
    border-radius: 8px;
    font-size: .63rem;
    font-weight: 800;
}

.motshow-tag--plate {
    background: #eff6ff;
    color: #1e40af;
}

.motshow-tag--owner {
    background: #fef9c3;
    color: #92400e;
}

.motshow-inline-photos {
    display: flex;
    gap: .35rem;
    margin-top: .48rem;
    padding-bottom: .1rem;
    overflow-x: auto;
}

.motshow-inline-photos img {
    width: 58px;
    height: 42px;
    object-fit: cover;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    flex-shrink: 0;
}
</style>

<div class="motshow-hero">
    <div class="motshow-hero-inner">
        <div class="d-flex align-items-start gap-3">
            <div class="motshow-avatar">
                @if($violator->photo)
                    <img src="{{ uploaded_file_url($violator->photo) }}"
                         alt="Motorist photo"
                         class="mob-photo-single"
                         data-full="{{ uploaded_file_url($violator->photo) }}"
                         data-caption="{{ $fullName }}">
                @else
                    {{ strtoupper(substr($violator->first_name, 0, 1) . substr($violator->last_name, 0, 1)) }}
                @endif
            </div>

            <div style="flex:1;min-width:0;">
                <div class="motshow-chip">
                    <i class="ph-fill ph-identification-card"></i>
                    Field Profile
                </div>

                <div class="motshow-name">{{ $fullName }}</div>

                <div class="motshow-subtitle">
                    @if($violator->license_number)
                        License {{ $violator->license_number }}
                    @else
                        No license number on file
                    @endif
                </div>

                <div class="motshow-meta-row">
                    @if($violator->gender)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-user"></i>
                        {{ $violator->gender }}
                    </span>
                    @endif

                    @if($age)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-cake"></i>
                        {{ $age }} yrs old
                    </span>
                    @endif

                    @if($violator->license_type)
                    <span class="motshow-meta-chip">
                        <i class="ph ph-cardholder"></i>
                        {{ $violator->license_type }}
                    </span>
                    @endif
                </div>

                <span class="motshow-status {{ $profileBadge['class'] }}">
                    <i class="ph-fill {{ $profileBadge['icon'] }}"></i>
                    {{ $profileBadge['label'] }}
                </span>
            </div>
        </div>

        <div class="motshow-stat-grid">
            <div class="motshow-stat">
                <div class="motshow-stat-num">{{ $violationCount }}</div>
                <div class="motshow-stat-label">Violations</div>
            </div>
            <div class="motshow-stat">
                <div class="motshow-stat-num">{{ $vehicleCount }}</div>
                <div class="motshow-stat-label">Vehicles</div>
            </div>
            <div class="motshow-stat">
                <div class="motshow-stat-num">{{ $incidentCount }}</div>
                <div class="motshow-stat-label">Incidents</div>
            </div>
        </div>
    </div>
</div>

@if($violationCount >= 2)
<div class="motshow-alert motshow-alert--danger">
    <div class="motshow-alert-icon">
        <i class="ph-fill ph-siren"></i>
    </div>
    <div>
        <div class="motshow-alert-title">Repeat-offender attention needed</div>
        <div class="motshow-alert-text">
            This motorist already has {{ $violationCount }} recorded violation{{ $violationCount === 1 ? '' : 's' }}.
            Review previous history before issuing another record.
        </div>
    </div>
</div>
@elseif($licenseExpired)
<div class="motshow-alert motshow-alert--amber">
    <div class="motshow-alert-icon">
        <i class="ph-fill ph-warning-diamond"></i>
    </div>
    <div>
        <div class="motshow-alert-title">License already expired</div>
        <div class="motshow-alert-text">
            The stored license expiry date has already passed. Double-check the presented license during field verification.
        </div>
    </div>
</div>
@endif

<div class="motshow-action-grid">
    <a href="{{ route('officer.violations.create', $violator) }}" class="motshow-action motshow-action--danger">
        <i class="ph-fill ph-file-plus"></i>
        <span>Record Violation</span>
    </a>
    <a href="{{ route('officer.motorists.vehicles.create', $violator) }}" class="motshow-action motshow-action--blue">
        <i class="ph-fill ph-car-simple"></i>
        <span>Add Vehicle</span>
    </a>
    <a href="{{ route('officer.motorists.edit', $violator) }}" class="motshow-action motshow-action--ghost">
        <i class="ph ph-pencil-simple"></i>
        <span>Edit Record</span>
    </a>
</div>

@if($violator->photo)
<div class="motshow-section">Motorist Photo</div>
<div class="motshow-card">
    <div class="motshow-card-body pt-3 text-center">
        <img src="{{ uploaded_file_url($violator->photo) }}"
             alt="Motorist photo"
             class="mob-photo-single motshow-photo-main"
             data-full="{{ uploaded_file_url($violator->photo) }}"
             data-caption="{{ $fullName }}">
    </div>
</div>
@endif

<div class="motshow-section">Record Overview</div>
<div class="motshow-card">
    <div class="motshow-card-head">Identity Snapshot</div>
    <div class="motshow-card-body">
        <div class="motshow-feature-box">
            <div class="motshow-info-grid">
                @if($violator->date_of_birth)
                <div>
                    <div class="motshow-label">Date of Birth</div>
                    <div class="motshow-value">{{ $violator->date_of_birth->format('M d, Y') }}</div>
                </div>
                @endif

                @if($violator->gender)
                <div>
                    <div class="motshow-label">Sex</div>
                    <div class="motshow-value">{{ $violator->gender }}</div>
                </div>
                @endif

                @if($violator->civil_status)
                <div>
                    <div class="motshow-label">Civil Status</div>
                    <div class="motshow-value">{{ $violator->civil_status }}</div>
                </div>
                @endif

                @if($violator->blood_type)
                <div>
                    <div class="motshow-label">Blood Type</div>
                    <div class="motshow-value">{{ $violator->blood_type }}</div>
                </div>
                @endif

                @if($violator->height)
                <div>
                    <div class="motshow-label">Height</div>
                    <div class="motshow-value">{{ $violator->height }}</div>
                </div>
                @endif

                @if($violator->weight)
                <div>
                    <div class="motshow-label">Weight</div>
                    <div class="motshow-value">{{ $violator->weight }}</div>
                </div>
                @endif

                @if($violator->valid_id)
                <div class="motshow-info-full">
                    <div class="motshow-label">Valid ID</div>
                    <div class="motshow-value motshow-value--soft">{{ $violator->valid_id }}</div>
                </div>
                @endif

                @if($violator->place_of_birth)
                <div class="motshow-info-full">
                    <div class="motshow-label">Place of Birth</div>
                    <div class="motshow-value motshow-value--soft">{{ $violator->place_of_birth }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="motshow-card">
    <div class="motshow-card-head">Contact and Address</div>
    <div class="motshow-card-body">
        <div class="motshow-info-grid">
            @if($violator->contact_number)
            <div>
                <div class="motshow-label">Contact Number</div>
                <div class="motshow-value">{{ $violator->contact_number }}</div>
            </div>
            @endif

            @if($violator->email)
            <div class="{{ $violator->contact_number ? '' : 'motshow-info-full' }}">
                <div class="motshow-label">Email</div>
                <div class="motshow-value motshow-value--soft" style="word-break:break-all;">{{ $violator->email }}</div>
            </div>
            @endif

            @if($violator->temporary_address)
            <div class="motshow-info-full">
                <div class="motshow-label">Current Address</div>
                <div class="motshow-value motshow-value--soft">{{ $violator->temporary_address }}</div>
            </div>
            @endif

            @if($violator->permanent_address)
            <div class="motshow-info-full">
                <div class="motshow-label">Permanent Address</div>
                <div class="motshow-value motshow-value--soft">{{ $violator->permanent_address }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($violator->license_number || $violator->license_type || $violator->license_expiry_date || $restrCodes)
<div class="motshow-card">
    <div class="motshow-card-head">License Information</div>
    <div class="motshow-card-body">
        <div class="motshow-info-grid">
            @if($violator->license_number)
            <div class="motshow-info-full">
                <div class="motshow-label">License Number</div>
                <div class="motshow-value" style="font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;">
                    {{ $violator->license_number }}
                </div>
            </div>
            @endif

            @if($violator->license_type)
            <div>
                <div class="motshow-label">License Type</div>
                <div class="motshow-value">{{ $violator->license_type }}</div>
            </div>
            @endif

            @if($violator->license_expiry_date)
            <div>
                <div class="motshow-label">Expiry Date</div>
                <div class="motshow-value {{ $licenseExpired ? 'text-danger' : '' }}">
                    {{ $violator->license_expiry_date->format('M d, Y') }}
                </div>
                <span class="motshow-license-flag {{ $licenseExpired ? 'motshow-license-flag--danger' : 'motshow-license-flag--safe' }}">
                    <i class="ph-fill {{ $licenseExpired ? 'ph-warning-circle' : 'ph-check-circle' }}"></i>
                    {{ $licenseExpired ? 'Expired' : 'Valid on file' }}
                </span>
            </div>
            @endif

            @if($restrCodes)
            <div class="motshow-info-full">
                <div class="motshow-label">Restrictions</div>
                <div class="motshow-value motshow-value--soft">
                    @foreach($restrCodes as $code)
                        <span class="motshow-license-code" title="{{ $restrDesc[$code] ?? '' }}">{{ $code }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<div class="motshow-section">Violation History</div>
<div class="motshow-list">
    @forelse($violator->violations as $viol)
        @php
            $isOverdue = $viol->status === 'pending' && $viol->created_at->lte(now()->subHours(72));
            $badgeClass = match($viol->status) {
                'settled' => 'mob-badge-settled',
                'contested' => 'mob-badge-contested',
                default => $isOverdue ? 'mob-badge-overdue' : 'mob-badge-pending',
            };
            $badgeLabel = match($viol->status) {
                'settled' => 'Settled',
                'contested' => 'Contested',
                default => $isOverdue ? 'Overdue' : 'Pending',
            };
        @endphp
        <a href="{{ route('officer.violations.show', $viol) }}" class="motshow-item">
            <div class="motshow-item-icon motshow-item-icon--danger">
                <i class="ph-fill ph-warning-circle"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="motshow-item-title">{{ $viol->violationType->name ?? '-' }}</div>
                <div class="motshow-item-meta">
                    {{ $viol->date_of_violation ? $viol->date_of_violation->format('M d, Y') : '-' }}
                    @if($viol->location)
                        &middot; {{ \Illuminate\Support\Str::limit($viol->location, 30) }}
                    @endif
                </div>
                @if($viol->ticket_number)
                <div class="motshow-item-submeta">Ticket #{{ $viol->ticket_number }}</div>
                @endif
            </div>
            <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
                <span class="mob-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.82rem;"></i>
            </div>
        </a>
    @empty
        <div class="motshow-card">
            <div class="mob-empty">
                <i class="ph ph-file-x mob-empty-icon"></i>
                <div class="mob-empty-text">No violations recorded</div>
            </div>
        </div>
    @endforelse
</div>

<div class="motshow-section mt-1">Vehicles on File</div>
<div class="motshow-list">
    @forelse($violator->vehicles as $veh)
        <div class="motshow-item motshow-item--static">
            <div class="motshow-item-icon motshow-item-icon--blue">
                <i class="ph-fill ph-car-profile"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="motshow-item-title">
                    {{ $veh->plate_number }}
                    @if($veh->vehicle_type)
                        <span class="motshow-tag motshow-tag--plate">{{ $veh->vehicle_type }}</span>
                    @endif
                </div>

                <div class="motshow-item-meta">
                    {{ trim($veh->make . ' ' . $veh->model) ?: '-' }}
                    @if($veh->color)
                        &middot; {{ $veh->color }}
                    @endif
                </div>

                @if($veh->or_number || $veh->cr_number)
                <div class="motshow-item-submeta">
                    @if($veh->or_number)
                        OR: {{ $veh->or_number }}
                    @endif
                    @if($veh->or_number && $veh->cr_number)
                        &middot;
                    @endif
                    @if($veh->cr_number)
                        CR: {{ $veh->cr_number }}
                    @endif
                </div>
                @endif

                @if($veh->chassis_number)
                <div class="motshow-item-submeta">Chassis: {{ $veh->chassis_number }}</div>
                @endif

                @if($veh->owner_name)
                <div class="mt-1">
                    <span class="motshow-tag motshow-tag--owner">
                        <i class="ph ph-user-circle"></i>
                        Owner: {{ $veh->owner_name }}
                    </span>
                </div>
                @endif

                @if($veh->photos->count() > 0)
                <div class="motshow-inline-photos">
                    @foreach($veh->photos->take(4) as $photo)
                    <img src="{{ uploaded_file_url($photo->photo) }}"
                         alt="Vehicle photo"
                         class="mob-photo-thumb"
                         data-full="{{ uploaded_file_url($photo->photo) }}"
                         data-caption="Vehicle photo - {{ $veh->plate_number }}">
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="motshow-card">
            <div class="mob-empty">
                <i class="ph-fill ph-truck mob-empty-icon"></i>
                <div class="mob-empty-text">No vehicles on file</div>
            </div>
        </div>
    @endforelse
</div>

<div class="motshow-section mt-1">Linked Incidents</div>
<div class="motshow-list">
    @forelse($incidents as $inc)
        @php
            $sc = ['open' => 'mob-badge-open', 'under_review' => 'mob-badge-review', 'closed' => 'mob-badge-closed'][$inc->status] ?? 'mob-badge-closed';
        @endphp
        <a href="{{ route('officer.incidents.show', $inc) }}" class="motshow-item">
            <div class="motshow-item-icon motshow-item-icon--slate">
                <i class="ph-fill ph-flag"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="motshow-item-title">{{ $inc->incident_number }}</div>
                <div class="motshow-item-meta">
                    {{ $inc->date_of_incident ? \Carbon\Carbon::parse($inc->date_of_incident)->format('M d, Y') : '-' }}
                    @if($inc->location)
                        &middot; {{ \Illuminate\Support\Str::limit($inc->location, 28) }}
                    @endif
                </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1 ms-2 flex-shrink-0">
                <span class="mob-badge {{ $sc }}">{{ ucfirst(str_replace('_', ' ', $inc->status)) }}</span>
                <i class="ph ph-caret-right" style="color:#cbd5e1;font-size:.82rem;"></i>
            </div>
        </a>
    @empty
        <div class="motshow-card">
            <div class="mob-empty">
                <i class="ph ph-flag mob-empty-icon"></i>
                <div class="mob-empty-text">No incidents recorded</div>
            </div>
        </div>
    @endforelse
</div>

@endsection
