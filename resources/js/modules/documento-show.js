/**
 * documento-show.js
 * Reloads the page every 5s while the document is still processing.
 */
function initDocumentoPolling() {
    const card = document.getElementById('docProcessingCard');
    if (!card) return;

    const intervalId = setInterval(() => {
        location.reload();
    }, 5000);

    // Clean up if navigating away before reload fires
    document.addEventListener('livewire:navigate', () => clearInterval(intervalId), { once: true });
}

document.addEventListener('DOMContentLoaded', initDocumentoPolling);
document.addEventListener('livewire:navigated', initDocumentoPolling);
