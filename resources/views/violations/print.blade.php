<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/app-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon.png') }}">
    <title>Violation Record #{{ $violation->id }} — {{ $violation->violator?->full_name ?? '(Deleted Motorist)' }}</title>
    <style>
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

        /* ─── VIOLATION HEADER STRIP ─── */
        .viol-header-strip {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
            background: #fef2f2; border: 1.5px solid #fca5a5;
            border-radius: 7px; padding: 8px 12px; margin-bottom: 13px;
        }
        .viol-id-block { display: block; }
        .off-recidivist    { color: #b91c1c; }
        .off-repeat        { color: #b45309; }
        .off-first         { color: #0369a1; }
        .off-none          { color: #15803d; }
        .viol-id-label { font-size: 8.5px; font-weight: 700; color: #a8a29e; text-transform: uppercase; letter-spacing: .05em; }
        .viol-id-num { font-size: 17px; font-weight: 900; color: #b91c1c; line-height: 1.1; }
        .viol-ticket { font-size: 11px; font-weight: 700; color: #57534e; font-family: 'Courier New', monospace; margin-top: 2px; }
        .status-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .status-overdue   { background:#fef2f2; color:#b91c1c; border:1.5px solid #fca5a5; }
        .status-pending   { background:#fef3c7; color:#92400e; border:1.5px solid #fcd34d; }
        .status-settled   { background:#f0fdf4; color:#15803d; border:1.5px solid #86efac; }
        .status-contested { background:#f8fafc; color:#475569; border:1.5px solid #cbd5e1; }
        .dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .dot-overdue   { background:#dc2626; }
        .dot-pending   { background:#f59e0b; }
        .dot-settled   { background:#22c55e; }
        .dot-contested { background:#94a3b8; }
        .fine-block { text-align: right; }
        .fine-label { font-size: 8.5px; font-weight: 700; color: #a8a29e; text-transform: uppercase; letter-spacing: .05em; }
        .fine-amount { font-size: 17px; font-weight: 900; color: #1c1917; line-height: 1.1; }

        /* ─── SECTION ─── */
        .section { margin-bottom: 13px; page-break-inside: avoid; }
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

        /* ─── INFO GRID ─── */
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
        .info-cell:last-child { border-bottom: none; }
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
        .info-val.mono  { font-family: 'Courier New', monospace; }

        /* ─── TWO-COLUMN LAYOUT ─── */
        .two-col { display: flex; gap: 12px; }
        .two-col > * { flex: 1; }

        /* ─── PHOTO PAGE ─── */
        .photo-page {
            page-break-before: always;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 14mm 14mm 14mm 14mm;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
        }
        .photo-page-title {
            font-size: 11px; font-weight: 900;
            text-transform: uppercase; letter-spacing: .12em;
            color: #b91c1c;
            border-bottom: 2px solid #b91c1c;
            padding: 3px 0 5px;
            margin-bottom: 12px;
            text-align: center;
        }
        .photo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5mm;
        }
        .photo-cell {
            display: flex; flex-direction: column;
            border: 1.5px solid #e7e2db; border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .photo-cell-label {
            font-size: 9px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .07em;
            color: #fff; background: #475569;
            padding: 3px 8px;
            text-align: center;
            flex-shrink: 0;
        }
        .photo-cell-label.red   { background: #b91c1c; }
        .photo-cell-label.blue  { background: #1d4ed8; }
        .photo-cell-label.green { background: #15803d; }
        .photo-cell-label.purple{ background: #6d28d9; }
        .photo-cell-img {
            height: 78mm;
            background: #f8fafc;
            display: flex; align-items: center; justify-content: center;
        }
        .photo-cell-img img { width: 100%; height: 100%; object-fit: contain; display: block; }

        /* ─── SIGNATURE FOOTER ─── */
        .doc-footer {
            margin-top: 18px; padding-top: 10px;
            border-top: 1px solid #e7e2db;
            display: flex; justify-content: space-between; align-items: flex-end;
        }
        .doc-footer-left { font-size: 9px; color: #a8a29e; }
        .doc-footer-sig { text-align: center; font-size: 9.5px; }
        .sig-line { width: 140px; border-top: 1.5px solid #1c1917; margin: 0 auto 3px; }
        .sig-label { font-size: 9px; color: #57534e; font-weight: 600; }

        /* ─── PRINT MEDIA ─── */
        @media print {
            html, body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            .no-print-toolbar { display: none !important; }
            .page, .photo-page {
                width: 100%; min-height: auto;
                padding: 0 !important;
                box-shadow: none; margin: 0;
            }
            .section { page-break-inside: avoid; }
            .photo-cell-img { height: 78mm; }
        }
    </style>
</head>
<body>

@php
    $isOverdue     = $violation->isOverdue();
    $displayStatus = $isOverdue ? 'overdue' : $violation->status;
    $statusLabels  = ['overdue' => 'Overdue', 'pending' => 'Pending', 'settled' => 'Settled', 'contested' => 'Contested'];
    $label         = $statusLabels[$displayStatus] ?? ucfirst($displayStatus);
    $vc            = $violation->violator->violations->count();
    if ($vc >= 3)      { $offLabel = 'Recidivist';      $offCls = 'off-recidivist'; }
    elseif ($vc >= 2)  { $offLabel = 'Repeat Offender'; $offCls = 'off-repeat'; }
    elseif ($vc === 1) { $offLabel = '1st Violation';   $offCls = 'off-first'; }
    else               { $offLabel = 'No Prior';        $offCls = 'off-none'; }
@endphp

<div class="page">

    {{-- Document Header --}}
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
    <div class="doc-form-title">Violation Record</div>


    {{-- Violation Header Strip --}}
    <div class="viol-header-strip">
        <div class="viol-id-block">
            <div class="viol-id-label">Violation Record</div>
            <div class="viol-id-num">#{{ $violation->id }}</div>
            @if($violation->ticket_number)
                <div class="viol-ticket">Ticket: {{ $violation->ticket_number }}</div>
            @endif
        </div>
        <div>
            <span class="status-pill status-{{ $displayStatus }}">
                <span class="dot dot-{{ $displayStatus }}"></span>
                {{ $label }}
            </span>
        </div>
        <div class="fine-block">
            <div class="fine-label">Fine Amount</div>
            @if($violation->violationType->fine_amount)
                <div class="fine-amount">₱{{ number_format($violation->violationType->fine_amount, 2) }}</div>
            @else
                <div style="font-size:11px;color:#a8a29e;font-style:italic;">No fine set</div>
            @endif
        </div>
    </div>


    <div class="two-col">
        {{-- LEFT COLUMN --}}
        <div>

            {{-- Violation Details --}}
            <div class="section">
                <div class="section-title">Violation Details</div>
                <div class="info-grid cols-2">
                    <div class="info-cell">
                        <span class="info-lbl">Violation Type</span>
                        <span class="info-val">{{ $violation->violationType?->name ?? '—' }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Date of Violation</span>
                        <span class="info-val">{{ $violation->date_of_violation->format('M d, Y') }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Location</span>
                        <span class="info-val @if(!$violation->location) empty @endif">
                            {{ $violation->location ?: '—' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Ticket No.</span>
                        <span class="info-val mono @if(!$violation->ticket_number) empty @endif">
                            {{ $violation->ticket_number ?: 'Not issued' }}
                        </span>
                    </div>
                    <div class="info-cell" style="grid-column: 1 / -1; border-right: none;">
                        <span class="info-lbl">Notes</span>
                        <span class="info-val @if(!$violation->notes) empty @endif">
                            {{ $violation->notes ?: '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Vehicle Information --}}
            <div class="section">
                <div class="section-title blue">Vehicle Involved</div>
                @if($violation->vehicle || $violation->vehicle_plate)
                <div class="info-grid cols-2">
                    <div class="info-cell">
                        <span class="info-lbl">Plate No.</span>
                        <span class="info-val mono">
                            {{ $violation->vehicle?->plate_number ?? $violation->vehicle_plate ?? '—' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Vehicle Type</span>
                        <span class="info-val @if(!$violation->vehicle?->vehicle_type) empty @endif">
                            {{ $violation->vehicle?->vehicle_type ?? '—' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Make / Model</span>
                        <span class="info-val @if(!($violation->vehicle?->make ?? $violation->vehicle_make)) empty @endif">
                            {{ implode(' / ', array_filter([$violation->vehicle?->make ?? $violation->vehicle_make, $violation->vehicle?->model ?? $violation->vehicle_model])) ?: '—' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Color</span>
                        <span class="info-val @if(!($violation->vehicle?->color ?? $violation->vehicle_color)) empty @endif">
                            {{ $violation->vehicle?->color ?? $violation->vehicle_color ?? '—' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">OR No.</span>
                        <span class="info-val mono @if(!$violation->vehicle_or_number) empty @endif">
                            {{ $violation->vehicle_or_number ?? '—' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">CR No.</span>
                        <span class="info-val mono @if(!$violation->vehicle_cr_number) empty @endif">
                            {{ $violation->vehicle_cr_number ?? '—' }}
                        </span>
                    </div>
                    @if($violation->vehicle_owner_name)
                    <div class="info-cell" style="grid-column: 1 / -1; border-right: none;">
                        <span class="info-lbl">Registered Owner (borrowed vehicle)</span>
                        <span class="info-val">{{ $violation->vehicle_owner_name }}</span>
                    </div>
                    @endif
                </div>
                @else
                <div style="font-size:10.5px;color:#a8a29e;font-style:italic;padding:6px 0;">No vehicle recorded.</div>
                @endif
            </div>

            {{-- Settlement Details --}}
            @if($violation->status === 'settled')
            <div class="section">
                <div class="section-title green">Settlement Details</div>
                <div class="info-grid cols-2">
                    <div class="info-cell">
                        <span class="info-lbl">OR Number</span>
                        <span class="info-val mono">{{ $violation->or_number ?? '—' }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Cashier</span>
                        <span class="info-val">{{ $violation->cashier_name ?? '—' }}</span>
                    </div>
                    <div class="info-cell" style="grid-column: 1 / -1; border-right: none;">
                        <span class="info-lbl">Date Settled</span>
                        <span class="info-val">{{ ($violation->settled_at ?? $violation->updated_at)->format('F d, Y  g:i A') }}</span>
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- /LEFT --}}

        {{-- RIGHT COLUMN --}}
        <div>

            {{-- Violator Information --}}
            <div class="section">
                <div class="section-title" style="background:#6d28d9;">Violator</div>
                <div class="info-grid cols-1">
                    <div class="info-cell">
                        <span class="info-lbl">Full Name</span>
                        <span class="info-val" style="font-size:12px;font-weight:900;">{{ $violation->violator?->full_name ?? '(Deleted Motorist)' }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">License No.</span>
                        <span class="info-val mono @if(!$violation->violator?->license_number) empty @endif">
                            {{ $violation->violator?->license_number ?: 'Not on file' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Contact</span>
                        <span class="info-val @if(!$violation->violator->contact_number) empty @endif">
                            {{ $violation->violator->contact_number ?: '—' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Address</span>
                        <span class="info-val @if(!($violation->violator->temporary_address ?? $violation->violator->address)) empty @endif">
                            {{ $violation->violator->temporary_address ?? $violation->violator->address ?? '—' }}
                        </span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Violation History</span>
                        <span class="info-val {{ $offCls }}">{{ $offLabel }} ({{ $vc }} total)</span>
                    </div>
                </div>
            </div>

            {{-- Record Info --}}
            <div class="section">
                <div class="section-title amber">Record Info</div>
                <div class="info-grid cols-1">
                    <div class="info-cell">
                        <span class="info-lbl">Recorded By</span>
                        <span class="info-val">{{ $violation->recorder?->name ?? '(Deleted User)' }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Recorded On</span>
                        <span class="info-val">{{ $violation->created_at->format('M d, Y  g:i A') }}</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-lbl">Last Updated</span>
                        <span class="info-val">{{ $violation->updated_at->format('M d, Y  g:i A') }}</span>
                    </div>
                    @if($violation->incident)
                    <div class="info-cell">
                        <span class="info-lbl">Linked Incident</span>
                        <span class="info-val mono">{{ $violation->incident->incident_number }}</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- /RIGHT --}}
    </div>{{-- /two-col --}}

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

    {{-- Footer --}}
    <div class="doc-footer">
        <div class="doc-footer-left">
            Record ID: #{{ $violation->id }}<br>
            Generated: {{ now()->format('F d, Y  g:i A') }}
        </div>
    </div>

</div>

{{-- ─── PAGE 2: ATTACHED PHOTOS ─── --}}
@php
    $allPhotos = collect();
    if ($violation->violator?->photo)
        $allPhotos->push(['url' => uploaded_file_url($violation->violator->photo), 'label' => 'Motorist Photo', 'color' => 'slate']);
    if ($violation->citation_ticket_photo)
        $allPhotos->push(['url' => uploaded_file_url($violation->citation_ticket_photo), 'label' => 'Citation Ticket', 'color' => 'red']);
    if ($violation->valid_id_photo)
        $allPhotos->push(['url' => uploaded_file_url($violation->valid_id_photo), 'label' => 'Valid ID', 'color' => 'purple']);
    // Pick first vehicle photo from violation photos (manual entry) or registered vehicle photos
    $firstVehiclePhoto = $violation->vehiclePhotos->first()?->photo
        ?? $violation->vehicle?->photos->first()?->photo;
    if ($firstVehiclePhoto)
        $allPhotos->push(['url' => uploaded_file_url($firstVehiclePhoto), 'label' => 'Vehicle Photo', 'color' => 'blue']);
    if ($violation->receipt_photo)
        $allPhotos->push(['url' => uploaded_file_url($violation->receipt_photo), 'label' => 'Receipt', 'color' => 'green']);
@endphp
@if($allPhotos->isNotEmpty())
<div class="photo-page">
    <div class="photo-page-title">Attached Photos — Violation #{{ $violation->id }}</div>
    <div class="photo-grid">
        @foreach($allPhotos as $photo)
        <div class="photo-cell">
            <div class="photo-cell-label {{ $photo['color'] }}">{{ $photo['label'] }}</div>
            <div class="photo-cell-img">
                <img src="{{ $photo['url'] }}" alt="{{ $photo['label'] }}">
            </div>
        </div>
        @endforeach
    </div>
    <div style="font-size:8.5px;color:#a8a29e;margin-top:8px;text-align:right;">
        {{ $allPhotos->count() }} attached photo(s) &nbsp;|&nbsp; Generated: {{ now()->format('F d, Y  g:i A') }}
    </div>
</div>
@endif

<script>

window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 600);
});
</script>
</body>
</html>
