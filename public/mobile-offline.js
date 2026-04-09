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
    var SYNC_INTERVAL_MS = 30000;
    var toastTimer = null;
    var syncing = false;
    var chip = document.getElementById(CHIP_ID);
    var toast = document.getElementById(TOAST_ID);
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
        });
    }

    function putRecord(record) {
        return withStore('readwrite', function (store) {
            store.put(record);
        });
    }

    function deleteRecord(id) {
        return withStore('readwrite', function (store) {
            store.delete(id);
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
        var record = {
            userId: currentUserId,
            state: 'pending',
            label: form.dataset.offlineLabel || document.title || 'Record',
            method: String(form.getAttribute('method') || 'POST').toUpperCase(),
            action: form.action,
            sourceUrl: window.location.href,
            sourcePath: window.location.pathname,
            entries: serializeFormData(formData),
            createdAt: new Date().toISOString(),
            lastError: ''
        };

        return addRecord(record).then(function () {
            showToast(record.label + ' saved offline. It will sync automatically when internet is back.', 'pending');
            return updateOfflineStatus();
        });
    }

    async function syncRecord(record) {
        var formData = rebuildFormData(record);
        var response;

        try {
            response = await fetch(record.action, {
                method: record.method || 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
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

        if (!response.ok) {
            return {
                ok: false,
                retryable: true,
                message: 'Server is unavailable right now. Will retry automatically.'
            };
        }

        if (isSamePath(response.url, record.sourceUrl)) {
            var html = await response.text();
            return {
                ok: false,
                retryable: false,
                message: extractResponseMessage(html) || 'The queued record needs manual review before it can sync.'
            };
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
            form.addEventListener('submit', function (event) {
                if (navigator.onLine) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                queueFormSubmission(form).catch(function () {
                    showToast('Unable to save this record offline on this device.', 'error');
                });
            }, true);
        });
    }

    function chipSummary(records) {
        if (!records.length) {
            if (navigator.onLine) {
                return 'You are online and there are no queued mobile records.';
            }

            return 'You are offline. New officer mobile submissions will be saved on this device and synced automatically later.';
        }

        return records.map(function (record, index) {
            var line = (index + 1) + '. ' + record.label + ' - ' + record.state;
            if (record.lastError) {
                line += ' - ' + record.lastError;
            }
            return line;
        }).join('\n');
    }

    function attachChipSummary() {
        if (!chip) return;

        chip.addEventListener('click', function () {
            getRecordsForCurrentUser().then(function (records) {
                window.alert(chipSummary(records));
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
            navigator.serviceWorker.register('/officer-sw.js').catch(function () {
                return null;
            });
        });
    }

    attachOfflineHandlers();
    attachChipSummary();
    attachLogoutCleanup();
    reconcileCachedPagesForUser();
    registerServiceWorker();
    updateOfflineStatus();

    if (navigator.onLine) {
        syncPendingRecords();
    }

    window.addEventListener('online', function () {
        showToast('Back online. Syncing queued records now.', 'syncing');
        syncPendingRecords();
    });

    window.addEventListener('offline', function () {
        updateOfflineStatus();
        showToast('You are offline. New records will be saved on this device.', 'offline');
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && navigator.onLine) {
            syncPendingRecords();
            return;
        }

        updateOfflineStatus();
    });

    window.setInterval(function () {
        if (navigator.onLine) {
            syncPendingRecords();
            return;
        }

        updateOfflineStatus();
    }, SYNC_INTERVAL_MS);
})();
