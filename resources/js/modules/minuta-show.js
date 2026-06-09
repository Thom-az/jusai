/**
 * minuta-show.js
 * Polls the draft status endpoint until generation is complete.
 * Works both on full page load and on wire:navigate transitions.
 */
function initMinutaPolling() {
    const card = document.getElementById('processingCard');
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
            .catch(() => {
                // Network error — keep retrying silently
            });
    }, 3000);
}

document.addEventListener('DOMContentLoaded', initMinutaPolling);
document.addEventListener('livewire:navigated', initMinutaPolling);
