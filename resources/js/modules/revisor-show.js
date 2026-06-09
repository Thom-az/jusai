/**
 * revisor-show.js
 * Polls the AI review status endpoint until the result is ready.
 * Works both on full page load and on wire:navigate transitions.
 */
function initRevisorPolling() {
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

document.addEventListener('DOMContentLoaded', initRevisorPolling);
document.addEventListener('livewire:navigated', initRevisorPolling);
