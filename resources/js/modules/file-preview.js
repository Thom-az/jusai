/**
 * file-preview.js
 * Global file preview modal.
 * Triggers on any [data-preview-doc-id] click — fetches a fresh signed URL and renders.
 */

function initFilePreview() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-preview-doc-id]');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const docId = btn.dataset.previewDocId;
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFilePreview'));

        // Reset state
        setPreviewLoading(true);
        document.getElementById('previewFileTitle').textContent = btn.dataset.previewTitle ?? 'Carregando…';
        modal.show();

        try {
            const res  = await fetch(`/documentos/${docId}/preview-url`, {
                headers: { 'Accept': 'application/json' },
            });

            if (!res.ok) throw new Error('Não foi possível carregar o arquivo.');

            const { url, mime, filename, title } = await res.json();

            document.getElementById('previewFileTitle').textContent    = title ?? filename;
            document.getElementById('previewFileSubtitle').textContent = filename;
            document.getElementById('previewDownloadBtn').href         = url;

            renderPreview(url, mime);
        } catch (err) {
            renderError(err.message);
        } finally {
            setPreviewLoading(false);
        }
    });
}

function setPreviewLoading(loading) {
    const spinner = document.getElementById('previewSpinner');
    const body    = document.getElementById('previewBody');
    if (spinner) spinner.classList.toggle('d-none', !loading);
    if (body)    body.classList.toggle('d-none', loading);
}

function renderPreview(url, mime) {
    const body = document.getElementById('previewBody');
    body.innerHTML = '';

    if (mime?.includes('pdf')) {
        const frame = document.createElement('iframe');
        frame.src   = url;
        frame.style.cssText = 'width:100%;height:75vh;border:none;border-radius:.5rem;';
        frame.title = 'Visualização do documento';
        body.appendChild(frame);
        return;
    }

    if (mime?.startsWith('image/')) {
        const img = document.createElement('img');
        img.src   = url;
        img.alt   = 'Documento';
        img.style.cssText = 'max-width:100%;max-height:75vh;object-fit:contain;border-radius:.5rem;display:block;margin:0 auto;';
        body.appendChild(img);
        return;
    }

    // Other file types — show download prompt
    body.innerHTML = `
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-arrow-down fs-1 text-secondary mb-3 d-block"></i>
            <div class="fw-semibold mb-1">Visualização não disponível para este tipo de arquivo</div>
            <div class="text-secondary small mb-4">Faça o download para abrir no aplicativo correspondente.</div>
            <a href="${url}" download class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-download me-2"></i>Baixar arquivo
            </a>
        </div>`;
}

function renderError(message) {
    const body = document.getElementById('previewBody');
    body.innerHTML = `
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle fs-1 text-danger mb-3 d-block"></i>
            <div class="fw-semibold text-danger mb-1">Erro ao carregar</div>
            <div class="text-secondary small">${message}</div>
        </div>`;
    body.classList.remove('d-none');
    document.getElementById('previewSpinner')?.classList.add('d-none');
}

// Limpa o iframe ao fechar para parar carregamento
document.getElementById('modalFilePreview')?.addEventListener('hidden.bs.modal', () => {
    const body = document.getElementById('previewBody');
    if (body) body.innerHTML = '';
    document.getElementById('previewDownloadBtn').href = '#';
    document.getElementById('previewFileSubtitle').textContent = '';
});

document.addEventListener('DOMContentLoaded', initFilePreview);
document.addEventListener('livewire:navigated', initFilePreview);
