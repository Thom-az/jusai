/* chamados-index.js — stepper modal, view toggle, filters, keyboard shortcuts */
import { Modal, Toast } from 'bootstrap';

const CATEGORY_LABELS = {
    tecnico: 'Técnico', financeiro: 'Financeiro', duvida: 'Dúvida',
    sugestao: 'Sugestão', bug: 'Bug', outros: 'Outros',
};

const PRIORITY_LABELS = {
    baixa: 'Baixa', media: 'Média', alta: 'Alta', critica: 'Crítica',
};

/* ── Storage keys ───────────────────────────────────────── */
const VIEW_KEY    = 'chamados_view';
const DENSITY_KEY = 'chamados_density';

/* ── DOM refs ───────────────────────────────────────────── */
const viewBtns    = document.querySelectorAll('.cham-view-btn');
const viewLista   = document.getElementById('viewLista');
const viewKanban  = document.getElementById('viewKanban');
const filterForm  = document.getElementById('filterForm');
const statusInput = document.getElementById('statusHiddenInput');
const listTable   = document.getElementById('chamListTable');
const densityBtn  = document.getElementById('chamDensityBtn');
const densityIcon = document.getElementById('chamDensityIcon');
const searchInput = document.getElementById('chamSearchInput');
const prioFilter  = document.getElementById('chamPrioFilter');

/* ── View toggle ────────────────────────────────────────── */
function applyView(view) {
    viewBtns.forEach(b => b.classList.toggle('active', b.dataset.view === view));
    viewLista?.classList.toggle('d-none',  view !== 'lista');
    viewKanban?.classList.toggle('d-none', view !== 'kanban');
    localStorage.setItem(VIEW_KEY, view);
}

applyView(localStorage.getItem(VIEW_KEY) || 'lista');

viewBtns.forEach(btn => btn.addEventListener('click', () => applyView(btn.dataset.view)));

/* ── Density toggle ─────────────────────────────────────── */
function applyDensity(density) {
    const compact = density === 'compact';
    listTable?.classList.toggle('is-compact', compact);
    if (densityIcon) {
        densityIcon.className = compact ? 'bi bi-layout-three-columns' : 'bi bi-layout-split';
    }
    if (densityBtn) densityBtn.title = compact ? 'Modo confortável (D)' : 'Modo compacto (D)';
    localStorage.setItem(DENSITY_KEY, density);
}

applyDensity(localStorage.getItem(DENSITY_KEY) || 'comfortable');

densityBtn?.addEventListener('click', () => {
    const next = listTable?.classList.contains('is-compact') ? 'comfortable' : 'compact';
    applyDensity(next);
});

/* ── Stage metric filters ───────────────────────────────── */
function showLoadingSkeleton() {
    document.getElementById('ticketsLoading')?.classList.remove('d-none');
    viewLista?.classList.add('d-none');
    viewKanban?.classList.add('d-none');
}

document.querySelectorAll('[data-filter-status]').forEach((card, idx) => {
    card.addEventListener('click', () => {
        const val = card.dataset.filterStatus;
        statusInput.value = statusInput.value === val ? '' : val;
        showLoadingSkeleton();
        filterForm.requestSubmit();
    });

    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
    });

    // store index for keyboard shortcut 1–6
    card.dataset.metricIndex = idx;
});

filterForm?.addEventListener('submit', showLoadingSkeleton);

/* Priority filter: auto-submit on change */
prioFilter?.addEventListener('change', () => {
    showLoadingSkeleton();
    filterForm.requestSubmit();
});

/* ── Keyboard shortcuts ─────────────────────────────────── */
const isTyping = () => {
    const el = document.activeElement;
    return el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' ||
           el.isContentEditable || el.tagName === 'SELECT';
};

document.addEventListener('keydown', e => {
    if (e.ctrlKey || e.metaKey || e.altKey) return;

    const key = e.key;

    // '/' — focus search
    if (key === '/') {
        if (isTyping()) return;
        e.preventDefault();
        searchInput?.focus();
        return;
    }

    // Escape — blur search or close modal
    if (key === 'Escape') {
        if (document.activeElement === searchInput) {
            searchInput.blur();
        }
        return;
    }

    if (isTyping()) return;

    // 'n' — open new ticket modal
    if (key === 'n' || key === 'N') {
        const modalEl = document.getElementById('modalNovoChamado');
        if (modalEl) Modal.getOrCreateInstance(modalEl).show();
        return;
    }

    // 'l' — switch to lista
    if (key === 'l' || key === 'L') { applyView('lista'); return; }

    // 'k' — switch to kanban
    if (key === 'k' || key === 'K') { applyView('kanban'); return; }

    // 'd' — toggle density
    if (key === 'd' || key === 'D') {
        densityBtn?.click();
        return;
    }

    // '1'–'6' — filter by stage
    const num = parseInt(key, 10);
    if (num >= 1 && num <= 6) {
        const card = document.querySelector(`[data-metric-index="${num - 1}"]`);
        card?.click();
    }
});

/* ── Stepper logic ──────────────────────────────────────── */
let currentStep  = 1;
const TOTAL_STEPS = 4;

const stepEls   = document.querySelectorAll('.stepper-step');
const panelEls  = document.querySelectorAll('.stepper-panel');
const btnBack   = document.getElementById('btnStepperBack');
const btnNext   = document.getElementById('btnStepperNext');
const btnSubmit = document.getElementById('btnStepperSubmit');

