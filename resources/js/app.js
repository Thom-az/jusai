import 'bootstrap';
import { Tooltip, Toast } from 'bootstrap';

const PAGE_NAVIGATION_STORAGE_KEY = 'jusai.pending.navigation';
const PAGE_NAVIGATION_MIN_DURATION = 420;
const PAGE_NAVIGATION_MAX_AGE = 15000;

const readPendingNavigation = () => {
    try {
        const raw = sessionStorage.getItem(PAGE_NAVIGATION_STORAGE_KEY);

        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw);

        if (!parsed?.startedAt || (Date.now() - parsed.startedAt) > PAGE_NAVIGATION_MAX_AGE) {
            sessionStorage.removeItem(PAGE_NAVIGATION_STORAGE_KEY);
            return null;
        }

        return parsed;
    } catch (error) {
        sessionStorage.removeItem(PAGE_NAVIGATION_STORAGE_KEY);
        return null;
    }
};

const persistPendingNavigation = (payload = {}) => {
    try {
        sessionStorage.setItem(PAGE_NAVIGATION_STORAGE_KEY, JSON.stringify({
            startedAt: Date.now(),
            ...payload,
        }));
    } catch (error) {
        // Ignora indisponibilidade do sessionStorage.
    }
};

const clearPendingNavigation = () => {
    try {
        sessionStorage.removeItem(PAGE_NAVIGATION_STORAGE_KEY);
    } catch (error) {
        // Sem acao necessaria.
    }
};

(function initPageLoader() {
    const loader = document.createElement('div');
    loader.id = 'pageLoader';
    loader.innerHTML = '<span class="page-loader__bar" aria-hidden="true"></span>';
    document.body.prepend(loader);

    const root = document.documentElement;
    const contentMain = document.querySelector('.content-main');
    const incomingNavigation = root.getAttribute('data-page-loading') === 'pending'
        ? readPendingNavigation()
        : null;

    const showLoader = (shouldDimContent = true) => {
        loader.classList.add('is-loading');
        if (contentMain && shouldDimContent) contentMain.classList.add('is-navigating');
    };

    const finishIncomingLoad = () => {
        loader.classList.remove('is-loading');
        if (contentMain) contentMain.classList.remove('is-navigating');
        root.removeAttribute('data-page-loading');
        clearPendingNavigation();
    };

    const startOutgoingLoad = (payload = {}) => {
        persistPendingNavigation(payload);
        showLoader();
    };

    if (incomingNavigation) {
        showLoader(false);

        const settleIncomingNavigation = () => {
            const elapsed = Date.now() - incomingNavigation.startedAt;
            const remaining = Math.max(0, PAGE_NAVIGATION_MIN_DURATION - elapsed);

            window.setTimeout(() => {
                finishIncomingLoad();
            }, remaining);
        };

        if (document.readyState === 'complete') {
            settleIncomingNavigation();
        } else {
            window.addEventListener('load', settleIncomingNavigation, { once: true });
        }
    } else {
        clearPendingNavigation();
    }

    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        const bootstrapToggle = anchor.getAttribute('data-bs-toggle');
        const blocksNavigationLoader = ['collapse', 'dropdown', 'modal', 'offcanvas', 'pill', 'tab'].includes(bootstrapToggle ?? '');

        if (
            !href ||
            href.startsWith('#') ||
            href.startsWith('javascript') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            blocksNavigationLoader ||
            anchor.hasAttribute('data-bs-dismiss') ||
            anchor.hasAttribute('data-disabled-action') ||
            anchor.hasAttribute('data-no-page-loader') ||
            anchor.hasAttribute('download') ||
            anchor.target === '_blank' ||
            event.defaultPrevented ||
            event.button !== 0 ||
            event.ctrlKey || event.metaKey || event.shiftKey || event.altKey
        ) return;

        let url;

        try {
            url = new URL(anchor.href, window.location.href);
        } catch (error) {
            return;
        }

        if (url.origin !== window.location.origin) {
            return;
        }

        const isSameDocumentAnchor = url.pathname === window.location.pathname
            && url.search === window.location.search
            && url.hash;

        if (isSameDocumentAnchor) {
            return;
        }

        event.preventDefault();

        startOutgoingLoad({
            href: `${url.pathname}${url.search}${url.hash}`,
        });

        window.setTimeout(() => {
            window.location.assign(url.href);
        }, 40);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
            return;
        }

        if (form.target === '_blank' || form.hasAttribute('data-no-page-loader')) {
            return;
        }

        if ((form.method || 'get').toLowerCase() === 'get') {
            startOutgoingLoad({
                href: form.action || window.location.href,
                method: 'get',
            });
        }
    });

    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) {
            return;
        }

        loader.classList.remove('is-loading');
        if (contentMain) contentMain.classList.remove('is-navigating');
        root.removeAttribute('data-page-loading');
        clearPendingNavigation();
    });
}());

