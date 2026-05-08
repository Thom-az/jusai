import 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-disabled-action]').forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();

            const toastElement = document.getElementById('appToast');
            const toastBody = document.getElementById('appToastBody');

            if (!toastElement || !toastBody || !window.bootstrap?.Toast) {
                return;
            }

            toastBody.textContent = element.getAttribute('data-disabled-action') ?? 'Este recurso será entregue na próxima etapa.';

            const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement);
            toast.show();
        });
    });
});
