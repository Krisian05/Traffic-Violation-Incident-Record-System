<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('images/app-icon.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/app-icon.png') }}">
<title>{{ $incident->incident_number }} — Incident Report</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Arial', sans-serif; font-size: 12px; color: #111; background: #fff; }
.page { max-width: 820px; margin: 0 auto; padding: 28px 32px; }

/* Header */
.rpt-header { border-bottom: 3px double #b91c1c; padding-bottom: 10px; margin-bottom: 4px; }
.rpt-header-agency { display: flex; align-items: center; gap: 14px; }
.rpt-seal { width: 110px; height: 110px; flex-shrink: 0; object-fit: contain; }
.rpt-seal-pnp { object-fit: cover; }
.rpt-agency-center { flex: 1; text-align: center; line-height: 1.35; }
.rpt-agency-republic { font-size: 9.5px; color: #111; }
.rpt-agency-npc      { font-size: 9.5px; color: #111; }
.rpt-agency-pro7     { font-size: 10.5px; font-weight: 600; color: #111; }
.rpt-agency-cebu     { font-size: 12.5px; font-weight: 800; color: #111; text-transform: uppercase; letter-spacing: .03em; }
.rpt-agency-station  { font-size: 14px; font-weight: 900; color: #111; text-transform: uppercase; letter-spacing: .03em; }
.rpt-agency-address  { font-size: 9px; color: #111; margin-top: 1px; }
.rpt-form-title      { text-align: center; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; color: #b91c1c; border-bottom: 2px solid #b91c1c; padding: 3px 0 5px; margin-bottom: 10px; }
.rpt-doc-meta { display: flex; justify-content: space-between; align-items: flex-start; margin-top: 8px; }
.rpt-number { font-size: 13px; font-weight: 700; font-family: monospace; color: #1d4ed8; }
.rpt-meta { text-align: right; font-size: 10px; color: #555; line-height: 1.7; }
.rpt-meta strong { color: #111; }

/* Status badge */
.status-badge { display: inline-block; padding: 1px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; border: 1px solid; }
.status-under_investigation { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
.status-cleared             { background:#fffbeb; color:#92400e; border-color:#fde68a; }
.status-solved              { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
.status-settled             { background:#faf5ff; color:#6b21a8; border-color:#d8b4fe; }

/* Sections */
.section { margin-bottom: 18px; }
.section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #374151; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; margin-bottom: 10px; }

/* Info grid */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; }
.info-row { display: flex; gap: 6px; }
.info-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; min-width: 80px; flex-shrink: 0; padding-top: 1px; }
.info-value { font-size: 11.5px; color: #111; }
.info-full { grid-column: 1 / -1; }

/* Motorists table */
table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
thead th { background: #f3f4f6; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #374151; padding: 5px 8px; text-align: left; border: 1px solid #d1d5db; }
tbody td { padding: 6px 8px; border: 1px solid #e5e7eb; vertical-align: top; }
tbody tr:nth-child(even) { background: #f9fafb; }

/* Media list */
.media-item { display: inline-flex; align-items: center; gap: 5px; margin: 3px 5px 3px 0; font-size: 10px; }
.media-badge { font-size: 8.5px; font-weight: 700; padding: 1px 5px; border-radius: 3px; text-transform: uppercase; }
.media-scene    { background:#dbeafe; color:#1d4ed8; }
.media-ticket   { background:#fef3c7; color:#92400e; }
.media-document { background:#ede9fe; color:#6d28d9; }
.media-other    { background:#f3f4f6; color:#374151; }

/* Footer */
.rpt-footer { margin-top: 28px; padding-top: 10px; border-top: 1px solid #d1d5db; display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; }

/* Signatures */
.sig-row { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px; }
.sig-box { border-top: 1px solid #111; padding-top: 4px; font-size: 9px; text-align: center; color: #374151; }

@media print {
    html, body { padding: 0 !important; margin: 0 !important; }
    body { font-size: 10px; }
    .no-print { display: none !important; }
    .page { margin: 0 !important; padding: 0 !important; }

    /* 9-column motorists table — landscape + fixed layout */
    table { table-layout: fixed !important; width: 100% !important; }
    thead th, tbody td { word-wrap: break-word; font-size: 9px !important; }
    thead th:nth-child(1) { width: 4%;  } /* # */
    thead th:nth-child(2) { width: 18%; } /* Name */
    thead th:nth-child(3) { width: 12%; } /* License No. */
    thead th:nth-child(4) { width: 8%;  } /* Lic. Type */
    thead th:nth-child(5) { width: 10%; } /* Restriction */
    thead th:nth-child(6) { width: 10%; } /* Plate No. */
    thead th:nth-child(7) { width: 10%; } /* Vehicle */
    thead th:nth-child(8) { width: 16%; } /* Charge */
    thead th:nth-child(9) { width: 12%; } /* Notes */

    @page { size: A4 landscape; margin: 8mm 14mm 12mm; }
}
</style>
</head>
<body>
<div class="page">


    {{-- Report Header --}}
    <div class="rpt-header">
        <div class="rpt-header-agency">
            <img src="{{ asset('images/PNP.png') }}" class="rpt-seal rpt-seal-pnp" alt="PNP Logo">
            <div class="rpt-agency-center">
                <div class="rpt-agency-republic">Republic of the Philippines</div>
                <div class="rpt-agency-npc">NATIONAL POLICE COMMISSION</div>
                <div class="rpt-agency-pro7">PHILIPPINE NATIONAL POLICE, POLICE REGIONAL OFFICE 7</div>
                <div class="rpt-agency-cebu">CEBU POLICE PROVINCIAL OFFICE</div>
                <div class="rpt-agency-station">BALAMBAN MUNICIPAL POLICE STATION</div>
                <div class="rpt-agency-address">Brgy. Sta Cruz-Sto Nino, Balamban, Cebu</div>
            </div>
            <img src="{{ asset('images/Balamban.png') }}" class="rpt-seal" alt="Balamban Seal">
        </div>
    </div>
    <div class="rpt-form-title">Incident Report</div>

    {{-- Document meta (number, status, recorded-by) --}}
    <div class="rpt-doc-meta">
        <div>
            <div class="rpt-number">{{ $incident->incident_number }}</div>
            <div style="margin-top:5px;">
                @php
                    $statusLabels = ['under_investigation' => 'Under Investigation', 'cleared' => 'Cleared', 'solved' => 'Solved', 'settled' => 'Settled'];
                @endphp
                <span class="status-badge status-{{ $incident->status }}">
                    {{ $statusLabels[$incident->status] ?? $incident->status }}
                </span>
            </div>
        </div>
        <div class="rpt-meta">
            <div><strong>Recorded by:</strong> {{ $incident->recorder->name ?? '—' }}</div>
            <div><strong>Date recorded:</strong> {{ $incident->created_at->format('M j, Y g:i A') }}</div>
            <div><strong>Printed:</strong> {{ now()->format('M j, Y g:i A') }}</div>
        </div>
    </div>

    {{-- Incident Details --}}
    <div class="section">
        <div class="section-title">Incident Details</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Date</span>
                <span class="info-value">{{ $incident->date_of_incident->format('F j, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Time</span>
                <span class="info-value">
                    @if($incident->time_of_incident)
                        {{ \Carbon\Carbon::parse($incident->time_of_incident)->format('g:i A') }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="info-row info-full">
                <span class="info-label">Location</span>
                <span class="info-value">{{ $incident->location }}</span>
            </div>
            @if($incident->description)
            <div class="info-row info-full">
                <span class="info-label">Narrative</span>
                <span class="info-value">{{ $incident->description }}</span>
            </div>
            @endif
        </div>
    </div>


    {{-- Involved Motorists --}}
    <div class="section">
        <div class="section-title">Involved Motorists ({{ $incident->motorists->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>License No.</th>
                    <th>Lic. Type</th>
                    <th>Restriction</th>
                    <th>Plate No.</th>
                    <th>Vehicle</th>
                    <th>Charge / Offense</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incident->motorists as $i => $m)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        {{ $m->display_name }}
                        @if($m->violator)
                            <br><span style="font-size:9px;color:#6b7280;">Registered</span>
                        @endif
                    </td>
                    <td style="font-family:monospace;">{{ $m->display_license }}</td>
                    <td>
                        @if($m->violator){{ $m->violator->license_type ?? '—' }}
                        @else{{ $m->license_type ?? '—' }}@endif
                    </td>
                    <td>
                        @php
                            $restrLabels = ['A'=>'Motorcycle','A1'=>'MC+Sidecar','B'=>'Light MV','B1'=>'Light MV Prof','B2'=>'Light+Trailer','C'=>'Med/Heavy Truck','D'=>'Bus','BE'=>'Light+HeavyTrailer','CE'=>'Large+Trailer'];
                            $codes = $m->violator
                                ? array_filter(explode(',', $m->violator->license_restriction ?? ''))
                                : array_filter(explode(',', $m->license_restriction ?? ''));
                        @endphp
                        @foreach($codes as $code)
                            <span title="{{ $restrLabels[trim($code)] ?? '' }}" style="font-weight:600;">{{ trim($code) }}</span>{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                        @if(empty($codes))—@endif
                    </td>
                    <td style="font-family:monospace;">{{ $m->display_plate }}</td>
                    <td>{{ $m->display_vehicle_type }}</td>
                    <td>{{ $m->chargeType?->name ?? '—' }}</td>
                    <td>{{ $m->notes ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Evidence / Media --}}
    @if($incident->media->count())
    <div class="section">
        <div class="section-title">Evidence &amp; Documents ({{ $incident->media->count() }})</div>
        <div>
            @foreach($incident->media as $media)
            <div class="media-item">
                <span class="media-badge media-{{ $media->media_type }}">{{ $media->media_type }}</span>
                {{ $media->caption ?: basename($media->file_path) }}
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Signatures --}}
    <div style="display: flex; justify-content: space-between; margin-top: 60pt;">
        <div>
            <div>Prepared by:</div>
            <div style="border-bottom: 1pt solid #000; width: 130pt; margin-top: 26pt; margin-left: 50pt;"></div>
            <div style="font-size: 9pt; font-weight: 600; margin-top: 2pt; text-align: center; width: 130pt; margin-left: 50pt;">{{ $incident->recorder->name ?? 'N/A' }}</div>
            <div style="font-size: 8pt; font-style: italic; margin-top: 2pt; text-align: center; width: 130pt; margin-left: 50pt;">Operation PNCO</div>
        </div>
        <div>
            <div>Noted by:</div>
            <div style="border-bottom: 1pt solid #000; width: 130pt; margin-top: 26pt; margin-left: 50pt;"></div>
            <div style="font-size: 9pt; font-weight: 600; margin-top: 2pt; text-align: center; width: 130pt; margin-left: 50pt;">PLTCOL RUEL L BURLAT</div>
            <div style="font-size: 8pt; font-style: italic; margin-top: 2pt; text-align: center; width: 130pt; margin-left: 50pt;">Chief of Police</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="rpt-footer">
        <span>Traffic Violation Incident Record System — Generated {{ now()->format('Y-m-d H:i') }}</span>
        <span>{{ $incident->incident_number }}</span>
    </div>

</div>

</body>
</html>
