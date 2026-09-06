// Lome+ - logique PWA (independante d'app.js, incluse sur tous les layouts, y compris admin).
// Enregistrement du Service Worker, flux de mise a jour, install prompt (Android/desktop + iOS),
// bandeau online/offline, et helpers Web Push exposes sur window.LomePush.

const TOAST_STYLE_ID = 'lomeplus-pwa-toast-style';

function ensureToastStyles() {
    if (document.getElementById(TOAST_STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = TOAST_STYLE_ID;
    style.textContent = `
        .lomeplus-toast {
            position: fixed;
            left: 50%;
            bottom: 90px;
            transform: translateX(-50%);
            background: #1f2937;
            color: #fff;
            padding: 12px 18px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.85rem;
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: calc(100vw - 32px);
        }
        .lomeplus-toast button {
            background: #ff7b00;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        .lomeplus-toast .lomeplus-toast-close {
            background: transparent;
            color: #d1d5db;
            padding: 2px 6px;
        }
        .lomeplus-offline-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #b45309;
            color: #fff;
            text-align: center;
            font-size: 0.8rem;
            padding: 6px 12px;
            z-index: 10001;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .lomeplus-install-banner {
            position: fixed;
            left: 12px;
            right: 12px;
            bottom: 12px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.18);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            max-width: 420px;
            margin: 0 auto;
        }
        .lomeplus-install-banner img { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; }
        .lomeplus-install-banner .lomeplus-install-text { flex: 1; font-size: 0.82rem; color: #1f2937; }
        .lomeplus-install-banner .lomeplus-install-title { font-weight: 700; margin-bottom: 2px; }
        .lomeplus-install-banner button.install { background: #ff7b00; color: #fff; border: none; padding: 8px 14px; border-radius: 9999px; font-weight: 600; font-size: 0.8rem; cursor: pointer; white-space: nowrap; }
        .lomeplus-install-banner button.dismiss { background: transparent; border: none; color: #9ca3af; font-size: 1.1rem; cursor: pointer; padding: 4px; line-height: 1; }
    `;
    document.head.appendChild(style);
}

function showToast(message, { actionLabel, onAction, timeout = 8000 } = {}) {
    ensureToastStyles();
    const toast = document.createElement('div');
    toast.className = 'lomeplus-toast';

    const text = document.createElement('span');
    text.textContent = message;
    toast.appendChild(text);

    if (actionLabel && onAction) {
        const btn = document.createElement('button');
        btn.textContent = actionLabel;
        btn.addEventListener('click', () => {
            onAction();
            toast.remove();
        });
        toast.appendChild(btn);
    }

    const close = document.createElement('button');
    close.className = 'lomeplus-toast-close';
    close.textContent = '×';
    close.addEventListener('click', () => toast.remove());
    toast.appendChild(close);

    document.body.appendChild(toast);

    if (timeout) {
        setTimeout(() => toast.remove(), timeout);
    }
}

// --- Service Worker : enregistrement + flux de mise a jour controle ---
function initServiceWorker() {
    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).then((registration) => {
            function promptUpdate(worker) {
                showToast('Nouvelle version disponible.', {
                    actionLabel: 'Recharger',
                    timeout: 0,
                    onAction: () => worker.postMessage('SKIP_WAITING'),
                });
            }

            if (registration.waiting && navigator.serviceWorker.controller) {
                promptUpdate(registration.waiting);
            }

            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                if (!newWorker) return;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        promptUpdate(newWorker);
                    }
                });
            });
        }).catch(() => {});

        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (refreshing) return;
            refreshing = true;
            window.location.reload();
        });

        navigator.serviceWorker.addEventListener('message', (event) => {
            const data = event.data;
            if (!data || !data.type) return;
            if (data.type === 'lomeplus:sync-queued') {
                showToast('Hors connexion : votre action sera envoyée dès le retour du réseau.');
            } else if (data.type === 'lomeplus:sync-success') {
                showToast('Action envoyée avec succès.');
            }
        });
    });
}

// --- Bandeau online/offline ---
function initOnlineBanner() {
    let banner = null;

    function show() {
        if (banner) return;
        banner = document.createElement('div');
        banner.className = 'lomeplus-offline-banner';
        banner.textContent = 'Vous êtes hors connexion. Certaines fonctionnalités sont limitées.';
        document.body.prepend(banner);
    }

    function hide() {
        if (banner) {
            banner.remove();
            banner = null;
        }
    }

    window.addEventListener('online', hide);
    window.addEventListener('offline', show);
    if (!navigator.onLine) show();
}

// --- Installation Android / desktop (beforeinstallprompt) ---
const INSTALL_DISMISS_KEY = 'lomeplus_install_dismissed_at';
const INSTALL_DISMISS_DAYS = 14;

