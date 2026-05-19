/**
 * casos-show.js
 * Tab persistence via URL hash for cases/show view.
 */
document.addEventListener('DOMContentLoaded', () => {
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
    if (!tabLinks.length) return;

    // Restore active tab from URL hash on page load
    const hash = window.location.hash;
    if (hash) {
        const tabEl = document.querySelector(`[data-bs-toggle="tab"][href="${hash}"]`);
        if (tabEl) {
            bootstrap.Tab.getOrCreateInstance(tabEl).show();
        }
    }

    // Persist active tab to URL hash on change
    tabLinks.forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => {
            const target = e.target.getAttribute('href');
            if (target) {
                history.replaceState(null, '', target);
            }
        });
    });
});
