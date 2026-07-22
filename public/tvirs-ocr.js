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

    function isPlausibleBirthDate(isoDate) {
        var parts = isoDate.split('-');
        var asDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        var now = new Date();
        var ageMs = now - asDate;
        var ageYears = ageMs / (365.25 * 24 * 60 * 60 * 1000);
        return ageYears >= 0 && ageYears <= 120;
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

        return data;
    }

    // Trims very large captures down to a size that recognizes noticeably faster
    // without losing enough detail to hurt accuracy — ID card text stays legible
    // well below the ~1920px long edge the camera can capture at.
    var OCR_MAX_DIMENSION = 1600;

    function prepareCanvasForOcr(sourceCanvas) {
        var longestEdge = Math.max(sourceCanvas.width, sourceCanvas.height);
        if (longestEdge <= OCR_MAX_DIMENSION) {
            return sourceCanvas;
        }

        var scale = OCR_MAX_DIMENSION / longestEdge;
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

    function recognizeIdFromCanvas(canvas, onStatus) {
        var setStatus = typeof onStatus === 'function' ? onStatus : function () {};

        setStatus('Loading text reader… (first scan may take a moment)');

        return getOcrWorker().then(function (worker) {
            setStatus('Reading text from ID…');
            var ocrCanvas = prepareCanvasForOcr(canvas);
            // Only text output is used — skipping blocks/hocr/tsv generation
            // recognizes measurably faster.
            return worker.recognize(ocrCanvas, {}, { text: true, blocks: false, hocr: false, tsv: false });
        }).then(function (result) {
            var text = (result && result.data && result.data.text) || '';
            if (text.trim().length < MIN_USABLE_TEXT_LENGTH) {
                return { text: text, parsed: { raw: text } };
            }
            return { text: text, parsed: parseIdText(text) };
        });
    }

    window.TvirsOcr = {
        preload: preloadOcrEngine,
        recognizeIdFromCanvas: recognizeIdFromCanvas,
        parseIdText: parseIdText
    };
})();