function goToStep(n) {
    currentStep = n;

    stepEls.forEach((el, i) => {
        const num  = i + 1;
        const done = num < n;
        el.classList.toggle('active',    num === n);
        el.classList.toggle('completed', done);
        el.querySelector('.stepper-dot-num')?.classList.toggle('d-none', done);
        el.querySelector('.stepper-dot-check')?.classList.toggle('d-none', !done);
    });

    panelEls.forEach(p => p.classList.toggle('d-none', +p.dataset.panel !== n));

    btnBack.style.display = n > 1 ? '' : 'none';
    btnNext.classList.toggle('d-none',   n === TOTAL_STEPS);
    btnSubmit.classList.toggle('d-none', n !== TOTAL_STEPS);

    if (n === TOTAL_STEPS) populateConfirmation();
}

function populateConfirmation() {
    const form = document.getElementById('formNovoChamado');
    const fd   = new FormData(form);

    setText('confirmTitle',    fd.get('title')    || '—');
    setText('confirmDesc',     fd.get('description') || '—');
    setText('confirmCategory', CATEGORY_LABELS[fd.get('category')] || fd.get('category') || '—');
    setText('confirmPriority', PRIORITY_LABELS[fd.get('priority')] || fd.get('priority') || '—');

    const files = form.querySelector('[name="attachments[]"]')?.files;
    setText('confirmAttach',
        files && files.length
            ? Array.from(files).map(f => f.name).join(', ')
            : 'Nenhum'
    );
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

/* Step validation */
function validateStep(n) {
    if (n === 1) {
        const title = document.querySelector('[name="title"]');
        const desc  = document.querySelector('[name="description"]');
        let ok = true;
        [title, desc].forEach(el => {
            const valid = el.value.trim().length > 0;
            el.classList.toggle('is-invalid', !valid);
            if (!valid) ok = false;
        });
        return ok;
    }

    if (n === 2) {
        const catPicked  = document.querySelector('[name="category"]:checked');
        const prioPicked = document.querySelector('[name="priority"]:checked');
        document.getElementById('categoryError')?.classList.toggle('d-none', !!catPicked);
        document.getElementById('priorityError')?.classList.toggle('d-none', !!prioPicked);
        return !!(catPicked && prioPicked);
    }

    return true;
}

btnNext?.addEventListener('click', () => {
    if (!validateStep(currentStep)) return;
    if (currentStep < TOTAL_STEPS) goToStep(currentStep + 1);
});

btnBack?.addEventListener('click', () => {
    if (currentStep > 1) goToStep(currentStep - 1);
});

/* Reset stepper on modal open */
document.getElementById('modalNovoChamado')?.addEventListener('show.bs.modal', () => {
    document.getElementById('formNovoChamado').reset();
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('#categoryError, #priorityError').forEach(el => el.classList.add('d-none'));
    document.getElementById('filePreview').innerHTML = '';
    document.querySelector('.desc-counter').textContent = '0 / 5000';
    goToStep(1);
});

/* ── Form submit ────────────────────────────────────────── */
btnSubmit?.addEventListener('click', async () => {
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Abrindo…';

    const form = document.getElementById('formNovoChamado');
    const fd   = new FormData(form);

    try {
        const res  = await fetch('/chamados', {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: fd,
        });
        const data = await res.json();

        if (data.success) {
            Modal.getInstance(document.getElementById('modalNovoChamado'))?.hide();
            const el = document.getElementById('successProtocol');
            if (el) el.textContent = data.protocol;
            setTimeout(() => new Modal(document.getElementById('modalSucesso')).show(), 300);
        } else {
            showToast(data.message || 'Erro ao abrir chamado. Tente novamente.');
        }
    } catch {
        showToast('Erro de conexão. Verifique sua internet e tente novamente.');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="bi bi-check-lg me-1"></i>Abrir chamado';
    }
});

/* ── File preview ───────────────────────────────────────── */
document.querySelector('[name="attachments[]"]')?.addEventListener('change', function () {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';

    Array.from(this.files).forEach(file => {
        const kb   = file.size / 1024;
        const size = kb < 1024 ? kb.toFixed(1) + ' KB' : (kb / 1024).toFixed(1) + ' MB';

        const item = document.createElement('div');
        item.className = 'file-preview-item';
        item.innerHTML = `
            <i class="bi bi-file-earmark text-secondary"></i>
            <span class="file-name">${escapeHtml(file.name)}</span>
            <span class="file-size">${size}</span>
        `;
        preview.appendChild(item);
    });
});

/* ── Description counter ────────────────────────────────── */
document.querySelector('[name="description"]')?.addEventListener('input', function () {
    const counter = document.querySelector('.desc-counter');
    if (counter) counter.textContent = this.value.length + ' / 5000';
    this.classList.remove('is-invalid');
});

document.querySelector('[name="title"]')?.addEventListener('input', function () {
    this.classList.remove('is-invalid');
});

/* ── Toast helper ───────────────────────────────────────── */
function showToast(msg) {
    const toastBody = document.getElementById('appToastBody');
    const toastEl   = document.getElementById('appToast');
    if (toastBody) toastBody.textContent = msg;
    if (toastEl)   new Toast(toastEl).show();
}

function escapeHtml(str) {
    return str.replace(/[&<>"']/g, m =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]
    );
}
