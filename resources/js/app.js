import 'bootstrap';
import { Tooltip, Toast } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('.app-shell');
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
        if (!shell) {
            return;
        }

        shell.dataset.sidebarState = state;

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
            if (!shell) {
                return;
            }

            const nextState = shell.dataset.sidebarState === 'collapsed' ? 'expanded' : 'collapsed';
            applySidebarState(nextState);
            localStorage.setItem(sidebarStorageKey, nextState);
        });
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
