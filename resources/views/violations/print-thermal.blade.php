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
            padding: 15px 10px 20px;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .center { text-align: center; }
        .bold   { font-weight: 700; }

        .banner { font-weight: 700; font-size: 16px; letter-spacing: .04em; }
        .agency-line { font-size: 14px; margin-top: 4px; }
        .agency-line.strong { font-weight: 700; font-size: 15px; }

        .checker {
            height: 6px;
            margin: 12px 0;
            background-image:
                linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000),
                linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000);
            background-size: 6px 6px;
            background-position: 0 0, 3px 3px;
        }
        .rule { border-top: 2px dashed #000; margin: 12px 0; }

        .section-hd { font-weight: 700; font-size: 15px; margin: 15px 0 6px; }

        .kv { display: flex; gap: 8px; margin-bottom: 6px; font-size: 14px; }
        .kv .k { flex: 0 0 90px; font-weight: 600; }
        .kv .v { flex: 1; font-weight: 700; word-break: break-word; }

        .qr-wrap { display: flex; flex-direction: column; align-items: center; margin: 15px 0; }
        .qr-wrap canvas { width: 140px !important; height: 140px !important; image-rendering: pixelated; }
        .qr-cap { font-size: 12px; font-weight: 600; margin-top: 6px; text-align: center; }

        .fine-box { border: 2px solid #000; padding: 12px; margin: 15px 0; text-align: center; }
        .fine-box .k { font-size: 14px; font-weight: 600; }
        .fine-box .amt { font-size: 22px; font-weight: 700; margin-top: 4px; }

        .sig-line { border-top: 2px solid #000; width: 200px; margin: 30px auto 6px; }
        .sig-cap { font-size: 13px; font-weight: 600; }

        .note { font-size: 13px; font-weight: 600; margin-top: 6px; }
        .stamp { font-weight: 700; font-size: 18px; letter-spacing: .03em; margin-top: 15px; }

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
@endphp

<div id="status">Ready to connect...</div>

<div class="slip" id="print-area">
    <div class="center">
        <div class="banner">&gt;&gt;&gt; CITATION TICKET &lt;&lt;&lt;</div>
        <div class="agency-line strong" style="margin-top:6px;">{{ strtoupper($stationName) }}</div>
        @if($lgu?->province)
            <div class="agency-line">{{ $lgu->province }}</div>
        @endif
    </div>

    <div class="qr-wrap">
        <canvas id="citQr"></canvas>
        <div class="qr-cap">Scan to verify</div>
    </div>

    <div class="checker"></div>

    <div class="section-hd">[Ticket Info]</div>
    <div class="kv"><span class="k">Ticket No:</span><span class="v">{{ $violation->ticket_number ?: '—' }}</span></div>
    <div class="kv"><span class="k">Record #:</span><span class="v">{{ $violation->id }}</span></div>
    <div class="kv"><span class="k">Date:</span><span class="v">{{ $violation->date_of_violation->format('M d, Y') }}</span></div>
    <div class="kv"><span class="k">Time:</span><span class="v">{{ $violation->date_of_violation->format('g:i A') }}</span></div>

    <div class="rule"></div>

    <div class="section-hd">[Violator]</div>
    <div class="kv"><span class="k">Name:</span><span class="v">{{ $violation->violator?->full_name ?? '(Deleted Motorist)' }}</span></div>
    @if($violation->violator?->license_number)
    <div class="kv"><span class="k">Lic. No:</span><span class="v">{{ $violation->violator->license_number }}</span></div>
    @endif

    @if($plate)
    <div class="rule"></div>
    <div class="section-hd">[Vehicle]</div>
    <div class="kv"><span class="k">Plate:</span><span class="v">{{ $plate }}</span></div>
    @if($mm)
    <div class="kv"><span class="k">Make/Model:</span><span class="v">{{ $mm }}</span></div>
    @endif
    @endif

    <div class="rule"></div>

    <div class="section-hd">[Violation]</div>
    <div class="kv"><span class="k">Type:</span><span class="v">{{ $violation->violationType?->name ?? '—' }}</span></div>
    @if($violation->location)
    <div class="kv"><span class="k">Location:</span><span class="v">{{ $violation->location }}</span></div>
    @endif

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
    <div class="section-hd center">[Pay via GCash]</div>
    <div class="qr-wrap">
        <canvas id="gcashQr"></canvas>
        <div class="qr-cap">Scan to pay via GCash / InstaPay</div>
        <div class="qr-cap" style="font-weight:400;">(sample placeholder — not a real account)</div>
    </div>
    @endif

    <div class="rule"></div>

    <div class="section-hd">[Issuing Officer]</div>
    <div class="kv"><span class="k">Name:</span><span class="v">{{ $violation->recorder?->name ?? '(Deleted User)' }}</span></div>

    <div class="center">
        <div class="sig-line"></div>
        <div class="sig-cap">Violator's Signature</div>
    </div>

    <div class="checker"></div>

    <div class="center note">
        Settle at {{ $lgu?->treasurer_office ?: 'the designated payment center' }}<br>
        within 72 hours to avoid penalty.
    </div>
    <div class="center note">
        Printed {{ now()->format('M d, Y g:i A') }}
    </div>

    <div class="center stamp">*** CITATION ISSUED ***</div>
    
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
// Generate QR Codes on load
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

// Convert canvas pixels to ESC/POS Raster Bit-Image (GS v 0)
function convertCanvasToEscPos(canvas) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const imgData = ctx.getImageData(0, 0, width, height).data;

    const bytesWidth = Math.ceil(width / 8);
    const buffer = new Uint8Array(8 + (bytesWidth * height));
    
    // GS v 0 m xL xH yL yH
    buffer[0] = 0x1D; // GS
    buffer[1] = 0x76; // v
    buffer[2] = 0x30; // 0
    buffer[3] = 0x00; // m (normal mode)
    buffer[4] = bytesWidth & 0xFF;
    buffer[5] = (bytesWidth >> 8) & 0xFF;
    buffer[6] = height & 0xFF;
    buffer[7] = (height >> 8) & 0xFF;

    let offset = 8;
    for (let y = 0; y < height; y++) {
        for (let x = 0; x < bytesWidth; x++) {
            let byte = 0;
            for (let bit = 0; bit < 8; bit++) {
                let pixelX = (x * 8) + bit;
                if (pixelX < width) {
                    let idx = ((y * width) + pixelX) * 4;
                    let r = imgData[idx];
                    let g = imgData[idx + 1];
                    let b = imgData[idx + 2];
                    let a = imgData[idx + 3];
                    // If pixel is dark and opaque, set the bit
                    if (a > 128 && (r + g + b) < 384) {
                        byte |= (1 << (7 - bit));
                    }
                }
            }
            buffer[offset++] = byte;
        }
    }
    return buffer;
}

// Send data in chunks (BLE MTU limits)
async function sendChunks(characteristic, data) {
    const CHUNK_SIZE = 100; // Safe size for cheap BLE printers
    for (let i = 0; i < data.length; i += CHUNK_SIZE) {
        const chunk = data.slice(i, i + CHUNK_SIZE);
        await characteristic.writeValue(chunk);
        // Small delay to prevent overwhelming the printer buffer
        await new Promise(resolve => setTimeout(resolve, 20));
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
        
        // 1. Render the HTML to Canvas
        updateStatus("Rendering receipt image...");
        const slip = document.getElementById('print-area');
        // Temporarily reset transforms for perfect rendering
        window.scrollTo(0, 0);
        const canvas = await html2canvas(slip, {
            scale: 1, // Must be 1 so it's exactly 384px wide
            backgroundColor: '#ffffff',
            logging: false
        });

        // 2. Convert Canvas to ESC/POS bytes
        updateStatus("Encoding print data...");
        const printData = convertCanvasToEscPos(canvas);

        // 3. Request Bluetooth Device
        updateStatus("Select your printer...");
        const device = await navigator.bluetooth.requestDevice({
            acceptAllDevices: true,
            optionalServices: [
                '000018f0-0000-1000-8000-00805f9b34fb', // Standard thermal printer
                '49535343-fe7d-4ae5-8fa9-9fafd205e455', // Alternative GOOJPRT
                'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // PT-210 alternative
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

        if (!printChar) {
            throw new Error("Could not find a writable print characteristic on this device.");
        }

        updateStatus("Printing...");
        
        // Initialize printer (ESC @)
        await printChar.writeValue(new Uint8Array([0x1B, 0x40]));
        
        // Send raster image chunks
        await sendChunks(printChar, printData);
        
        // Feed lines (LF x 4)
        await printChar.writeValue(new Uint8Array([0x0A, 0x0A, 0x0A, 0x0A]));

        updateStatus("✅ Print Complete!");
        
        // Disconnect after 2 seconds
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
