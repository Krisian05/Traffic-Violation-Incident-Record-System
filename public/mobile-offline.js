(function () {
    'use strict';

    var DB_NAME = 'tvirs-mobile-offline';
    var STORE_NAME = 'queued_forms';
    var DB_VERSION = 1;
    var CHIP_ID = 'mob-sync-chip';
    var TOAST_ID = 'mob-sync-toast';
    var FORM_SELECTOR = 'form[data-offline-sync="true"]';
    var PAGE_CACHE_PREFIX = 'tvirs-mobile-pages-';
    var LAST_USER_KEY = 'tvirs-mobile-last-user';
    var MOTORIST_LINK_KEY = 'tvirs-offline-motorist-links';
    var SYNC_INTERVAL_MS = 30000;
    var OFFLINE_VIOLATION_CREATE_PATH = '/officer/offline/violations/create';
    var DUPLICATE_STATES = { pending: true, failed: true };
    var serviceWorkerVersion = cleanedString(document.body.dataset.officerSwVersion || '');
    var toastTimer = null;
    var syncing = false;
    var chip = document.getElementById(CHIP_ID);
    var toast = document.getElementById(TOAST_ID);
    var syncSheet = document.getElementById('mob-sync-sheet');
    var syncSheetBody = document.getElementById('mob-sync-sheet-body');
    var syncSheetTitle = document.getElementById('mob-sync-sheet-title');
    var syncSheetSubtitle = document.getElementById('mob-sync-sheet-subtitle');
    var currentUserId = String(document.body.dataset.authUserId || '');

    function openDb() {
        return new Promise(function (resolve, reject) {
            var request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = function () {
                var db = request.result;
                var store;

                if (db.objectStoreNames.contains(STORE_NAME)) {
                    store = request.transaction.objectStore(STORE_NAME);
                } else {
                    store = db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
                }

                if (!store.indexNames.contains('userId')) {
                    store.createIndex('userId', 'userId', { unique: false });
                }

                if (!store.indexNames.contains('state')) {
                    store.createIndex('state', 'state', { unique: false });
                }

                if (!store.indexNames.contains('createdAt')) {
                    store.createIndex('createdAt', 'createdAt', { unique: false });
                }
            };

            request.onsuccess = function () { resolve(request.result); };
            request.onerror = function () { reject(request.error); };
        });
    }

    function withStore(mode, handler) {
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var transaction = db.transaction(STORE_NAME, mode);
                var store = transaction.objectStore(STORE_NAME);
                var result = handler(store, transaction);

                transaction.oncomplete = function () {
                    db.close();
                    resolve(result);
                };

                transaction.onerror = function () {
                    db.close();
                    reject(transaction.error);
                };

                transaction.onabort = function () {
                    db.close();
                    reject(transaction.error);
                };
            });
        });
    }

    function addRecord(record) {
        return withStore('readwrite', function (store) {
            store.add(record);
        }).then(function () {
            notifyOfflineDataChanged();
        });
    }

    function putRecord(record) {
        return withStore('readwrite', function (store) {
            store.put(record);
        }).then(function () {
            notifyOfflineDataChanged();
        });
    }

    function deleteRecord(id) {
        return withStore('readwrite', function (store) {
            store.delete(id);
        }).then(function () {
            notifyOfflineDataChanged();
        });
    }

    function getAllRecords() {
        return withStore('readonly', function (store) {
            return new Promise(function (resolve, reject) {
                var request = store.getAll();
                request.onsuccess = function () { resolve(request.result || []); };
                request.onerror = function () { reject(request.error); };
            });
        });
    }

    function getRecordsForCurrentUser() {
        return getAllRecords().then(function (records) {
            return (records || [])
                .filter(function (record) { return String(record.userId || '') === currentUserId; })
                .sort(function (a, b) { return Number(a.id || 0) - Number(b.id || 0); });
        });
    }

    function dedupeExistingQueuedRecords() {
        return getRecordsForCurrentUser().then(function (records) {
            var ordered = (records || []).slice().sort(function (a, b) {
                var aPending = String(a.state || '') === 'pending' ? 0 : 1;
                var bPending = String(b.state || '') === 'pending' ? 0 : 1;

                if (aPending !== bPending) {
                    return aPending - bPending;
                }

                return Number(a.id || 0) - Number(b.id || 0);
            });

            var seen = {};
            var deletions = [];

            ordered.forEach(function (record) {
                if (!DUPLICATE_STATES[String(record.state || '')]) {
                    return;
                }

                var key = recordDuplicateLookupKey(record);

                if (seen[key]) {
                    deletions.push(deleteRecord(record.id));
                    return;
                }

                seen[key] = true;
            });

            if (!deletions.length) {
                return 0;
            }

            return Promise.all(deletions).then(function () {
                return deletions.length;
            });
        });
    }

    function migrateLegacyQueuedRecords() {
        return getRecordsForCurrentUser().then(function (records) {
            var updates = [];

            (records || []).forEach(function (record) {
                if (normalizeQueuedRecord(record)) {
                    updates.push(putRecord(record));
                }
            });

            if (!updates.length) {
                return 0;
            }

            return Promise.all(updates).then(function () {
                return updates.length;
            });
        });
    }

    function listOfflineMotorists() {
        return getRecordsForCurrentUser().then(function (records) {
            var queuedViolationCounts = {};
            var motorists = [];

            (records || []).forEach(function (record) {
                normalizeQueuedRecord(record);

                if (!DUPLICATE_STATES[String(record.state || '')]) {
                    return;
                }

                if (inferRecordType(record) === 'offline-violation-create' && record.parentOfflineMotoristKey) {
                    queuedViolationCounts[record.parentOfflineMotoristKey] = (queuedViolationCounts[record.parentOfflineMotoristKey] || 0) + 1;
                }
            });

            (records || []).forEach(function (record) {
                normalizeQueuedRecord(record);

                if (!DUPLICATE_STATES[String(record.state || '')] || inferRecordType(record) !== 'motorist-create') {
                    return;
                }

                motorists.push({
                    id: record.id,
                    state: record.state,
                    lastError: record.lastError || '',
                    offlineMotoristKey: cleanedString(record.offlineMotoristKey),
                    queuedViolations: queuedViolationCounts[record.offlineMotoristKey] || 0,
                    summary: record.summary || buildMotoristSummary(record.entries || [])
                });
            });

            return motorists.sort(function (a, b) {
                return Number(b.id || 0) - Number(a.id || 0);
            });
        });
    }

    function getOfflineMotoristByKey(offlineMotoristKey) {
        var targetKey = cleanedString(offlineMotoristKey);

        if (!targetKey) {
            return Promise.resolve(null);
        }

        return listOfflineMotorists().then(function (motorists) {
            for (var i = 0; i < motorists.length; i += 1) {
                if (cleanedString(motorists[i].offlineMotoristKey) === targetKey) {
                    return motorists[i];
                }
            }

            return null;
        });
    }

    function sameOfflineMotoristSummary(summaryA, summaryB) {
        if (!summaryA || !summaryB) {
            return false;
        }

        var duplicateIdentityA = motoristDuplicateIdentity(summaryA);
        var duplicateIdentityB = motoristDuplicateIdentity(summaryB);

        if (duplicateIdentityA && duplicateIdentityB) {
            return duplicateIdentityA === duplicateIdentityB;
        }

        return false;
    }

    function motoristDuplicateIdentity(summary) {
        if (!summary) {
            return '';
        }

        var license = cleanedString(summary.licenseNumber).toLowerCase();
        if (license) {
            return 'motorist-license|' + license;
        }

        var firstName = cleanedString(summary.firstName).toLowerCase();
        var lastName = cleanedString(summary.lastName).toLowerCase();

        if (!firstName || !lastName) {
            return '';
        }

        return 'motorist-name|' + firstName + '|' + lastName;
    }

    function recordDuplicateLookupKey(record) {
        if (!record) {
            return '';
        }

        normalizeQueuedRecord(record);

        if (inferRecordType(record) === 'motorist-create') {
            var motoristKey = motoristDuplicateIdentity(record.summary || buildMotoristSummary(record.entries || []));
            if (motoristKey) {
                return motoristKey;
            }
        }

        return [
            String(record.action || ''),
            String(record.method || ''),
            recordFingerprint(record)
        ].join('|');
    }

    function formDuplicateLookupKey(form, entries, metadata, fingerprint) {
        var resolvedEntries = entries || serializeFormData(new FormData(form));
        var resolvedMetadata = metadata || buildRecordMetadata(form, resolvedEntries);

        if (cleanedString(resolvedMetadata.recordType) === 'motorist-create') {
            var motoristKey = motoristDuplicateIdentity(resolvedMetadata.summary || buildMotoristSummary(resolvedEntries));
            if (motoristKey) {
                return motoristKey;
            }
        }

        return [
            String(form.action || ''),
            String(form.getAttribute('method') || 'POST').toUpperCase(),
            fingerprint || buildFingerprint(resolvedEntries)
        ].join('|');
    }

    function bindQueuedMotoristToForm(form, offlineMotoristKey, duplicateLookupKey) {
        if (!form) {
            return;
        }

        var normalizedOfflineKey = cleanedString(offlineMotoristKey);
        var normalizedDuplicateKey = cleanedString(duplicateLookupKey);

        if (normalizedOfflineKey) {
            form.dataset.offlineMotoristKey = normalizedOfflineKey;
        } else {
            delete form.dataset.offlineMotoristKey;
        }

        if (normalizedDuplicateKey) {
            form.dataset.offlineDuplicateKey = normalizedDuplicateKey;
        } else {
            delete form.dataset.offlineDuplicateKey;
        }
    }

    function clearQueuedMotoristBinding(form) {
        if (!form) {
            return false;
        }

        var hadBinding = !!(cleanedString(form.dataset.offlineMotoristKey) || cleanedString(form.dataset.offlineDuplicateKey));
        delete form.dataset.offlineMotoristKey;
        delete form.dataset.offlineDuplicateKey;
        return hadBinding;
    }

    function findOfflineMotoristForForm(form) {
        if (!form) {
            return Promise.resolve(null);
        }

        var boundOfflineKey = cleanedString(form.dataset.offlineMotoristKey);
        if (boundOfflineKey) {
            return getOfflineMotoristByKey(boundOfflineKey).then(function (motorist) {
                if (motorist) {
                    return motorist;
                }

                clearQueuedMotoristBinding(form);
                return null;
            });
        }

        var lookup = buildMotoristSummary(serializeFormData(new FormData(form)));

        if (!cleanedString(lookup.firstName) || !cleanedString(lookup.lastName)) {
            return Promise.resolve(null);
        }

        return listOfflineMotorists().then(function (motorists) {
            for (var i = 0; i < motorists.length; i += 1) {
                if (sameOfflineMotoristSummary(motorists[i].summary, lookup)) {
                    return motorists[i];
                }
            }

            return null;
        });
    }

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function pathOf(url) {
        try {
            return new URL(url, window.location.origin).pathname;
        } catch (error) {
            return String(url || '');
        }
    }

    function isSamePath(urlA, urlB) {
        return pathOf(urlA) === pathOf(urlB);
    }

    function isLoginUrl(url) {
        return pathOf(url) === '/login';
    }

    function notifyOfflineDataChanged(detail) {
        window.dispatchEvent(new CustomEvent('tvirs-offline-updated', {
            detail: detail || {}
        }));
    }

    function notifyOfflineRecordQueued(record) {
        window.dispatchEvent(new CustomEvent('tvirs-offline-record-queued', {
            detail: { record: record }
        }));
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function serializeFormData(formData) {
        var entries = [];
        formData.forEach(function (value, name) {
            if (value instanceof File) {
                if (!value.name && value.size === 0) {
                    return;
                }

                entries.push({
                    name: name,
                    kind: 'file',
                    file: value,
                    filename: value.name || 'upload',
                    mimeType: value.type || 'application/octet-stream',
                    lastModified: value.lastModified || Date.now()
                });
                return;
            }

            entries.push({
                name: name,
                kind: 'text',
                value: String(value == null ? '' : value)
            });
        });
        return entries;
    }

    function cleanedString(value) {
        return String(value == null ? '' : value).replace(/\s+/g, ' ').trim();
    }

    function getEntryValues(entries, name) {
        return (entries || []).filter(function (entry) {
            return entry.name === name && entry.kind === 'text';
        }).map(function (entry) {
            return cleanedString(entry.value);
        }).filter(Boolean);
    }

    function getLastEntryValue(entries, name) {
        var values = getEntryValues(entries, name);
        return values.length ? values[values.length - 1] : '';
    }

    function generateOfflineKey(prefix) {
        return [
            prefix || 'queued',
            Date.now().toString(36),
            Math.random().toString(36).slice(2, 8)
        ].join('-');
    }

    function buildMotoristDisplayName(firstName, middleName, lastName) {
        var first = cleanedString(firstName);
        var middle = cleanedString(middleName);
        var last = cleanedString(lastName);
        var lead = [last, first].filter(Boolean).join(', ');

        if (lead && middle) {
            return lead + ' ' + middle.charAt(0).toUpperCase() + '.';
        }

        return lead || [first, middle, last].filter(Boolean).join(' ') || 'Unnamed Motorist';
    }

    function buildMotoristSummary(entries) {
        var firstName = getLastEntryValue(entries, 'first_name');
        var middleName = getLastEntryValue(entries, 'middle_name');
        var lastName = getLastEntryValue(entries, 'last_name');
        var licenseNumber = getLastEntryValue(entries, 'license_number');
        var address = getLastEntryValue(entries, 'address') || getLastEntryValue(entries, 'permanent_address');
        var displayName = buildMotoristDisplayName(firstName, middleName, lastName);
        var initials = ((firstName.charAt(0) || '') + (lastName.charAt(0) || '')).toUpperCase() || 'OF';

        return {
            displayName: displayName,
            firstName: firstName,
            middleName: middleName,
            lastName: lastName,
            initials: initials,
            licenseNumber: licenseNumber,
            gender: getLastEntryValue(entries, 'gender'),
            contactNumber: getLastEntryValue(entries, 'contact_number'),
            address: address,
            searchText: cleanedString([
                firstName,
                middleName,
                lastName,
                licenseNumber,
                address
            ].join(' ')).toLowerCase()
        };
    }

    function buildViolationSummary(form, entries) {
        var violationTypeField = form ? form.querySelector('[name="violation_type_id"]') : null;
        var violationTypeName = '';

        if (violationTypeField && violationTypeField.selectedIndex >= 0) {
            violationTypeName = cleanedString(violationTypeField.options[violationTypeField.selectedIndex].textContent || '');
        }

        return {
            violationTypeId: getLastEntryValue(entries, 'violation_type_id'),
            violationTypeName: violationTypeName,
            dateOfViolation: getLastEntryValue(entries, 'date_of_violation'),
            ticketNumber: getLastEntryValue(entries, 'ticket_number'),
            location: getLastEntryValue(entries, 'location'),
            status: getLastEntryValue(entries, 'status')
        };
    }

    function inferRecordType(record) {
        var explicitType = cleanedString(record && record.recordType);
        if (explicitType) {
            return explicitType;
        }

        var actionPath = pathOf(record && record.action);
        var method = String(record && record.method || 'POST').toUpperCase();

        if (method === 'POST' && actionPath === '/officer/motorists') {
            return 'motorist-create';
        }

        if ((record && record.sourcePath) === OFFLINE_VIOLATION_CREATE_PATH || actionPath === OFFLINE_VIOLATION_CREATE_PATH) {
            return 'offline-violation-create';
        }

        return '';
    }

    function normalizeQueuedRecord(record) {
        if (!record) {
            return false;
        }

        var changed = false;
        var recordType = inferRecordType(record);

        if (recordType && record.recordType !== recordType) {
            record.recordType = recordType;
            changed = true;
        }

        if (recordType === 'motorist-create') {
            if (!cleanedString(record.offlineMotoristKey)) {
                record.offlineMotoristKey = generateOfflineKey('motorist');
                changed = true;
            }

            var motoristSummary = buildMotoristSummary(record.entries || []);
            if (JSON.stringify(record.summary || {}) !== JSON.stringify(motoristSummary)) {
                record.summary = motoristSummary;
                changed = true;
            }
        }

        if (recordType === 'offline-violation-create') {
            var parentKey = cleanedString(record.parentOfflineMotoristKey || getLastEntryValue(record.entries || [], 'offline_motorist_key'));
            if (parentKey && record.parentOfflineMotoristKey !== parentKey) {
                record.parentOfflineMotoristKey = parentKey;
                changed = true;
            }

            var violationSummary = record.summary || {
                violationTypeId: getLastEntryValue(record.entries || [], 'violation_type_id'),
                violationTypeName: '',
                dateOfViolation: getLastEntryValue(record.entries || [], 'date_of_violation'),
                ticketNumber: getLastEntryValue(record.entries || [], 'ticket_number'),
                location: getLastEntryValue(record.entries || [], 'location'),
                status: getLastEntryValue(record.entries || [], 'status')
            };

            if (JSON.stringify(record.summary || {}) !== JSON.stringify(violationSummary)) {
                record.summary = violationSummary;
                changed = true;
            }
        }

        return changed;
    }

    function getMotoristLinkMap() {
        try {
            return JSON.parse(window.localStorage.getItem(MOTORIST_LINK_KEY) || '{}') || {};
        } catch (error) {
            return {};
        }
    }

    function saveMotoristLinkMap(map) {
        try {
            window.localStorage.setItem(MOTORIST_LINK_KEY, JSON.stringify(map || {}));
        } catch (error) {
            return;
        }
    }

    function rememberSyncedMotorist(record, serverId) {
        var offlineKey = cleanedString(record && record.offlineMotoristKey);
        var normalizedServerId = cleanedString(serverId);

        if (!offlineKey || !normalizedServerId) {
            return;
        }

        var map = getMotoristLinkMap();
        map[offlineKey] = {
            serverId: normalizedServerId,
            syncedAt: new Date().toISOString(),
            displayName: record.summary && record.summary.displayName ? record.summary.displayName : record.label
        };
        saveMotoristLinkMap(map);
    }

    function getSyncedMotoristId(offlineKey) {
        var normalizedKey = cleanedString(offlineKey);
        if (!normalizedKey) {
            return '';
        }

        var map = getMotoristLinkMap();
        return cleanedString(map[normalizedKey] && map[normalizedKey].serverId);
    }

    function buildOfflineViolationHref(offlineMotoristKey) {
        return OFFLINE_VIOLATION_CREATE_PATH + '#motorist=' + encodeURIComponent(cleanedString(offlineMotoristKey));
    }

    function buildRecordMetadata(form, entries) {
        var recordType = cleanedString(form.dataset.offlineRecordType) || inferRecordType({
            method: form.getAttribute('method'),
            action: form.action,
            sourcePath: window.location.pathname
        });

        if (recordType === 'motorist-create') {
            return {
                recordType: recordType,
                offlineMotoristKey: cleanedString(form.dataset.offlineMotoristKey) || generateOfflineKey('motorist'),
                summary: buildMotoristSummary(entries)
            };
        }

        if (recordType === 'offline-violation-create') {
            return {
                recordType: recordType,
                parentOfflineMotoristKey: cleanedString(form.dataset.offlineParentKey || getLastEntryValue(entries, 'offline_motorist_key')),
                summary: buildViolationSummary(form, entries)
            };
        }

        return {
            recordType: recordType
        };
    }

    function entryFingerprint(entry) {
        if (entry.kind === 'file') {
            var size = typeof entry.size === 'number'
                ? entry.size
                : ((entry.file && typeof entry.file.size === 'number') ? entry.file.size : 0);

            return [
                entry.name,
                entry.kind,
                entry.filename || '',
                entry.mimeType || '',
                String(size),
                String(entry.lastModified || 0)
            ].join('::');
        }

        return [
            entry.name,
            entry.kind,
            entry.value == null ? '' : String(entry.value)
        ].join('::');
    }

    function buildFingerprint(entries) {
        return (entries || []).map(entryFingerprint).join('||');
    }

    function recordFingerprint(record) {
        return record && record.fingerprint ? record.fingerprint : buildFingerprint(record ? record.entries : []);
    }

    function rebuildFormData(record) {
        var formData = new FormData();

        (record.entries || []).forEach(function (entry) {
            if (entry.kind === 'file') {
                formData.append(entry.name, entry.file, entry.filename || 'upload');
                return;
            }

            formData.append(entry.name, entry.value == null ? '' : entry.value);
        });

        var csrfToken = getCsrfToken();
        if (csrfToken && formData.has('_token')) {
            formData.set('_token', csrfToken);
        }

        return formData;
    }

    function describeCount(count, noun) {
        return count + ' ' + noun + (count === 1 ? '' : 's');
    }

    function initialsFromName(name) {
        var parts = cleanedString(name).split(/\s+/).filter(Boolean);

        if (!parts.length) {
            return 'OF';
        }

        if (parts.length === 1) {
            return parts[0].slice(0, 2).toUpperCase();
        }

        return ((parts[0].charAt(0) || '') + (parts[parts.length - 1].charAt(0) || '')).toUpperCase() || 'OF';
    }

    function toastIcon(tone) {
        if (tone === 'success') return 'ph-fill ph-check-circle';
        if (tone === 'error') return 'ph-fill ph-warning-circle';
        if (tone === 'pending') return 'ph-fill ph-cloud-arrow-up';
        if (tone === 'syncing') return 'ph-fill ph-arrows-clockwise';
        return 'ph-fill ph-wifi-slash';
    }

    function showToast(message, tone) {
        if (!toast) return;

        clearTimeout(toastTimer);

        toast.className = 'mob-sync-toast show mob-sync-toast--' + (tone || 'pending');
        toast.innerHTML =
            '<div class="mob-sync-toast-inner">' +
                '<i class="' + toastIcon(tone || 'pending') + '"></i>' +
                '<div>' + escapeHtml(message) + '</div>' +
            '</div>';

        toastTimer = window.setTimeout(function () {
            toast.className = 'mob-sync-toast';
            toast.innerHTML = '';
        }, 4200);
    }

    function chipStateMeta(state, pendingCount, failedCount) {
        if (state === 'syncing') {
            return {
                className: 'mob-sync-chip show mob-sync-chip--syncing',
                icon: 'ph-fill ph-arrows-clockwise',
                text: 'Syncing queued records...'
            };
        }

        if (failedCount > 0) {
            return {
                className: 'mob-sync-chip show mob-sync-chip--error',
                icon: 'ph-fill ph-warning-octagon',
                text: describeCount(failedCount, 'record') + ' needs review'
            };
        }

        if (pendingCount > 0) {
            return {
                className: 'mob-sync-chip show mob-sync-chip--pending',
                icon: 'ph-fill ph-cloud-arrow-up',
                text: describeCount(pendingCount, 'queued record')
            };
        }

        return {
            className: 'mob-sync-chip show mob-sync-chip--offline',
            icon: 'ph-fill ph-wifi-slash',
            text: 'Offline mode active'
        };
    }

    function updateChipText(meta) {
        if (!chip) return;
        chip.className = meta.className;
        chip.innerHTML = '<i class="' + meta.icon + '"></i><span>' + escapeHtml(meta.text) + '</span>';
    }

    function queueBadgeMeta(state) {
        if (String(state || '') === 'failed') {
            return {
                className: 'mob-sync-sheet-badge mob-sync-sheet-badge--failed',
                text: 'Needs review'
            };
        }

        return {
            className: 'mob-sync-sheet-badge mob-sync-sheet-badge--pending',
            text: 'Pending'
        };
    }

    function buildQueuedMotoristLookup(records) {
        var lookup = {};

        (records || []).forEach(function (record) {
            normalizeQueuedRecord(record);

            if (inferRecordType(record) !== 'motorist-create') {
                return;
            }

            var key = cleanedString(record.offlineMotoristKey);
            if (!key) {
                return;
            }

            lookup[key] = {
                displayName: cleanedString(record.summary && record.summary.displayName) || cleanedString(record.label) || 'Unnamed Motorist',
                initials: cleanedString(record.summary && record.summary.initials) || initialsFromName(record.summary && record.summary.displayName),
                licenseNumber: cleanedString(record.summary && record.summary.licenseNumber)
            };
        });

        return lookup;
    }

    function queuedRecordPresentation(record, motoristLookup) {
        normalizeQueuedRecord(record);

        var type = inferRecordType(record);
        var badge = queueBadgeMeta(record.state);
        var title = cleanedString(record.label) || 'Queued record';
        var metaParts = [];
        var initials = 'OF';

        if (type === 'motorist-create') {
            title = cleanedString(record.summary && record.summary.displayName) || title || 'Unnamed Motorist';
            initials = cleanedString(record.summary && record.summary.initials) || initialsFromName(title);
            metaParts.push('Motorist record');

            if (cleanedString(record.summary && record.summary.licenseNumber)) {
                metaParts.push('License ' + cleanedString(record.summary.licenseNumber));
            }
        } else if (type === 'offline-violation-create') {
            var linkedMotorist = motoristLookup[cleanedString(record.parentOfflineMotoristKey)] || null;
            var linkedName = linkedMotorist ? cleanedString(linkedMotorist.displayName) : '';
            var violationType = cleanedString(record.summary && record.summary.violationTypeName);

            title = linkedName || title || 'Queued violation';
            initials = linkedMotorist
                ? (cleanedString(linkedMotorist.initials) || initialsFromName(linkedName))
                : initialsFromName(title);
            metaParts.push(violationType ? 'Violation: ' + violationType : 'Violation record');
        } else {
            initials = initialsFromName(title);
            metaParts.push('Officer mobile record');
        }

        return {
            badge: badge,
            title: title,
            meta: metaParts.join(' - '),
            initials: initials,
            lastError: cleanedString(record.lastError)
        };
    }

    function renderChipSummary(records) {
        if (!syncSheetBody || !syncSheetTitle || !syncSheetSubtitle) {
            return;
        }

        if (!(records || []).length) {
            syncSheetTitle.textContent = navigator.onLine ? 'No queued records' : 'Offline mode active';
            syncSheetSubtitle.textContent = navigator.onLine
                ? 'This device is clear right now.'
                : 'New officer mobile submissions will be saved here until internet returns.';
            syncSheetBody.innerHTML =
                '<div class="mob-sync-sheet-empty">' +
                    '<div class="mob-sync-sheet-empty-icon"><i class="ph-fill ph-cloud-check"></i></div>' +
                    '<div class="mob-sync-sheet-empty-title">' + escapeHtml(navigator.onLine ? 'Everything is synced' : 'Ready for offline saving') + '</div>' +
                    '<div class="mob-sync-sheet-empty-text">' +
                        escapeHtml(navigator.onLine
                            ? 'There are no queued mobile records on this device.'
                            : 'New records you save offline will appear here and publish automatically once the device reconnects.') +
                    '</div>' +
                '</div>';
            return;
        }

        syncSheetTitle.textContent = describeCount(records.length, 'queued record');
        syncSheetSubtitle.textContent = 'Saved on this device and synced automatically once internet returns.';

        var motoristLookup = buildQueuedMotoristLookup(records);
        syncSheetBody.innerHTML =
            '<div class="mob-sync-sheet-list">' +
            records.map(function (record) {
                var item = queuedRecordPresentation(record, motoristLookup);

                return '' +
                    '<div class="mob-sync-sheet-item">' +
                        '<div class="mob-sync-sheet-avatar">' + escapeHtml(item.initials) + '</div>' +
                        '<div class="mob-sync-sheet-copy">' +
                            '<div class="mob-sync-sheet-item-title">' + escapeHtml(item.title) + '</div>' +
                            '<div class="mob-sync-sheet-item-meta">' + escapeHtml(item.meta) + '</div>' +
                            (item.lastError
                                ? '<div class="mob-sync-sheet-item-error">' + escapeHtml(item.lastError) + '</div>'
                                : '') +
                        '</div>' +
                        '<div class="' + item.badge.className + '">' + escapeHtml(item.badge.text) + '</div>' +
                    '</div>';
            }).join('') +
            '</div>';
    }

    function setChipSummaryOpen(isOpen) {
        if (!syncSheet) {
            return;
        }

        syncSheet.hidden = !isOpen;
        syncSheet.classList.toggle('open', isOpen);
        document.body.classList.toggle('mob-sheet-open', isOpen);
    }

    function closeChipSummary() {
        setChipSummaryOpen(false);
    }

    function openChipSummary(records) {
        renderChipSummary(records || []);
        setChipSummaryOpen(true);
    }

    function updateOfflineStatus() {
        return getRecordsForCurrentUser().then(function (records) {
            var pendingCount = records.filter(function (record) { return record.state === 'pending'; }).length;
            var failedCount = records.filter(function (record) { return record.state === 'failed'; }).length;

            if (syncing || pendingCount > 0 || failedCount > 0 || !navigator.onLine) {
                updateChipText(chipStateMeta(syncing ? 'syncing' : '', pendingCount, failedCount));
                return;
            }

            if (chip) {
                chip.className = 'mob-sync-chip';
                chip.innerHTML = '';
            }
        }).catch(function () {
            if (!navigator.onLine) {
                updateChipText(chipStateMeta('', 0, 0));
            }
        });
    }

    function extractResponseMessage(html) {
        if (!html) return '';

        try {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var selectors = [
                '.mob-alert-danger div',
                '.alert-danger div',
                '.invalid-feedback',
                '.field-error',
                '.text-danger'
            ];

            for (var i = 0; i < selectors.length; i += 1) {
                var node = doc.querySelector(selectors[i]);
                if (node) {
                    var text = node.textContent.replace(/\s+/g, ' ').trim();
                    if (text) return text;
                }
            }
        } catch (error) {
            return '';
        }

        return '';
    }

    function queueFormSubmission(form) {
        var formData = new FormData(form);
        var entries = serializeFormData(formData);
        var metadata = buildRecordMetadata(form, entries);
        var fingerprint = buildFingerprint(entries);
        var duplicateLookupKey = formDuplicateLookupKey(form, entries, metadata, fingerprint);

        if (metadata.recordType === 'motorist-create') {
            bindQueuedMotoristToForm(form, metadata.offlineMotoristKey, duplicateLookupKey);
        }

        var record = {
            userId: currentUserId,
            state: 'pending',
            label: form.dataset.offlineLabel || document.title || 'Record',
            method: String(form.getAttribute('method') || 'POST').toUpperCase(),
            action: form.action,
            sourceUrl: window.location.href,
            sourcePath: window.location.pathname,
            entries: entries,
            fingerprint: fingerprint,
            recordType: metadata.recordType || '',
            offlineMotoristKey: metadata.offlineMotoristKey || '',
            parentOfflineMotoristKey: metadata.parentOfflineMotoristKey || '',
            summary: metadata.summary || {},
            createdAt: new Date().toISOString(),
            lastError: ''
        };

        return getRecordsForCurrentUser().then(function (records) {
            var duplicate = (records || []).find(function (existingRecord) {
                return !!DUPLICATE_STATES[String(existingRecord.state || '')]
                    && recordDuplicateLookupKey(existingRecord) === duplicateLookupKey;
            });

            if (duplicate) {
                if (record.recordType === 'motorist-create') {
                    bindQueuedMotoristToForm(form, duplicate.offlineMotoristKey, duplicateLookupKey);
                    showToast('This motorist is already queued offline on this device. You can record a linked violation now.', 'pending');
                    notifyOfflineRecordQueued(duplicate);
                    notifyOfflineDataChanged({ duplicate: true, record: duplicate });
                } else if (record.recordType === 'offline-violation-create') {
                    showToast('This violation is already queued for that offline motorist.', 'pending');
                } else {
                    showToast(record.label + ' is already queued offline.', 'pending');
                }
                return updateOfflineStatus();
            }

            return addRecord(record).then(function () {
                if (record.recordType === 'motorist-create') {
                    bindQueuedMotoristToForm(form, record.offlineMotoristKey, duplicateLookupKey);
                    showToast('Motorist saved offline. You can record a linked violation now on this page.', 'pending');
                } else if (record.recordType === 'offline-violation-create') {
                    showToast('Violation queued for this offline motorist. It will sync after the motorist record.', 'pending');
                } else {
                    showToast(record.label + ' saved offline. It will sync automatically when internet is back.', 'pending');
                }
                notifyOfflineRecordQueued(record);
                return updateOfflineStatus();
            });
        }).finally(function () {
            delete form.dataset.offlineQueueBusy;
        });
    }

    function queueOfflineFormWithGuard(form) {
        if (form.dataset.offlineQueueBusy === '1') {
            return Promise.resolve();
        }

        form.dataset.offlineQueueBusy = '1';

        return queueFormSubmission(form);
    }

    function setOfflineSubmitState(form, isQueued) {
        var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        buttons.forEach(function (button) {
            if (!button.dataset.offlineOriginalLabel) {
                button.dataset.offlineOriginalLabel = button.tagName === 'INPUT'
                    ? (button.value || '')
                    : button.innerHTML;
            }

            if (!isQueued) {
                button.disabled = false;
                if (button.tagName === 'INPUT') {
                    button.value = button.dataset.offlineOriginalLabel;
                } else {
                    button.innerHTML = button.dataset.offlineOriginalLabel;
                }
                return;
            }

            button.disabled = true;
            if (button.tagName === 'INPUT') {
                button.value = 'Queued Offline';
            } else {
                button.innerHTML = '<i class="ph ph-check-circle"></i> Queued Offline';
            }
        });
    }

    function formHasQueuedDuplicate(form) {
        var formData = new FormData(form);
        var entries = serializeFormData(formData);
        var metadata = buildRecordMetadata(form, entries);
        var fingerprint = buildFingerprint(entries);
        var duplicateLookupKey = formDuplicateLookupKey(form, entries, metadata, fingerprint);

        if (cleanedString(metadata.recordType) === 'motorist-create') {
            var boundDuplicateKey = cleanedString(form.dataset.offlineDuplicateKey);
            var boundOfflineKey = cleanedString(form.dataset.offlineMotoristKey);

            if (boundDuplicateKey && boundDuplicateKey !== duplicateLookupKey) {
                clearQueuedMotoristBinding(form);
            } else if (boundDuplicateKey && boundOfflineKey) {
                return Promise.resolve(true);
            }
        }

        return getRecordsForCurrentUser().then(function (records) {
            return (records || []).some(function (existingRecord) {
                return !!DUPLICATE_STATES[String(existingRecord.state || '')]
                    && recordDuplicateLookupKey(existingRecord) === duplicateLookupKey;
            });
        });
    }

    function formRequiresForcedQueue(form) {
        return cleanedString(form.dataset.offlineRecordType) === 'offline-violation-create';
    }

    function forcedQueueParentKey(form) {
        var linkedField = form.querySelector('[name="offline_motorist_key"]');
        return cleanedString(form.dataset.offlineParentKey || (linkedField ? linkedField.value : ''));
    }

    function setOfflineSubmitLocked(form, label) {
        var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        buttons.forEach(function (button) {
            if (!button.dataset.offlineOriginalLabel) {
                button.dataset.offlineOriginalLabel = button.tagName === 'INPUT'
                    ? (button.value || '')
                    : button.innerHTML;
            }

            button.disabled = true;
            if (button.tagName === 'INPUT') {
                button.value = label || 'Loading...';
            } else {
                button.innerHTML = '<i class="ph ph-hourglass"></i> ' + escapeHtml(label || 'Loading...');
            }
        });
    }

    function refreshQueuedFormStates() {
        document.querySelectorAll(FORM_SELECTOR).forEach(function (form) {
            if (formRequiresForcedQueue(form) && !forcedQueueParentKey(form)) {
                setOfflineSubmitLocked(form, 'Loading Motorist');
                return;
            }

            if (navigator.onLine && !formRequiresForcedQueue(form)) {
                setOfflineSubmitState(form, false);
                return;
            }

            formHasQueuedDuplicate(form).then(function (hasDuplicate) {
                setOfflineSubmitState(form, hasDuplicate);
            }).catch(function () {
                return null;
            });
        });
    }

    async function syncRecord(record) {
        normalizeQueuedRecord(record);

        var formData = rebuildFormData(record);
        var targetAction = record.action;
        var response;

        if (inferRecordType(record) === 'offline-violation-create') {
            var parentOfflineMotoristKey = cleanedString(record.parentOfflineMotoristKey || getLastEntryValue(record.entries || [], 'offline_motorist_key'));
            var syncedMotoristId = getSyncedMotoristId(parentOfflineMotoristKey);

            if (!syncedMotoristId) {
                var currentRecords = await getRecordsForCurrentUser();
                var parentRecord = (currentRecords || []).find(function (existingRecord) {
                    normalizeQueuedRecord(existingRecord);
                    return inferRecordType(existingRecord) === 'motorist-create'
                        && cleanedString(existingRecord.offlineMotoristKey) === parentOfflineMotoristKey;
                });

                if (parentRecord && String(parentRecord.state || '') === 'failed') {
                    return {
                        ok: false,
                        retryable: false,
                        message: 'Linked offline motorist needs review before this violation can sync.'
                    };
                }

                return {
                    ok: false,
                    retryable: true,
                    message: 'Waiting for the linked offline motorist to sync first.'
                };
            }

            targetAction = '/officer/motorists/' + encodeURIComponent(syncedMotoristId) + '/violations';
        }

        try {
            response = await fetch(targetAction, {
                method: record.method || 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html;q=0.9, */*;q=0.8'
                }
            });
        } catch (error) {
            return {
                ok: false,
                retryable: true,
                message: 'Connection is still unstable. Will retry automatically.'
            };
        }

        if (response.status === 401 || response.status === 403 || response.status === 419 || isLoginUrl(response.url)) {
            return {
                ok: false,
                retryable: true,
                message: 'Please log in again before queued records can sync.'
            };
        }

        if (response.status === 422) {
            var payloadMessage = '';

            try {
                var payload = await response.clone().json();
                if (payload && payload.message) {
                    payloadMessage = cleanedString(payload.message);
                }

                if (!payloadMessage && payload && payload.errors) {
                    var errorKeys = Object.keys(payload.errors);
                    if (errorKeys.length && payload.errors[errorKeys[0]] && payload.errors[errorKeys[0]][0]) {
                        payloadMessage = cleanedString(payload.errors[errorKeys[0]][0]);
                    }
                }
            } catch (error) {
                payloadMessage = '';
            }

            return {
                ok: false,
                retryable: false,
                message: payloadMessage || 'The queued record needs manual review before it can sync.'
            };
        }

        if (!response.ok) {
            return {
                ok: false,
                retryable: true,
                message: 'Server is unavailable right now. Will retry automatically.'
            };
        }

        if (isSamePath(response.url, record.sourceUrl) || isSamePath(response.url, record.action)) {
            var html = await response.text();
            return {
                ok: false,
                retryable: false,
                message: extractResponseMessage(html) || 'The queued record needs manual review before it can sync.'
            };
        }

        if (inferRecordType(record) === 'motorist-create') {
            var motoristMatch = pathOf(response.url).match(/^\/officer\/motorists\/(\d+)\/?$/);
            if (motoristMatch && motoristMatch[1]) {
                rememberSyncedMotorist(record, motoristMatch[1]);
            }
        }

        return { ok: true };
    }

    async function syncPendingRecords() {
        if (syncing || !navigator.onLine || !currentUserId) {
            return updateOfflineStatus();
        }

        syncing = true;
        await updateOfflineStatus();

        var syncedCount = 0;
        var failedCount = 0;
        var retryMessage = '';

        try {
            var records = await getRecordsForCurrentUser();
            var pendingRecords = records.filter(function (record) { return record.state === 'pending'; });

            for (var i = 0; i < pendingRecords.length; i += 1) {
                var record = pendingRecords[i];
                var result = await syncRecord(record);

                if (result.ok) {
                    await deleteRecord(record.id);
                    syncedCount += 1;
                    continue;
                }

                if (result.retryable) {
                    record.lastError = result.message;
                    await putRecord(record);
                    retryMessage = result.message;
                    break;
                }

                record.state = 'failed';
                record.lastError = result.message;
                await putRecord(record);
                failedCount += 1;
            }
        } finally {
            syncing = false;
            await updateOfflineStatus();
            refreshQueuedFormStates();
        }

        if (failedCount > 0) {
            showToast(describeCount(failedCount, 'queued record') + ' needs review before syncing.', 'error');
            return;
        }

        if (syncedCount > 0) {
            showToast(describeCount(syncedCount, 'queued record') + ' synced successfully.', 'success');
            return;
        }

        if (retryMessage) {
            showToast(retryMessage, 'offline');
        }
    }

    function attachOfflineHandlers() {
        document.querySelectorAll(FORM_SELECTOR).forEach(function (form) {
            function reevaluateQueuedState() {
                if (formRequiresForcedQueue(form) && !forcedQueueParentKey(form)) {
                    setOfflineSubmitLocked(form, 'Loading Motorist');
                    return;
                }

                if (!navigator.onLine || formRequiresForcedQueue(form)) {
                    formHasQueuedDuplicate(form).then(function (hasDuplicate) {
                        setOfflineSubmitState(form, hasDuplicate);
                    }).catch(function () {
                        return null;
                    });
                } else {
                    setOfflineSubmitState(form, false);
                }
            }

            form.querySelectorAll('input, textarea, select').forEach(function (field) {
                field.addEventListener('input', reevaluateQueuedState);
                field.addEventListener('change', reevaluateQueuedState);
            });

            form.addEventListener('submit', function (event) {
                if (formRequiresForcedQueue(form) && !forcedQueueParentKey(form)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    showToast('Open this page from a queued offline motorist before recording a violation.', 'error');
                    return;
                }

                if (navigator.onLine && !formRequiresForcedQueue(form)) {
                    setOfflineSubmitState(form, false);
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                queueOfflineFormWithGuard(form).then(function () {
                    return formHasQueuedDuplicate(form).then(function (hasDuplicate) {
                        setOfflineSubmitState(form, hasDuplicate);
                    });
                }).catch(function () {
                    showToast('Unable to save this record offline on this device.', 'error');
                });
            }, true);

            reevaluateQueuedState();
        });
    }

    function attachChipSummary() {
        if (!chip) return;

        chip.addEventListener('click', function () {
            getRecordsForCurrentUser().then(function (records) {
                openChipSummary(records);
            });
        });

        if (syncSheet) {
            syncSheet.querySelectorAll('[data-sync-sheet-close]').forEach(function (element) {
                element.addEventListener('click', closeChipSummary);
            });
        }

        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && syncSheet && syncSheet.classList.contains('open')) {
                closeChipSummary();
            }
        });

        window.addEventListener('tvirs-offline-updated', function () {
            if (!syncSheet || !syncSheet.classList.contains('open')) {
                return;
            }

            getRecordsForCurrentUser().then(function (records) {
                renderChipSummary(records);
            }).catch(function () {
                return null;
            });
        });
    }

    function clearDynamicPageCaches() {
        if (!('caches' in window)) return;

        caches.keys().then(function (keys) {
            return Promise.all(keys.filter(function (key) {
                return key.indexOf(PAGE_CACHE_PREFIX) === 0;
            }).map(function (key) {
                return caches.delete(key);
            }));
        }).catch(function () {
            return null;
        });
    }

    function attachLogoutCleanup() {
        var logoutForm = document.getElementById('logout-form');
        if (!logoutForm) return;

        logoutForm.addEventListener('submit', function () {
            clearDynamicPageCaches();
        });
    }

    function reconcileCachedPagesForUser() {
        try {
            var lastUserId = window.localStorage.getItem(LAST_USER_KEY);

            if (lastUserId && currentUserId && lastUserId !== currentUserId) {
                clearDynamicPageCaches();
            }

            if (currentUserId) {
                window.localStorage.setItem(LAST_USER_KEY, currentUserId);
            }
        } catch (error) {
            return;
        }
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;

        window.addEventListener('load', function () {
            var serviceWorkerUrl = '/officer-sw.js';

            if (serviceWorkerVersion) {
                serviceWorkerUrl += '?v=' + encodeURIComponent(serviceWorkerVersion);
            }

            navigator.serviceWorker.register(serviceWorkerUrl).then(function (registration) {
                if (registration && typeof registration.update === 'function') {
                    registration.update().catch(function () {
                        return null;
                    });
                }
            }).catch(function () {
                return null;
            });
        });
    }

    attachOfflineHandlers();
    attachChipSummary();
    attachLogoutCleanup();
    reconcileCachedPagesForUser();
    registerServiceWorker();
    window.TvirsOffline = {
        listOfflineMotorists: listOfflineMotorists,
        getOfflineMotoristByKey: getOfflineMotoristByKey,
        findOfflineMotoristForForm: findOfflineMotoristForForm,
        getSyncedMotoristId: getSyncedMotoristId,
        buildOfflineViolationHref: buildOfflineViolationHref
    };

    migrateLegacyQueuedRecords().finally(function () {
        dedupeExistingQueuedRecords().finally(function () {
            notifyOfflineDataChanged({ phase: 'ready' });
            updateOfflineStatus();
            refreshQueuedFormStates();

            if (navigator.onLine) {
                syncPendingRecords();
            }
        });
    });

    window.addEventListener('online', function () {
        refreshQueuedFormStates();
        showToast('Back online. Syncing queued records now.', 'syncing');
        syncPendingRecords();
    });

    window.addEventListener('offline', function () {
        refreshQueuedFormStates();
        updateOfflineStatus();
        showToast('You are offline. New records will be saved on this device.', 'offline');
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && navigator.onLine) {
            refreshQueuedFormStates();
            syncPendingRecords();
            return;
        }

        refreshQueuedFormStates();
        updateOfflineStatus();
    });

    window.setInterval(function () {
        if (navigator.onLine) {
            refreshQueuedFormStates();
            syncPendingRecords();
            return;
        }

        refreshQueuedFormStates();
        updateOfflineStatus();
    }, SYNC_INTERVAL_MS);
})();
