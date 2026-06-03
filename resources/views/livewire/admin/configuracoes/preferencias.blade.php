<div>

    {{-- =====================================================================
         Banner de sucesso
         ===================================================================== --}}
    <div
        x-show="savedBanner"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="alert alert-success border-success-subtle d-flex align-items-center gap-2 rounded-3 shadow-sm mb-3"
        role="alert"
        style="position: sticky; top: 1rem; z-index: 100;"
    >
        <i class="bi bi-check-circle-fill text-success fs-6"></i>
        <span class="small fw-semibold flex-grow-1">Preferências salvas com sucesso.</span>
        <button type="button" class="btn-close btn-close-sm" @click="savedBanner = false"></button>
    </div>

    {{-- =====================================================================
         Card 1 — Aparência
         ===================================================================== --}}
    <div class="settings-card mb-3">
        <h6 class="settings-card-title">Aparência</h6>
        <p class="settings-card-description">Escolha como a interface é exibida. A alteração é aplicada imediatamente.</p>

        <div class="row g-3">

            {{-- Claro --}}
            <div class="col-4">
                <div
                    class="theme-option-card {{ $theme === 'light' ? 'selected' : '' }}"
                    wire:click="setTheme('light')"
                    role="button"
                    tabindex="0"
                    @keydown.enter="$el.click()"
                    aria-pressed="{{ $theme === 'light' ? 'true' : 'false' }}"
                >
                    <div class="theme-preview theme-preview--light">
                        <div class="tp-bar"></div>
                        <div class="tp-content">
                            <div class="tp-line tp-line--wide"></div>
                            <div class="tp-line tp-line--short"></div>
                            <div class="tp-card"></div>
                        </div>
                    </div>
                    <div class="theme-option-label">
                        <span class="theme-option-check {{ $theme === 'light' ? 'visible' : '' }}">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>
                        <span>Claro</span>
                    </div>
                </div>
            </div>

            {{-- Escuro --}}
            <div class="col-4">
                <div
                    class="theme-option-card {{ $theme === 'dark' ? 'selected' : '' }}"
                    wire:click="setTheme('dark')"
                    role="button"
                    tabindex="0"
                    @keydown.enter="$el.click()"
                    aria-pressed="{{ $theme === 'dark' ? 'true' : 'false' }}"
                >
                    <div class="theme-preview theme-preview--dark">
                        <div class="tp-bar"></div>
                        <div class="tp-content">
                            <div class="tp-line tp-line--wide"></div>
                            <div class="tp-line tp-line--short"></div>
                            <div class="tp-card"></div>
                        </div>
                    </div>
                    <div class="theme-option-label">
                        <span class="theme-option-check {{ $theme === 'dark' ? 'visible' : '' }}">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>
                        <span>Escuro</span>
                    </div>
                </div>
            </div>

            {{-- Sistema --}}
            <div class="col-4">
                <div
                    class="theme-option-card {{ $theme === 'system' ? 'selected' : '' }}"
                    wire:click="setTheme('system')"
                    role="button"
                    tabindex="0"
                    @keydown.enter="$el.click()"
                    aria-pressed="{{ $theme === 'system' ? 'true' : 'false' }}"
                >
                    <div class="theme-preview theme-preview--system">
                        <div class="tp-bar"></div>
                        <div class="tp-content">
                            <div class="tp-line tp-line--wide"></div>
                            <div class="tp-line tp-line--short"></div>
                            <div class="tp-card"></div>
                        </div>
                    </div>
                    <div class="theme-option-label">
                        <span class="theme-option-check {{ $theme === 'system' ? 'visible' : '' }}">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>
                        <span>Sistema</span>
                    </div>
                </div>
            </div>

        </div>

        <p class="mt-2 mb-0 text-secondary" style="font-size:.75rem;">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Sistema</strong> detecta automaticamente a preferência de aparência do seu dispositivo.
        </p>
    </div>

    {{-- =====================================================================
         Card 2 — Regional
         ===================================================================== --}}
    <div class="settings-card mb-3">
        <h6 class="settings-card-title">Regional</h6>
        <p class="settings-card-description">Define o fuso horário usado para exibir datas, prazos e notificações.</p>

        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label class="form-label small fw-semibold" for="timezone">Fuso horário</label>
                <select
                    wire:model="timezone"
                    id="timezone"
                    class="form-select @error('timezone') is-invalid @enderror"
                >
                    @foreach($timezones as $group => $options)
                        <optgroup label="{{ $group }}">
                            @foreach($options as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('timezone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold" for="locale">Idioma</label>
                <select id="locale" class="form-select" disabled>
                    <option>Português (Brasil)</option>
                </select>
                <div class="text-secondary mt-1" style="font-size:.72rem;">
                    <i class="bi bi-translate me-1"></i>Outros idiomas em breve.
                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         Card 3 — Notificações
         ===================================================================== --}}
    <div class="settings-card mb-3">
        <h6 class="settings-card-title">Notificações</h6>
        <p class="settings-card-description">Configure como e quando você quer ser avisado sobre eventos importantes.</p>

        {{-- Canais --}}
        <div class="notif-section-label">Canais</div>
        <div class="row g-2 mb-4">

            {{-- E-mail --}}
            <div class="col-12 col-sm-6">
                <label class="notif-channel-card {{ $notifyEmail ? 'active' : '' }}" for="notifyEmail">
                    <div class="d-flex align-items-center gap-3">
                        <div class="notif-channel-icon">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size:.875rem;">E-mail</div>
                            <div class="text-secondary" style="font-size:.76rem;">Resumos e alertas por e-mail</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                wire:model.live="notifyEmail"
                                id="notifyEmail"
                                role="switch"
                            >
                        </div>
                    </div>
                </label>
            </div>

            {{-- Navegador --}}
            <div class="col-12 col-sm-6">
                <label class="notif-channel-card {{ $notifyBrowser ? 'active' : '' }}" for="notifyBrowser">
                    <div class="d-flex align-items-center gap-3">
                        <div class="notif-channel-icon">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size:.875rem;">Navegador</div>
                            <div class="text-secondary" style="font-size:.76rem;">Notificações push no dispositivo</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                wire:model.live="notifyBrowser"
                                id="notifyBrowser"
                                role="switch"
                            >
                        </div>
                    </div>
                </label>
            </div>

        </div>

        {{-- Horário de silêncio --}}
        <div class="notif-section-label">Horário de silêncio</div>
        <div class="notif-quiet-card mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="flex-grow-1">
                    <div class="fw-semibold" style="font-size:.875rem;">
                        <i class="bi bi-moon-stars me-1 text-secondary"></i>
                        Silenciar notificações
                    </div>
                    <div class="text-secondary" style="font-size:.76rem;">
                        Nenhuma notificação é enviada no período configurado.
                    </div>
                </div>
                <div class="form-check form-switch ms-2 mb-0 flex-shrink-0">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        wire:model.live="quietEnabled"
                        id="quietEnabled"
                        role="switch"
                    >
                </div>
            </div>

            @if($quietEnabled)
                <div class="d-flex align-items-center flex-wrap gap-2 mt-3 pt-3"
                     style="border-top: 1px solid rgba(215,220,229,.5);">
                    <label class="text-secondary small fw-semibold mb-0 flex-shrink-0">Das</label>
                    <input
                        type="time"
                        wire:model="quietStart"
                        class="form-control form-control-sm @error('quietStart') is-invalid @enderror"
                        style="width: auto;"
                    >
                    <label class="text-secondary small fw-semibold mb-0 flex-shrink-0">até</label>
                    <input
                        type="time"
                        wire:model="quietEnd"
                        class="form-control form-control-sm @error('quietEnd') is-invalid @enderror"
                        style="width: auto;"
                    >
                    <span class="text-secondary ms-1" style="font-size:.74rem;">
                        (fuso: {{ $timezone }})
                    </span>
                </div>
                @error('quietStart')
                    <div class="text-danger mt-1" style="font-size:.8rem;">{{ $message }}</div>
                @enderror
                @error('quietEnd')
                    <div class="text-danger mt-1" style="font-size:.8rem;">{{ $message }}</div>
                @enderror
            @endif
        </div>

        {{-- Matriz de eventos --}}
        <div class="notif-section-label">Eventos</div>
        <div class="notif-matrix">

            {{-- Cabeçalho --}}
            <div class="notif-matrix-row notif-matrix-header">
                <div class="notif-matrix-event">Evento</div>
                <div class="notif-matrix-channel text-center">
                    <i class="bi bi-envelope me-1"></i>E-mail
                </div>
                <div class="notif-matrix-channel text-center">
                    <i class="bi bi-bell me-1"></i>Push
                </div>
            </div>

            {{-- Linhas --}}
            @foreach($eventLabels as $key => [$label, $description])
                <div class="notif-matrix-row" wire:key="event-row-{{ $key }}">

                    <div class="notif-matrix-event">
                        <div class="fw-semibold" style="font-size:.875rem; line-height:1.3;">{{ $label }}</div>
                        <div class="text-secondary" style="font-size:.76rem;">{{ $description }}</div>
                    </div>

                    <div class="notif-matrix-channel text-center">
                        <input
                            type="checkbox"
                            class="form-check-input notif-check"
                            wire:model="notifEvents.{{ $key }}.email"
                            id="notif-{{ $key }}-email"
                            {{ !$notifyEmail ? 'disabled' : '' }}
                            title="{{ !$notifyEmail ? 'Ative o canal E-mail para configurar este evento' : '' }}"
                        >
                    </div>

                    <div class="notif-matrix-channel text-center">
                        <input
                            type="checkbox"
                            class="form-check-input notif-check"
                            wire:model="notifEvents.{{ $key }}.browser"
                            id="notif-{{ $key }}-browser"
                            {{ !$notifyBrowser ? 'disabled' : '' }}
                            title="{{ !$notifyBrowser ? 'Ative o canal Navegador para configurar este evento' : '' }}"
                        >
                    </div>

                </div>
            @endforeach
        </div>

        <p class="text-secondary mt-2 mb-0" style="font-size:.74rem;">
            <i class="bi bi-info-circle me-1"></i>
            As colunas ficam desabilitadas quando o canal correspondente está desativado.
        </p>
    </div>

    {{-- =====================================================================
         Ação: Salvar
         ===================================================================== --}}
    <div class="d-flex align-items-center gap-3">
        <button
            type="button"
            class="btn btn-primary rounded-pill px-4"
            wire:click="salvar()"
            wire:loading.attr="disabled"
            wire:target="salvar"
        >
            <span wire:loading.remove wire:target="salvar">
                <i class="bi bi-floppy me-1"></i>Salvar preferências
            </span>
            <span wire:loading wire:target="salvar" class="d-inline-flex align-items-center gap-1">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                Salvando…
            </span>
        </button>

        @if($saved)
            <span class="text-success small d-inline-flex align-items-center gap-1"
                  x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <i class="bi bi-check-circle-fill"></i>
                Salvo com sucesso!
            </span>
        @endif
    </div>

