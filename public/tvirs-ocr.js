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
                langPath: OCR_LANG_PATH
            });
        }).catch(function (error) {
            workerPromise = null;
            throw error;
        });

        return workerPromise;
    }

    // Preload the (small) engine script in the background as soon as the scanner
    // opens, so it is likely already available by the time a scan is attempted.
    // The heavy WASM core / language data only load once recognize() actually runs.
    function preloadOcrEngine() {
        loadOcrEngine().catch(function () {
            // Silently ignore — the explicit recognize() call will surface any error.
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

    function normalizeDate(value) {
        var s = String(value || '').trim();
        if (!s) return '';

        var iso = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
        if (iso) {
            return iso[1] + '-' + iso[2].padStart(2, '0') + '-' + iso[3].padStart(2, '0');
        }

        var mdy = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
        if (mdy) {
            return mdy[3] + '-' + mdy[1].padStart(2, '0') + '-' + mdy[2].padStart(2, '0');
        }

        var textDate = s.match(/^([A-Za-z]{3,9})\.?\s+(\d{1,2}),?\s+(\d{4})$/);
        if (textDate) {
            var monNum = MONTHS[textDate[1].slice(0, 3).toLowerCase()];
            if (monNum) {
                return textDate[3] + '-' + String(monNum).padStart(2, '0') + '-' + textDate[2].padStart(2, '0');
            }
        }

        return '';
    }

    function findAfterLabel(lines, labelPattern) {
        var re = new RegExp('(?:' + labelPattern + ')\\s*[:\\-]?\\s*(.+)', 'i');
        var labelOnlyRe = new RegExp('^(?:' + labelPattern + ')\\s*[:\\-]?\\s*$', 'i');

        for (var i = 0; i < lines.length; i++) {
            var match = lines[i].match(re);
            if (match && match[1] && match[1].trim()) {
                return match[1].trim();
            }
            if (labelOnlyRe.test(lines[i]) && lines[i + 1]) {
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
                return /,/.test(l) && /^[A-Z\s,.'\-]+$/.test(l) && l.length > 5 && l.length < 60;
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
            if (normalizedDob) data.date_of_birth = normalizedDob;
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

        return data;
    }

    function recognizeIdFromCanvas(canvas, onStatus) {
        var setStatus = typeof onStatus === 'function' ? onStatus : function () {};

        setStatus('Loading text reader… (first scan may take a moment)');

        return getOcrWorker().then(function (worker) {
            setStatus('Reading text from ID…');
            return worker.recognize(canvas);
        }).then(function (result) {
            var text = (result && result.data && result.data.text) || '';
            return { text: text, parsed: parseIdText(text) };
        });
    }

    window.TvirsOcr = {
        preload: preloadOcrEngine,
        recognizeIdFromCanvas: recognizeIdFromCanvas,
        parseIdText: parseIdText
    };
})();
