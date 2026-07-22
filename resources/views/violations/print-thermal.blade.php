<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citation #{{ $violation->ticket_number ?? $violation->id }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f1f5f9;
            color: #000;
            padding-bottom: 80px;
        }

        /* The printable slip must be exactly 384 pixels wide for 48mm ESC/POS (8 dots/mm) */
        .slip {
            width: 384px;
            margin: 20px auto;
            padding: 10px 12px;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            font-size: 15px;
            line-height: 1.3;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: 700; }
        
        .header-title { font-size: 15px; font-weight: 700; margin-top: 2px; }
        .ticket-title { font-size: 20px; font-weight: 900; margin: 4px 0; letter-spacing: -0.5px; }
        .ticket-no-label { font-size: 16px; font-weight: 700; margin-top: 4px; }
        .ticket-no { font-size: 21px; font-weight: 900; letter-spacing: -0.5px; line-height: 1.1; margin-bottom: 6px; }
        .fine-amt { font-size: 18px; font-weight: 900; margin-top: 2px; }

        .divider { border-top: 2px dashed #000; margin: 8px 0; }
        .divider-double { border-top: 4px double #000; margin: 8px 0; }
        
        .row { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2px; }
        .col { display: flex; flex-direction: column; }
        
        .section-title { font-weight: 900; margin-bottom: 4px; text-transform: uppercase; font-size: 16px; }
        
        .kv-line { margin-bottom: 2px; }
        .kv-line .k { font-weight: 700; }

        .status-box { border: 2px solid #000; padding: 1px 6px; display: inline-block; font-weight: 900; }
        
        .qr-section { display: flex; justify-content: space-between; margin: 12px 0; }
        .qr-box { display: flex; flex-direction: column; align-items: center; width: 48%; }
        .qr-box canvas { width: 130px !important; height: 130px !important; image-rendering: pixelated; margin-bottom: 4px; }
        .qr-label { font-size: 13px; font-weight: 900; text-align: center; text-transform: uppercase; }
        .qr-sub { font-size: 10px; text-align: center; font-weight: 600; line-height: 1.1; margin-top: 2px; }

        .signature-line {
            display: flex; align-items: flex-end; margin-top: 20px; font-size: 16px; font-weight: 700;
        }
        .signature-line .line {
            flex-grow: 1; border-bottom: 2px solid #000; margin-left: 8px; height: 20px;
        }

        .footer-text { font-size: 13px; font-weight: 600; text-align: center; margin-top: 8px; line-height: 1.2; }

        /* Floating Toolbar */
        .toolbar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            padding: 15px;
            box-shadow: 0 -4px 6px -1px rgb(0 0 0 / 0.1);
            display: flex;
            gap: 10px;
            justify-content: center;
            z-index: 100;
        }
        .toolbar button {
            flex: 1;
            max-width: 250px;
            padding: 12px;
            border-radius: 8px;
            border: none;
            background: #2563eb;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .toolbar button:disabled { background: #94a3b8; }
        
        #status {
            text-align: center;
            font-size: 13px;
            color: #64748b;
            margin-top: 10px;
            font-family: system-ui, sans-serif;
            font-weight: 600;
        }
    </style>
    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

@php
    $lgu = $violation->lgu;
    $stationName = $lgu?->name ?? config('app.name');
    $plate = $violation->vehicle?->plate_number ?? $violation->vehicle_plate;
    $mm = trim(($violation->vehicle?->make ?? $violation->vehicle_make) . ' ' . ($violation->vehicle?->model ?? $violation->vehicle_model));
    $color = $violation->vehicle?->color;
    
    $statusText = 'UNPAID';
    if (in_array($violation->status, ['paid', 'settled'])) {
        $statusText = 'PAID';
    } elseif ($violation->status === 'contested') {
        $statusText = 'CONTESTED';
    }
@endphp

<div id="status">Ready to connect...</div>

<div class="slip" id="print-area">
    <div class="text-center">
        <div>REPUBLIKA NG PILIPINAS</div>
        <div class="header-title">{{ strtoupper($stationName) }}</div>
        @if($lgu?->province)
            <div>Traffic Enforcement Unit</div>
            <div>Poblacion, {{ $lgu->province }}</div>
        @else
            <div>Traffic Enforcement Unit</div>
        @endif
    </div>
    
    <div class="divider-double"></div>
    <div class="text-center ticket-title">TRAFFIC CITATION TICKET</div>
    <div class="divider-double"></div>
    
    <div class="ticket-no-label">Ticket No:</div>
    <div class="ticket-no">{{ $violation->ticket_number ?: '—' }}</div>
    
    <div class="row">
        <div>Date: {{ $violation->date_of_violation->format('F d, Y') }}</div>
        <div>{{ $violation->date_of_violation->format('h:i A') }}</div>
    </div>
    <div class="kv-line" style="margin-top:4px;">
        <span class="k">Status:</span> <span class="status-box">{{ $statusText }}</span>
    </div>
    
    <div class="divider"></div>
    
    <div class="section-title">VIOLATOR INFORMATION:</div>
    <div class="kv-line">Name: {{ strtoupper($violation->violator?->full_name ?? '(Deleted Motorist)') }}</div>
    <div class="kv-line">License No: {{ $violation->violator?->license_number ?? '—' }}</div>
    @if($violation->violator?->address)
    <div class="kv-line">Address: {{ $violation->violator->address }}</div>
    @endif
    <div class="divider"></div>
    
    <div class="section-title">VIOLATION:</div>
    <div class="kv-line">Type: {{ $violation->violationType?->name ?? '—' }}</div>
    <div class="kv-line">Location: {{ $violation->location ?? '—' }}</div>
    <div class="fine-amt">Fine Amount: PHP {{ number_format($violation->violationType?->fine_amount ?? 0, 2) }}</div>
    
    <div class="divider"></div>
    
    <div class="section-title">VEHICLE:</div>
    <div class="row">
        <div>Plate No: {{ $plate ?: '—' }}</div>
        <div>Type: {{ strtoupper($violation->vehicle?->type ?? 'MV') }}</div>
    </div>
    <div class="kv-line">Make/Model: {{ $mm ?: '—' }}{{ $color ? ' / ' . $color : '' }}</div>
    
    <div class="divider"></div>
    
    <div class="section-title">RECORDED BY:</div>
    <div class="kv-line">Off. {{ strtoupper($violation->recorder?->name ?? '(Deleted User)') }}</div>

    <div class="divider"></div>

    <div class="section-title">VIOLATOR SIGNATURE:</div>
    <div class="center" style="margin-top:10px;">
        @if($violation->signature_photo)
            <img src="{{ uploaded_file_url($violation->signature_photo) }}" alt="Signature" style="max-height: 50px; max-width: 180px; display: block; margin: 0 auto 4px;">
        @endif
        <div style="border-top: 1.5px solid #000; width: 180px; margin: 25px auto 4px;"></div>
        <div style="font-size:11px;font-weight:600;">Violator's Signature @if($violation->signature_photo)[Digital]@endif</div>
    </div>
    <div class="kv-line">Badge: {{ $violation->recorder?->badge_number ?? '—' }}</div>
    
    <div class="divider"></div>
    
    <div class="qr-section">
        <div class="qr-box" style="{{ !$gcashQrPayload ? 'width:100%;' : '' }}">
            <canvas id="citQr"></canvas>
            <div class="qr-label">SCAN TO VERIFY</div>
            <div class="qr-sub">Scan to verify and view<br>the ticket details.</div>
        </div>
        @if($gcashQrPayload)
        <div class="qr-box">
            <div class="qr-label">GCASH PAYMENT</div>
            <canvas id="gcashQr"></canvas>
            <div class="qr-label">SCAN TO PAY<br>PHP {{ number_format($violation->violationType?->fine_amount ?? 0, 2) }}</div>
        </div>
        @endif
    </div>
    
    <div class="divider"></div>
    
    <div class="signature-line">
        Violator's Signature: <div class="line"></div>
    </div>
    
    <div class="divider" style="margin-top:12px;"></div>
    
    <div class="footer-text">
        <div>Pay within 72 hours to avoid additional penalties.</div>
        <div style="margin-top:4px;">Present this ticket at the {{ $lgu?->name ?? 'Municipal' }} Treasurer's Office or scan the GCash QR above.</div>
        <div style="margin-top:8px;">TVIRS - Traffic Violation Incident Record System</div>
    </div>
    
    <!-- Extra padding at the bottom of the receipt for tearing -->
    <div style="height: 40px;"></div>
</div>

<div class="toolbar">
    <button type="button" id="btn-print" onclick="printViaWebBluetooth()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 7 10 10"/><path d="M17 7 7 17"/></svg>
        Connect & Print (Bluetooth)
    </button>
</div>

<script>
window.addEventListener('load', function () {
    var qrData = @json($violation->ticket_number ? $violation->ticket_number . " — " . ($violation->violator?->full_name ?? "") : url("/violations/" . $violation->id));
    QRCode.toCanvas(document.getElementById('citQr'), qrData, {
        width: 140, margin: 1, color: { dark: '#000000', light: '#ffffff' }
    });

    @if($gcashQrPayload)
    QRCode.toCanvas(document.getElementById('gcashQr'), @json($gcashQrPayload), {
        width: 140, margin: 1, color: { dark: '#000000', light: '#ffffff' }
    });
    @endif
});

// --- Web Bluetooth ESC/POS Driver ---

function updateStatus(msg) {
    document.getElementById('status').innerText = msg;
    console.log("[Printer]", msg);
}

function convertCanvasToEscPos(canvas) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const imgData = ctx.getImageData(0, 0, width, height).data;

    const bytesWidth = Math.ceil(width / 8);
    const buffer = new Uint8Array(8 + (bytesWidth * height));
    
    // GS v 0 m xL xH yL yH
    buffer[0] = 0x1D; buffer[1] = 0x76; buffer[2] = 0x30; buffer[3] = 0x00;
    buffer[4] = bytesWidth & 0xFF; buffer[5] = (bytesWidth >> 8) & 0xFF;
    buffer[6] = height & 0xFF; buffer[7] = (height >> 8) & 0xFF;

    let offset = 8;
    for (let y = 0; y < height; y++) {
        for (let x = 0; x < bytesWidth; x++) {
            let byte = 0;
            for (let bit = 0; bit < 8; bit++) {
                let pixelX = (x * 8) + bit;
                if (pixelX < width) {
                    let idx = ((y * width) + pixelX) * 4;
                    let r = imgData[idx], g = imgData[idx + 1], b = imgData[idx + 2], a = imgData[idx + 3];
                    if (a > 128 && (r + g + b) < 384) byte |= (1 << (7 - bit));
                }
            }
            buffer[offset++] = byte;
        }
    }
    return buffer;
}

async function sendChunks(characteristic, data) {
    const CHUNK_SIZE = 100;
    for (let i = 0; i < data.length; i += CHUNK_SIZE) {
        await characteristic.writeValue(data.slice(i, i + CHUNK_SIZE));
        await new Promise(r => setTimeout(r, 20));
    }
}

async function printViaWebBluetooth() {
    const btn = document.getElementById('btn-print');
    
    if (!navigator.bluetooth) {
        alert("Web Bluetooth is not supported in this browser. Please use Google Chrome for Android.");
        return;
    }

    try {
        btn.disabled = true;
        updateStatus("Rendering receipt image...");
        window.scrollTo(0, 0);
        const slip = document.getElementById('print-area');
        const canvas = await html2canvas(slip, { scale: 1, backgroundColor: '#ffffff', logging: false });

        updateStatus("Encoding print data...");
        const printData = convertCanvasToEscPos(canvas);

        updateStatus("Select your printer...");
        const device = await navigator.bluetooth.requestDevice({
            acceptAllDevices: true,
            optionalServices: [
                '000018f0-0000-1000-8000-00805f9b34fb',
                '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
                '0000fee7-0000-1000-8000-00805f9b34fb',
                '0000ae00-0000-1000-8000-00805f9b34fb'
            ]
        });

        updateStatus("Connecting to " + device.name + "...");
        const server = await device.gatt.connect();

        updateStatus("Searching for Print Service...");
        const services = await server.getPrimaryServices();
        let printChar = null;

        for (let service of services) {
            const characteristics = await service.getCharacteristics();
            for (let char of characteristics) {
                if (char.properties.write || char.properties.writeWithoutResponse) {
                    printChar = char;
                    break;
                }
            }
            if (printChar) break;
        }

        if (!printChar) throw new Error("No writable print characteristic found.");

        updateStatus("Printing...");
        await printChar.writeValue(new Uint8Array([0x1B, 0x40]));
        await sendChunks(printChar, printData);
        await printChar.writeValue(new Uint8Array([0x0A, 0x0A, 0x0A, 0x0A]));

        updateStatus("✅ Print Complete!");
        
        setTimeout(() => {
            if (device.gatt.connected) device.gatt.disconnect();
            btn.disabled = false;
        }, 2000);

    } catch (error) {
        console.error(error);
        updateStatus("❌ Error: " + error.message);
        btn.disabled = false;
    }
}
</script>
</body>
</html>
