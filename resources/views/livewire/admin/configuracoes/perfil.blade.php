<div>

{{-- ============================================================
     SEÇÃO: Foto e Informações Pessoais
     — sem x-data: scope herdado de perfilPageState() no wrapper
     ============================================================ --}}
<div class="settings-card">

    {{-- Banner de sucesso --}}
    <div
        x-show="perfilSaved"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-3 border-0"
        style="background: rgba(15, 118, 110, 0.1); color: #0f766e;"
        role="alert"
    >
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
        <span>Perfil atualizado com sucesso.</span>
    </div>

    {{-- AVATAR --}}
    <div class="d-flex align-items-center gap-4 mb-4">
        <div
            class="avatar-upload-wrap position-relative"
            @click="$refs.avatarInput.click()"
            role="button"
            tabindex="0"
            @keydown.enter="$refs.avatarInput.click()"
            title="Clique para alterar a foto"
            style="cursor: pointer;"
        >
            <div class="avatar-profile" style="width:80px;height:80px;font-size:1.6rem;border-radius:50%;overflow:hidden;flex-shrink:0;">
                <template x-if="perfilPreview">
                    <img :src="perfilPreview" alt="Preview" style="width:80px;height:80px;object-fit:cover;border-radius:50%;">
                </template>
                <template x-if="!perfilPreview">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                             alt="{{ auth()->user()->name }}"
                             style="width:80px;height:80px;object-fit:cover;border-radius:50%;">
                    @else
                        <div class="avatar-chip" style="width:80px;height:80px;font-size:1.6rem;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            {{ auth()->user()->initials ?? mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    @endif
                </template>
            </div>

            <div class="avatar-upload-overlay position-absolute inset-0 d-flex align-items-center justify-content-center rounded-circle"
                 style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.45);border-radius:50%;opacity:0;transition:opacity 150ms ease;">
                <i class="bi bi-camera-fill text-white" style="font-size:1.2rem;"></i>
            </div>
        </div>

        <div>
            <div class="fw-semibold mb-1">Foto de perfil</div>
            <div class="text-secondary small mb-2">JPG, PNG ou WebP — máx. 2 MB</div>
            <button type="button"
                    class="btn btn-outline-secondary btn-sm rounded-pill"
                    @click="$refs.avatarInput.click()">
                <i class="bi bi-upload me-1"></i> Alterar foto
            </button>
        </div>

        <input
            type="file"
            x-ref="avatarInput"
            wire:model="avatarUpload"
            accept="image/jpeg,image/png,image/webp"
            class="d-none"
            @change="onAvatarSelected($event)"
        >
    </div>

    @error('avatarUpload')
        <div class="text-danger small mb-3">{{ $message }}</div>
    @enderror

    <hr class="my-4" style="border-color: var(--jusai-border);">

    {{-- Nome --}}
    <div class="mb-3">
        <label class="form-label fw-medium" for="perfilNome">
            Nome completo <span class="text-danger">*</span>
        </label>
        <input
            type="text"
            id="perfilNome"
            wire:model="name"
            @input="markDirty()"
            class="form-control rounded-3 @error('name') is-invalid @enderror"
            placeholder="Seu nome completo"
            autocomplete="name"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Email --}}
    <div class="mb-3">
        <label class="form-label fw-medium" for="perfilEmail">
            Email <span class="text-danger">*</span>
        </label>
        <input
            type="email"
            id="perfilEmail"
            wire:model="email"
            @input="markDirty()"
            class="form-control rounded-3 @error('email') is-invalid @enderror"
            placeholder="seu@email.com.br"
            autocomplete="email"
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            <i class="bi bi-info-circle me-1"></i>
            Ao alterar o email, um link de confirmação será enviado para o novo endereço.
        </div>
    </div>

    {{-- Telefone + Cargo --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label fw-medium" for="perfilTelefone">Telefone</label>
            <input
                type="tel"
                id="perfilTelefone"
                wire:model="phone"
                @input="let _v = maskPhone($event.target.value); $event.target.value = _v; markDirty()"
                class="form-control rounded-3 @error('phone') is-invalid @enderror"
                placeholder="(11) 91234-5678"
                maxlength="15"
                autocomplete="tel"
            >
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label fw-medium" for="perfilCargo">Cargo no escritório</label>
            <select
                id="perfilCargo"
                wire:model="jobTitle"
                @change="markDirty()"
                class="form-select rounded-3 @error('jobTitle') is-invalid @enderror"
            >
                @foreach($jobTitles as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('jobTitle')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- OAB --}}
    <div class="row g-3 mb-4">
        <div class="col-md-5">
            <label class="form-label fw-medium" for="perfilOab">
                Nº da OAB
                <span class="text-secondary fw-normal small">(opcional)</span>
            </label>
            <div class="input-group">
                <input
                    type="text"
                    id="perfilOab"
                    wire:model="oabNumber"
                    @input="let _v = maskOabNumber($event.target.value); $event.target.value = _v; markDirty()"
                    class="form-control rounded-start-3 @error('oabNumber') is-invalid @enderror"
                    placeholder="123.456"
                    maxlength="7"
                >
                <span class="input-group-text" style="border-radius:0;">/</span>
                <select
                    wire:model="oabUf"
                    @change="markDirty()"
                    class="form-select @error('oabUf') is-invalid @enderror"
                    style="border-radius: 0 0.75rem 0.75rem 0; max-width: 90px;"
                    aria-label="UF da OAB"
                >
                    <option value="">UF</option>
                    @foreach($ufs as $uf)
                        <option value="{{ $uf }}">{{ $uf }}</option>
                    @endforeach
                </select>
            </div>
            @error('oabNumber')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            <div class="form-text">Formato: 123.456/SP</div>
        </div>
    </div>

    {{-- Botão salvar --}}
    <div class="d-flex align-items-center gap-3">
        <button
            type="button"
            wire:click="salvarPerfil"
            wire:loading.attr="disabled"
            :disabled="!perfilDirty && !perfilAvatarChanged"
            class="btn btn-primary rounded-pill px-4"
        >
            <span wire:loading.remove wire:target="salvarPerfil">
                <i class="bi bi-check2 me-1"></i> Salvar alterações
            </span>
            <span wire:loading wire:target="salvarPerfil">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Salvando...
            </span>
        </button>

        <span
            x-show="!perfilDirty && !perfilAvatarChanged"
            class="text-secondary small"
            style="display:none;"
        >
            Nenhuma alteração pendente
        </span>
    </div>

</div>{{-- /settings-card perfil --}}


{{-- ============================================================
     SEÇÃO: Alterar Senha
     — sem x-data: scope herdado de perfilPageState() no wrapper
     ============================================================ --}}
<div class="settings-card">

    <div class="settings-card-title">Alterar senha</div>
    <div class="settings-card-description">
        Escolha uma senha forte com ao menos 8 caracteres, letra maiúscula, número e símbolo.
    </div>

    {{-- Banner de sucesso --}}
    <div
        x-show="senhaSaved"
        x-transition
        class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-3 border-0"
        style="background: rgba(15, 118, 110, 0.1); color: #0f766e;"
        role="alert"
    >
        <i class="bi bi-check-circle-fill"></i>
        <span>Senha alterada com sucesso.</span>
    </div>

    <form @submit.prevent>
    {{-- Senha atual --}}
    <div class="mb-3" style="max-width: 400px;">
        <label class="form-label fw-medium" for="senhaAtual">Senha atual</label>
        <div class="input-group">
            <input
                :type="showCurrent ? 'text' : 'password'"
                id="senhaAtual"
                wire:model="currentPassword"
                class="form-control rounded-start-3 @error('currentPassword') is-invalid @enderror"
                placeholder="Sua senha atual"
                autocomplete="current-password"
            >
            <button type="button"
                    class="btn btn-outline-secondary"
                    @click="showCurrent = !showCurrent"
                    :title="showCurrent ? 'Ocultar senha' : 'Mostrar senha'"
                    style="border-radius: 0 0.75rem 0.75rem 0;">
                <i :class="showCurrent ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
        </div>
        @error('currentPassword')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Nova senha --}}
    <div class="mb-2" style="max-width: 400px;">
        <label class="form-label fw-medium" for="novaSenha">Nova senha</label>
        <div class="input-group">
            <input
                :type="showNew ? 'text' : 'password'"
                id="novaSenha"
                wire:model="newPassword"
                x-on:input="newPassword = $event.target.value; updateStrength()"
                class="form-control rounded-start-3 @error('newPassword') is-invalid @enderror"
                placeholder="Mínimo 8 caracteres"
                autocomplete="new-password"
            >
            <button type="button"
                    class="btn btn-outline-secondary"
                    @click="showNew = !showNew"
                    :title="showNew ? 'Ocultar senha' : 'Mostrar senha'"
                    style="border-radius: 0 0.75rem 0.75rem 0;">
                <i :class="showNew ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
        </div>
        @error('newPassword')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Indicador de força --}}
    <div class="mb-3" style="max-width: 400px;" x-show="newPassword.length > 0" x-cloak>
        <div class="d-flex gap-1 mb-1">
            <template x-for="i in 4">
                <div
                    class="rounded-pill"
                    style="height: 4px; flex: 1; transition: background 200ms ease;"
                    :style="`background: ${i <= strength.score ? strength.color : 'var(--jusai-border, #d7dce5)'}`"
                ></div>
            </template>
        </div>
        <div class="d-flex align-items-center gap-2">
            <small :style="`color: ${strength.color}`" x-text="strength.label" class="fw-medium"></small>
            <small class="text-secondary">
                — <span x-text="strength.hint"></span>
            </small>
        </div>
    </div>

    {{-- Confirmar senha --}}
    <div class="mb-4" style="max-width: 400px;">
        <label class="form-label fw-medium" for="confirmarSenha">Confirmar nova senha</label>
        <div class="input-group">
            <input
                :type="showConfirm ? 'text' : 'password'"
                id="confirmarSenha"
                wire:model="newPasswordConfirmation"
                class="form-control rounded-start-3 @error('newPasswordConfirmation') is-invalid @enderror"
                placeholder="Repita a nova senha"
                autocomplete="new-password"
            >
            <button type="button"
                    class="btn btn-outline-secondary"
                    @click="showConfirm = !showConfirm"
                    :title="showConfirm ? 'Ocultar senha' : 'Mostrar senha'"
                    style="border-radius: 0 0.75rem 0.75rem 0;">
                <i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
        </div>
        @error('newPasswordConfirmation')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Requisitos --}}
    <div class="mb-4 d-flex flex-wrap gap-2" style="max-width: 400px;">
        <span class="badge rounded-pill px-3 py-2"
              :class="req.length ? 'text-bg-success' : 'text-bg-light'"
              style="font-size: 0.75rem; font-weight:500;">
            <i class="bi" :class="req.length ? 'bi-check' : 'bi-dot'"></i> 8+ caracteres
        </span>
        <span class="badge rounded-pill px-3 py-2"
              :class="req.upper ? 'text-bg-success' : 'text-bg-light'"
              style="font-size: 0.75rem; font-weight:500;">
            <i class="bi" :class="req.upper ? 'bi-check' : 'bi-dot'"></i> Maiúscula
        </span>
        <span class="badge rounded-pill px-3 py-2"
              :class="req.number ? 'text-bg-success' : 'text-bg-light'"
              style="font-size: 0.75rem; font-weight:500;">
            <i class="bi" :class="req.number ? 'bi-check' : 'bi-dot'"></i> Número
        </span>
        <span class="badge rounded-pill px-3 py-2"
              :class="req.symbol ? 'text-bg-success' : 'text-bg-light'"
              style="font-size: 0.75rem; font-weight:500;">
            <i class="bi" :class="req.symbol ? 'bi-check' : 'bi-dot'"></i> Símbolo
        </span>
    </div>

    {{-- Botão salvar senha --}}
    <button
        type="submit"
        wire:click="salvarSenha"
        wire:loading.attr="disabled"
        class="btn btn-primary rounded-pill px-4"
    >
        <span wire:loading.remove wire:target="salvarSenha">
            <i class="bi bi-shield-lock me-1"></i> Alterar senha
        </span>
        <span wire:loading wire:target="salvarSenha">
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            Salvando...
        </span>
    </button>
    </form>

