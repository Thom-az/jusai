/**
 * admin/prompts.js
 * Manages AI prompt edit/preview/reset modals with AJAX saves.
 */

const getCsrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Toast helper (reuses admin layout's appToast) ──────────────
function showToast(message, type = 'success') {
    const el   = document.getElementById('appToast');
    const body = document.getElementById('appToastBody');
    if (!el || !body) return;

    body.textContent = message;
    el.classList.toggle('text-bg-danger', type === 'error');
    el.classList.toggle('border-success',  type === 'success');

    const toast = bootstrap.Toast.getOrCreateInstance(el, { delay: 4000 });
    toast.show();
}

// ── State ──────────────────────────────────────────────────────
let currentKey   = '';
let currentLabel = '';

// ── Preview modal ──────────────────────────────────────────────
document.getElementById('modalPreviewPrompt')?.addEventListener('show.bs.modal', (e) => {
    const btn = e.relatedTarget;
    if (!btn) return;

    currentKey   = btn.dataset.key   ?? '';
    currentLabel = btn.dataset.label ?? '';
    const content = btn.dataset.content ?? '';

    document.getElementById('previewLabel').textContent  = currentLabel;
    document.getElementById('previewKey').textContent    = currentKey;
    document.getElementById('previewContent').textContent = content;
    document.getElementById('previewChars').textContent  = content.length + ' chars';
});

// Preview → open Edit
document.getElementById('btnPreviewToEdit')?.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('modalPreviewPrompt'))?.hide();

    const btn = document.querySelector(`[data-bs-target="#modalEditPrompt"][data-key="${currentKey}"]`);
    if (btn) btn.click();
});

// ── Edit modal ─────────────────────────────────────────────────
document.getElementById('modalEditPrompt')?.addEventListener('show.bs.modal', (e) => {
    const btn = e.relatedTarget;
    if (!btn) return;

    currentKey   = btn.dataset.key   ?? '';
    currentLabel = btn.dataset.label ?? '';
    const content = btn.dataset.content ?? '';

    document.getElementById('editLabel').textContent    = currentLabel;
    document.getElementById('editKey').textContent      = currentKey;
    document.getElementById('editContent').value        = content;
    document.getElementById('editCharCount').textContent = content.length + ' caracteres';
    document.getElementById('editChars').textContent    = content.length + ' chars';
    document.getElementById('editError').classList.add('d-none');
    document.getElementById('resetKeyLabel').textContent = currentLabel;
});

document.getElementById('editContent')?.addEventListener('input', function () {
    const len = this.value.length;
    document.getElementById('editCharCount').textContent = len + ' caracteres';
    document.getElementById('editChars').textContent     = len + ' chars';
});

// Save prompt
document.getElementById('btnSavePrompt')?.addEventListener('click', async () => {
    const content  = document.getElementById('editContent').value.trim();
    const errorEl  = document.getElementById('editError');
    const saveBtn  = document.getElementById('btnSavePrompt');

    if (content.length < 10) {
        errorEl.textContent = 'O prompt precisa ter pelo menos 10 caracteres.';
        errorEl.classList.remove('d-none');
        return;
    }

    errorEl.classList.add('d-none');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Salvando…';

    try {
        const res  = await fetch(`/admin/prompts/${encodeURIComponent(currentKey)}`, {
            method:  'PATCH',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     getCsrf(),
                'Accept':           'application/json',
            },
            body: JSON.stringify({ content }),
        });

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message ?? 'Erro ao salvar.');
        }

        // Update row's "last edited" column
        const updatedEl = document.querySelector(`.prompt-updated-at[data-key="${currentKey}"]`);
        if (updatedEl) {
            updatedEl.innerHTML = `${data.updated_by} &bull; ${data.updated_at}`;
        }

        // Update data-content on the row's buttons so re-opening shows new content
        document.querySelectorAll(`[data-key="${currentKey}"]`).forEach(el => {
            el.dataset.content = content;
        });

        bootstrap.Modal.getInstance(document.getElementById('modalEditPrompt'))?.hide();
        showToast(`Prompt "${currentLabel}" salvo com sucesso.`);
    } catch (err) {
        errorEl.textContent = err.message;
        errorEl.classList.remove('d-none');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar prompt';
    }
});

// ── Reset ──────────────────────────────────────────────────────
document.getElementById('btnResetPrompt')?.addEventListener('click', () => {
    bootstrap.Modal.getInstance(document.getElementById('modalEditPrompt'))?.hide();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirmReset')).show();
});

document.getElementById('btnConfirmReset')?.addEventListener('click', async () => {
    const resetBtn = document.getElementById('btnConfirmReset');
    resetBtn.disabled = true;
    resetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Restaurando…';

    try {
        const res  = await fetch(`/admin/prompts/${encodeURIComponent(currentKey)}/reset`, {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrf(),
                'Accept':       'application/json',
            },
        });

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.error ?? 'Erro ao restaurar.');
        }

        // Update data-content on all buttons for this key
        document.querySelectorAll(`[data-key="${currentKey}"]`).forEach(el => {
            el.dataset.content = data.content;
        });

        const updatedEl = document.querySelector(`.prompt-updated-at[data-key="${currentKey}"]`);
        if (updatedEl) {
            updatedEl.innerHTML = `<span class="text-muted fst-italic">padrão</span>`;
        }

        bootstrap.Modal.getInstance(document.getElementById('modalConfirmReset'))?.hide();
        showToast(`Prompt "${currentLabel}" restaurado para o padrão.`);
    } catch (err) {
        bootstrap.Modal.getInstance(document.getElementById('modalConfirmReset'))?.hide();
        showToast(err.message, 'error');
    } finally {
        resetBtn.disabled = false;
        resetBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i>Sim, restaurar';
    }
});
