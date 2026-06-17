import * as bootstrap from 'bootstrap';

// ─── Global toast — escuta app:toast de qualquer página ──────────────────────
;(function () {
    const typeMap = {
        success: { bg: 'bg-success-subtle', text: 'text-success-emphasis', icon: 'bi-check-circle-fill' },
        warning: { bg: 'bg-warning-subtle', text: 'text-warning-emphasis', icon: 'bi-exclamation-triangle-fill' },
        danger:  { bg: 'bg-danger-subtle',  text: 'text-danger-emphasis',  icon: 'bi-x-circle-fill' },
    };

    window.addEventListener('app:toast', (e) => {
        const { message, type = 'success' } = e.detail;
        const c = typeMap[type] || typeMap.success;
        const toastEl = document.getElementById('globalToast');
        if (!toastEl) return;

        document.getElementById('globalToastMessage').textContent = message;
        document.getElementById('globalToastIcon').className = `bi flex-shrink-0 fs-6 ${c.icon}`;
        document.getElementById('globalToastInner').className =
            `d-flex align-items-center gap-2 p-3 rounded ${c.bg} ${c.text}`;

        Toast.getOrCreateInstance(toastEl, { delay: 4500 }).show();
    });
}());

const sk = {
    text:    (w = '60%', mb = 2) => `<div class="skeleton skeleton-text mb-${mb}" style="width:${w}"></div>`,
    heading: (w = '40%')         => `<div class="skeleton skeleton-heading mb-2" style="width:${w}"></div>`,
    circle:  (s = '2.5rem')      => `<div class="skeleton skeleton-circle flex-shrink-0" style="width:${s};height:${s}"></div>`,
    btn:     (w = '8rem')        => `<div class="skeleton skeleton-btn rounded-pill" style="width:${w}"></div>`,
    badge:   (w = '5rem')        => `<div class="skeleton skeleton-badge" style="width:${w}"></div>`,
    block:   (h = '3rem', w = '100%', r = '.5rem') => `<div class="skeleton" style="width:${w};height:${h};border-radius:${r}"></div>`,
    input:   () => `<div class="mb-3"><div class="skeleton skeleton-text mb-1" style="width:30%"></div><div class="skeleton" style="height:2.5rem;border-radius:.5rem"></div></div>`,
    metricCard: () => `<div class="metric-card"><div class="d-flex align-items-start justify-content-between gap-3"><div class="flex-grow-1"><div class="skeleton skeleton-text mb-2" style="width:52%"></div><div class="skeleton mb-2" style="width:45%;height:1.85rem;border-radius:.5rem"></div><div class="skeleton skeleton-text" style="width:68%"></div></div><div class="skeleton skeleton-circle flex-shrink-0" style="width:3rem;height:3rem"></div></div></div>`,
    listRow: (w = '60%') => `<div class="list-item d-flex align-items-center gap-3 mb-2"><div class="skeleton skeleton-circle flex-shrink-0" style="width:2.25rem;height:2.25rem"></div><div class="flex-grow-1"><div class="skeleton skeleton-text mb-1" style="width:${w}"></div><div class="skeleton skeleton-text" style="width:40%"></div></div><div class="skeleton skeleton-badge flex-shrink-0" style="width:5rem"></div><div class="skeleton skeleton-text flex-shrink-0" style="width:3.5rem;margin-bottom:0"></div></div>`,
};

