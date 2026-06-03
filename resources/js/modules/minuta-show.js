/**
 * minuta-show.js
 * Polls the draft status endpoint until generation is complete.
 * Reads the URL from data-status-url on #processingCard.
 */
document.addEventListener('DOMContentLoaded', () => {
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
});
