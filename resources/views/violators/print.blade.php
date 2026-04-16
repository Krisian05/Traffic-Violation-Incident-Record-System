<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/app-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon.png') }}">
    <title>Motorist Record — {{ $violator->full_name }}</title>
    <style>
        /* ─── RESET & BASE ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 12px; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1a1a1a;
            background: #f0f0f0;
            padding: 20px;
        }


        @page { size: A4 portrait; margin: 8mm 14mm 14mm; }

        /* ─── PAGE (A4) ─── */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 14mm 14mm 14mm 14mm;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
        }

        /* ─── DOCUMENT HEADER ─── */
        .doc-header {
            display: flex; align-items: center; gap: 14px;
            padding-bottom: 10px;
            border-bottom: 3px double #b91c1c;
            margin-bottom: 4px;
        }
        .doc-seal { width: 110px; height: 110px; flex-shrink: 0; object-fit: contain; }
        .doc-seal-pnp { object-fit: cover; }
        .doc-agency { flex: 1; text-align: center; line-height: 1.35; }
        .doc-agency-republic { font-size: 9.5px; color: #111; }
        .doc-agency-npc      { font-size: 9.5px; color: #111; }
        .doc-agency-pro7     { font-size: 10.5px; font-weight: 600; color: #111; }
        .doc-agency-cebu     { font-size: 12.5px; font-weight: 800; color: #111; text-transform: uppercase; letter-spacing: .03em; }
        .doc-agency-station  { font-size: 14px; font-weight: 900; color: #111; text-transform: uppercase; letter-spacing: .03em; }
        .doc-agency-address  { font-size: 9px; color: #111; margin-top: 1px; }
        .doc-form-title {
            text-align: center;
            font-size: 11px; font-weight: 900;
            text-transform: uppercase; letter-spacing: .12em;
            color: #b91c1c;
            border-bottom: 2px solid #b91c1c;
            padding: 3px 0 5px;
            margin-bottom: 13px;
        }
        .doc-meta { font-size: 9.5px; color: #78716c; margin-top: 4px; }

        /* ─── PROFILE ROW ─── */
        .profile-row {
            display: flex; gap: 14px; align-items: flex-start;
            margin-bottom: 14px;
        }
        .profile-photo {
            width: 90px; height: 110px; flex-shrink: 0;
            border: 2px solid #e7e2db;
            border-radius: 6px; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            background: #f5f0e8;
        }
        .profile-photo img { width: 100%; height: 100%; object-fit: cover; }
        .profile-photo-placeholder { font-size: 36px; color: #d6d3d1; }
        .profile-name-block { flex: 1; }
        .profile-fullname {
            font-size: 18px; font-weight: 900; color: #1c1917;
            line-height: 1.2; margin-bottom: 4px;
        }
        .profile-license {
            font-size: 11px; font-weight: 700; color: #b91c1c;
            font-family: 'Courier New', monospace;
            background: #fef2f2; padding: 2px 8px; border-radius: 4px;
            border: 1px solid #fca5a5; display: inline-block; margin-bottom: 8px;
        }
        .profile-status-row { display: flex; gap: 8px; flex-wrap: wrap; }
        .profile-chip {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 9.5px; font-weight: 700; padding: 2px 8px;
            border-radius: 20px; border: 1px solid;
        }
        .chip-rec  { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
        .chip-rep  { background:#fffbeb;color:#b45309;border-color:#fde68a; }
        .chip-once { background:#f0f9ff;color:#0369a1;border-color:#7dd3fc; }
        .chip-ok   { background:#f0fdf4;color:#15803d;border-color:#86efac; }

        /* ─── SECTION ─── */
        .section { margin-bottom: 13px; }
        .section-title {
            font-size: 10px; font-weight: 900;
            text-transform: uppercase; letter-spacing: .1em;
            color: #fff; background: #dc2626;
            padding: 3px 8px; border-radius: 4px;
            margin-bottom: 7px; display: inline-block;
        }
        .section-title.blue  { background: #1d4ed8; }
        .section-title.green { background: #15803d; }
        .section-title.amber { background: #b45309; }
        .section-title.slate { background: #475569; }

        /* ─── INFO GRID (label : value pairs) ─── */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border: 1px solid #e7e2db;
            border-radius: 5px;
            overflow: hidden;
        }
        .info-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .info-grid.cols-1 { grid-template-columns: 1fr; }
        .info-cell {
            padding: 5px 8px;
            border-right: 1px solid #e7e2db;
            border-bottom: 1px solid #e7e2db;
        }
        .info-cell:nth-child(3n) { border-right: none; }
        .info-grid.cols-2 .info-cell:nth-child(3n) { border-right: 1px solid #e7e2db; }
        .info-grid.cols-2 .info-cell:nth-child(2n) { border-right: none; }
        .info-cell:last-child, .info-cell:nth-last-child(2):nth-child(odd),
        .info-cell:nth-last-child(3):nth-child(3n+1) { border-bottom: none; }
        .info-lbl {
            font-size: 8.5px; font-weight: 700;
            color: #a8a29e; text-transform: uppercase; letter-spacing: .05em;
            display: block; margin-bottom: 1px;
        }
        .info-val {
            font-size: 11px; font-weight: 600; color: #1c1917;
            word-break: break-word;
        }
        .info-val.empty { color: #d6d3d1; font-style: italic; font-weight: 400; }

        /* ─── VIOLATIONS TABLE ─── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .data-table thead tr { background: #1c1917; color: #fff; }
        .data-table thead th {
            padding: 5px 8px;
            font-size: 8.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .07em;
            text-align: left; border: none;
        }
        .data-table thead th.center { text-align: center; }
        .data-table tbody tr:nth-child(even) { background: #fafaf9; }
        .data-table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #f0ebe3;
            vertical-align: top;
            color: #1c1917;
        }
        .data-table tbody td.center { text-align: center; }
        .status-dot {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 10px; font-weight: 700;
        }
        .dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .dot-pending  { background: #f59e0b; }
        .dot-settled  { background: #22c55e; }
        .dot-contested{ background: #94a3b8; }

        /* ─── VEHICLES ─── */
        .vehicle-block {
            border: 1px solid #e7e2db; border-radius: 5px;
            padding: 7px 10px; margin-bottom: 6px;
            page-break-inside: avoid;
        }
        .vehicle-plate {
            font-size: 12px; font-weight: 900;
            color: #1c1917; font-family: 'Courier New', monospace;
            margin-bottom: 3px;
        }
        .vehicle-detail { font-size: 10px; color: #57534e; }

        /* ─── VIOLATION SUMMARY ─── */
        .vtype-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 4px 8px;
            border-bottom: 1px solid #f0ebe3;
            font-size: 10.5px;
        }
        .vtype-row:last-child { border-bottom: none; }
        .vtype-name { font-weight: 600; color: #1c1917; }
        .vtype-count {
            font-size: 10px; font-weight: 800;
            padding: 1px 7px; border-radius: 20px;
            background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5;
        }

        /* ─── FOOTER ─── */
        .doc-footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #e7e2db;
            display: flex; justify-content: space-between; align-items: flex-end;
        }
        .doc-footer-left { font-size: 9px; color: #a8a29e; }
        .doc-footer-sig {
            text-align: center; font-size: 9.5px;
        }
        .sig-line {
            width: 140px; border-top: 1.5px solid #1c1917;
            margin: 0 auto 3px;
        }
        .sig-label { font-size: 9px; color: #57534e; font-weight: 600; }

        /* ─── TWO-COLUMN LAYOUT ─── */
        .two-col { display: flex; gap: 12px; }
        .two-col > * { flex: 1; }

        /* ─── PRINT MEDIA ─── */
        @media print {
            html, body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            .no-print-toolbar { display: none !important; }
            .page {
                width: 100%; min-height: auto;
                padding: 0 !important;
                box-shadow: none;
                margin: 0;
            }
            .section { page-break-inside: avoid; }

            /* 12-column violation history table — fixed layout to prevent overflow */
            .data-table { table-layout: fixed !important; width: 100% !important; }
            .data-table thead th,
            .data-table tbody td { word-wrap: break-word; font-size: 8px !important; padding: 3px 4px !important; }
            /* Violation History column widths */
            .data-table thead th:nth-child(1)  { width: 3%;  } /* # */
            .data-table thead th:nth-child(2)  { width: 9%;  } /* Date */
            .data-table thead th:nth-child(3)  { width: 9%;  } /* Ticket No. */
            .data-table thead th:nth-child(4)  { width: 13%; } /* Violation Type */
            .data-table thead th:nth-child(5)  { width: 12%; } /* Location */
            .data-table thead th:nth-child(6)  { width: 9%;  } /* Vehicle */
            .data-table thead th:nth-child(7)  { width: 9%;  } /* Reg. Owner */
            .data-table thead th:nth-child(8)  { width: 7%;  } /* Status */
            .data-table thead th:nth-child(9)  { width: 8%;  } /* OR No. */
            .data-table thead th:nth-child(10) { width: 8%;  } /* Cashier */
            .data-table thead th:nth-child(11) { width: 8%;  } /* Recorded By */
            .data-table thead th:nth-child(12) { width: 5%;  } /* Notes */

            @page { size: A4 landscape; margin: 8mm 12mm 12mm; }
        }
    </style>
</head>
<body>

@php
    $vc = $violator->violations->count();
    $settled = $violator->violations->where('status', 'settled')->count();
    if ($vc >= 3)       { $statusLabel = 'Recidivist';      $statusCls = 'chip-rec'; }
    elseif ($vc >= 2)   { $statusLabel = 'Repeat Offender'; $statusCls = 'chip-rep'; }
    elseif ($vc === 1)  { $statusLabel = '1 Violation';     $statusCls = 'chip-once'; }
    else                { $statusLabel = 'No Violations';   $statusCls = 'chip-ok'; }
@endphp

<div class="page">

    {{-- ── DOCUMENT HEADER ── --}}
    <div class="doc-header">
        <img src="{{ asset('images/PNP.png') }}" class="doc-seal doc-seal-pnp" alt="PNP Logo">
        <div class="doc-agency">
            <div class="doc-agency-republic">Republic of the Philippines</div>
            <div class="doc-agency-npc">NATIONAL POLICE COMMISSION</div>
            <div class="doc-agency-pro7">PHILIPPINE NATIONAL POLICE, POLICE REGIONAL OFFICE 7</div>
            <div class="doc-agency-cebu">CEBU POLICE PROVINCIAL OFFICE</div>
            <div class="doc-agency-station">BALAMBAN MUNICIPAL POLICE STATION</div>
            <div class="doc-agency-address">Brgy. Sta Cruz-Sto Nino, Balamban, Cebu</div>
        </div>
        <img src="{{ asset('images/Balamban.png') }}" class="doc-seal" alt="Balamban Seal">
    </div>
    <div class="doc-form-title">Motorist Record</div>


    {{-- ── PROFILE ROW ── --}}
    <div class="profile-row">
        <div class="profile-photo">
            @if($violator->photo)
                <img src="{{ uploaded_file_url($violator->photo) }}" alt="Photo">
            @else
                <span class="profile-photo-placeholder">&#9786;</span>
            @endif
        </div>
        <div class="profile-name-block">
            <div class="profile-fullname">{{ $violator->full_name }}</div>
            @if($violator->license_number)
                <div class="profile-license">LIC: {{ $violator->license_number }}</div>
            @endif
            <div class="profile-status-row">
                <span class="profile-chip {{ $statusCls }}">{{ $statusLabel }}</span>
                <span class="profile-chip chip-once">{{ $vc }} Violation{{ $vc !== 1 ? 's' : '' }}</span>
                <span class="profile-chip chip-ok">{{ $settled }} Settled</span>
                <span class="profile-chip chip-rep">{{ $violator->vehicles->count() }} Vehicle{{ $violator->vehicles->count() !== 1 ? 's' : '' }}</span>
            </div>
        </div>
    </div>

    {{-- ── PERSONAL INFORMATION ── --}}
    <div class="section">
        <div class="section-title">Personal Information</div>
        <div class="info-grid">
            <div class="info-cell">
                <span class="info-lbl">First Name</span>
                <span class="info-val">{{ $violator->first_name }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Middle Name</span>
                <span class="info-val {{ $violator->middle_name ? '' : 'empty' }}">{{ $violator->middle_name ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Last Name</span>
                <span class="info-val">{{ $violator->last_name }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Date of Birth</span>
                <span class="info-val {{ $violator->date_of_birth ? '' : 'empty' }}">
                    {{ $violator->date_of_birth ? $violator->date_of_birth->format('F d, Y') : 'N/A' }}
                </span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Place of Birth</span>
                <span class="info-val {{ $violator->place_of_birth ? '' : 'empty' }}">{{ $violator->place_of_birth ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Gender</span>
                <span class="info-val {{ $violator->gender ? '' : 'empty' }}">{{ $violator->gender ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Civil Status</span>
                <span class="info-val {{ $violator->civil_status ? '' : 'empty' }}">{{ $violator->civil_status ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Blood Type</span>
                <span class="info-val {{ $violator->blood_type ? '' : 'empty' }}">{{ $violator->blood_type ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Valid ID</span>
                <span class="info-val {{ $violator->valid_id ? '' : 'empty' }}">{{ $violator->valid_id ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Height</span>
                <span class="info-val {{ $violator->height ? '' : 'empty' }}">{{ $violator->height ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Weight</span>
                <span class="info-val {{ $violator->weight ? '' : 'empty' }}">{{ $violator->weight ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Contact Number</span>
                <span class="info-val {{ $violator->contact_number ? '' : 'empty' }}">{{ $violator->contact_number ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Email Address</span>
                <span class="info-val {{ $violator->email ? '' : 'empty' }}">{{ $violator->email ?? 'N/A' }}</span>
            </div>
        </div>
        {{-- Addresses on full-width rows --}}
        <div class="info-grid cols-1" style="margin-top:4px;">
            <div class="info-cell">
                <span class="info-lbl">Temporary / Current Address</span>
                <span class="info-val {{ $violator->temporary_address ? '' : 'empty' }}">{{ $violator->temporary_address ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Permanent Address</span>
                <span class="info-val {{ $violator->permanent_address ? '' : 'empty' }}">{{ $violator->permanent_address ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    {{-- ── LICENSE INFORMATION ── --}}
    <div class="section">
        <div class="section-title amber">License Information</div>
        <div class="info-grid cols-3">
            <div class="info-cell">
                <span class="info-lbl">License Number</span>
                <span class="info-val {{ $violator->license_number ? '' : 'empty' }}">{{ $violator->license_number ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">License Type</span>
                <span class="info-val {{ $violator->license_type ? '' : 'empty' }}">{{ $violator->license_type ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Restriction Code</span>
                <span class="info-val {{ $violator->license_restriction ? '' : 'empty' }}">{{ $violator->license_restriction ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Date Issued</span>
                <span class="info-val {{ $violator->license_issued_date ? '' : 'empty' }}">{{ $violator->license_issued_date?->format('M d, Y') ?? 'N/A' }}</span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Expiry Date</span>
                <span class="info-val {{ $violator->license_expiry_date ? '' : 'empty' }}"
                      @if($violator->license_expiry_date?->isPast()) style="color:#dc2626;font-weight:700;" @endif>
                    {{ $violator->license_expiry_date?->format('M d, Y') ?? 'N/A' }}
                    @if($violator->license_expiry_date?->isPast()) (EXPIRED) @endif
                </span>
            </div>
            <div class="info-cell">
                <span class="info-lbl">Conditions / Remarks</span>
                <span class="info-val {{ $violator->license_conditions ? '' : 'empty' }}">{{ $violator->license_conditions ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    {{-- ── REGISTERED VEHICLES ── --}}
    <div class="section">
        <div class="section-title amber">Registered Vehicles ({{ $violator->vehicles->count() }})</div>
        @if($violator->vehicles->isEmpty())
            <p style="font-size:10.5px;color:#a8a29e;font-style:italic;">No vehicles registered.</p>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Plate Number</th>
                    <th>Type</th>
                    <th>Make / Model</th>
                    <th>Color</th>
                    <th>Year</th>
                    <th>OR No.</th>
                    <th>CR No.</th>
                    <th>Chassis No.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($violator->vehicles as $i => $v)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-family:'Courier New',monospace;font-weight:700;">{{ $v->plate_number }}</td>
                    <td>{{ $v->vehicle_type ?? '—' }}</td>
                    <td>{{ implode(' ', array_filter([$v->make, $v->model])) ?: '—' }}</td>
                    <td>{{ $v->color ?? '—' }}</td>
                    <td>{{ $v->year ?? '—' }}</td>
                    <td style="font-family:'Courier New',monospace;">{{ $v->or_number ?? '—' }}</td>
                    <td style="font-family:'Courier New',monospace;">{{ $v->cr_number ?? '—' }}</td>
                    <td style="font-family:'Courier New',monospace;">{{ $v->chassis_number ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- ── VIOLATIONS BY TYPE (summary) ── --}}
    @if($violationsByType->isNotEmpty())
    <div class="section">
        <div class="section-title">Violation Summary by Type</div>
        <div style="border:1px solid #e7e2db;border-radius:5px;overflow:hidden;">
            @foreach($violationsByType as $row)
            <div class="vtype-row">
                <span class="vtype-name">{{ $row['type']->name }}</span>
                <span class="vtype-count">{{ $row['count'] }} time{{ $row['count'] !== 1 ? 's' : '' }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── VIOLATION HISTORY ── --}}
    <div class="section">
        <div class="section-title">Violation History ({{ $violator->violations->count() }} record{{ $violator->violations->count() !== 1 ? 's' : '' }})</div>
        @if($violator->violations->isEmpty())
            <p style="font-size:10.5px;color:#a8a29e;font-style:italic;">No violation records on file.</p>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Ticket No.</th>
                    <th>Violation Type</th>
                    <th>Location</th>
                    <th>Vehicle</th>
                    <th>Reg. Owner</th>
                    <th class="center">Status</th>
                    <th>OR No.</th>
                    <th>Cashier</th>
                    <th>Recorded By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($violator->violations->sortBy('date_of_violation') as $i => $viol)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="white-space:nowrap;">{{ $viol->date_of_violation->format('M d, Y') }}</td>
                    <td style="font-family:'Courier New',monospace;font-size:10px;">{{ $viol->ticket_number ?? '—' }}</td>
                    <td style="font-weight:600;">{{ $viol->violationType?->name ?? '—' }}</td>
                    <td>{{ $viol->location ?? '—' }}</td>
                    <td style="font-family:'Courier New',monospace;">
                        @if($viol->vehicle)
                            {{ $viol->vehicle->plate_number }}
                        @elseif($viol->vehicle_plate)
                            {{ $viol->vehicle_plate }}
                            <span style="font-size:8px;color:#b45309;font-family:Arial,sans-serif;">[manual]</span>
                            @if($viol->vehicle_make || $viol->vehicle_model || $viol->vehicle_color)
                                <br><span style="font-family:Arial,sans-serif;font-size:9px;color:#57534e;">
                                    {{ implode(' · ', array_filter([$viol->vehicle_make, $viol->vehicle_model, $viol->vehicle_color])) }}
                                </span>
                            @endif
                            @if($viol->vehicle_or_number || $viol->vehicle_cr_number)
                                <br><span style="font-family:Arial,sans-serif;font-size:8.5px;color:#78716c;">
                                    OR: {{ $viol->vehicle_or_number ?? '—' }} / CR: {{ $viol->vehicle_cr_number ?? '—' }}
                                </span>
                            @endif
                            @if($viol->vehicle_chassis)
                                <br><span style="font-family:Arial,sans-serif;font-size:8.5px;color:#78716c;">
                                    Chassis: {{ $viol->vehicle_chassis }}
                                </span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td style="font-size:9.5px;">{{ $viol->vehicle_owner_name ?? '—' }}</td>
                    <td class="center">
                        @if($viol->status === 'settled')
                            <span class="status-dot"><span class="dot dot-settled"></span>Settled</span>
                        @elseif($viol->status === 'contested')
                            <span class="status-dot"><span class="dot dot-contested"></span>Contested</span>
                        @else
                            <span class="status-dot"><span class="dot dot-pending"></span>Pending</span>
                        @endif
                    </td>
                    <td style="font-family:'Courier New',monospace;font-size:9.5px;">{{ $viol->or_number ?? '—' }}</td>
                    <td style="font-size:9.5px;">{{ $viol->cashier_name ?? '—' }}</td>
                    <td>{{ $viol->recorder?->name ?? '—' }}</td>
                    <td style="font-size:9.5px;color:#57534e;">{{ $viol->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- ── INCIDENT INVOLVEMENT ── --}}
    @php $incidents = $violator->incidentMotorists->filter(fn($m) => $m->incident)->sortByDesc(fn($m) => $m->incident->date_of_incident); @endphp
    <div class="section">
        <div class="section-title">
            Incident Involvement
            <span style="font-size:9px;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:9999px;padding:1px 7px;margin-left:6px;">
                {{ $incidents->count() }}
            </span>
        </div>
        @if($incidents->isEmpty())
            <p style="font-size:9.5px;color:#a8a29e;font-style:italic;margin:0;">No incident involvement recorded.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Incident No.</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Charge</th>
                        <th class="center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incidents as $im)
                    <tr>
                        <td style="font-family:'Courier New',monospace;font-weight:600;color:#1d4ed8;">{{ $im->incident->incident_number }}</td>
                        <td style="white-space:nowrap;">{{ $im->incident->date_of_incident->format('M d, Y') }}</td>
                        <td>{{ $im->incident->location ?? '—' }}</td>
                        <td>{{ $im->chargeType->name ?? '—' }}</td>
                        <td class="center">
                            @php
                                $label = match($im->incident->status) {
                                    'open'         => 'Open',
                                    'under_review' => 'Under Review',
                                    'closed'       => 'Closed',
                                    default        => ucfirst($im->incident->status),
                                };
                            @endphp
                            <span class="status-dot">
                                <span class="dot {{ $im->incident->status === 'closed' ? 'dot-settled' : ($im->incident->status === 'open' ? 'dot-pending' : 'dot-contested') }}"></span>
                                {{ $label }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Signatures --}}
    <div style="display: flex; justify-content: space-between; margin-top: 60pt; margin-bottom: 20pt;">
        <div>
            <div>Prepared by:</div>
            <div style="border-bottom: 1pt solid #000; width: 130pt; margin-top: 26pt; margin-left: 50pt;"></div>
            <div style="font-size: 9pt; font-weight: 600; margin-top: 2pt; text-align: center; width: 130pt; margin-left: 50pt;">{{ Auth::user()->name ?? 'N/A' }}</div>
            <div style="font-size: 8pt; font-style: italic; margin-top: 2pt; text-align: center; width: 130pt; margin-left: 50pt;">Operation PNCO</div>
        </div>
        <div>
            <div>Noted by:</div>
            <div style="border-bottom: 1pt solid #000; width: 130pt; margin-top: 26pt; margin-left: 50pt;"></div>
            <div style="font-size: 9pt; font-weight: 600; margin-top: 2pt; text-align: center; width: 130pt; margin-left: 50pt;">PLTCOL RUEL L BURLAT</div>
            <div style="font-size: 8pt; font-style: italic; margin-top: 2pt; text-align: center; width: 130pt; margin-left: 50pt;">Chief of Police</div>
        </div>
    </div>

    {{-- ── DOCUMENT FOOTER ── --}}
    <div class="doc-footer">
        <div class="doc-footer-left">
            <div>Record ID: #{{ $violator->id }} &nbsp;|&nbsp; Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
            <div style="margin-top:2px;">This document is confidential and for official use only.</div>
        </div>
    </div>

</div>{{-- /page --}}

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 600);
    });
</script>
</body>
</html>
