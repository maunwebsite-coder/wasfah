import './bootstrap';

const hasDOM = typeof document !== 'undefined';
const hasWindow = typeof window !== 'undefined';

const readCookie = (name) => {
    if (!hasDOM) {
        return null;
    }

    const cookies = document.cookie ? document.cookie.split('; ') : [];

    for (let i = 0; i < cookies.length; i += 1) {
        const [key, ...rest] = cookies[i].split('=');

        if (key === decodeURIComponent(name)) {
            return decodeURIComponent(rest.join('='));
        }
    }

    return null;
};

const persistUserTimezone = () => {
    if (!hasDOM) {
        return;
    }

    let timezone = null;

    try {
        timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    } catch (error) {
        timezone = null;
    }

    if (!timezone) {
        return;
    }

    const cookieName = 'user_timezone';
    const current = readCookie(cookieName);

    if (current === timezone) {
        return;
    }

    const encoded = encodeURIComponent(timezone);
    const maxAge = 60 * 60 * 24 * 180; // 180 days
    const secure = window.location.protocol === 'https:' ? ';Secure' : '';

    document.cookie = `${cookieName}=${encoded};path=/;max-age=${maxAge};SameSite=Lax${secure}`;
};

const idle = (callback, timeout = 800) => {
    if (!hasWindow) {
        return;
    }

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(callback, { timeout });
    } else {
        window.setTimeout(callback, timeout);
    }
};

const lazyModules = [
    {
        name: 'header',
        priority: 'high',
        condition: () =>
            document.getElementById('user-menu-container') ||
            document.getElementById('mobileSearchBtn') ||
            document.querySelector('[data-header]'),
        loader: () => import('./header.js'),
    },
    {
        name: 'mobile-menu',
        priority: 'high',
        condition: () => document.getElementById('mobileMenuBtn'),
        loader: () => import('./mobile-menu.js'),
    },
    {
        name: 'mobile-tab-bar',
        priority: 'high',
        condition: () => document.querySelector('[data-mobile-tab-bar]'),
        loader: () => import('./mobile-tab-bar.js'),
    },
    {
        name: 'search',
        priority: 'high',
        condition: () =>
            document.getElementById('search-input') ||
            document.getElementById('mobile-search-input'),
        loader: () => import('./search.js'),
    },
    {
        name: 'home-recipes',
        condition: () =>
            document.getElementById('recipeCards') ||
            document.getElementById('latest-recipes-list'),
        loader: () => import('./script.js'),
    },
    {
        name: 'save-recipe',
        priority: 'high',
        maxAttempts: 3,
        retryDelay: 500,
        condition: () =>
            document.querySelector('.save-btn, .save-recipe-btn, #save-recipe-page-btn') ||
            document.getElementById('recipeCards') ||
            document.querySelector('[data-save-button]'),
        loader: () => import('./save-recipe.js'),
    },
    {
        name: 'recipe-page',
        condition: () => document.getElementById('recipe-title'),
        loader: () => import('./recipe.js'),
    },
    {
        name: 'recipe-save-button',
        condition: () => document.getElementById('save-recipe-page-btn'),
        loader: () => import('./recipe-save-button.js'),
    },
    {
        name: 'rating',
        condition: () => document.querySelector('.star-rating'),
        loader: () => import('./rating.js'),
    },
    {
        name: 'notifications',
        condition: () =>
            document.querySelector('[data-notification-badge]') ||
            document.getElementById('notifications-panel'),
        loader: () => import('./notification-manager.js'),
    },
    {
        name: 'confirmation-modal',
        condition: () => document.getElementById('confirmationModal'),
        loader: () => import('./confirmation-modal.js'),
    },
    {
        name: 'made-recipe',
        condition: () => document.getElementById('made-recipe-btn'),
        loader: () => import('./made-recipe.js'),
    },
    {
        name: 'share-recipe',
        condition: () =>
            document.querySelector('[data-share-recipe]') ||
            document.getElementById('share-modal') ||
            document.querySelector('[id^="share-recipe-btn"]') ||
            document.getElementById('print-recipe-btn') ||
            document.getElementById('share-whatsapp-btn'),
        loader: () => import('./share-recipe.js'),
    },
    {
        name: 'workshops',
        condition: () => document.getElementById('workshops-container'),
        loader: () => import('./workshops.js'),
    },
    {
        name: 'local-time',
        priority: 'high',
        condition: () => document.querySelector('[data-local-time]'),
        loader: () => import('./local-time.js'),
    },
    {
        name: 'whatsapp-booking',
        priority: 'high',
        condition: () => document.querySelector('.js-whatsapp-booking'),
        loader: () => import('./whatsapp-booking.js'),
    },
    {
        name: 'content-localization',
        maxAttempts: 3,
        retryDelay: 400,
        condition: () => hasWindow && Boolean(window.__CONTENT_TRANSLATIONS),
        loader: () => import('./content-localization.js'),
    },
];

const loadedModules = new Set();

const tryLoadModule = (entry) => {
    if (loadedModules.has(entry.name)) {
        return;
    }

    let conditionMet = false;
    try {
        conditionMet = entry.condition();
    } catch (error) {
        console.error(`Error while checking condition for ${entry.name}`, error);
    }

    if (!conditionMet) {
        const maxAttempts = entry.maxAttempts ?? 1;
        entry.attempts = entry.attempts ?? 0;

        if (entry.attempts < maxAttempts - 1 && hasWindow) {
            entry.attempts += 1;
            window.setTimeout(
                () => tryLoadModule(entry),
                entry.retryDelay ?? 600,
            );
        }
        return;
    }

    const importModule = () =>
        entry
            .loader()
            .then(() => loadedModules.add(entry.name))
            .catch((error) => {
                console.error(`Failed to load module "${entry.name}"`, error);
                loadedModules.delete(entry.name);
            });

    if (entry.priority === 'high') {
        importModule();
    } else {
        idle(importModule);
    }
};

const bootstrapLazyModules = () => {
    if (!hasDOM) {
        return;
    }

    lazyModules.forEach((entry) => tryLoadModule(entry));
};

const unregisterServiceWorkers = () => {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    navigator.serviceWorker
        .getRegistrations()
        .then((registrations) => {
            registrations.forEach((registration) => {
                registration.unregister().catch(() => {});
            });
        })
        .catch(() => {});
};

if (hasDOM) {
    persistUserTimezone();
}

if (hasDOM) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrapLazyModules, {
            once: true,
        });
    } else {
        bootstrapLazyModules();
    }
}

if (hasWindow) {
    window.__bootstrapLazyModules = bootstrapLazyModules;
}

if (hasWindow) {
    idle(unregisterServiceWorkers, 500);
}