function getSkeletonForUrl(href) {
    let p;
    try { p = new URL(href, window.location.href).pathname.replace(/\/$/, '') || '/'; } catch { p = href; }

    const wrap = (inner) => `<div class="container-fluid px-0">${inner}</div>`;

    const pageHeader = (btnW = '9rem', sub = true) => `
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>${sk.heading('12rem')}${sub ? sk.text('18rem') : ''}</div>
            ${btnW ? `<div class="skeleton skeleton-btn rounded-pill" style="width:${btnW}"></div>` : ''}
        </div>`;

    const formSurface = (n = 4) =>
        `<div class="surface-card p-4">${Array.from({length: n}, () => sk.input()).join('')}</div>`;

    const filterSurface = (n = 4) => {
        const col = n <= 3 ? 'col-sm-6 col-lg-4' : 'col-sm-6 col-lg-3';
        return `
        <div class="surface-card p-3 mb-4">
            <div class="row g-3">
                ${Array.from({length: n}, () => `<div class="${col}">${sk.input()}</div>`).join('')}
            </div>
        </div>`;
    };

    const listRows = (ws = [62, 71, 55, 78, 65, 58]) => ws.map(w => sk.listRow(`${w}%`)).join('');

    // /dashboard
    if (p === '/dashboard' || p === '') {
        return wrap(`
            ${pageHeader()}
            <div class="row g-3 mb-4">
                ${[0,1,2,3].map(() => `<div class="col-sm-6 col-xxl-3">${sk.metricCard()}</div>`).join('')}
            </div>
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="surface-card p-4">
                        ${sk.heading('10rem')}
                        ${listRows([62, 71, 55, 78, 65])}
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="surface-card p-4">
                        ${sk.heading('8rem')}
                        ${[0,1,2,3].map(() => `<div class="d-flex align-items-center gap-3 mb-3">${sk.circle('2.25rem')}<div class="flex-grow-1">${sk.text('70%', 0)}</div></div>`).join('')}
                    </div>
                </div>
            </div>`);
    }

    // /casos/create — must be before the wildcard /casos/{id}
    if (p === '/casos/create') {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            ${formSurface(5)}`);
    }

    // /casos/{id}/edit
    if (/^\/casos\/[^/]+\/edit$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            ${formSurface(5)}`);
    }

    // /casos/{id} (show)
    if (/^\/casos\/[^/]+$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">${sk.btn('6rem')}<div>${sk.heading('16rem')}${sk.text('12rem')}</div></div>
                <div class="d-flex gap-2">${sk.btn('7rem')}${sk.btn('5rem')}</div>
            </div>
            <div class="surface-card p-0 mb-4">
                <div class="d-flex gap-3 p-3 border-bottom">${[0,1,2].map(() => sk.badge('6rem')).join('')}</div>
                <div class="p-4">
                    ${[0,1,2,3].map(() => `<div class="surface-card p-3 mb-3"><div class="d-flex align-items-center gap-3">${sk.circle('2.5rem')}<div class="flex-grow-1">${sk.text('55%',1)}${sk.text('35%')}</div>${sk.btn('5rem')}</div></div>`).join('')}
                </div>
            </div>`);
    }

    // /casos (list)
    if (p === '/casos') {
        return wrap(`
            ${pageHeader('9rem')}
            ${filterSurface(4)}
            <div class="surface-card p-4">${listRows()}</div>`);
    }

    // /documentos/create — before wildcard
    if (p === '/documentos/create') {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            ${formSurface(4)}`);
    }

    // /documentos/{id}/edit
    if (/^\/documentos\/[^/]+\/edit$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            ${formSurface(4)}`);
    }

    // /documentos/{id} (show) — 2-column AI summary + info sidebar
    if (/^\/documentos\/[^/]+$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">${sk.btn('6rem')}<div>${sk.heading('16rem')}${sk.text('12rem')}</div></div>
                ${sk.btn('9rem')}
            </div>
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="surface-card p-4">
                        ${sk.heading('10rem')}
                        ${[0,1,2,3,4].map(() => sk.text('100%')).join('')}
                        <div class="mt-3">${[0,1,2].map(() => sk.text('80%')).join('')}</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="surface-card p-4">
                        ${sk.heading('8rem')}
                        ${[0,1,2,3].map(() => `<div class="d-flex justify-content-between mb-2">${sk.text('40%',0)}${sk.text('35%',0)}</div>`).join('')}
                    </div>
                </div>
            </div>`);
    }

    // /documentos (list)
    if (p === '/documentos') {
        return wrap(`
            ${pageHeader('10rem')}
            ${filterSurface(3)}
            <div class="surface-card p-4">${listRows()}</div>`);
    }

    // /chat (index)
    if (p === '/chat') {
        return wrap(`
            ${pageHeader('0')}
            <div class="row g-4">
                <div class="col-lg-4"><div class="surface-card p-4">${sk.heading('8rem')}${[0,1,2,3].map(() => sk.block('2.5rem', '100%', '.75rem') + '<div class="mb-2"></div>').join('')}</div></div>
                <div class="col-lg-8"><div class="surface-card p-0"><div class="px-4 py-3 border-bottom">${sk.text('8rem', 0)}</div><div class="p-4">${listRows([70, 65, 72, 58, 68])}</div></div></div>
            </div>`);
    }

    // /casos/{id}/chat
    if (/^\/casos\/[^/]+\/chat$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            <div class="row g-4">
                <div class="col-lg-8"><div class="surface-card p-4">${sk.block('22rem', '100%', '.5rem')}</div></div>
                <div class="col-lg-4">
                    <div class="surface-card p-4 mb-3">${sk.heading('10rem')}${[0,1,2,3].map(() => `<div class="d-flex justify-content-between mb-2">${sk.text('40%',0)}${sk.text('35%',0)}</div>`).join('')}</div>
                    <div class="surface-card p-4">${sk.heading('8rem')}${[0,1,2,3,4].map(() => sk.text('80%')).join('')}</div>
                </div>
            </div>`);
    }

    // /revisor/{id} (show) — 2-column result + details
    if (/^\/revisor\/[^/]+$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">${sk.btn('6rem')}${sk.heading('14rem')}</div>
                ${sk.badge('6rem')}
            </div>
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="surface-card p-4">
                        ${sk.heading('10rem')}
                        ${[0,1,2,3,4,5].map(() => sk.text('100%')).join('')}
                        <div class="mt-4">${[0,1,2].map(() => sk.text('80%')).join('')}</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="surface-card p-4">
                        ${sk.heading('8rem')}
                        ${[0,1,2,3].map(() => `<div class="d-flex justify-content-between mb-2">${sk.text('40%',0)}${sk.text('35%',0)}</div>`).join('')}
                    </div>
                </div>
            </div>`);
    }

    // /revisor (list) — form left + list right
    if (p === '/revisor') {
        return wrap(`
            ${pageHeader('10rem')}
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="surface-card p-4">
                        ${sk.heading('8rem')}
                        ${[0,1,2].map(() => sk.input()).join('')}
                        ${sk.btn('7rem')}
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="surface-card p-4">
                        ${sk.heading('10rem')}
                        ${listRows([62, 55, 71, 68, 58])}
                    </div>
                </div>
            </div>`);
    }

    // /minutas/create — antes do wildcard
    if (p === '/minutas/create') {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            <div class="row g-4">
                <div class="col-lg-8">${formSurface(4)}</div>
                <div class="col-lg-4"><div class="surface-card p-4">${sk.heading('10rem')}${[0,1,2,3].map(() => sk.text('80%')).join('')}</div></div>
            </div>`);
    }

    // /minutas/{id}/edit
    if (/^\/minutas\/[^/]+\/edit$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            <div class="row g-4">
                <div class="col-lg-8">${formSurface(2)}${sk.block('14rem')}</div>
                <div class="col-lg-4">${formSurface(2)}</div>
            </div>`);
    }

    // /minutas/{id} (show)
    if (/^\/minutas\/[^/]+$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">${sk.btn('6rem')}<div>${sk.heading('16rem')}${sk.text('12rem')}</div></div>
                ${sk.badge('6rem')}
            </div>
            <div class="row g-4">
                <div class="col-lg-8"><div class="surface-card p-4">${sk.heading('10rem')}${[0,1,2,3,4,5,6].map(() => sk.text('100%')).join('')}</div></div>
                <div class="col-lg-4"><div class="surface-card p-4">${sk.heading('8rem')}${[0,1,2,3].map(() => `<div class="d-flex justify-content-between mb-2">${sk.text('40%',0)}${sk.text('35%',0)}</div>`).join('')}</div></div>
            </div>`);
    }

    // /minutas (list)
    if (p === '/minutas') {
        return wrap(`
            ${pageHeader('9rem')}
            <div class="surface-card p-0"><div class="p-4">${listRows()}</div></div>`);
    }

    // /chamados — header + badge strip + command bar + list
    if (p === '/chamados') {
        return wrap(`
            ${pageHeader('9rem')}
            <div class="d-flex gap-2 mb-4 flex-wrap">
                ${[0,1,2,3,4,5].map(() => sk.badge('7rem')).join('')}
            </div>
            <div class="surface-card p-3 mb-3">${sk.block('2.5rem', '100%', '.5rem')}</div>
            <div class="surface-card p-4">${listRows()}</div>`);
    }

    // /configuracoes or /profile
    if (p === '/configuracoes' || p === '/profile') {
        return wrap(`
            <div class="mb-4">${sk.heading('12rem')}${sk.text('18rem')}</div>
            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="surface-card p-4 text-center">
                        <div class="d-flex justify-content-center mb-3">${sk.circle('5rem')}</div>
                        ${sk.heading('10rem')}
                        <div class="d-flex justify-content-center">${sk.text('8rem')}</div>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="surface-card p-4">
                        ${sk.heading('10rem')}
                        ${[0,1,2,3].map(() => sk.input()).join('')}
                        ${sk.btn('8rem')}
                    </div>
                </div>
            </div>`);
    }

    // /admin (dashboard) — 4 metric cards + 2-column lists
    if (p === '/admin') {
        return wrap(`
            ${pageHeader('9rem')}
            <div class="row g-3 mb-4">
                ${[0,1,2,3].map(() => `<div class="col-sm-6 col-xxl-3">${sk.metricCard()}</div>`).join('')}
            </div>
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="surface-card p-4">
                        ${sk.heading('10rem')}
                        ${listRows([62, 71, 55, 78, 65])}
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="surface-card p-4">
                        ${sk.heading('10rem')}
                        ${listRows([55, 68, 72, 60, 58])}
                    </div>
                </div>
            </div>`);
    }

    // /admin/organizations/create — before wildcard
    if (p === '/admin/organizations/create') {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            ${formSurface(5)}`);
    }

    // /admin/organizations/{id}/edit
    if (/^\/admin\/organizations\/[^/]+\/edit$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center gap-3 mb-4">${sk.btn('6rem')}${sk.heading('14rem')}</div>
            ${formSurface(5)}`);
    }

    // /admin/organizations/{id}
    if (/^\/admin\/organizations\/[^/]+$/.test(p)) {
        return wrap(`
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">${sk.btn('6rem')}${sk.heading('14rem')}</div>
                ${sk.btn('8rem')}
            </div>
            <div class="surface-card p-4">${listRows()}</div>`);
    }

    // /admin/* generic (financeiro, chamados, leads, organizations list)
    if (p.startsWith('/admin/')) {
        return wrap(`
            ${pageHeader('9rem')}
            ${filterSurface(3)}
            <div class="surface-card p-4">${listRows()}</div>`);
    }

    // /configuracoes/* — apenas o conteúdo (sidebar é preservado pelo click handler)
    if (p.startsWith('/configuracoes')) {
        return `
            <div class="settings-section-header placeholder-glow">
                ${sk.text('9rem', 1)}${sk.text('15rem')}
            </div>
            <div class="settings-skeleton-card placeholder-glow mb-3">
                ${sk.input()}${sk.input()}${sk.input()}${sk.btn('8rem')}
            </div>
            <div class="settings-skeleton-card placeholder-glow">
                ${sk.input()}${sk.input()}${sk.btn('8rem')}
            </div>`;
    }

    // fallback
    return wrap(`
        ${pageHeader()}
        <div class="row g-3 mb-4">
            ${[0,1,2,3].map(() => `<div class="col-sm-6 col-xxl-3">${sk.metricCard()}</div>`).join('')}
        </div>
        <div class="surface-card p-4">${listRows()}</div>`);
}

const PAGE_NAVIGATION_STORAGE_KEY = 'jusai.pending.navigation';
const PAGE_NAVIGATION_MIN_DURATION = 250;
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
    const incomingNavigation = root.getAttribute('data-page-loading') === 'pending'
        ? readPendingNavigation()
        : null;

    // Sempre consulta o DOM ao executar para suportar navegação SPA (Livewire).
    const showLoader = (shouldDimContent = true) => {
        loader.classList.add('is-loading');
        if (shouldDimContent) {
            const contentMain = document.querySelector('.content-main');
            if (contentMain) contentMain.classList.add('is-navigating');
        }
    };

    const finishLoad = () => {
        loader.classList.remove('is-loading');
        const contentMain = document.querySelector('.content-main');
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
                finishLoad();
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

    // Capture-phase: roda ANTES do Livewire e do Bootstrap para links wire:navigate.
    // Garante skeleton + progress bar independente do Livewire estar inicializado.
    document.addEventListener('click', (event) => {
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        const anchor = event.target.closest('a[href]');
        if (!anchor) return;

        const isWireNav = anchor.hasAttribute('wire:navigate') || anchor.hasAttribute('wire:navigate.hover');
        if (!isWireNav) return;
        if (anchor.getAttribute('href')?.startsWith('#')) return;

        showLoader(false);

        const cm = document.querySelector('.content-main');
        if (cm) {
            // Para configurações, preserva o sidebar e só anima o .settings-content
            const settingsContent = cm.querySelector('.settings-content');
            const target = settingsContent ?? cm;
            target.style.transition = 'opacity 0.06s ease';
            target.style.opacity = '0';
            setTimeout(() => {
                target.innerHTML = getSkeletonForUrl(anchor.href);
                target.style.transition = '';
                target.style.opacity = '1';
            }, 60);
        }
    }, { capture: true });

    // Bubble-phase: links internos sem wire:navigate (full-page reload).
    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        const bootstrapToggle = anchor.getAttribute('data-bs-toggle');
        const blocksNavigationLoader = ['collapse', 'dropdown', 'modal', 'offcanvas', 'pill', 'tab'].includes(bootstrapToggle ?? '');

        if (anchor.hasAttribute('wire:navigate') || anchor.hasAttribute('wire:navigate.hover')) {
            return;
        }

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

        const contentMainNow = document.querySelector('.content-main');
        if (contentMainNow) {
            contentMainNow.style.transition = 'opacity 0.06s ease';
            contentMainNow.style.opacity = '0';
            window.setTimeout(() => {
                contentMainNow.innerHTML = getSkeletonForUrl(url.href);
                contentMainNow.style.transition = '';
                contentMainNow.style.opacity = '1';
            }, 60);
        }

        window.setTimeout(() => {
            window.location.assign(url.href);
        }, 120);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
            return;
        }

        if (form.target === '_blank' || form.hasAttribute('data-no-page-loader')) {
            return;
        }

        const method = (form.method || 'get').toLowerCase();

        if (method === 'get') {
            startOutgoingLoad({
                href: form.action || window.location.href,
                method: 'get',
            });
        } else {
            showLoader();
            const btn = form.querySelector('[type="submit"]:not([data-no-loading])');
            if (btn) {
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${btn.textContent.trim()}`;
                window.addEventListener('pageshow', () => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }, { once: true });
            }
        }
    });

    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) {
            return;
        }

        loader.classList.remove('is-loading');
        const contentMain = document.querySelector('.content-main');
        if (contentMain) contentMain.classList.remove('is-navigating');
        root.removeAttribute('data-page-loading');
        clearPendingNavigation();
    });

    // Integração com navegação SPA do Livewire (wire:navigate / wire:navigate.hover).
    // O skeleton já foi injetado pelo capture listener no click — aqui só garante o loader.
    document.addEventListener('livewire:navigating', () => {
        showLoader(false);
    });

    document.addEventListener('livewire:navigated', () => {
        finishLoad();
        initShellPage();
        applyTheme(localStorage.getItem(themeStorageKey) || 'light');
        if (document.getElementById('notifToggle')) {
            initNotifications();
        }

        // Fade suave para o conteúdo real após o Livewire trocar o DOM.
        const contentMain = document.querySelector('.content-main');
        if (!contentMain) return;

        contentMain.style.opacity = '0';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                contentMain.style.transition = 'opacity 0.22s ease';
                contentMain.style.opacity = '1';
                // Limpa o inline style após a transição terminar.
                contentMain.addEventListener('transitionend', () => {
                    contentMain.style.transition = '';
                    contentMain.style.opacity = '';
                }, { once: true });
            });
        });
    });
}());