</div>{{-- /settings-card senha --}}


{{-- Estilos específicos da seção Perfil --}}
@assets
<style>
.avatar-upload-wrap:hover .avatar-upload-overlay,
.avatar-upload-wrap:focus .avatar-upload-overlay {
    opacity: 1 !important;
}
[data-theme="dark"] .text-bg-light {
    background: rgba(255,255,255,0.08) !important;
    color: rgba(255,255,255,0.55) !important;
}
[data-theme="dark"] .input-group-text {
    background: rgba(255,255,255,0.04);
    border-color: rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.55);
}
[data-theme="dark"] .form-control,
[data-theme="dark"] .form-select {
    background: rgba(255,255,255,0.04);
    border-color: rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.88);
}
[data-theme="dark"] .form-control::placeholder { color: rgba(255,255,255,0.28); }
[data-theme="dark"] .form-control:focus,
[data-theme="dark"] .form-select:focus {
    background: rgba(255,255,255,0.06);
    border-color: rgba(37,99,235,0.5);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
    color: rgba(255,255,255,0.92);
}
[data-theme="dark"] .alert-success {
    background: rgba(15,118,110,0.12) !important;
    color: #34d399 !important;
}
[data-theme="dark"] hr {
    border-color: rgba(255,255,255,0.07) !important;
}
</style>
@endassets

</div>
