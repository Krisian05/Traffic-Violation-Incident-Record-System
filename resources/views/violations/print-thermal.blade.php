<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citation #{{ $violation->ticket_number ?? $violation->id }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: 58mm auto; margin: 0; }

        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.55;
            color: #000;
            background: #d4d4d4;
            -webkit-font-smoothing: none;
        }

        .slip {
            width: 48mm;
            margin: 0 auto;
            padding: 3mm 2mm 5mm;
            background: #fff;
        }

        .center { text-align: center; }
        .bold   { font-weight: 700; }

        /* ─── header, arrow-bracket motif like the printer self-test ─── */
        .banner {
            font-weight: 700;
            font-size: 13px;
            letter-spacing: .04em;
        }
        .agency-line { font-size: 11px; margin-top: .8mm; }
        .agency-line.strong { font-weight: 700; font-size: 12px; }

        /* ─── checkerboard rule, matches the printer self-test divider ─── */
        .checker {
            height: 5px;
            margin: 2.2mm 0;
            background-image:
                linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000),
                linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000);
            background-size: 5px 5px;
            background-position: 0 0, 2.5px 2.5px;
        }
        .rule { border-top: 1px dashed #000; margin: 2mm 0; }

        /* ─── [SECTION] bracket headers, like [BlueTooth Info] ─── */
        .section-hd {
            font-weight: 700;
            font-size: 12px;
            margin: 2.5mm 0 1mm;
        }

        /* ─── label: value rows, like "System:   PT210_UB" ─── */
        .kv { display: flex; gap: 1.5mm; margin-bottom: 1mm; }
        .kv .k { flex: 0 0 21mm; font-weight: 600; }
        .kv .v { flex: 1; font-weight: 700; word-break: break-word; }

        .qr-wrap { display: flex; flex-direction: column; align-items: center; margin: 2.5mm 0; }
        .qr-wrap canvas { width: 34mm !important; height: 34mm !important; image-rendering: pixelated; }
        .qr-cap { font-size: 10.5px; font-weight: 600; margin-top: 1mm; }

        .fine-box {
            border: 1.5px solid #000;
            padding: 2mm;
            margin: 2.5mm 0;
            text-align: center;
        }
        .fine-box .k { font-size: 11px; font-weight: 600; }
        .fine-box .amt { font-size: 18px; font-weight: 700; margin-top: .5mm; }

        .sig-line { border-top: 1px solid #000; width: 34mm; margin: 6mm auto 1mm; }
        .sig-cap { font-size: 10.5px; font-weight: 600; }

        .note { font-size: 10.5px; font-weight: 600; margin-top: .8mm; }

        .stamp {
            font-weight: 700;
            font-size: 15px;
            letter-spacing: .03em;
            margin-top: 2.5mm;
        }

        .toolbar {
            max-width: 48mm;
            margin: 3mm auto 0;
            display: flex;
            gap: 6px;
        }
        .toolbar button, .toolbar a {
            flex: 1;
            display: block;
            text-align: center;
            font-family: system-ui, sans-serif;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 4px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #1e293b;
            text-decoration: none;
        }
        .toolbar .primary { background: #1d4ed8; border-color: #1d4ed8; color: #fff; }

        @media print {
            body { background: #fff; }
            .toolbar, .no-print { display: none !important; }
            .slip { padding: 0; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
</head>
<body>

@php
    $lgu = $violation->lgu;
    $stationName = $lgu?->name ?? config('app.name');
    $plate = $violation->vehicle?->plate_number ?? $violation->vehicle_plate;
    $mm = trim(($violation->vehicle?->make ?? $violation->vehicle_make) . ' ' . ($violation->vehicle?->model ?? $violation->vehicle_model));
@endphp

<div class="slip">

    {{-- Header --}}
    <div class="center">
        <div class="banner">&gt;&gt;&gt; CITATION TICKET &lt;&lt;&lt;</div>
        <div class="agency-line strong" style="margin-top:1.5mm;">{{ strtoupper($stationName) }}</div>
        @if($lgu?->province)
            <div class="agency-line">{{ $lgu->province }}</div>
        @endif
    </div>

    {{-- QR --}}
    <div class="qr-wrap">
        <canvas id="citQr"></canvas>
        <div class="qr-cap">Scan to verify</div>
    </div>

    <div class="checker"></div>

    {{-- Ticket info --}}
    <div class="section-hd">[Ticket Info]</div>
    <div class="kv"><span class="k">Ticket No:</span><span class="v">{{ $violation->ticket_number ?: '—' }}</span></div>
    <div class="kv"><span class="k">Record #:</span><span class="v">{{ $violation->id }}</span></div>
    <div class="kv"><span class="k">Date:</span><span class="v">{{ $violation->date_of_violation->format('M d, Y') }}</span></div>
    <div class="kv"><span class="k">Time:</span><span class="v">{{ $violation->date_of_violation->format('g:i A') }}</span></div>

    <div class="rule"></div>

    {{-- Violator --}}
    <div class="section-hd">[Violator]</div>
    <div class="kv"><span class="k">Name:</span><span class="v">{{ $violation->violator?->full_name ?? '(Deleted Motorist)' }}</span></div>
    @if($violation->violator?->license_number)
    <div class="kv"><span class="k">Lic. No:</span><span class="v">{{ $violation->violator->license_number }}</span></div>
    @endif

    {{-- Vehicle --}}
    @if($plate)
    <div class="rule"></div>
    <div class="section-hd">[Vehicle]</div>
    <div class="kv"><span class="k">Plate:</span><span class="v">{{ $plate }}</span></div>
    @if($mm)
    <div class="kv"><span class="k">Make/Model:</span><span class="v">{{ $mm }}</span></div>
    @endif
    @endif

    <div class="rule"></div>

    {{-- Violation --}}
    <div class="section-hd">[Violation]</div>
    <div class="kv"><span class="k">Type:</span><span class="v">{{ $violation->violationType?->name ?? '—' }}</span></div>
    @if($violation->location)
    <div class="kv"><span class="k">Location:</span><span class="v">{{ $violation->location }}</span></div>
    @endif

    {{-- Fine --}}
    <div class="fine-box">
        <div class="k">Fine Amount</div>
        @if($violation->violationType?->fine_amount)
            <div class="amt">&#8369;{{ number_format($violation->violationType->fine_amount, 2) }}</div>
        @else
            <div class="v">Not set</div>
        @endif
    </div>

    @if($gcashQrPayload)
    <div class="rule"></div>

    {{-- GCash / InstaPay --}}
    <div class="section-hd center">[Pay via GCash]</div>
    <div class="qr-wrap">
        <canvas id="gcashQr"></canvas>
        <div class="qr-cap">Scan to pay via GCash / InstaPay</div>
        <div class="qr-cap" style="font-weight:400;">(sample placeholder — not a real account)</div>
    </div>
    @endif

    <div class="rule"></div>

    {{-- Officer --}}
    <div class="section-hd">[Issuing Officer]</div>
    <div class="kv"><span class="k">Name:</span><span class="v">{{ $violation->recorder?->name ?? '(Deleted User)' }}</span></div>

    <div class="center">
        <div class="sig-line"></div>
        <div class="sig-cap">Violator's Signature</div>
    </div>

    <div class="checker"></div>

    <div class="center note">
        Settle at {{ $lgu?->treasurer_office ?: 'the designated payment center' }}
        within 72 hours to avoid penalty.
    </div>
    <div class="center note">
        Printed {{ now()->format('M d, Y g:i A') }}
    </div>

    <div class="center stamp">*** CITATION ISSUED ***</div>

</div>

<div class="toolbar no-print">
    <button type="button" class="primary" onclick="window.print()">Standard Print</button>
    <button type="button" class="primary" style="background:#0f172a; border-color:#0f172a;" onclick="printViaRawBT()">Print via RawBT</button>
    <a href="javascript:window.close()">Close</a>
</div>

<script>
window.addEventListener('load', function () {
    var pending = {{ $gcashQrPayload ? 2 : 1 }};
    function done() {
        pending--;
        // We disabled auto-print because mobile browsers block window.print() inside setTimeout
        // if it wasn't triggered by a direct user tap.
    }

    var qrData = @json($violation->ticket_number ? $violation->ticket_number . " — " . ($violation->violator?->full_name ?? "") : url("/violations/" . $violation->id));
    
    QRCode.toCanvas(document.getElementById('citQr'), qrData, {
        width: 140,
        margin: 1,
        color: { dark: '#000000', light: '#ffffff' }
    }, function (error) {
        if (error) console.error(error);
        done();
    });

    @if($gcashQrPayload)
    QRCode.toCanvas(document.getElementById('gcashQr'), @json($gcashQrPayload), {
        width: 140,
        margin: 1,
        color: { dark: '#000000', light: '#ffffff' }
    }, function (error) {
        if (error) console.error(error);
        done();
    });
    @endif
});

// RawBT Intent for Android Bluetooth Printers
function printViaRawBT() {
    // Convert the entire page to base64 image or just send the URL to RawBT.
    // The simplest way for RawBT is to send the current URL.
    var url = window.location.href;
    var intent = "intent:" + url + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
    window.location.href = intent;
}
</script>
</body>
</html>
