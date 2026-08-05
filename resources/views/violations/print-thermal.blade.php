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
            padding: 15px;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            
            /* User requested large bold text for readability on thermal */
            font-size: 18px; 
            font-weight: 900; 
            line-height: 1.4;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .header-text { font-size: 16px; font-weight: 900; }
        .title-text { font-size: 22px; font-weight: 900; margin: 15px 0; letter-spacing: -0.5px; }
        
        .field-row { margin-bottom: 12px; }
        .field-label { display: block; margin-bottom: 2px; }
        .field-value { 
            border-bottom: 2px solid #000; 
            min-height: 25px; 
            display: flex;
            align-items: flex-end;
            padding-bottom: 2px;
        }

        .flex-row { display: flex; gap: 15px; margin-bottom: 12px; }
        .flex-col { flex: 1; }
        
        .divider { border-top: 3px dashed #000; margin: 20px 0; }

        .footer-text { 
            font-size: 15px; 
            text-align: justify; 
            margin-top: 15px; 
            line-height: 1.3;
        }
        
        .qr-container { display: flex; flex-direction: column; align-items: center; margin: 20px 0; }
        .qr-container canvas { width: 180px !important; height: 180px !important; image-rendering: pixelated; margin-bottom: 8px; }

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
            max-width: 300px;
            padding: 15px;
            border-radius: 8px;
            border: none;
            background: #2563eb;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .toolbar button:disabled { background: #94a3b8; }
        
        #status {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-top: 10px;
            font-family: system-ui, sans-serif;
            font-weight: 700;
        }
    </style>
    <!-- Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

@php
    $lgu = $violation->lgu;
    $stationName = $lgu?->name ?? config('app.name');
    $plate = $violation->vehicle?->plate_number ?? $violation->vehicle_plate;
    $mm = trim(($violation->vehicle?->make ?? $violation->vehicle_make) . ' ' . ($violation->vehicle?->model ?? $violation->vehicle_model));
    $color = $violation->vehicle?->color;
@endphp

<div id="status">Ready to connect...</div>

