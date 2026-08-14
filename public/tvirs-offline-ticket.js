/**
 * TVIRS Mobile — Offline Citation Ticket Generator & Printer
 * Allows traffic officers to render, preview, and print 58mm/80mm thermal citation slips
 * directly from IndexedDB queued records without internet connection.
 */
(function () {
    'use strict';

    var MODAL_ID = 'tvirs-offline-ticket-modal';
    var PRINT_STYLE_ID = 'tvirs-offline-ticket-print-style';
    var activeData = null;

    function getAuthOfficer() {
        try {
            var raw = localStorage.getItem('tvirs-mobile-auth-user');
            if (raw) {
                var user = JSON.parse(raw);
                if (user && (user.name || user.email)) {
                    return user;
                }
            }
        } catch (e) {}

        var bodyUserId = document.body ? document.body.dataset.authUserId : '';
        return {
            id: bodyUserId || '',
            name: 'TRAFFIC OFFICER'
        };
    }

    function cleaned(str) {
        return String(str == null ? '' : str).replace(/\s+/g, ' ').trim();
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTicketDate(isoOrDateStr) {
        var d = isoOrDateStr ? new Date(isoOrDateStr) : new Date();
        if (isNaN(d.getTime())) d = new Date();
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var month = months[d.getMonth()];
        var day = String(d.getDate()).padStart(2, '0');
        var year = d.getFullYear();
        return month + ' ' + day + ', ' + year;
    }

    function formatTicketTime(isoOrDateStr) {
        var d = isoOrDateStr ? new Date(isoOrDateStr) : new Date();
        if (isNaN(d.getTime())) d = new Date();
        var hours = d.getHours();
        var minutes = String(d.getMinutes()).padStart(2, '0');
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return {
            time: String(hours).padStart(2, '0') + ':' + minutes,
            ampm: ampm
        };
    }

    function ensurePrintStyles() {
        if (document.getElementById(PRINT_STYLE_ID)) return;

        var style = document.createElement('style');
        style.id = PRINT_STYLE_ID;
        style.textContent = [
            '@media print {',
            '  body * { visibility: hidden !important; }',
            '  #' + MODAL_ID + ', #' + MODAL_ID + ' * { visibility: visible !important; }',
            '  #' + MODAL_ID + ' { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; height: auto !important; background: transparent !important; z-index: 999999 !important; padding: 0 !important; margin: 0 !important; display: block !important; }',
            '  #' + MODAL_ID + ' .off-ticket-backdrop { display: none !important; }',
            '  #' + MODAL_ID + ' .off-ticket-actions { display: none !important; }',
            '  #' + MODAL_ID + ' .off-ticket-header { display: none !important; }',
            '  #' + MODAL_ID + ' .off-ticket-container { box-shadow: none !important; border: none !important; padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: 384px !important; background: #fff !important; }',
            '  #' + MODAL_ID + ' .off-ticket-slip { box-shadow: none !important; border: none !important; margin: 0 !important; width: 100% !important; max-width: 384px !important; padding: 10px 0 !important; font-size: 14px !important; }',
            '  @page { margin: 2mm; size: 58mm auto; }',
            '}'
        ].join('\n');
        document.head.appendChild(style);
    }

    async function extractTicketData(recordOrData) {
        if (!recordOrData) return null;

        var officer = getAuthOfficer();
        var data = {
            officerName: officer.name || 'TRAFFIC OFFICER',
            officerId: officer.id || '',
            date: formatTicketDate(),
            time: formatTicketTime().time,
            ampm: formatTicketTime().ampm,
            motoristName: '',
            motoristAddress: '',
            motoristLicense: '',
            plateNumber: 'NONE',
            make: '',
            color: '',
            violationType: 'TRAFFIC CITATION',
            fineAmount: '',
            location: 'BALAMBAN PATROL SECTOR',
            ticketNumber: '',
            isOffline: true
        };

        var entries = recordOrData.entries || [];
        function getVal(name) {
            var found = entries.filter(function (e) { return e.name === name; });
            return found.length ? cleaned(found[found.length - 1].value) : '';
        }

        // Check if raw record from IndexedDB
        if (recordOrData.entries) {
            data.ticketNumber = getVal('ticket_number') || ('OFF-' + String(recordOrData.id || Date.now()).slice(-6));
            data.location = getVal('location') || getVal('place_of_violation') || 'BALAMBAN, CEBU';
            data.violationType = (recordOrData.summary && recordOrData.summary.violationTypeName) || getVal('violation_type_name') || 'TRAFFIC VIOLATION';
            data.plateNumber = getVal('vehicle_plate') || getVal('plate_number') || 'N/A';
            data.make = getVal('vehicle_make') || getVal('make') || '';
            data.color = getVal('vehicle_color') || getVal('color') || '';

            var rawDate = getVal('date_of_violation') || recordOrData.createdAt;
            if (rawDate) {
                data.date = formatTicketDate(rawDate);
                var t = formatTicketTime(rawDate);
                data.time = t.time;
                data.ampm = t.ampm;
            }

            // Motorist details
            var parentKey = recordOrData.parentOfflineMotoristKey || getVal('offline_motorist_key');
            if (parentKey && window.TvirsOffline && typeof window.TvirsOffline.getOfflineMotoristByKey === 'function') {
                try {
                    var motorist = await window.TvirsOffline.getOfflineMotoristByKey(parentKey);
                    if (motorist && motorist.summary) {
                        data.motoristName = motorist.summary.displayName || (motorist.summary.firstName + ' ' + motorist.summary.lastName);
                        data.motoristAddress = motorist.summary.address || '';
                        data.motoristLicense = motorist.summary.licenseNumber || 'NO LICENSE ON FILE';
                    }
                } catch (e) {}
            }

            if (!data.motoristName && recordOrData.summary) {
                data.motoristName = recordOrData.summary.motoristName || recordOrData.summary.displayName || '';
            }
        } else {
            // Direct object passed
            data.motoristName = recordOrData.motoristName || 'UNNAMED MOTORIST';
            data.motoristAddress = recordOrData.motoristAddress || '';
            data.motoristLicense = recordOrData.motoristLicense || 'N/A';
            data.plateNumber = recordOrData.plateNumber || 'N/A';
            data.make = recordOrData.make || '';
            data.color = recordOrData.color || '';
            data.violationType = recordOrData.violationType || 'TRAFFIC CITATION';
            data.fineAmount = recordOrData.fineAmount || '';
            data.location = recordOrData.location || 'BALAMBAN, CEBU';
            data.ticketNumber = recordOrData.ticketNumber || ('OFF-' + String(Date.now()).slice(-6));
            if (recordOrData.date) data.date = recordOrData.date;
        }

        if (!data.motoristName) data.motoristName = 'MOTORIST (OFFLINE)';
        if (!data.ticketNumber) data.ticketNumber = 'OFF-' + Math.floor(100000 + Math.random() * 900000);

        return data;
    }

    function buildSlipHtml(d) {
        return [
            '<div class="off-ticket-slip" id="off-print-slip" style="width:100%;max-width:384px;margin:0 auto;background:#fff;padding:15px;color:#000;font-family:\'Courier New\',Courier,monospace;font-size:15px;font-weight:900;line-height:1.4;box-sizing:border-box;text-align:left;">',
            '  <div style="text-align:center;font-size:14px;font-weight:900;margin-bottom:10px;">',
            '    <div>Republic of the Philippines</div>',
            '    <div>Province of Cebu</div>',
            '    <div>Municipality of Balamban</div>',
            '    <div style="margin-top:6px;font-size:16px;font-weight:900;">BALAMBAN POLICE STATION</div>',
            '    <div style="font-size:11px;font-weight:700;color:#555;margin-top:2px;">OFFICE OF THE TRAFFIC SECTION (TVIRS)</div>',
            '  </div>',
            '  <div style="text-align:center;font-size:18px;font-weight:900;margin:12px 0 10px;letter-spacing:-0.5px;border-top:2px solid #000;border-bottom:2px solid #000;padding:4px 0;">',
            '    TRAFFIC CITATION TICKET',
            '  </div>',
            '  <div style="display:flex;margin-bottom:8px;align-items:flex-end;">',
            '    <span style="font-size:13px;font-weight:900;margin-right:6px;white-space:nowrap;">DATE:</span>',
            '    <span style="border-bottom:1.5px solid #000;flex-grow:1;padding-bottom:1px;font-size:13px;font-weight:900;">' + escapeHtml(d.date) + '</span>',
            '  </div>',
            '  <div style="display:flex;margin-bottom:8px;align-items:flex-end;">',
            '    <span style="font-size:13px;font-weight:900;margin-right:6px;white-space:nowrap;">TO:</span>',
            '    <span style="border-bottom:1.5px solid #000;flex-grow:1;padding-bottom:1px;font-size:13px;font-weight:900;">' + escapeHtml(d.motoristName.toUpperCase()) + '</span>',
            '  </div>',
            d.motoristLicense ? (
                '  <div style="display:flex;margin-bottom:8px;align-items:flex-end;">' +
                '    <span style="font-size:13px;font-weight:900;margin-right:6px;white-space:nowrap;">LICENSE NO.:</span>' +
                '    <span style="border-bottom:1.5px solid #000;flex-grow:1;padding-bottom:1px;font-size:13px;font-weight:900;">' + escapeHtml(d.motoristLicense.toUpperCase()) + '</span>' +
                '  </div>'
            ) : '',
            d.motoristAddress ? (
                '  <div style="display:flex;margin-bottom:8px;align-items:flex-end;">' +
                '    <span style="font-size:13px;font-weight:900;margin-right:6px;white-space:nowrap;">ADDRESS:</span>' +
                '    <span style="border-bottom:1.5px solid #000;flex-grow:1;padding-bottom:1px;font-size:12px;font-weight:900;">' + escapeHtml(d.motoristAddress.toUpperCase()) + '</span>' +
                '  </div>'
            ) : '',
            '  <div style="display:flex;margin-bottom:8px;align-items:flex-end;">',
            '    <span style="font-size:13px;font-weight:900;margin-right:6px;white-space:nowrap;">PLATE NO.:</span>',
            '    <span style="border-bottom:1.5px solid #000;flex-grow:1;padding-bottom:1px;font-size:13px;font-weight:900;">' + escapeHtml(d.plateNumber.toUpperCase()) + '</span>',
            '  </div>',
            (d.make || d.color) ? (
                '  <div style="display:flex;gap:8px;margin-bottom:8px;align-items:flex-end;">' +
                '    <div style="flex:1;display:flex;align-items:flex-end;"><span style="font-size:12px;font-weight:900;margin-right:4px;">MAKE:</span><span style="border-bottom:1.5px solid #000;flex-grow:1;font-size:12px;font-weight:900;">' + escapeHtml((d.make || 'N/A').toUpperCase()) + '</span></div>' +
                '    <div style="flex:1;display:flex;align-items:flex-end;"><span style="font-size:12px;font-weight:900;margin-right:4px;">COLOR:</span><span style="border-bottom:1.5px solid #000;flex-grow:1;font-size:12px;font-weight:900;">' + escapeHtml((d.color || 'N/A').toUpperCase()) + '</span></div>' +
                '  </div>'
            ) : '',
            '  <div style="text-align:center;font-size:15px;font-weight:900;margin:18px 0 10px;border-top:1.5px dashed #000;padding-top:8px;">VIOLATION(S)</div>',
            '  <div style="margin-bottom:12px;">',
            '    <div style="display:flex;align-items:flex-start;">',
            '      <span style="margin-right:8px;font-size:16px;font-weight:900;">[X]</span>',
            '      <span style="font-size:15px;font-weight:900;">' + escapeHtml(d.violationType.toUpperCase()) + '</span>',
            '    </div>',
            '  </div>',
            '  <div style="margin-bottom:8px;">',
            '    <div style="font-size:12px;font-weight:900;margin-bottom:2px;">PLACE OF APPREHENSION:</div>',
            '    <div style="border-bottom:1.5px solid #000;width:100%;font-size:13px;font-weight:900;padding-bottom:1px;">' + escapeHtml(d.location.toUpperCase()) + '</div>',
            '  </div>',
            '  <div style="display:flex;gap:8px;margin-bottom:12px;align-items:flex-end;">',
            '    <div style="flex:2;display:flex;align-items:flex-end;"><span style="font-size:12px;font-weight:900;margin-right:4px;">TIME:</span><span style="border-bottom:1.5px solid #000;flex-grow:1;font-size:13px;font-weight:900;">' + escapeHtml(d.time) + '</span></div>',
            '    <div style="flex:1;display:flex;align-items:flex-end;"><span style="border-bottom:1.5px solid #000;flex-grow:1;font-size:13px;font-weight:900;text-align:center;">' + escapeHtml(d.ampm) + '</span></div>',
            '  </div>',
            '  <div style="margin:16px 0;border:2px solid #000;padding:8px;border-radius:4px;background:#fafafa;">',
            '    <div style="font-size:11px;font-weight:900;letter-spacing:0.04em;margin-bottom:2px;">CITATION REF NO. (OFFLINE QUEUED):</div>',
            '    <div style="font-size:16px;font-weight:900;font-family:Arial Black,sans-serif;letter-spacing:0.04em;">' + escapeHtml(d.ticketNumber) + '</div>',
            d.fineAmount ? ('    <div style="border-top:1.5px dashed #000;margin-top:6px;padding-top:4px;display:flex;justify-content:space-between;"><span style="font-size:12px;font-weight:900;">FINE AMOUNT:</span><span style="font-size:15px;font-weight:900;">PHP ' + escapeHtml(d.fineAmount) + '</span></div>') : '',
            '  </div>',
            '  <div style="text-align:center;margin-top:24px;">',
            '    <div style="border-bottom:1.5px solid #000;width:75%;margin:0 auto;height:24px;"></div>',
            '    <div style="font-size:11px;font-weight:700;margin-top:4px;">Driver / Violator Signature</div>',
            '  </div>',
            '  <div style="margin-top:20px;text-align:center;">',
            '    <div style="font-size:11px;font-weight:700;margin-bottom:2px;">Apprehending Traffic Officer:</div>',
            '    <div style="border-bottom:1.5px solid #000;width:85%;margin:0 auto;font-size:13px;font-weight:900;padding:4px 0;">' + escapeHtml(d.officerName.toUpperCase()) + '</div>',
            '  </div>',
            '  <div style="font-size:11px;text-align:justify;margin-top:16px;line-height:1.35;border-top:1px dashed #000;padding-top:8px;">',
            '    You are directed to settle this citation within 72 hours at the Traffic Operation Management Office. Failure to settle within the stipulated period will result in criminal charges pursuant to the Municipal Traffic Enforcement Code.',
            '  </div>',
            '  <div style="text-align:center;margin-top:14px;font-size:10px;color:#444;font-weight:700;">',
            '    *** TVIRS OFFLINE FIELD COPY ***',
            '  </div>',
            '</div>'
        ].join('\n');
    }

    function createModalElement() {
        var existing = document.getElementById(MODAL_ID);
        if (existing) return existing;

        var modal = document.createElement('div');
        modal.id = MODAL_ID;
        modal.style.cssText = 'position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:12px;';
        modal.innerHTML = [
            '<div class="off-ticket-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.8);backdrop-filter:blur(4px);"></div>',
            '<div class="off-ticket-container" style="position:relative;background:#fff;width:100%;max-width:440px;max-height:92vh;border-radius:20px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);z-index:1;">',
            '  <div class="off-ticket-header" style="background:#1d4ed8;color:#fff;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">',
            '    <div style="display:flex;align-items:center;gap:8px;">',
            '      <i class="ph-bold ph-printer" style="font-size:1.25rem;"></i>',
            '      <div>',
            '        <div style="font-weight:800;font-size:.92rem;line-height:1.2;">Citation Ticket Print</div>',
            '        <div style="font-size:.7rem;opacity:.9;">Pocket Printer / 58mm Thermal Slip</div>',
            '      </div>',
            '    </div>',
            '    <button type="button" id="off-ticket-close-btn" style="background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;">',
            '      <i class="ph-bold ph-x"></i>',
            '    </button>',
            '  </div>',
            '  <div id="off-ticket-status-bar" style="display:none;background:#fef3c7;color:#92400e;padding:6px 12px;font-size:.76rem;font-weight:700;text-align:center;border-bottom:1px solid #fde68a;"></div>',
            '  <div id="off-ticket-body" style="flex:1;overflow-y:auto;background:#f1f5f9;padding:12px;display:flex;justify-content:center;">',
            '    <!-- Slip will be rendered here -->',
            '  </div>',
            '  <div class="off-ticket-actions" style="background:#fff;border-top:1px solid #e2e8f0;padding:10px 14px;display:flex;flex-direction:column;gap:8px;flex-shrink:0;">',
            '    <div style="display:flex;gap:8px;">',
            '      <button type="button" id="off-btn-print-bt" style="flex:1;background:#2563eb;color:#fff;border:none;padding:10px 12px;border-radius:10px;font-size:.84rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">',
            '        <i class="ph-bold ph-bluetooth"></i> Bluetooth Print',
            '      </button>',
            '      <button type="button" id="off-btn-print-sys" style="flex:1;background:#0f172a;color:#fff;border:none;padding:10px 12px;border-radius:10px;font-size:.84rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">',
            '        <i class="ph-bold ph-printer"></i> System Print / PDF',
            '      </button>',
            '    </div>',
            '  </div>',
            '</div>'
        ].join('\n');

        document.body.appendChild(modal);

        modal.querySelector('.off-ticket-backdrop').addEventListener('click', closeModal);
        modal.querySelector('#off-ticket-close-btn').addEventListener('click', closeModal);
        modal.querySelector('#off-btn-print-sys').addEventListener('click', function () {
            window.print();
        });
        modal.querySelector('#off-btn-print-bt').addEventListener('click', function () {
            printViaWebBluetooth();
        });

        return modal;
    }

    function setStatusBar(msg, isError) {
        var modal = document.getElementById(MODAL_ID);
        if (!modal) return;
        var bar = modal.querySelector('#off-ticket-status-bar');
        if (!bar) return;

        if (!msg) {
            bar.style.display = 'none';
            bar.textContent = '';
            return;
        }

        bar.style.display = 'block';
        bar.style.background = isError ? '#fee2e2' : '#fef3c7';
        bar.style.color = isError ? '#991b1b' : '#92400e';
        bar.textContent = msg;
    }

    function convertCanvasToEscPos(canvas) {
        var ctx = canvas.getContext('2d');
        var width = canvas.width;
        var height = canvas.height;
        var imgData = ctx.getImageData(0, 0, width, height).data;
        var bytesWidth = Math.ceil(width / 8);
        var buffer = new Uint8Array(8 + (bytesWidth * height));

        // ESC/POS raster bit image: GS v 0 m xL xH yL yH
        buffer[0] = 0x1D; buffer[1] = 0x76; buffer[2] = 0x30; buffer[3] = 0x00;
        buffer[4] = bytesWidth & 0xFF; buffer[5] = (bytesWidth >> 8) & 0xFF;
        buffer[6] = height & 0xFF; buffer[7] = (height >> 8) & 0xFF;

        var offset = 8;
        for (var y = 0; y < height; y++) {
            for (var x = 0; x < bytesWidth; x++) {
                var byte = 0;
                for (var bit = 0; bit < 8; bit++) {
                    var pixelX = (x * 8) + bit;
                    if (pixelX < width) {
                        var idx = ((y * width) + pixelX) * 4;
                        var r = imgData[idx], g = imgData[idx + 1], b = imgData[idx + 2], a = imgData[idx + 3];
                        if (a > 128 && (r + g + b) < 420) {
                            byte |= (1 << (7 - bit));
                        }
                    }
                }
                buffer[offset++] = byte;
            }
        }
        return buffer;
    }

    async function sendChunks(characteristic, data) {
        var CHUNK_SIZE = 100;
        for (var i = 0; i < data.length; i += CHUNK_SIZE) {
            await characteristic.writeValue(data.slice(i, i + CHUNK_SIZE));
            await new Promise(function (r) { setTimeout(r, 25); });
        }
    }

    async function printViaWebBluetooth() {
        var btn = document.getElementById('off-btn-print-bt');
        if (!navigator.bluetooth) {
            alert('Web Bluetooth is only supported in Google Chrome on Android or Bluetooth-enabled browsers. Please use "System Print / PDF" instead.');
            return;
        }

        try {
            if (btn) btn.disabled = true;
            setStatusBar('Preparing thermal print canvas...');

            var slip = document.getElementById('off-print-slip');
            if (!slip) throw new Error('Ticket slip not found.');

            var canvas = null;
            if (window.html2canvas) {
                canvas = await window.html2canvas(slip, { scale: 1, backgroundColor: '#ffffff', logging: false });
            } else {
                throw new Error('Canvas renderer library not loaded yet. Use System Print.');
            }

            setStatusBar('Select your Bluetooth Pocket Printer...');
            var printData = convertCanvasToEscPos(canvas);

            var device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: [
                    '000018f0-0000-1000-8000-00805f9b34fb',
                    '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                    'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
                    '0000fee7-0000-1000-8000-00805f9b34fb',
                    '0000ae00-0000-1000-8000-00805f9b34fb'
                ]
            });

            setStatusBar('Connecting to ' + (device.name || 'printer') + '...');
            var server = await device.gatt.connect();

            setStatusBar('Finding ESC/POS printer service...');
            var services = await server.getPrimaryServices();
            var printChar = null;

            for (var i = 0; i < services.length; i++) {
                var chars = await services[i].getCharacteristics();
                for (var j = 0; j < chars.length; j++) {
                    if (chars[j].properties.write || chars[j].properties.writeWithoutResponse) {
                        printChar = chars[j];
                        break;
                    }
                }
                if (printChar) break;
            }

            if (!printChar) throw new Error('No writable ESC/POS characteristic found on device.');

            setStatusBar('Printing citation slip...');
            await printChar.writeValue(new Uint8Array([0x1B, 0x40])); // Init printer
            await sendChunks(printChar, printData);
            await printChar.writeValue(new Uint8Array([0x0A, 0x0A, 0x0A, 0x0A])); // Feed lines

            setStatusBar('✅ Citation Ticket Printed Successfully!');
            setTimeout(function () {
                if (device.gatt && device.gatt.connected) device.gatt.disconnect();
                if (btn) btn.disabled = false;
            }, 2500);

        } catch (error) {
            console.error('[OfflineTicket Print Error]', error);
            setStatusBar('❌ ' + (error.message || 'Print error'), true);
            if (btn) btn.disabled = false;
        }
    }

    async function openTicketPreview(recordOrData) {
        ensurePrintStyles();
        var modal = createModalElement();
        var body = modal.querySelector('#off-ticket-body');

        setStatusBar('Loading citation details...');
        modal.style.display = 'flex';
        document.body.classList.add('mob-sheet-open');

        var data = await extractTicketData(recordOrData);
        activeData = data;

        body.innerHTML = buildSlipHtml(data);
        setStatusBar('');
    }

    function closeModal() {
        var modal = document.getElementById(MODAL_ID);
        if (modal) {
            modal.style.display = 'none';
        }
        document.body.classList.remove('mob-sheet-open');
        setStatusBar('');
    }

    window.TvirsOfflineTicket = {
        openPreview: openTicketPreview,
        close: closeModal,
        printBluetooth: printViaWebBluetooth,
        printSystem: function () { window.print(); }
    };
})();