document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
    const sidebarToggleIcons = document.querySelectorAll('[data-sidebar-toggle-icon]');
    const sidebarStorageKey = 'jusai.sidebar.state';
    const navTooltipTargets = document.querySelectorAll('.shell-sidebar .sidebar-link[data-bs-toggle="tooltip"]');
    const toggleTooltipTarget = document.querySelector('.shell-sidebar [data-sidebar-toggle][data-bs-toggle="tooltip"]');

    const updateTooltips = (state) => {
        navTooltipTargets.forEach((el) => {
            if (state === 'collapsed') {
                Tooltip.getOrCreateInstance(el);
            } else {
                Tooltip.getInstance(el)?.dispose();
            }
        });

        if (!toggleTooltipTarget) return;
        if (state === 'collapsed') {
            Tooltip.getOrCreateInstance(toggleTooltipTarget);
        } else {
            Tooltip.getInstance(toggleTooltipTarget)?.dispose();
        }
    };

    const applySidebarState = (state) => {
        document.documentElement.setAttribute('data-sidebar-state', state);

        sidebarToggleIcons.forEach((icon) => {
            icon.classList.remove('bi-chevron-left', 'bi-chevron-right');
            icon.classList.add(state === 'collapsed' ? 'bi-chevron-right' : 'bi-chevron-left');
        });

        sidebarToggleButtons.forEach((btn) => {
            btn.setAttribute('aria-label', state === 'collapsed' ? 'Expandir menu' : 'Recolher menu');
        });

        updateTooltips(state);
    };

    const savedSidebarState = localStorage.getItem(sidebarStorageKey) ?? 'expanded';
    applySidebarState(savedSidebarState);

    sidebarToggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const nextState = document.documentElement.getAttribute('data-sidebar-state') === 'collapsed' ? 'expanded' : 'collapsed';
            applySidebarState(nextState);
            localStorage.setItem(sidebarStorageKey, nextState);
        });
    });

    const themeToggle = document.getElementById('themeToggle');
    const themeToggleIcon = document.getElementById('themeToggleIcon');
    const themeStorageKey = 'jusai.theme';

    const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        if (themeToggleIcon) {
            themeToggleIcon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        }
        if (themeToggle) {
            themeToggle.setAttribute('aria-label', theme === 'dark' ? 'Mudar para tema claro' : 'Mudar para tema escuro');
        }
    };

    applyTheme(localStorage.getItem(themeStorageKey) || 'light');

    themeToggle?.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        localStorage.setItem(themeStorageKey, next);
    });

    document.querySelectorAll('[data-disabled-action]').forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();

            const toastElement = document.getElementById('appToast');
            const toastBody = document.getElementById('appToastBody');

            if (!toastElement || !toastBody) {
                return;
            }

            toastBody.textContent = element.getAttribute('data-disabled-action') ?? 'Este recurso sera entregue na proxima etapa.';

            const toast = Toast.getOrCreateInstance(toastElement);
            toast.show();
        });
    });
});
