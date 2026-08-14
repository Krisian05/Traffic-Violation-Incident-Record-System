/**
 * TVIRS Mobile — Offline Citation Ticket Generator & Printer
 * Exactly matches the online /violations/{id}/print-thermal layout, styling, and typography.
 */
(function () {
    'use strict';

    var MODAL_ID = 'tvirs-offline-ticket-modal';
    var PRINT_STYLE_ID = 'tvirs-offline-ticket-print-style';
    var activeData = null;

    var VIOLATION_TYPE_MAP = {
        '1': { name: 'DRIVING WITHOUT LICENSE', fine: '750.00' },
        '2': { name: 'NO HELMET / FAILURE TO WEAR HELMET', fine: '500.00' },
        '3': { name: 'RECKLESS DRIVING', fine: '1,000.00' },
        '4': { name: 'ILLEGAL PARKING / OBSTRUCTION', fine: '500.00' },
        '5': { name: 'OVER-SPEEDING', fine: '1,000.00' },
        '6': { name: 'UNREGISTERED MOTOR VEHICLE', fine: '1,500.00' },
        '7': { name: 'DISREGARDING TRAFFIC SIGN', fine: '500.00' }
    };

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
            name: 'PATROL OFFICER'
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
            '  #' + MODAL_ID + ' #off-ticket-status-bar { display: none !important; }',
            '  #' + MODAL_ID + ' .off-ticket-container { box-shadow: none !important; border: none !important; padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: 384px !important; background: #fff !important; }',
            '  #' + MODAL_ID + ' .slip { box-shadow: none !important; border: none !important; margin: 0 auto !important; width: 384px !important; max-width: 384px !important; padding: 10px 5px !important; font-size: 18px !important; }',
            '  @page { margin: 2mm; size: 58mm auto; }',
            '}'
        ].join('\n');
        document.head.appendChild(style);
    }

    async function extractTicketData(recordOrData) {
        if (!recordOrData) return null;

        var officer = getAuthOfficer();
        var data = {
            stationName: 'BALAMBAN MUNICIPAL POLICE STATION',
            lguName: 'Balamban',
            provinceName: 'Cebu',
            ordinanceRef: 'Ordinance No. 2005-09',
            officerName: officer.name || 'PATROL OFFICER',
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
            qrUrl: ''
        };

        var entries = recordOrData.entries || [];
        function getVal(name) {
            var found = entries.filter(function (e) { return e.name === name; });
            return found.length ? cleaned(found[found.length - 1].value) : '';
        }

        if (recordOrData.entries) {
            var typeId = getVal('violation_type_id');
            var mapped = VIOLATION_TYPE_MAP[typeId] || null;

            data.ticketNumber = getVal('ticket_number') || ('TVIRS-CEB-BAL-' + new Date().getFullYear() + '-' + String(recordOrData.id || Date.now()).slice(-6));
            data.location = getVal('location') || getVal('place_of_violation') || 'BALAMBAN, CEBU';
            data.violationType = (recordOrData.summary && recordOrData.summary.violationTypeName) || (mapped ? mapped.name : '') || getVal('violation_type_name') || 'DRIVING WITHOUT LICENSE';
            data.fineAmount = (mapped ? mapped.fine : '') || (recordOrData.summary && recordOrData.summary.fineAmount) || '750.00';
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

            var parentKey = recordOrData.parentOfflineMotoristKey || getVal('offline_motorist_key');
            if (parentKey && window.TvirsOffline && typeof window.TvirsOffline.getOfflineMotoristByKey === 'function') {
                try {
                    var motorist = await window.TvirsOffline.getOfflineMotoristByKey(parentKey);
                    if (motorist && motorist.summary) {
                        data.motoristName = motorist.summary.displayName || (motorist.summary.firstName + ' ' + motorist.summary.lastName);
                        data.motoristAddress = motorist.summary.address || '';
                        data.motoristLicense = motorist.summary.licenseNumber || '';
                    }
                } catch (e) {}
            }

            if (!data.motoristName && recordOrData.summary) {
                data.motoristName = recordOrData.summary.motoristName || recordOrData.summary.displayName || '';
            }
        } else {
            data.motoristName = recordOrData.motoristName || 'UNNAMED MOTORIST';
            data.motoristAddress = recordOrData.motoristAddress || '';
            data.motoristLicense = recordOrData.motoristLicense || '';
            data.plateNumber = recordOrData.plateNumber || 'N/A';
            data.make = recordOrData.make || '';
            data.color = recordOrData.color || '';
            data.violationType = recordOrData.violationType || 'TRAFFIC CITATION';
            data.fineAmount = recordOrData.fineAmount || '750.00';
            data.location = recordOrData.location || 'BALAMBAN, CEBU';
            data.ticketNumber = recordOrData.ticketNumber || ('TVIRS-CEB-BAL-' + new Date().getFullYear() + '-' + Math.floor(100000 + Math.random() * 900000));
            if (recordOrData.date) data.date = recordOrData.date;
        }

        if (!data.motoristName) data.motoristName = 'MOTORIST (FIELD ISSUED)';
        data.qrUrl = window.location.origin + '/pay?ref=' + encodeURIComponent(data.ticketNumber);

        return data;
    }

    function buildSlipHtml(d) {
        var ticketLen = d.ticketNumber.length;
        var ticketFontSize = ticketLen > 26 ? '13px' : (ticketLen > 22 ? '15px' : (ticketLen > 18 ? '17px' : '20px'));

        return [
            '<div class="slip" id="print-area" style="width:384px;margin:10px auto;padding:15px;background:#fff;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);font-family:\'Courier New\',Courier,monospace;color:#000;font-size:18px;font-weight:900;line-height:1.4;box-sizing:border-box;text-align:left;">',
            '  <div class="text-center header-text" style="text-align:center;font-size:16px;font-weight:900;">',
            '    <div>Republic of the Philippines</div>',
            '    <div>Province of ' + escapeHtml(d.provinceName) + '</div>',
            '    <div>Municipality of ' + escapeHtml(d.lguName) + '</div>',
            '    <div style="margin-top:10px;font-size:18px;font-weight:900;">' + escapeHtml(d.stationName.toUpperCase()) + '</div>',
            '  </div>',
            '  <div class="text-center title-text" style="text-align:center;font-size:22px;font-weight:900;margin:15px 0;letter-spacing:-0.5px;">',
            '    TRAFFIC CITATION TICKET',
            '  </div>',
            '  <div class="field-row" style="display:flex;align-items:flex-end;margin-bottom:10px;width:100%;">',
            '    <div class="field-label" style="font-size:14px;font-weight:900;white-space:nowrap;flex-shrink:0;margin-right:6px;">DATE:</div>',
            '    <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:block;padding-bottom:2px;flex-grow:1;font-size:14px;font-weight:900;word-break:break-word;line-height:1.3;">' + escapeHtml(d.date) + '</div>',
            '  </div>',
            '  <div class="field-row" style="display:flex;align-items:flex-end;margin-bottom:10px;width:100%;">',
            '    <div class="field-label" style="font-size:14px;font-weight:900;white-space:nowrap;flex-shrink:0;margin-right:6px;">TO:</div>',
            '    <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:block;padding-bottom:2px;flex-grow:1;font-size:14px;font-weight:900;word-break:break-word;line-height:1.3;">' + escapeHtml(d.motoristName.toUpperCase()) + '</div>',
            '  </div>',
            '  <div class="field-row" style="display:flex;align-items:flex-end;margin-bottom:10px;width:100%;">',
            '    <div class="field-label" style="font-size:14px;font-weight:900;white-space:nowrap;flex-shrink:0;margin-right:6px;">ADDRESS:</div>',
            '    <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:block;padding-bottom:2px;flex-grow:1;font-size:14px;font-weight:900;word-break:break-word;line-height:1.3;">' + escapeHtml((d.motoristAddress || 'BALAMBAN, CEBU').toUpperCase()) + '</div>',
            '  </div>',
            '  <div class="field-row" style="display:flex;align-items:flex-end;margin-bottom:10px;width:100%;">',
            '    <div class="field-label" style="font-size:14px;font-weight:900;white-space:nowrap;flex-shrink:0;margin-right:6px;">VEHICLE PLATE NO.:</div>',
            '    <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:block;padding-bottom:2px;flex-grow:1;font-size:14px;font-weight:900;word-break:break-word;line-height:1.3;">' + escapeHtml(d.plateNumber.toUpperCase()) + '</div>',
            '  </div>',
            '  <div class="flex-row" style="display:flex;gap:8px;margin-bottom:10px;align-items:flex-end;width:100%;">',
            '    <div class="flex-col" style="flex:1;display:flex;align-items:flex-end;overflow:hidden;">',
            '      <div class="field-label" style="font-size:14px;font-weight:900;white-space:nowrap;flex-shrink:0;margin-right:6px;">MAKE:</div>',
            '      <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:block;padding-bottom:2px;flex-grow:1;font-size:14px;font-weight:900;word-break:break-word;line-height:1.3;">' + escapeHtml((d.make || 'N/A').toUpperCase()) + '</div>',
            '    </div>',
            '    <div class="flex-col" style="flex:1;display:flex;align-items:flex-end;overflow:hidden;">',
            '      <div class="field-label" style="font-size:14px;font-weight:900;white-space:nowrap;flex-shrink:0;margin-right:6px;">COLOR:</div>',
            '      <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:block;padding-bottom:2px;flex-grow:1;font-size:14px;font-weight:900;word-break:break-word;line-height:1.3;">' + escapeHtml((d.color || 'N/A').toUpperCase()) + '</div>',
            '    </div>',
            '  </div>',
            '  <div class="text-center title-text" style="text-align:center;font-size:22px;font-weight:900;margin:25px 0 15px;letter-spacing:-0.5px;">VIOLATION(S)</div>',
            '  <div style="margin-bottom:20px;">',
            '    <div style="display:flex;align-items:flex-start;">',
            '      <span style="margin-right:12px;font-size:20px;font-weight:900;">[X]</span>',
            '      <span style="font-size:20px;font-weight:900;">' + escapeHtml(d.violationType.toUpperCase()) + '</span>',
            '    </div>',
            '  </div>',
            '  <div style="margin-bottom:12px;">',
            '    <div class="field-label" style="display:block;margin-bottom:3px;font-size:14px;font-weight:900;">PLACE OF VIOLATION:</div>',
            '    <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:block;padding-bottom:2px;width:100%;font-size:14px;font-weight:900;word-break:break-word;line-height:1.3;">' + escapeHtml(d.location.toUpperCase()) + '</div>',
            '  </div>',
            '  <div class="flex-row" style="display:flex;gap:8px;margin-bottom:10px;align-items:flex-end;width:100%;">',
            '    <div class="flex-col" style="flex:2;display:flex;align-items:flex-end;overflow:hidden;">',
            '      <div class="field-label" style="font-size:14px;font-weight:900;white-space:nowrap;flex-shrink:0;margin-right:6px;">TIME OF VIOLATION:</div>',
            '      <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:block;padding-bottom:2px;flex-grow:1;font-size:14px;font-weight:900;word-break:break-word;line-height:1.3;">' + escapeHtml(d.time) + '</div>',
            '    </div>',
            '    <div class="flex-col" style="flex:1;display:flex;align-items:flex-end;overflow:hidden;">',
            '      <div class="field-label" style="font-size:14px;font-weight:900;white-space:nowrap;flex-shrink:0;margin-right:6px;">(AM/PM)</div>',
            '      <div class="field-value" style="border-bottom:2px solid #000;min-height:24px;display:flex;justify-content:center;padding-bottom:2px;flex-grow:1;font-size:14px;font-weight:900;line-height:1.3;">' + escapeHtml(d.ampm) + '</div>',
            '    </div>',
            '  </div>',
            '  <div class="text-center" style="text-align:center;margin-top:40px;">',
            '    <div style="border-bottom:2px solid #000;width:80%;margin:0 auto;height:30px;"></div>',
            '    <div style="margin-top:5px;font-size:14px;font-weight:900;">Driver\'s Signature</div>',
            '  </div>',
            '  <div class="footer-text" style="font-size:15px;text-align:justify;margin-top:15px;line-height:1.3;">',
            '    You are directed to settle this within 72 hours to the ' + escapeHtml(d.lguName) + ' Traffic Operation Management Office from the date hereof for disposition appropriation in the citation.',
            '    <br><br>',
            '    Failure to settle within the period stipulated will mean a waiver and criminal complaint against you will be filed in pursuant to the provisions of ' + escapeHtml(d.ordinanceRef) + ' otherwise known as the Municipal Traffic Enforcement Code 2005.',
            '  </div>',
            '  <div style="margin:20px 0;border:2px solid #000;padding:10px;border-radius:4px;text-align:left;">',
            '    <div style="font-size:13px;font-weight:900;letter-spacing:0.05em;margin-bottom:4px;">CITATION / O.R. REF NO.:</div>',
            '    <div style="font-size:' + ticketFontSize + ';font-weight:900;font-family:\'Arial Black\',\'Segoe UI Black\',Arial,sans-serif;-webkit-text-stroke:0.5px #000;letter-spacing:0.02em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;margin-bottom:8px;">',
            '      <strong>' + escapeHtml(d.ticketNumber) + '</strong>',
            '    </div>',
            d.fineAmount ? (
                '    <div style="border-top:2px dashed #000;padding-top:6px;display:flex;justify-content:space-between;align-items:center;">' +
                '      <span style="font-size:14px;font-weight:800;">AMOUNT DUE:</span>' +
                '      <span style="font-size:20px;font-weight:900;">PHP ' + escapeHtml(d.fineAmount) + '</span>' +
                '    </div>'
            ) : '',
            '  </div>',
            '  <div style="margin-top:35px;margin-bottom:30px;">',
            '    <div style="font-size:14px;font-weight:900;">Apprehending Traffic Officer:</div>',
            '    <div style="border-bottom:2px solid #000;min-height:40px;margin-top:15px;text-align:center;display:flex;align-items:flex-end;justify-content:center;padding-bottom:5px;font-size:16px;font-weight:900;">',
            '      ' + escapeHtml(d.officerName.toUpperCase()),
            '    </div>',
            '  </div>',
            '  <div class="qr-container" style="display:flex;flex-direction:column;align-items:center;margin:20px 0;">',
            '    <canvas id="offlineThermalTicketQr" style="width:180px;height:180px;image-rendering:pixelated;margin-bottom:8px;"></canvas>',
            '    <div style="font-size:14px;font-weight:700;margin-top:5px;text-align:center;">SCAN TO VIEW &amp; PAY CITATION</div>',
            '  </div>',
            '  <div style="height:40px;"></div>',
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
            '        <div style="font-size:.7rem;opacity:.9;">Official Thermal Citation Slip</div>',
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
            '      <button type="button" id="off-btn-print-bt" style="flex:1;background:#2563eb;color:#fff;border:none;padding:12px 14px;border-radius:10px;font-size:.88rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">',
            '        <i class="ph-bold ph-bluetooth"></i> Connect & Print',
            '      </button>',
            '      <button type="button" id="off-btn-print-sys" style="flex:1;background:#0f172a;color:#fff;border:none;padding:12px 14px;border-radius:10px;font-size:.88rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">',
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
            await new Promise(function (r) { setTimeout(r, 20); });
        }
    }

    async function printViaWebBluetooth() {
        var btn = document.getElementById('off-btn-print-bt');
        if (!navigator.bluetooth) {
            alert('Web Bluetooth is not supported in this browser. Please use Google Chrome for Android or tap "System Print / PDF".');
            return;
        }

        try {
            if (btn) btn.disabled = true;
            setStatusBar('Rendering receipt image...');

            var slip = document.getElementById('print-area');
            if (!slip) throw new Error('Ticket slip not found.');

            var canvas = null;
            if (window.html2canvas) {
                canvas = await window.html2canvas(slip, { scale: 1, backgroundColor: '#ffffff', logging: false, useCORS: true, allowTaint: true });
            } else {
                throw new Error('Canvas renderer library not loaded yet. Use System Print.');
            }

            setStatusBar('Encoding print data...');
            var printData = convertCanvasToEscPos(canvas);

            setStatusBar('Select your printer...');
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

            setStatusBar('Searching for Print Service...');
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

            if (!printChar) throw new Error('No writable print characteristic found.');

            setStatusBar('Printing...');
            await printChar.writeValue(new Uint8Array([0x1B, 0x40]));
            await sendChunks(printChar, printData);
            await printChar.writeValue(new Uint8Array([0x0A, 0x0A, 0x0A, 0x0A]));

            setStatusBar('✅ Print Complete!');
            setTimeout(function () {
                if (device.gatt && device.gatt.connected) device.gatt.disconnect();
                if (btn) btn.disabled = false;
            }, 2000);

        } catch (error) {
            console.error(error);
            setStatusBar('❌ Error: ' + (error.message || 'Print error'), true);
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

        // Generate QR code
        setTimeout(function () {
            var qrCanvas = document.getElementById('offlineThermalTicketQr');
            if (qrCanvas && window.QRCode && typeof window.QRCode.toCanvas === 'function') {
                window.QRCode.toCanvas(qrCanvas, data.qrUrl || data.ticketNumber, {
                    width: 180,
                    margin: 1
                }, function (err) {
                    if (err) console.warn('[QR Code Error]', err);
                });
            }
        }, 50);
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