function wasInstallDismissedRecently() {
    try {
        const at = parseInt(localStorage.getItem(INSTALL_DISMISS_KEY) || '0', 10);
        if (!at) return false;
        return Date.now() - at < INSTALL_DISMISS_DAYS * 24 * 60 * 60 * 1000;
    } catch (e) {
        return false;
    }
}

function markInstallDismissed() {
    try {
        localStorage.setItem(INSTALL_DISMISS_KEY, String(Date.now()));
    } catch (e) {}
}

function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function buildInstallBanner({ onInstall }) {
    ensureToastStyles();
    const banner = document.createElement('div');
    banner.className = 'lomeplus-install-banner';
    banner.innerHTML = `
        <img src="/assets/icons/pwa/icon-96.png" alt="Lome+">
        <div class="lomeplus-install-text">
            <div class="lomeplus-install-title">Installer Lome+</div>
            <div>Accédez plus vite à vos annonces, même hors connexion.</div>
        </div>
    `;

    const installBtn = document.createElement('button');
    installBtn.className = 'install';
    installBtn.textContent = 'Installer';
    installBtn.addEventListener('click', () => {
        onInstall();
        banner.remove();
    });

    const dismissBtn = document.createElement('button');
    dismissBtn.className = 'dismiss';
    dismissBtn.textContent = '×';
    dismissBtn.addEventListener('click', () => {
        markInstallDismissed();
        banner.remove();
    });

    banner.appendChild(installBtn);
    banner.appendChild(dismissBtn);
    document.body.appendChild(banner);
    return banner;
}

function initInstallPrompt() {
    if (isStandalone() || wasInstallDismissedRecently()) return;

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        buildInstallBanner({
            onInstall: () => {
                event.prompt();
            },
        });
    });
}

// --- Instructions "Ajouter à l'écran d'accueil" pour iOS Safari (pas de beforeinstallprompt) ---
function isIosSafari() {
    const ua = window.navigator.userAgent;
    const isIos = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
    const isSafari = /Safari/.test(ua) && !/CriOS|FxiOS|EdgiOS/.test(ua);
    return isIos && isSafari;
}

function initIosInstallHint() {
    if (!isIosSafari() || isStandalone() || wasInstallDismissedRecently()) return;

    ensureToastStyles();
    const banner = document.createElement('div');
    banner.className = 'lomeplus-install-banner';
    banner.innerHTML = `
        <img src="/assets/icons/pwa/icon-96.png" alt="Lome+">
        <div class="lomeplus-install-text">
            <div class="lomeplus-install-title">Installer Lome+</div>
            <div>Appuyez sur Partager, puis "Sur l'écran d'accueil".</div>
        </div>
    `;
    const dismissBtn = document.createElement('button');
    dismissBtn.className = 'dismiss';
    dismissBtn.textContent = '×';
    dismissBtn.addEventListener('click', () => {
        markInstallDismissed();
        banner.remove();
    });
    banner.appendChild(dismissBtn);
    document.body.appendChild(banner);
}

// --- Web Push : helpers d'abonnement exposes pour la page profil ---
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function getVapidPublicKey() {
    const meta = document.querySelector('meta[name="webpush-public-key"]');
    return meta ? meta.getAttribute('content') : '';
}

async function pushSubscribe() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        throw new Error('Les notifications push ne sont pas supportées par ce navigateur.');
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        throw new Error('Permission refusée.');
    }

    const registration = await navigator.serviceWorker.ready;
    const publicKey = getVapidPublicKey();
    if (!publicKey) {
        throw new Error('Clé VAPID publique manquante.');
    }

    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
    });

    await fetch('/push/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            Accept: 'application/json',
        },
        body: JSON.stringify(subscription.toJSON()),
    });

    return subscription;
}

async function pushUnsubscribe() {
    if (!('serviceWorker' in navigator)) return;
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();
    if (!subscription) return;

    await fetch('/push/unsubscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            Accept: 'application/json',
        },
        body: JSON.stringify({ endpoint: subscription.endpoint }),
    });

    await subscription.unsubscribe();
}

async function pushGetStatus() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || typeof Notification === 'undefined') {
        return { supported: false, permission: 'unsupported', subscribed: false };
    }

    const permission = Notification.permission;
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();

    return { supported: true, permission, subscribed: !!subscription };
}

window.LomePush = {
    subscribe: pushSubscribe,
    unsubscribe: pushUnsubscribe,
    getStatus: pushGetStatus,
};

// --- Init ---
initServiceWorker();
initOnlineBanner();
initInstallPrompt();
initIosInstallHint();