// Theme helpers — module-scoped so livewire:navigated can re-apply after DOM morph
const themeStorageKey = 'jusai.theme';

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    const icon = document.getElementById('themeToggleIcon');
    if (icon) icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
    const btn = document.getElementById('themeToggle');
    if (btn) btn.setAttribute('aria-label', theme === 'dark' ? 'Mudar para tema claro' : 'Mudar para tema escuro');
}

// Sidebar + theme: persistent layout elements — bind once only
function initShellPersistent() {
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

    applyTheme(localStorage.getItem(themeStorageKey) || 'light');

    // Use event delegation so the listener survives Livewire DOM morphing
    document.addEventListener('click', (e) => {
        if (e.target.closest('#themeToggle')) {
            const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem(themeStorageKey, next);
        }
    });

    // table-responsive clips Bootstrap dropdowns (overflow-x:auto coerces overflow-y to auto).
    // On show: make it overflow:visible so the menu renders outside; restore on hide.
    document.addEventListener('show.bs.dropdown', (e) => {
        const tr = e.target.closest('.table-responsive');
        if (tr) tr.style.overflow = 'visible';
    }, { capture: true });

    document.addEventListener('hidden.bs.dropdown', (e) => {
        const tr = e.target.closest('.table-responsive');
        if (tr) tr.style.overflow = '';
    });
}

