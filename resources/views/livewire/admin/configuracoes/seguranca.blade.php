<div>

    {{-- Toast ----------------------------------------------------------------- --}}
    <div
        x-show="toast.show"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        :class="{
            'alert-success border-success-subtle': toast.type === 'success',
            'alert-warning border-warning-subtle': toast.type === 'warning',
            'alert-danger  border-danger-subtle':  toast.type === 'danger',
        }"
        class="alert d-flex align-items-center gap-2 rounded-3 shadow-sm mb-3"
        style="position: sticky; top: 1rem; z-index: 100;"
    >
        <i class="bi fs-6" :class="{
            'bi-check-circle-fill text-success':           toast.type === 'success',
            'bi-exclamation-triangle-fill text-warning':   toast.type === 'warning',
            'bi-x-circle-fill text-danger':                toast.type === 'danger',
        }"></i>
        <span x-text="toast.message" class="small fw-semibold flex-grow-1"></span>
        <button type="button" class="btn-close btn-close-sm" @click="toast.show = false"></button>
    </div>

    {{-- =========================================================================
         Card 1 — Autenticação de dois fatores (2FA)
         ========================================================================= --}}
    <div class="settings-card mb-3">

        {{-- Cabeçalho com status --}}
        <div class="d-flex align-items-start justify-content-between mb-1">
            <div>
                <h6 class="settings-card-title mb-0">Autenticação de dois fatores (2FA)</h6>
                <p class="settings-card-description mb-0 mt-1">
                    Adiciona uma camada extra de segurança à sua conta usando um aplicativo autenticador
                    (Google Authenticator, Authy, etc.).
                </p>
            </div>
            <span class="ms-3 flex-shrink-0 {{ $twoFaEnabled ? 'sec-badge--on' : 'sec-badge--off' }} sec-badge">
                {{ $twoFaEnabled ? 'Ativo' : 'Inativo' }}
            </span>
        </div>

        <hr class="my-3" style="border-color: rgba(215,220,229,.4);">

        {{-- ── Estado: IDLE (não configurado) ─────────────────────────────── --}}
        @if($twoFaStep === 'idle' && !$twoFaEnabled)
            <div class="d-flex align-items-center gap-3">
                <div class="sec-icon-wrap">
                    <i class="bi bi-shield-lock text-secondary"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="mb-0 small text-secondary">
                        Ative o 2FA para proteger sua conta mesmo que sua senha seja comprometida.
                    </p>
                </div>
                <button
                    type="button"
                    class="btn btn-primary rounded-pill btn-sm flex-shrink-0"
                    wire:click="initTwoFa()"
                    wire:loading.attr="disabled"
                    wire:target="initTwoFa"
                >
                    <span wire:loading.remove wire:target="initTwoFa">
                        <i class="bi bi-shield-plus me-1"></i>Ativar 2FA
                    </span>
                    <span wire:loading wire:target="initTwoFa">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>Gerando…
                    </span>
                </button>
            </div>
        @endif

        {{-- ── Estado: SETUP (QR + verificação) ──────────────────────────── --}}
        @if($twoFaStep === 'setup')
            <div class="row g-4 align-items-start">

                {{-- QR Code --}}
                <div class="col-12 col-md-auto text-center">
                    <div class="qr-code-wrapper mx-auto">
                        @if($this->qrCodeSvg)
                            {!! $this->qrCodeSvg !!}
                        @else
                            <div class="qr-code-placeholder">
                                <i class="bi bi-qr-code" style="font-size:4rem; opacity:.3;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="text-secondary mt-2" style="font-size:.74rem;">Escaneie com o aplicativo</div>
                </div>

                {{-- Instruções + verificação --}}
                <div class="col-12 col-md">
                    <ol class="setup-steps mb-3">
                        <li>Abra o <strong>Google Authenticator</strong>, <strong>Authy</strong> ou similar.</li>
                        <li>Toque em <strong>Adicionar conta</strong> e escaneie o QR ao lado.</li>
                        <li>
                            Ou use a chave manual:
                            <div
                                class="manual-key-box mt-1"
                                x-data="{ copied: false }"
                            >
                                <code class="flex-grow-1" style="font-size:.85rem; letter-spacing:.05em; word-break:break-all;">
                                    {{ $this->manualKey }}
                                </code>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 flex-shrink-0"
                                    style="font-size:.72rem;"
                                    @click="navigator.clipboard.writeText('{{ $this->manualKey }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                >
                                    <i class="bi" :class="copied ? 'bi-check2' : 'bi-clipboard'"></i>
                                    <span x-text="copied ? 'Copiado' : 'Copiar'"></span>
                                </button>
                            </div>
                        </li>
                        <li>Digite o código de 6 dígitos exibido no aplicativo:</li>
                    </ol>

                    <div class="d-flex align-items-start gap-2">
                        <div class="flex-grow-1" style="max-width: 180px;">
                            <input
                                wire:model="twoFaCode"
                                type="text"
                                inputmode="numeric"
                                pattern="\d{6}"
                                maxlength="6"
                                class="form-control form-control-lg text-center fw-bold tracking-wider @error('twoFaCode') is-invalid @enderror"
                                placeholder="000000"
                                autocomplete="one-time-code"
                                wire:keydown.enter="confirmTwoFa()"
                            >
                            @error('twoFaCode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button
                            type="button"
                            class="btn btn-success rounded-pill"
                            wire:click="confirmTwoFa()"
                            wire:loading.attr="disabled"
                            wire:target="confirmTwoFa"
                        >
                            <span wire:loading.remove wire:target="confirmTwoFa">
                                <i class="bi bi-check2-circle me-1"></i>Verificar
                            </span>
                            <span wire:loading wire:target="confirmTwoFa">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-secondary rounded-pill btn-sm"
                            wire:click="cancelTwoFa()"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Estado: SHOW-CODES (códigos de recuperação) ────────────────── --}}
        @if($twoFaStep === 'show-codes')
            <div class="alert alert-success rounded-3 mb-3 d-flex gap-2 align-items-start">
                <i class="bi bi-check-circle-fill mt-1 flex-shrink-0"></i>
                <div>
                    <strong>2FA ativado com sucesso!</strong>
                    Guarde os códigos de recuperação abaixo em local seguro.
                    Eles são exibidos apenas uma vez e permitem acessar sua conta caso perca o dispositivo.
                </div>
            </div>

            <div class="recovery-codes-grid mb-3">
                @foreach($newRecoveryCodes as $code)
                    <code class="recovery-code-item">{{ $code }}</code>
                @endforeach
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button
                    type="button"
                    class="btn btn-outline-secondary rounded-pill btn-sm"
                    x-data="{ copied: false }"
                    @click="
                        navigator.clipboard.writeText('{{ implode('\n', $newRecoveryCodes) }}');
                        copied = true; setTimeout(() => copied = false, 2500)
                    "
                >
                    <i class="bi" :class="copied ? 'bi-check2' : 'bi-clipboard'"></i>
                    <span x-text="copied ? 'Copiado!' : 'Copiar todos'"></span>
                </button>

                <button
                    type="button"
                    class="btn btn-primary rounded-pill btn-sm"
                    wire:click="doneWithCodes()"
                >
                    <i class="bi bi-check2 me-1"></i>Concluir
                </button>
            </div>
        @endif

        {{-- ── Estado: IDLE (2FA ativo) ────────────────────────────────────── --}}
        @if($twoFaStep === 'idle' && $twoFaEnabled)
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="sec-icon-wrap sec-icon-wrap--success">
                    <i class="bi bi-shield-check text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="mb-0 small">
                        <strong>2FA está ativo</strong> — sua conta está protegida com autenticação de dois fatores.
                    </p>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button
                    type="button"
                    class="btn btn-outline-secondary rounded-pill btn-sm"
                    wire:click="regenerateRecoveryCodes()"
                    wire:loading.attr="disabled"
                    wire:target="regenerateRecoveryCodes"
                >
                    <i class="bi bi-arrow-repeat me-1"></i>Regenerar códigos de recuperação
                </button>

                <button
                    type="button"
                    class="btn btn-outline-danger rounded-pill btn-sm"
                    wire:click="startDisableTwoFa()"
                >
                    <i class="bi bi-shield-x me-1"></i>Desativar 2FA
                </button>
            </div>
        @endif

        {{-- ── Estado: DISABLE (confirmação para desativar) ───────────────── --}}
        @if($twoFaStep === 'disable')
            <div class="alert alert-warning rounded-3 mb-3 small d-flex gap-2 align-items-start">
                <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                <span>
                    Desativar o 2FA reduz a segurança da sua conta.
                    Digite um <strong>código TOTP</strong> do seu aplicativo ou um <strong>código de recuperação</strong>.
                </span>
            </div>

            <div class="d-flex align-items-start gap-2">
                <div class="flex-grow-1" style="max-width: 220px;">
                    <input
                        wire:model="twoFaCode"
                        type="text"
                        class="form-control text-center @error('twoFaCode') is-invalid @enderror"
                        placeholder="000000 ou XXXX-XXXX"
                        autocomplete="one-time-code"
                        wire:keydown.enter="disableTwoFa()"
                    >
                    @error('twoFaCode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button
                    type="button"
                    class="btn btn-danger rounded-pill btn-sm"
                    wire:click="disableTwoFa()"
                    wire:loading.attr="disabled"
                    wire:target="disableTwoFa"
                >
                    <span wire:loading.remove wire:target="disableTwoFa">Confirmar desativação</span>
                    <span wire:loading wire:target="disableTwoFa">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </span>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary rounded-pill btn-sm"
                    wire:click="$set('twoFaStep', 'idle')"
                >
                    Cancelar
                </button>
            </div>
        @endif

    </div>{{-- /card 2FA --}}

    {{-- =========================================================================
         Card 2 — Sessões ativas
         ========================================================================= --}}
    <div class="settings-card mb-3">
        <h6 class="settings-card-title">Sessões ativas</h6>
        <p class="settings-card-description">
            Encerre sessões abertas em outros dispositivos se você suspeitar de acesso não autorizado.
        </p>

        {{-- Sessão atual --}}
        <div class="session-row mb-3">
            <div class="session-device-icon">
                <i class="bi bi-laptop"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold small">Este dispositivo</div>
                <div class="text-secondary" style="font-size:.77rem;">
                    IP: {{ request()->ip() }}
                    &nbsp;·&nbsp;
                    {{ \Illuminate\Support\Str::limit(request()->userAgent() ?? '—', 60) }}
                </div>
            </div>
            <span class="sec-badge sec-badge--on" style="font-size:.68rem;">Atual</span>
        </div>

        {{-- Formulário de revogação --}}
        <div x-data="{ open: false }">
            <button
                type="button"
                class="btn btn-outline-danger rounded-pill btn-sm"
                @click="open = !open"
            >
                <i class="bi bi-box-arrow-right me-1"></i>Encerrar outras sessões
            </button>

            <div x-show="open" x-cloak class="mt-3 pt-3" style="border-top: 1px solid rgba(215,220,229,.5);">
                <p class="small text-secondary mb-2">
                    Todos os outros dispositivos serão desconectados. Esta sessão permanece ativa.
                </p>
                <div class="d-flex align-items-start gap-2">
                    <div style="max-width: 260px;" class="flex-grow-1">
                        <input
                            wire:model="sessionPassword"
                            type="password"
                            class="form-control form-control-sm @error('sessionPassword') is-invalid @enderror"
                            placeholder="Confirme sua senha"
                            autocomplete="current-password"
                            wire:keydown.enter="revokeOtherSessions()"
                        >
                        @error('sessionPassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button
                        type="button"
                        class="btn btn-danger rounded-pill btn-sm"
                        wire:click="revokeOtherSessions()"
                        wire:loading.attr="disabled"
                        wire:target="revokeOtherSessions"
                    >
                        <span wire:loading.remove wire:target="revokeOtherSessions">Confirmar</span>
                        <span wire:loading wire:target="revokeOtherSessions">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         Card 3 — Política de senha (admin-only)
         ========================================================================= --}}
    <div class="settings-card mb-3">
        <div class="d-flex align-items-start justify-content-between mb-1">
            <div>
                <h6 class="settings-card-title mb-0">Política de senha do escritório</h6>
                <p class="settings-card-description mb-0 mt-1">
                    Define requisitos mínimos de senha para todos os membros da equipe.
                </p>
            </div>
            @cannot('manage-security-policy')
                <span class="ms-2 flex-shrink-0 badge bg-secondary rounded-pill fw-normal" style="font-size:.68rem;">
                    Somente administradores
                </span>
            @endcannot
        </div>

        <hr class="my-3" style="border-color: rgba(215,220,229,.4);">

        @can('manage-security-policy')

            {{-- Toggle de ativação --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <label class="fw-semibold small" for="policyEnabled">Política ativa</label>
                <div class="form-check form-switch mb-0">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        wire:model.live="policyEnabled"
                        id="policyEnabled"
                        role="switch"
                    >
                </div>
            </div>

            @if($policyEnabled)
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold" for="policyMinLength">
                            Mínimo de caracteres
                        </label>
                        <input
                            type="number"
                            wire:model="policyMinLength"
                            id="policyMinLength"
                            class="form-control form-control-sm @error('policyMinLength') is-invalid @enderror"
                            min="6"
                            max="64"
                        >
                        @error('policyMinLength')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold" for="policyExpiry">
                            Expirar senha em (dias)
                        </label>
                        <select wire:model="policyExpiry" id="policyExpiry" class="form-select form-select-sm">
                            <option value="0">Nunca</option>
                            <option value="30">30 dias</option>
                            <option value="60">60 dias</option>
                            <option value="90">90 dias</option>
                            <option value="180">180 dias</option>
                        </select>
                    </div>
                </div>

                <div class="policy-check-group mb-3">
                    <div class="policy-check-label">Requisitos obrigatórios</div>

                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            wire:model="policyUppercase"
                            id="policyUppercase"
                        >
                        <label class="form-check-label small" for="policyUppercase">
                            Letra maiúscula (A-Z)
                        </label>
                    </div>
                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            wire:model="policyNumbers"
                            id="policyNumbers"
                        >
                        <label class="form-check-label small" for="policyNumbers">
                            Número (0-9)
                        </label>
                    </div>
                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            wire:model="policySymbols"
                            id="policySymbols"
                        >
                        <label class="form-check-label small" for="policySymbols">
                            Símbolo especial (!@#$…)
                        </label>
                    </div>
                </div>
            @endif

            <button
                type="button"
                class="btn btn-primary rounded-pill btn-sm"
                wire:click="savePasswordPolicy()"
                wire:loading.attr="disabled"
                wire:target="savePasswordPolicy"
            >
                <span wire:loading.remove wire:target="savePasswordPolicy">
                    <i class="bi bi-floppy me-1"></i>Salvar política
                </span>
                <span wire:loading wire:target="savePasswordPolicy">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>Salvando…
                </span>
            </button>

        @else

            {{-- Visualização somente leitura --}}
            @if($policyEnabled)
                <div class="policy-readonly">
                    <div class="row g-2">
                        <div class="col-auto">
                            <span class="policy-tag"><i class="bi bi-check2 me-1"></i>Mín. {{ $policyMinLength }} caracteres</span>
                        </div>
                        @if($policyUppercase)
                            <div class="col-auto"><span class="policy-tag"><i class="bi bi-check2 me-1"></i>Maiúscula</span></div>
                        @endif
                        @if($policyNumbers)
                            <div class="col-auto"><span class="policy-tag"><i class="bi bi-check2 me-1"></i>Número</span></div>
                        @endif
                        @if($policySymbols)
                            <div class="col-auto"><span class="policy-tag"><i class="bi bi-check2 me-1"></i>Símbolo</span></div>
                        @endif
                        @if($policyExpiry > 0)
                            <div class="col-auto"><span class="policy-tag"><i class="bi bi-clock me-1"></i>Expira em {{ $policyExpiry }} dias</span></div>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-secondary small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Nenhuma política de senha configurada para este escritório.
                </p>
            @endif

        @endcan
    </div>

    {{-- =========================================================================
         Card 4 — LGPD / Conta
         ========================================================================= --}}
    <div class="danger-zone">
        <h6 class="settings-card-title text-danger mb-1">
            <i class="bi bi-exclamation-triangle me-1"></i>Zona de risco
        </h6>
        <p class="settings-card-description mb-3">
            Ações irreversíveis relacionadas à privacidade e exclusão de dados (LGPD).
        </p>

        {{-- Exportar dados --}}
        <div class="lgpd-row mb-3">
            <div class="flex-grow-1">
                <div class="fw-semibold small">Exportar meus dados</div>
                <div class="text-secondary" style="font-size:.78rem;">
                    Solicite uma cópia de todos os seus dados pessoais armazenados na plataforma.
                    O arquivo será preparado em até 15 dias úteis.
                </div>
            </div>

            @if(!$exportRequested)
                <button
                    type="button"
                    class="btn btn-outline-secondary rounded-pill btn-sm flex-shrink-0"
                    wire:click="requestDataExport()"
                    wire:loading.attr="disabled"
                    wire:target="requestDataExport"
                >
                    <i class="bi bi-download me-1"></i>Solicitar exportação
                </button>
            @else
                <span class="text-success small d-inline-flex align-items-center gap-1 flex-shrink-0">
                    <i class="bi bi-check-circle-fill"></i>
                    Solicitação registrada
                </span>
            @endif
        </div>

        {{-- Excluir conta --}}
        <div class="lgpd-row">
            <div class="flex-grow-1">
                <div class="fw-semibold small text-danger">Excluir minha conta</div>
                <div class="text-secondary" style="font-size:.78rem;">
                    Remove permanentemente seu acesso ao escritório e inicia o processo de exclusão
                    dos seus dados em até 30 dias. <strong>Esta ação não pode ser desfeita.</strong>
                </div>
            </div>

            <button
                type="button"
                class="btn btn-danger rounded-pill btn-sm flex-shrink-0"
                wire:click="$set('deleteConfirm', true)"
            >
                <i class="bi bi-person-x me-1"></i>Excluir conta
            </button>
        </div>

        {{-- Confirmação de exclusão --}}
        <div wire:show="deleteConfirm" class="mt-3 pt-3" style="border-top: 1px solid rgba(185,28,28,.2);">
            <div class="alert alert-danger rounded-3 small mb-3 d-flex gap-2">
                <i class="bi bi-exclamation-octagon-fill mt-1 flex-shrink-0"></i>
                <span>
                    Você está prestes a <strong>excluir sua conta</strong>. Isso encerrará seu acesso ao escritório
                    imediatamente. Confirme sua senha para continuar.
                </span>
            </div>

            <div class="d-flex align-items-start gap-2">
                <div style="max-width: 260px;" class="flex-grow-1">
                    <input
                        wire:model="deletePassword"
                        type="password"
                        class="form-control form-control-sm @error('deletePassword') is-invalid @enderror"
                        placeholder="Confirme sua senha"
                        autocomplete="current-password"
                    >
                    @error('deletePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button
                    type="button"
                    class="btn btn-danger rounded-pill btn-sm"
                    wire:click="deleteAccount()"
                    wire:loading.attr="disabled"
                    wire:target="deleteAccount"
                >
                    <span wire:loading.remove wire:target="deleteAccount">Confirmar exclusão</span>
                    <span wire:loading wire:target="deleteAccount">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </span>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary rounded-pill btn-sm"
                    wire:click="$set('deleteConfirm', false)"
                >
                    Cancelar
                </button>
            </div>
        </div>

    </div>{{-- /danger-zone --}}

</div>{{-- /x-data --}}

@assets
<style>
/* ── Security badges ────────────────────────────────────────────────────── */
.sec-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    white-space: nowrap;
}
.sec-badge--on  { background: rgba(5,150,105,.1);  color: #059669; }
.sec-badge--off { background: rgba(107,114,128,.1);color: #6b7280; }
[data-theme="dark"] .sec-badge--on  { background: rgba(5,150,105,.2);  color: #6ee7b7; }
[data-theme="dark"] .sec-badge--off { background: rgba(107,114,128,.2);color: #9ca3af; }

/* ── 2FA icon wrappers ──────────────────────────────────────────────────── */
.sec-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(107,114,128,.08);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.sec-icon-wrap--success { background: rgba(5,150,105,.1); }

/* ── QR Code wrapper ────────────────────────────────────────────────────── */
.qr-code-wrapper {
    width: 200px;
    height: 200px;
    border: 1px solid var(--jusai-border, #d7dce5);
    border-radius: 1rem;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    padding: 4px;
}
.qr-code-wrapper svg {
    width: 100%;
    height: 100%;
    display: block;
}
.qr-code-placeholder {
    color: var(--jusai-muted, #9ca3af);
}
[data-theme="dark"] .qr-code-wrapper {
    border-color: rgba(255,255,255,.1);
    background: #fff; /* QR precisa de fundo branco */
}

/* ── Setup steps ────────────────────────────────────────────────────────── */
.setup-steps {
    padding-left: 1.25rem;
    font-size: .875rem;
    color: var(--jusai-graphite, #1f2937);
}
.setup-steps li { margin-bottom: .5rem; }
[data-theme="dark"] .setup-steps { color: rgba(255,255,255,.8); }

.manual-key-box {
    display: flex;
    align-items: center;
    gap: .5rem;
    background: rgba(37,99,235,.04);
    border: 1px solid rgba(37,99,235,.12);
    border-radius: .6rem;
    padding: .5rem .75rem;
}
[data-theme="dark"] .manual-key-box {
    background: rgba(37,99,235,.08);
    border-color: rgba(96,165,250,.18);
}

/* ── Recovery codes grid ────────────────────────────────────────────────── */
.recovery-codes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: .5rem;
}
.recovery-code-item {
    display: block;
    background: rgba(37,99,235,.04);
    border: 1px solid rgba(37,99,235,.12);
    border-radius: .5rem;
    padding: .45rem .75rem;
    font-size: .875rem;
    font-weight: 600;
    letter-spacing: .05em;
    text-align: center;
    color: var(--jusai-graphite, #1f2937);
}
[data-theme="dark"] .recovery-code-item {
    background: rgba(96,165,250,.06);
    border-color: rgba(96,165,250,.16);
    color: rgba(255,255,255,.85);
}

/* ── Session row ────────────────────────────────────────────────────────── */
.session-row {
    display: flex;
    align-items: center;
    gap: .875rem;
    padding: .875rem 1rem;
    border: 1px solid var(--jusai-border, #d7dce5);
    border-radius: .875rem;
    background: rgba(255,255,255,.5);
}
.session-device-icon {
    width: 40px;
    height: 40px;
    border-radius: .75rem;
    background: rgba(37,99,235,.07);
    color: var(--jusai-action, #2563eb);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
[data-theme="dark"] .session-row {
    border-color: rgba(255,255,255,.08);
    background: rgba(255,255,255,.03);
}
[data-theme="dark"] .session-device-icon {
    background: rgba(96,165,250,.1);
    color: #60a5fa;
}

/* ── Password policy ────────────────────────────────────────────────────── */
.policy-check-group {
    border: 1px solid var(--jusai-border, #d7dce5);
    border-radius: .75rem;
    padding: .875rem 1rem;
    display: flex;
    flex-direction: column;
    gap: .4rem;
}
.policy-check-label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--jusai-muted, #6b7280);
    margin-bottom: .25rem;
}
[data-theme="dark"] .policy-check-group {
    border-color: rgba(255,255,255,.08);
}
[data-theme="dark"] .policy-check-label {
    color: rgba(255,255,255,.3);
}

.policy-readonly { }
.policy-tag {
    display: inline-flex;
    align-items: center;
    padding: .2rem .6rem;
    border-radius: 999px;
    background: rgba(5,150,105,.08);
    color: #059669;
    font-size: .72rem;
    font-weight: 600;
}
[data-theme="dark"] .policy-tag {
    background: rgba(5,150,105,.15);
    color: #6ee7b7;
}

/* ── LGPD rows ──────────────────────────────────────────────────────────── */
.lgpd-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
</style>
@endassets
