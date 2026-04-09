var STATIC_CACHE = 'tvirs-mobile-static-v4';
var PAGE_CACHE = 'tvirs-mobile-pages-v4';
var EXTERNAL_CACHE = 'tvirs-mobile-external-v4';
var OFFLINE_FALLBACK = '/offline-mobile.html';
var STATIC_URLS = [
    '/manifest.json',
    '/favicon.ico',
    '/images/Balamban.png',
    '/images/PNP.png',
    OFFLINE_FALLBACK
];
var PAGE_URLS = [
    '/officer/motorists',
    '/officer/motorists/create',
    '/officer/offline/violations/create'
];
var EXTERNAL_URLS = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css',
    'https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css',
    'https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css'
];

function cacheExternalUrl(cache, url) {
    return fetch(new Request(url, { mode: 'no-cors' }))
        .then(function (response) {
            if (response && (response.ok || response.type === 'opaque')) {
                return cache.put(url, response.clone());
            }
            return null;
        })
        .catch(function () {
            return null;
        });
}

function cacheInternalUrl(cache, url) {
    return fetch(url, { credentials: 'same-origin' })
        .then(function (response) {
            if (response && response.ok) {
                return cache.put(url, response.clone());
            }
            return null;
        })
        .catch(function () {
            return null;
        });
}

self.addEventListener('install', function (event) {
    event.waitUntil((async function () {
        var staticCache = await caches.open(STATIC_CACHE);
        await Promise.allSettled(STATIC_URLS.map(function (url) {
            return cacheInternalUrl(staticCache, url);
        }));

        var pageCache = await caches.open(PAGE_CACHE);
        await Promise.allSettled(PAGE_URLS.map(function (url) {
            return cacheInternalUrl(pageCache, url);
        }));

        var externalCache = await caches.open(EXTERNAL_CACHE);
        await Promise.allSettled(EXTERNAL_URLS.map(function (url) {
            return cacheExternalUrl(externalCache, url);
        }));

        await self.skipWaiting();
    })());
});

self.addEventListener('activate', function (event) {
    event.waitUntil((async function () {
        var cacheNames = await caches.keys();
        await Promise.all(cacheNames.filter(function (name) {
            return [STATIC_CACHE, PAGE_CACHE, EXTERNAL_CACHE].indexOf(name) === -1;
        }).map(function (name) {
            return caches.delete(name);
        }));

        await self.clients.claim();
    })());
});

function isOfficerNavigation(request, url) {
    return request.mode === 'navigate' && url.origin === self.location.origin && url.pathname.indexOf('/officer') === 0;
}

function isStaticAsset(url) {
    if (url.origin !== self.location.origin) return false;
    return /^\/(manifest\.json|favicon\.ico|offline-mobile\.html)/.test(url.pathname)
        || url.pathname.indexOf('/images/') === 0;
}

function isExternalCacheable(url) {
    return url.origin === 'https://cdn.jsdelivr.net'
        || url.origin === 'https://unpkg.com'
        || url.origin === 'https://psgc.gitlab.io';
}

async function staleWhileRevalidate(request, cacheName) {
    var cache = await caches.open(cacheName);
    var cached = await cache.match(request);

    if (cached) {
        fetch(request).then(function (response) {
            if (response && (response.ok || response.type === 'opaque')) {
                cache.put(request, response.clone());
            }
        }).catch(function () {
            return null;
        });

        return cached;
    }

    try {
        var response = await fetch(request);
        if (response && (response.ok || response.type === 'opaque')) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        return Response.error();
    }
}

async function networkFirstPage(request) {
    var pageCache = await caches.open(PAGE_CACHE);
    var staticCache = await caches.open(STATIC_CACHE);
    var pagePath = new URL(request.url).pathname;

    try {
        var response = await fetch(request);
        var finalUrl = new URL(response.url);

        if (response.ok && finalUrl.origin === self.location.origin && finalUrl.pathname.indexOf('/officer') === 0) {
            pageCache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        var cached = await pageCache.match(request);
        if (cached) return cached;

        cached = await pageCache.match(pagePath);
        if (cached) return cached;

        cached = await staticCache.match(request);
        if (cached) return cached;

        cached = await staticCache.match(pagePath);
        if (cached) return cached;

        return caches.match(OFFLINE_FALLBACK);
    }
}

self.addEventListener('fetch', function (event) {
    if (event.request.method !== 'GET') return;

    var url = new URL(event.request.url);

    if (isOfficerNavigation(event.request, url)) {
        event.respondWith(networkFirstPage(event.request));
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(staleWhileRevalidate(event.request, STATIC_CACHE));
        return;
    }

    if (isExternalCacheable(url)) {
        event.respondWith(staleWhileRevalidate(event.request, EXTERNAL_CACHE));
    }
});
