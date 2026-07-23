(function () {
    'use strict';

    var OCR_ENGINE_SRC = '/vendor/tesseract/tesseract.min.js';
    var OCR_WORKER_PATH = '/vendor/tesseract/worker.min.js';
    // A directory (not a specific file) so Tesseract.js can pick between
    // tesseract-core-simd-lstm.wasm.js and tesseract-core-lstm.wasm.js based on
    // whether this device's browser supports WASM SIMD — pointing this at one
    // fixed file would break OCR on devices lacking SIMD support.
    var OCR_CORE_PATH = '/vendor/tesseract';
    var OCR_LANG_PATH = '/vendor/tesseract';

    var engineLoadPromise = null;
    var workerPromise = null;

    // Set by recognizeIdFromCanvas() while a scan is actively in progress, so
    // load/recognize progress from the worker's `logger` reaches the caller's
    // status UI. A no-op the rest of the time (e.g. during background preload).
    var activeStatusCallback = function () {};

    var OCR_STATUS_LABELS = {
        'loading tesseract core': 'Loading text reader engine',
        'loading language traineddata': 'Loading language data',
        'initializing tesseract': 'Preparing text reader',
        'recognizing text': 'Reading text from ID'
    };

    function reportOcrProgress(m) {
        if (!m || !m.status) return;
        var label = OCR_STATUS_LABELS[m.status] || m.status;
        var pct = typeof m.progress === 'number' ? Math.round(m.progress * 100) : null;
        activeStatusCallback(label + (pct !== null ? '… ' + pct + '%' : '…'));
    }

    function loadOcrEngine() {
        if (typeof window.Tesseract !== 'undefined') {
            return Promise.resolve();
        }

        if (engineLoadPromise) {
            return engineLoadPromise;
        }

        engineLoadPromise = new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = OCR_ENGINE_SRC;
            script.async = true;
            script.onload = function () { resolve(); };
            script.onerror = function () {
                engineLoadPromise = null;
                reject(new Error('Failed to load the OCR engine.'));
            };
            document.head.appendChild(script);
        });

        return engineLoadPromise;
    }

    function getOcrWorker() {
        if (workerPromise) {
            return workerPromise;
        }

        workerPromise = loadOcrEngine().then(function () {
            return window.Tesseract.createWorker('eng', 1, {
                workerPath: OCR_WORKER_PATH,
                corePath: OCR_CORE_PATH,
                langPath: OCR_LANG_PATH,
                logger: reportOcrProgress
            });
        }).then(function (worker) {
            // Sparse-text mode suits ID cards better than the default fully-automatic
            // layout analysis, since a photo/logo sits alongside scattered text fields
            // rather than one uniform paragraph block.
            return worker.setParameters({ tessedit_pageseg_mode: '11' }).then(function () {
                return worker;
            });
        }).catch(function (error) {
            workerPromise = null;
            throw error;
        });

        return workerPromise;
    }

    // Fully initializes the OCR worker — engine script, WASM core, and language
    // data — in the background as soon as the scanner opens. This is the part
    // that actually takes multiple seconds; doing it ahead of time means the
    // officer's first "Snap & Read" only pays the (fast) recognition cost
    // instead of also waiting for engine/data loading at that moment.
    function preloadOcrEngine() {
        getOcrWorker().catch(function () {
            // Silently ignore — an explicit recognize() call will surface any error.
        });
    }

    function titleCase(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    var MONTHS = {
        jan: 1, feb: 2, mar: 3, apr: 4, may: 5, jun: 6,
        jul: 7, aug: 8, sep: 9, oct: 10, nov: 11, dec: 12
    };

    // Rejects calendar-impossible dates (e.g. month 13, Feb 30) that a naive
    // regex match would otherwise happily "normalize" — OCR misreads (a smudged
    // digit, a merged character) commonly produce exactly this kind of garbage.
    function isValidCalendarDate(year, month, day) {
        if (year < 1900 || year > 2099) return false;
        if (month < 1 || month > 12) return false;
        if (day < 1 || day > 31) return false;

        var d = new Date(year, month - 1, day);
        return d.getFullYear() === year && d.getMonth() === month - 1 && d.getDate() === day;
    }

    function formatDate(year, month, day) {
        return year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
    }

    function normalizeDate(value) {
        var s = String(value || '').trim();
        if (!s) return '';

        var iso = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
        if (iso) {
            var isoY = parseInt(iso[1], 10), isoM = parseInt(iso[2], 10), isoD = parseInt(iso[3], 10);
            return isValidCalendarDate(isoY, isoM, isoD) ? formatDate(isoY, isoM, isoD) : '';
        }

        var mdy = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
        if (mdy) {
            var mdyM = parseInt(mdy[1], 10), mdyD = parseInt(mdy[2], 10), mdyY = parseInt(mdy[3], 10);
            return isValidCalendarDate(mdyY, mdyM, mdyD) ? formatDate(mdyY, mdyM, mdyD) : '';
        }

        var textDate = s.match(/^([A-Za-z]{3,9})\.?\s+(\d{1,2}),?\s+(\d{4})$/);
        if (textDate) {
            var monNum = MONTHS[textDate[1].slice(0, 3).toLowerCase()];
            var textD = parseInt(textDate[2], 10), textY = parseInt(textDate[3], 10);
            if (monNum && isValidCalendarDate(textY, monNum, textD)) {
                return formatDate(textY, monNum, textD);
            }
        }

        return '';
    }

    var VALID_BLOOD_TYPES = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

    // PH licenses print height in meters (e.g. "1.68"); the form field wants
    // whole centimeters. A value under 3 is assumed to be meters (no adult is
    // 3m tall); anything else is assumed to already be centimeters.
    function normalizeHeightToCm(value) {
        var num = parseFloat(value);
        if (!isFinite(num) || num <= 0) return '';
        if (num < 3) num *= 100;
        return String(Math.round(num));
    }

    function isPlausibleBirthDate(isoDate) {
        var parts = isoDate.split('-');
        var asDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        var now = new Date();
        var ageMs = now - asDate;
        var ageYears = ageMs / (365.25 * 24 * 60 * 60 * 1000);
        return ageYears >= 0 && ageYears <= 120;
    }

    // Field-label captions that can end up on the same OCR'd line as, or
    // immediately after, the label being searched for — most notably the PH
    // driver's license's own merged "Last Name, First Name, Middle Name"
    // caption row, whose commas Tesseract commonly misreads as periods,
    // making it look like a single line of prose rather than a header. A
    // "value" that is actually still one of these labels must be rejected,
    // not accepted — mirroring OcrController.php's server-side label guards,
    // which have no client-side equivalent otherwise.
    var LABEL_WORDS_RE = /\b(last\s*name|first\s*name|middle\s*name|given\s*name|apelyido|pangalan|surname|sex|gender|date\s*of\s*birth|birth\s*date|dob|address|license\s*no|lic\s*no|id\s*no|control\s*no|card\s*no|nationality|expir\w*|valid\s*until|weight|height|blood\s*type|license\s*type|conditions|remarks|restrictions|agency\s*code)\b/i;

    function isLabelContaminated(value) {
        return LABEL_WORDS_RE.test(value);
    }

    function findAfterLabel(lines, labelPattern) {
        var re = new RegExp('(?:' + labelPattern + ')\\s*[:\\-]?\\s*(.+)', 'i');
        var labelOnlyRe = new RegExp('^(?:' + labelPattern + ')\\s*[:\\-]?\\s*$', 'i');

        for (var i = 0; i < lines.length; i++) {
            var match = lines[i].match(re);
            if (match && match[1] && match[1].trim() && !isLabelContaminated(match[1].trim())) {
                return match[1].trim();
            }
            if (labelOnlyRe.test(lines[i]) && lines[i + 1] && !isLabelContaminated(lines[i + 1])) {
                return lines[i + 1].trim();
            }
        }

        return '';
    }

    // Best-effort extraction of motorist fields from free-form OCR text off any
    // government-issued ID. Deliberately conservative: a field is only filled
    // when a reasonably confident match is found, since the officer reviews and
    // corrects the autofilled form before saving.
    function parseIdText(rawText) {
        var text = String(rawText || '');
        var lines = text.split(/\r?\n/).map(function (l) { return l.trim(); }).filter(Boolean);
        var data = { raw: text };

        var licenseMatch = text.match(/\b([A-Z]\d{2}[-\s]?\d{2}[-\s]?\d{6})\b/);
        if (licenseMatch) {
            data.license_number = licenseMatch[1].replace(/\s/g, '-');
        } else {
            var idNoText = findAfterLabel(lines, 'license\\s*no|lic\\s*no|id\\s*no|control\\s*no|card\\s*no');
            if (idNoText) {
                var cleanedId = idNoText.match(/[A-Z0-9\-]{6,}/i);
                if (cleanedId) data.license_number = cleanedId[0];
            }
        }

        var lastName = findAfterLabel(lines, 'last\\s*name|apelyido|surname');
        var firstName = findAfterLabel(lines, 'first\\s*name|given\\s*name|pangalan');
        var middleName = findAfterLabel(lines, 'middle\\s*name|middle\\s*initial');

        if (lastName) data.last_name = titleCase(lastName);
        if (firstName) data.first_name = titleCase(firstName);
        if (middleName) data.middle_name = titleCase(middleName);

        // Fallback: a standalone "LAST NAME, FIRST NAME MIDDLE" line, common on
        // the front of driver's licenses and several other PH ID cards.
        if (!data.last_name && !data.first_name) {
            var nameLine = lines.find(function (l) {
                return /,/.test(l) && /^[A-Z\s,.'\-]+$/.test(l) && l.length > 5 && l.length < 60 && !isLabelContaminated(l);
            });
            if (nameLine) {
                var parts = nameLine.split(',');
                if (parts.length >= 2) {
                    data.last_name = titleCase(parts[0].trim());
                    var rest = parts[1].trim().split(/\s+/).filter(Boolean);
                    if (rest[0]) data.first_name = titleCase(rest[0]);
                    if (rest.length > 1) data.middle_name = titleCase(rest.slice(1).join(' '));
                }
            }
        }

        var dobLabelText = findAfterLabel(lines, 'date\\s*of\\s*birth|birth\\s*date|dob');
        var dobSource = dobLabelText || text;
        var dobMatch = dobSource.match(/\b(\d{4}-\d{1,2}-\d{1,2})\b/)
            || dobSource.match(/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})\b/)
            || dobSource.match(/\b([A-Za-z]{3,9}\.?\s+\d{1,2},?\s+\d{4})\b/);
        if (dobMatch) {
            var normalizedDob = normalizeDate(dobMatch[1]);
            // Calendar-valid but implausible as a birth date (in the future, or
            // implying an age over 120) is still almost certainly an OCR misread.
            if (normalizedDob && isPlausibleBirthDate(normalizedDob)) {
                data.date_of_birth = normalizedDob;
            }
        }

        var expiryLabelText = findAfterLabel(lines, 'expir\\w*|exp\\s*date|valid\\s*until');
        if (expiryLabelText) {
            var expMatch = expiryLabelText.match(/\b(\d{4}-\d{1,2}-\d{1,2})\b/)
                || expiryLabelText.match(/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})\b/);
            if (expMatch) {
                var normalizedExpiry = normalizeDate(expMatch[1]);
                if (normalizedExpiry) data.license_expiry_date = normalizedExpiry;
            }
        }

        var sexText = findAfterLabel(lines, 'sex|gender');
        if (sexText) {
            if (/^m(ale)?$/i.test(sexText.trim())) data.gender = 'Male';
            else if (/^f(emale)?$/i.test(sexText.trim())) data.gender = 'Female';
        }

        var addressText = findAfterLabel(lines, 'address');
        if (addressText) data.address = addressText;

        // License Type: derived from the card's own title/header rather than
        // a labeled field — a plain "DRIVER'S LICENSE" heading (no
        // "Professional") means Non-Professional.
        if (/professional\s*driver.?s\s*licen[sc]e/i.test(text)) {
            data.license_type = 'Professional';
        } else if (/driver.?s\s*licen[sc]e/i.test(text)) {
            data.license_type = 'Non-Professional';
        }

        var bloodTypeText = findAfterLabel(lines, 'blood\\s*type');
        if (bloodTypeText) {
            var bt = bloodTypeText.trim().toUpperCase().match(/^(O|A|B|AB)[+\-]/);
            if (bt && VALID_BLOOD_TYPES.indexOf(bt[0]) !== -1) {
                data.blood_type = bt[0];
            }
        }

        var heightText = findAfterLabel(lines, 'height');
        var heightMatch = heightText && heightText.match(/\d+(\.\d+)?/);
        if (heightMatch) {
            var heightCm = normalizeHeightToCm(heightMatch[0]);
            if (heightCm) data.height = heightCm;
        }

        var weightText = findAfterLabel(lines, 'weight');
        var weightMatch = weightText && weightText.match(/\d+(\.\d+)?/);
        if (weightMatch) data.weight = String(Math.round(parseFloat(weightMatch[0])));

        // "NONE" is the card's own explicit "nothing to report" value, not
        // real data worth autofilling.
        var conditionsText = findAfterLabel(lines, 'conditions|remarks');
        if (conditionsText && conditionsText.trim().toLowerCase() !== 'none') {
            data.license_conditions = conditionsText.trim();
        }

        // Fallback: a merged multi-column header row, e.g. "Nationality Sex
        // Date of Birth Weight(kg) Height(m)" immediately followed by ONE
        // values line in the same order (e.g. "PHL M 1975/06/28 70 1.68") —
        // the standard PH driver's license layout. The label-based lookups
        // above miss this because no single field's label stands alone on
        // its own line; match each header to the value at the same ORDINAL
        // position instead, same approach as OcrController.php's regex tier.
        if (!data.gender || !data.date_of_birth || !data.weight || !data.height) {
            for (var ci = 0; ci < lines.length - 1; ci++) {
                var colMatches = lines[ci].match(/Nationality|Sex|Date\s+of\s+Birth|Weight(?:\s*\(kg\))?|Height(?:\s*\(m\))?/gi);
                if (!colMatches || colMatches.length < 2) continue;

                var columns = colMatches.map(function (c) { return c.toLowerCase().replace(/\s+/g, ' ').trim(); });
                var values = lines[ci + 1].trim().split(/\s+/).filter(Boolean);
                if (values.length < columns.length) continue;

                columns.forEach(function (col, idx) {
                    var val = values[idx] || '';
                    if (!val) return;
                    if (!data.gender && col.indexOf('sex') !== -1 && /^(m|f|male|female)$/i.test(val)) {
                        data.gender = /^m/i.test(val) ? 'Male' : 'Female';
                    }
                    if (!data.date_of_birth && col.indexOf('date of birth') !== -1) {
                        var dm = val.match(/(\d{4})\/(\d{1,2})\/(\d{1,2})/);
                        if (dm) {
                            var iso = normalizeDate(dm[1] + '-' + dm[2] + '-' + dm[3]);
                            if (iso && isPlausibleBirthDate(iso)) data.date_of_birth = iso;
                        }
                    }
                    if (!data.weight && col.indexOf('weight') !== -1 && /^\d+(\.\d+)?$/.test(val)) {
                        data.weight = String(Math.round(parseFloat(val)));
                    }
                    if (!data.height && col.indexOf('height') !== -1 && /^\d+(\.\d+)?$/.test(val)) {
                        var hcm = normalizeHeightToCm(val);
                        if (hcm) data.height = hcm;
                    }
                });
                break;
            }
        }

        return data;
    }

    // Trims very large captures down to a size that recognizes noticeably faster
    // without losing enough detail to hurt accuracy — ID card text stays legible
    // well below the ~1920px long edge the camera can capture at. This budget is
    // for the on-device Tesseract path specifically, where higher resolution
    // means a slower, hotter CPU/WASM workload on the officer's phone.
    var OCR_MAX_DIMENSION = 1280;

    // The cloud path (Gemini/OCR.Space) has no local compute cost, so small
    // print — license number, DOB, blood type — benefits from noticeably more
    // pixels and less compression than the on-device budget above allows.
    var OCR_SERVER_MAX_DIMENSION = 2200;
    var OCR_SERVER_JPEG_QUALITY = 0.92;

    function prepareCanvasForOcr(sourceCanvas, maxDimension) {
        var limit = maxDimension || OCR_MAX_DIMENSION;
        var longestEdge = Math.max(sourceCanvas.width, sourceCanvas.height);
        if (longestEdge <= limit) {
            return sourceCanvas;
        }

        var scale = limit / longestEdge;
        var scaledCanvas = document.createElement('canvas');
        scaledCanvas.width = Math.round(sourceCanvas.width * scale);
        scaledCanvas.height = Math.round(sourceCanvas.height * scale);

        var ctx = scaledCanvas.getContext('2d');
        ctx.drawImage(sourceCanvas, 0, 0, scaledCanvas.width, scaledCanvas.height);
        return scaledCanvas;
    }

    // A recognition with almost no text is more likely camera noise/blur than a
    // genuine (near-)blank ID, so treat it as "nothing usable was read".
    var MIN_USABLE_TEXT_LENGTH = 6;

    // Tier 3: on-device Tesseract.js. Used when the cloud OCR endpoint is
    // unreachable or errors out, so enforcers are never stuck without a scanner.
    function recognizeIdFromCanvasLocal(canvas, onStatus) {
        var setStatus = typeof onStatus === 'function' ? onStatus : function () {};

        // Route the worker's own load/recognize progress (via its `logger`) to
        // the caller's status UI for the duration of this scan.
        activeStatusCallback = setStatus;
        setStatus('Preparing text reader…');

        var finish = function (outcome) {
            activeStatusCallback = function () {};
            return outcome;
        };

        return getOcrWorker().then(function (worker) {
            var ocrCanvas = prepareCanvasForOcr(canvas);
            // Only text output is used — skipping blocks/hocr/tsv generation
            // recognizes measurably faster.
            return worker.recognize(ocrCanvas, {}, { text: true, blocks: false, hocr: false, tsv: false });
        }).then(function (result) {
            var text = (result && result.data && result.data.text) || '';
            if (text.trim().length < MIN_USABLE_TEXT_LENGTH) {
                return finish({ text: text, parsed: { raw: text } });
            }
            return finish({ text: text, parsed: parseIdText(text) });
        }).catch(function (error) {
            finish();
            throw error;
        });
    }

    var OCR_SERVER_ENDPOINT = '/api/ocr/scan';
    var OCR_SERVER_TIMEOUT_MS = 15000;

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Tiers 1 & 2: the backend tries Gemini then OCR.Space and returns already
    // structured, parsed field data — no client-side parsing needed here.
    function recognizeIdFromCanvasViaServer(canvas, onStatus) {
        var setStatus = typeof onStatus === 'function' ? onStatus : function () {};
        setStatus('Reading ID (cloud)…');

        var ocrCanvas = prepareCanvasForOcr(canvas, OCR_SERVER_MAX_DIMENSION);
        var imageData = ocrCanvas.toDataURL('image/jpeg', OCR_SERVER_JPEG_QUALITY);

        var controller = ('AbortController' in window) ? new AbortController() : null;
        var timeoutId = controller ? setTimeout(function () { controller.abort(); }, OCR_SERVER_TIMEOUT_MS) : null;

        return fetch(OCR_SERVER_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ image: imageData }),
            credentials: 'same-origin',
            signal: controller ? controller.signal : undefined
        }).then(function (response) {
            if (timeoutId) clearTimeout(timeoutId);
            if (!response.ok) {
                throw new Error('OCR server responded with ' + response.status);
            }
            return response.json();
        }).then(function (json) {
            if (!json || !json.success || !json.data) {
                throw new Error((json && json.message) || 'OCR server returned no data.');
            }
            return { text: '', parsed: json.data };
        }).catch(function (error) {
            if (timeoutId) clearTimeout(timeoutId);
            throw error;
        });
    }

    function recognizeIdFromCanvas(canvas, onStatus) {
        var setStatus = typeof onStatus === 'function' ? onStatus : function () {};

        return recognizeIdFromCanvasViaServer(canvas, setStatus).catch(function () {
            setStatus('Cloud reader unavailable — using on-device text reader…');
            return recognizeIdFromCanvasLocal(canvas, setStatus);
        });
    }

    window.TvirsOcr = {
        preload: preloadOcrEngine,
        recognizeIdFromCanvas: recognizeIdFromCanvas,
        recognizeIdFromCanvasLocal: recognizeIdFromCanvasLocal,
        parseIdText: parseIdText
    };
})();