<div class="slip" id="print-area">
    <div class="text-center header-text">
        <div>Republic of the Philippines</div>
        <div>Province of Cebu</div>
        <div>Municipality of {{ $lgu?->name ?? 'Balamban' }}</div>
        <div style="margin-top: 10px; font-size: 18px;">{{ strtoupper($stationName) }}</div>
    </div>
    
    <div class="text-center title-text">TRAFFIC CITATION TICKET</div>
    
    <div class="flex-row">
        <div class="field-label" style="margin-top: 5px;">DATE:</div>
        <div class="field-value" style="flex-grow: 1; justify-content: center;">
            {{ $violation->date_of_violation->format('M d, Y') }}
        </div>
    </div>
    
    <div class="field-row">
        <div class="field-label">TO:</div>
        <div class="field-value">{{ strtoupper($violation->violator?->full_name ?? '') }}</div>
    </div>
    
    <div class="field-row">
        <div class="field-label">ADDRESS:</div>
        <div class="field-value">{{ strtoupper($violation->violator?->permanent_address ?: ($violation->violator?->temporary_address ?: ($violation->violator?->address ?? ''))) }}</div>
    </div>
    
    <div class="field-row">
        <div class="field-label">VEHICLE PLATE NO.:</div>
        <div class="field-value">{{ strtoupper($plate) }}</div>
    </div>
    
    <div class="flex-row">
        <div class="flex-col">
            <div class="field-label">MAKE:</div>
            <div class="field-value">{{ strtoupper($mm) }}</div>
        </div>
        <div class="flex-col">
            <div class="field-label">COLOR:</div>
            <div class="field-value">{{ strtoupper($color) }}</div>
        </div>
    </div>
    
    <div class="text-center title-text" style="margin: 25px 0 15px;">VIOLATION(S)</div>
    
    <div style="margin-bottom: 20px;">
        <div style="display: flex; align-items: flex-start;">
            <span style="margin-right: 12px; font-size: 20px; font-weight: 900;">[X]</span>
            <span style="font-size: 20px; font-weight: 900;">{{ strtoupper($violation->violationType?->name ?? '') }}</span>
        </div>
    </div>
    
    <div class="field-row">
        <div class="field-label">Place of Violation:</div>
        <div class="field-value">{{ strtoupper($violation->location ?? '') }}</div>
    </div>
    
    <div class="flex-row">
        <div class="flex-col" style="flex: 2;">
            <div class="field-label">Time of Violation:</div>
            <div class="field-value">{{ $violation->date_of_violation->format('h:i') }}</div>
        </div>
        <div class="flex-col" style="flex: 1;">
            <div class="field-label">(AM/PM)</div>
            <div class="field-value" style="justify-content: center;">{{ $violation->date_of_violation->format('A') }}</div>
        </div>
    </div>
    
    <div class="text-center" style="margin-top: 40px;">
        <div style="border-bottom: 2px solid #000; width: 80%; margin: 0 auto; height: 30px;"></div>
        <div style="margin-top: 5px;">Driver's Signature</div>
    </div>
    
    <div class="footer-text">
        You are directed to settle this within 72 hours to the {{ $lgu?->name ?? 'Balamban' }} Traffic Operation Management Office from the date hereof for disposition appropriation in the citation.
        <br><br>
        Failure to settle within the period stipulated will mean a waiver and criminal complaint against you will be filed in pursuant to the provisions of {{ $lgu?->ordinance_reference ?? 'Ordinance No. 2005-09' }} otherwise known as the Municipal Traffic Enforcement Code 2005.
    </div>
    
    @php
        $ticketStr = $violation->ticket_number ?: (string)$violation->id;
        $ticketLen = strlen($ticketStr);
        // Auto-scale font size so ticket number ALWAYS fits on a single line on 48mm/80mm thermal paper
        $ticketFontSize = $ticketLen > 26 ? '12px' : ($ticketLen > 22 ? '14px' : ($ticketLen > 18 ? '16px' : '20px'));
    @endphp

    <div style="margin: 20px 0; border: 2px solid #000; padding: 10px; border-radius: 4px; text-align: left;">
        <div style="font-size: 13px; font-weight: 800; letter-spacing: 0.05em; margin-bottom: 4px;">CITATION / O.R. REF NO.:</div>
        <div style="font-size: {{ $ticketFontSize }}; font-weight: 900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2; margin-bottom: 8px;">
            {{ $ticketStr }}
        </div>
        @if($violation->violationType?->fine_amount)
        <div style="border-top: 2px dashed #000; padding-top: 6px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 14px; font-weight: 800;">AMOUNT DUE:</span>
            <span style="font-size: 20px; font-weight: 900;">PHP {{ number_format($violation->violationType->fine_amount, 2) }}</span>
        </div>
        @endif
    </div>
    
    <div style="margin-top: 35px; margin-bottom: 30px;">
        <div>Apprehending Traffic Officer:</div>
        <div style="border-bottom: 2px solid #000; min-height: 40px; margin-top: 15px; text-align: center; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 5px;">
            {{ strtoupper($violation->recorder?->name ?? '') }}
        </div>
    </div>
    
    <div class="qr-container">
        <img id="thermalTicketQrImg" 
             src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode(route('guest-payment.show', $violation->public_payment_token)) }}" 
             alt="Citation QR Code" 
             style="width: 180px; height: 180px; image-rendering: pixelated; margin-bottom: 8px; display: block;"
             crossorigin="anonymous"
             onerror="this.onerror=null; this.src='https://quickchart.io/qr?size=220&text={{ urlencode(route('guest-payment.show', $violation->public_payment_token)) }}';">
        <canvas id="thermalTicketQr" style="display: none;"></canvas>
        <div style="font-size: 14px; font-weight: 700; margin-top: 5px;">SCAN TO VIEW &amp; PAY CITATION</div>
    </div>
    
    <div style="height: 40px;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
<script>
const qrUrl = @json(route("guest-payment.show", $violation->public_payment_token));
if (document.getElementById('thermalTicketQr')) {
    QRCode.toCanvas(document.getElementById('thermalTicketQr'), qrUrl, {
        width: 180,
        margin: 1
    }, function (err) {
        if (!err) {
            const canvas = document.getElementById('thermalTicketQr');
            const img = document.getElementById('thermalTicketQrImg');
            if (canvas && img) {
                try {
                    img.src = canvas.toDataURL('image/png');
                } catch(e) {}
            }
        }
    });
}
</script>

<div class="toolbar">
    <button type="button" id="btn-print" onclick="printViaWebBluetooth()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7 7 10 10"/><path d="M17 7 7 17"/></svg>
        Connect & Print
    </button>
</div>

<script>
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
        const canvas = await html2canvas(slip, { scale: 1, backgroundColor: '#ffffff', logging: false, useCORS: true, allowTaint: true });

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