// Page-specific elements that are re-rendered on Livewire navigation
function initShellPage() {

    document.querySelectorAll('[data-disabled-action]').forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            const message = element.getAttribute('data-disabled-action') || 'Este recurso será entregue na próxima etapa.';
            window.dispatchEvent(new CustomEvent('app:toast', { detail: { message, type: 'warning' } }));
        });
    });
}

// Global confirm-delete interceptor — intercepts forms with data-confirm-delete on submit button
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-confirm-delete]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    const form = btn.closest('form');
    if (!form) return;
    const msg = btn.dataset.confirmDelete || 'Esta ação é irreversível e não poderá ser desfeita.';
    const titleText = btn.dataset.confirmTitle || 'Confirmar exclusão';
    const titleEl = document.getElementById('confirmDeleteTitle');
    const msgEl = document.getElementById('confirmDeleteMessage');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (titleEl) titleEl.textContent = titleText;
    if (msgEl) msgEl.textContent = msg;
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirmDelete'));
    modal.show();
    if (confirmBtn._deleteHandler) {
        confirmBtn.removeEventListener('click', confirmBtn._deleteHandler);
    }
    confirmBtn._deleteHandler = () => { modal.hide(); form.submit(); };
    confirmBtn.addEventListener('click', confirmBtn._deleteHandler, { once: true });
}, true);