</div>

@assets
<style>
/* ── Theme option cards ───────────────────────────────────────────────────── */
.theme-option-card {
    border: 2px solid var(--jusai-border, #d7dce5);
    border-radius: 1rem;
    overflow: hidden;
    cursor: pointer;
    transition: border-color 180ms ease, box-shadow 180ms ease;
    background: transparent;
    user-select: none;
}

.theme-option-card:hover {
    border-color: rgba(37, 99, 235, 0.4);
}

.theme-option-card.selected {
    border-color: var(--jusai-action, #2563eb);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.theme-option-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.55rem 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    border-top: 1px solid var(--jusai-border, #d7dce5);
    background: rgba(255,255,255,.97);
}

.theme-option-check {
    color: var(--jusai-action, #2563eb);
    visibility: hidden;
    font-size: .85rem;
    line-height: 1;
}
.theme-option-check.visible { visibility: visible; }

/* Mini UI previews */
.theme-preview {
    height: 80px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.tp-bar {
    height: 14px;
    flex-shrink: 0;
}

.tp-content {
    flex: 1;
    padding: 6px 7px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.tp-line          { height: 5px; border-radius: 3px; }
.tp-line--wide    { width: 80%; }
.tp-line--short   { width: 50%; }
.tp-card          { height: 20px; border-radius: 4px; flex-shrink: 0; }

/* Light */
.theme-preview--light           { background: #f4f6fb; }
.theme-preview--light .tp-bar   { background: #fff; border-bottom: 1px solid #e2e6ed; }
.theme-preview--light .tp-line  { background: #d7dce5; }
.theme-preview--light .tp-card  { background: #fff; border: 1px solid #e2e6ed; }

/* Dark */
.theme-preview--dark            { background: #111827; }
.theme-preview--dark .tp-bar    { background: #1f2937; border-bottom: 1px solid #374151; }
.theme-preview--dark .tp-line   { background: #374151; }
.theme-preview--dark .tp-card   { background: #1f2937; border: 1px solid #374151; }

/* System — diagonal split */
.theme-preview--system          { background: linear-gradient(135deg, #f4f6fb 50%, #111827 50%); }
.theme-preview--system .tp-bar  { background: linear-gradient(135deg, #fff 50%, #1f2937 50%); border-bottom: 1px solid #e2e6ed; }
.theme-preview--system .tp-line { background: linear-gradient(135deg, #d7dce5 50%, #374151 50%); }
.theme-preview--system .tp-card { background: linear-gradient(135deg, #fff 50%, #1f2937 50%); }

/* Dark mode adaptation for the cards */
[data-theme="dark"] .theme-option-card        { border-color: rgba(255,255,255,0.1); }
[data-theme="dark"] .theme-option-card:hover  { border-color: rgba(96, 165, 250, 0.4); }
[data-theme="dark"] .theme-option-card.selected {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.14);
}
[data-theme="dark"] .theme-option-label       { background: rgba(255,255,255,0.04); border-top-color: rgba(255,255,255,0.07); color: rgba(255,255,255,0.8); }
[data-theme="dark"] .theme-option-check       { color: #60a5fa; }

/* ── Notifications ────────────────────────────────────────────────────────── */
.notif-section-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--jusai-muted, #6b7280);
    margin-bottom: 0.6rem;
}

[data-theme="dark"] .notif-section-label { color: rgba(255,255,255,0.3); }

.notif-channel-card {
    display: block;
    border: 1px solid var(--jusai-border, #d7dce5);
    border-radius: 0.875rem;
    padding: 0.875rem 1rem;
    cursor: pointer;
    transition: border-color 150ms ease, background 150ms ease;
    background: rgba(255,255,255,.5);
}
.notif-channel-card:hover { border-color: rgba(37, 99, 235, 0.3); }
.notif-channel-card.active {
    border-color: rgba(37, 99, 235, 0.35);
    background: rgba(37, 99, 235, 0.03);
}

.notif-channel-icon {
    width: 36px;
    height: 36px;
    border-radius: 0.6rem;
    background: rgba(37, 99, 235, 0.08);
    color: var(--jusai-action, #2563eb);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

[data-theme="dark"] .notif-channel-card       { border-color: rgba(255,255,255,0.08); background: rgba(255,255,255,0.03); }
[data-theme="dark"] .notif-channel-card.active { border-color: rgba(96,165,250,0.25); background: rgba(37,99,235,0.07); }
[data-theme="dark"] .notif-channel-icon        { background: rgba(96,165,250,0.12); color: #60a5fa; }

.notif-quiet-card {
    border: 1px solid var(--jusai-border, #d7dce5);
    border-radius: 0.875rem;
    padding: 0.875rem 1rem;
    background: rgba(255,255,255,.5);
}

[data-theme="dark"] .notif-quiet-card { border-color: rgba(255,255,255,0.08); background: rgba(255,255,255,0.03); }

/* Matrix */
.notif-matrix {
    border: 1px solid var(--jusai-border, #d7dce5);
    border-radius: 0.875rem;
    overflow: hidden;
}

.notif-matrix-row {
    display: grid;
    grid-template-columns: 1fr 80px 80px;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(215,220,229,.4);
}
.notif-matrix-row:last-child { border-bottom: none; }

.notif-matrix-header {
    background: rgba(248, 250, 252, 0.95);
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--jusai-muted, #6b7280);
    letter-spacing: 0.04em;
}

.notif-check {
    width: 1.1rem;
    height: 1.1rem;
    cursor: pointer;
}
.notif-check:disabled { opacity: .35; cursor: not-allowed; }

[data-theme="dark"] .notif-matrix         { border-color: rgba(255,255,255,0.08); }
[data-theme="dark"] .notif-matrix-row     { border-bottom-color: rgba(255,255,255,0.06); }
[data-theme="dark"] .notif-matrix-header  { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.32); }
</style>
@endassets
