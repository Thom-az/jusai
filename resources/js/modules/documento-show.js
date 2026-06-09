/**
 * documento-show.js
 * Polls the document status endpoint until processing is complete,
 * then reloads once. Works on full load and wire:navigate transitions.
 */
function initDocumentoPolling() {
    const card = document.getElementById('docProcessingCard');
    if (!card) return;

    const statusUrl = card.dataset.statusUrl;
    if (!statusUrl) return;

    const intervalId = setInterval(() => {
        fetch(statusUrl)
            .then(r => r.json())
            .then(d => {
                if (d.ready) {
                    clearInterval(intervalId);
                    location.reload();
                }
            })
            .catch(() => {});
    }, 3000);

    document.addEventListener('livewire:navigate', () => clearInterval(intervalId), { once: true });
}

document.addEventListener('DOMContentLoaded', initDocumentoPolling);
document.addEventListener('livewire:navigated', initDocumentoPolling);
