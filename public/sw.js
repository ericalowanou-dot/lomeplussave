// Service Worker Lome+ - PWA
// Portee racine ("/") : controle toutes les pages du site.
// Utilise Workbox via CDN (pattern officiel pour un SW non-bundle) pour les strategies
// de cache/routage/expiration/background-sync eprouvees, tout en gardant un fichier
// simple et lisible pour la logique specifique (precache, push, mise a jour).

importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.1.0/workbox-sw.js');

const STATIC_SHELL = [
    '/manifest.json',
    '/offline.html',
    '/assets/icons/pwa/icon-192.png',
    '/assets/icons/pwa/icon-512.png',
];

let CACHE_VERSION = 'dev';
const CACHE_PREFIX = 'lomeplus-';

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        let precacheUrls = [...STATIC_SHELL];

        try {
            const res = await fetch('/build-precache.json', { cache: 'no-store' });
            if (res.ok) {
                const data = await res.json();
                if (data.version) CACHE_VERSION = data.version;
                if (Array.isArray(data.urls)) precacheUrls = precacheUrls.concat(data.urls);
            }
        } catch (e) {
            // Hors-ligne des la premiere installation : on garde juste le shell statique.
        }

        const cache = await caches.open(CACHE_PREFIX + CACHE_VERSION);
        await Promise.allSettled(
            precacheUrls.map((url) => cache.add(url).catch(() => {}))
        );
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(
            keys
                .filter((key) => key.startsWith(CACHE_PREFIX) && key !== CACHE_PREFIX + CACHE_VERSION)
                .map((key) => caches.delete(key))
        );
        await self.clients.claim();
    })());
});

// --- Mise a jour controlee : on n'appelle jamais self.skipWaiting() automatiquement.
// Le front (resources/js/pwa.js) affiche un toast et envoie ce message apres accord utilisateur.
self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// --- Strategies de routage (Workbox) ---
const { registerRoute, setCatchHandler } = workbox.routing;
const { NetworkFirst, StaleWhileRevalidate, CacheFirst } = workbox.strategies;
const { ExpirationPlugin } = workbox.expiration;
const { Queue } = workbox.backgroundSync;

// Pages HTML : reseau d'abord, sinon derniere version connue de CETTE page, sinon offline.html
registerRoute(
    ({ request }) => request.mode === 'navigate',
    new NetworkFirst({
        cacheName: CACHE_PREFIX + 'pages',
        networkTimeoutSeconds: 8,
        plugins: [new ExpirationPlugin({ maxEntries: 60, maxAgeSeconds: 7 * 24 * 60 * 60 })],
    })
);

// Bundles Vite + CSS/JS statiques meme origine : stale-while-revalidate
registerRoute(
    ({ url, request }) =>
        url.origin === self.location.origin &&
        (request.destination === 'script' || request.destination === 'style') &&
        (url.pathname.startsWith('/build/') || url.pathname.startsWith('/css/') || url.pathname.startsWith('/js/')),
    new StaleWhileRevalidate({ cacheName: CACHE_PREFIX + 'assets' })
);

// Images (annonces, publicites, medias, icones) : cache-first avec expiration
registerRoute(
    ({ url, request }) =>
        url.origin === self.location.origin &&
        (request.destination === 'image' ||
            url.pathname.startsWith('/images/') ||
            url.pathname.startsWith('/media/') ||
            url.pathname.startsWith('/publicites/')),
    new CacheFirst({
        cacheName: CACHE_PREFIX + 'images',
        plugins: [new ExpirationPlugin({ maxEntries: 200, maxAgeSeconds: 30 * 24 * 60 * 60, purgeOnQuotaError: true })],
    })
);

// Polices et CDN externes (Google Fonts, Bootstrap, FontAwesome...) : cache-first
registerRoute(
    ({ url }) => url.origin !== self.location.origin,
    new CacheFirst({
        cacheName: CACHE_PREFIX + 'cdn',
        plugins: [new ExpirationPlugin({ maxEntries: 60, maxAgeSeconds: 60 * 24 * 60 * 60 })],
    })
);

setCatchHandler(async ({ event }) => {
    if (event.request.mode === 'navigate') {
        const cache = await caches.open(CACHE_PREFIX + CACHE_VERSION);
        return (await cache.match('/offline.html')) || Response.error();
    }
    return Response.error();
});

// --- Background Sync : actions ecrites hors-ligne, rejouees au retour du reseau ---
// Volontairement limite a des actions courtes et sans upload (pas la creation/edition d'annonce).
async function notifyClients(message) {
    const clientsList = await self.clients.matchAll({ type: 'window' });
    clientsList.forEach((client) => client.postMessage(message));
}

const offlineActionsQueue = new Queue('lomeplus-offline-actions', {
    maxRetentionTime: 24 * 60, // minutes (24h)
    onSync: async ({ queue }) => {
        let entry;
        while ((entry = await queue.shiftRequest())) {
            try {
                await fetch(entry.request.clone());
                await notifyClients({ type: 'lomeplus:sync-success' });
            } catch (error) {
                await queue.unshiftRequest(entry);
                throw error;
            }
        }
    },
});

registerRoute(
    ({ url, request }) =>
        request.method === 'POST' &&
        url.origin === self.location.origin &&
        (url.pathname === '/messages/send' ||
            /^\/articles\/\d+\/like$/.test(url.pathname) ||
            /^\/articles\/\d+\/comments$/.test(url.pathname)),
    async ({ request }) => {
        try {
            return await fetch(request.clone());
        } catch (error) {
            await offlineActionsQueue.pushRequest({ request: request.clone() });
            await notifyClients({ type: 'lomeplus:sync-queued' });
            return new Response(
                JSON.stringify({ queued: true, message: 'Hors-ligne : action mise en file, elle sera envoyee des le retour du reseau.' }),
                { status: 202, headers: { 'Content-Type': 'application/json' } }
            );
        }
    },
    'POST'
);

// --- Push notifications ---
self.addEventListener('push', (event) => {
    let payload = {};
    try {
        payload = event.data ? event.data.json() : {};
    } catch (e) {
        payload = { title: 'Lome+', body: event.data ? event.data.text() : '' };
    }

    const title = payload.title || 'Lome+';
    const options = {
        body: payload.body || '',
        icon: payload.icon || '/assets/icons/pwa/icon-192.png',
        badge: '/assets/icons/pwa/icon-96.png',
        data: { url: (payload.data && payload.data.url) || '/' },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil((async () => {
        const clientsList = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        for (const client of clientsList) {
            if (client.url === targetUrl && 'focus' in client) {
                return client.focus();
            }
        }
        return self.clients.openWindow(targetUrl);
    })());
});