// ─── Notification centre ──────────────────────────────────────────────────────
const NOTIF_POLL_INTERVAL = 30000;
const NOTIF_UNREAD_URL    = '/notificacoes/unread';
const NOTIF_READ_URL      = (id) => `/notificacoes/${id}/read`;
const NOTIF_READ_ALL_URL  = '/notificacoes/read-all';
let _notifTimer = null;

function renderNotifications(data) {
    const badge   = document.getElementById('notifBadge');
    const list    = document.getElementById('notifList');
    const empty   = document.getElementById('notifEmpty');
    const markAll = document.getElementById('notifMarkAll');

    if (!badge || !list) return;

    const count = data.count ?? 0;
    badge.textContent = count > 9 ? '9+' : String(count);
    badge.style.display = count > 0 ? '' : 'none';

    const items = data.notifications ?? [];

    if (items.length === 0) {
        if (empty) empty.style.display = '';
        list.querySelectorAll('.notif-item').forEach(el => el.remove());
        return;
    }

    if (empty) empty.style.display = 'none';
    list.querySelectorAll('.notif-item').forEach(el => el.remove());

    const iconMap = {
        document_analysis_complete: { icon: 'bi-file-earmark-check-fill', color: 'text-success' },
        document_analysis_error:    { icon: 'bi-file-earmark-x-fill',     color: 'text-danger'  },
    };

    items.forEach(n => {
        const { icon = 'bi-bell-fill', color = 'text-primary' } = iconMap[n.type] ?? {};
        const item = document.createElement('div');
        item.className = 'notif-item d-flex align-items-start gap-3 px-3 py-2 border-bottom';
        item.style.cssText = 'cursor:pointer;transition:background 0.12s';
        item.dataset.id = n.id;
        item.dataset.url = n.url ?? '';
        item.innerHTML = `
            <i class="bi ${icon} ${color} flex-shrink-0 mt-1" style="font-size:1.1rem;"></i>
            <div class="flex-grow-1 min-width-0">
                <div class="fw-semibold small lh-sm">${n.title}</div>
                <div class="text-secondary" style="font-size:0.78rem;">${n.message}</div>
                <div class="text-secondary" style="font-size:0.72rem;margin-top:2px;">${n.created_at}</div>
            </div>
            ${n.url ? `<a href="${n.url}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill px-2 flex-shrink-0" style="font-size:0.72rem;white-space:nowrap;" @click.stop>Ver</a>` : ''}
        `;

        item.addEventListener('click', () => {
            fetch(NOTIF_READ_URL(n.id), { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' } })
                .catch(() => {});
            item.remove();
            if (!list.querySelector('.notif-item')) {
                if (empty) empty.style.display = '';
                badge.style.display = 'none';
            } else {
                const remaining = list.querySelectorAll('.notif-item').length;
                badge.textContent = remaining > 9 ? '9+' : String(remaining);
            }
            if (n.url) window.location.assign(n.url);
        });

        list.appendChild(item);
    });
}

function fetchNotifications() {
    fetch(NOTIF_UNREAD_URL)
        .then(r => r.ok ? r.json() : null)
        .then(data => { if (data) renderNotifications(data); })
        .catch(() => {});
}

function initNotifications() {
    if (_notifTimer) clearInterval(_notifTimer);

    const markAll = document.getElementById('notifMarkAll');
    if (markAll && !markAll._notifBound) {
        markAll._notifBound = true;
        markAll.addEventListener('click', () => {
            fetch(NOTIF_READ_ALL_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' } })
                .catch(() => {});
            const badge = document.getElementById('notifBadge');
            const empty = document.getElementById('notifEmpty');
            const list  = document.getElementById('notifList');
            if (badge) badge.style.display = 'none';
            if (list)  list.querySelectorAll('.notif-item').forEach(el => el.remove());
            if (empty) empty.style.display = '';
        });
    }

    fetchNotifications();
    _notifTimer = setInterval(fetchNotifications, NOTIF_POLL_INTERVAL);
}

document.addEventListener('DOMContentLoaded', () => {
    initShellPersistent();
    initShellPage();

    if (document.getElementById('notifToggle')) {
        initNotifications();
    }
});
