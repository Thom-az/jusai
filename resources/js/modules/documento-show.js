/**
 * documento-show.js
 * Polls the document status endpoint until processing is complete,
 * then reloads once. Uses exponential backoff to reduce server load.
 */
let _pollingTimeout = null;

function initDocumentoPolling() {
    if (_pollingTimeout !== null) {
        clearTimeout(_pollingTimeout);
        _pollingTimeout = null;
    }

    const card = document.getElementById('docProcessingCard');
    if (!card) return;

    const statusUrl = card.dataset.statusUrl;
    if (!statusUrl) return;

    const MIN_INTERVAL = 3000;
    const MAX_INTERVAL = 30000;
    let interval = MIN_INTERVAL;

    function poll() {
        fetch(statusUrl)
            .then(r => r.json())
            .then(d => {
                if (d.ready) {
                    _pollingTimeout = null;
                    location.reload();
                } else {
                    interval = Math.min(interval * 1.5, MAX_INTERVAL);
                    _pollingTimeout = setTimeout(poll, interval);
                }
            })
            .catch(() => {
                interval = Math.min(interval * 2, MAX_INTERVAL);
                _pollingTimeout = setTimeout(poll, interval);
            });
    }

    _pollingTimeout = setTimeout(poll, MIN_INTERVAL);

    document.addEventListener('livewire:navigate', () => {
        clearTimeout(_pollingTimeout);
        _pollingTimeout = null;
    }, { once: true });
}

document.addEventListener('DOMContentLoaded', initDocumentoPolling);
document.addEventListener('livewire:navigated', initDocumentoPolling);
