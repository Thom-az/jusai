<div>

{{-- ============================================================
     Banner view-only (sem manage-firm)
     ============================================================ --}}
@unless($canEdit)
    <div class="alert settings-viewonly-banner d-flex align-items-center gap-2 mb-4 rounded-3 border-0">
        <i class="bi bi-eye" aria-hidden="true"></i>
        <span>Você está em modo de visualização. Fale com o administrador do escritório para editar estes dados.</span>
    </div>
@endunless


@error('geral')
    <div class="alert alert-danger rounded-3 border-0 mb-4">{{ $message }}</div>
@enderror


{{-- ============================================================
     CARD 1: Identidade e logotipo
     ============================================================ --}}
<div class="settings-card">
    <div class="settings-card-title">Identidade</div>
    <div class="settings-card-description">Logo, nome fantasia e razão social do escritório.</div>

    {{-- Logos --}}
    <div class="row g-3 mb-4">

        {{-- Logo modo claro --}}
        <div class="col-md-6">
            <label class="form-label fw-medium">Logotipo (modo claro)</label>
            <div class="logo-upload-area rounded-3 d-flex flex-column align-items-center justify-content-center gap-2 p-3"
                 style="border: 2px dashed var(--jusai-border); min-height: 120px; cursor: {{ $canEdit ? 'pointer' : 'default' }}; transition: border-color 200ms;"
                 @if($canEdit) @click="$refs.logoInput.click()" @endif
                 @mouseenter="if(@js($canEdit)) $event.target.style.borderColor='var(--jusai-action)'"
                 @mouseleave="$event.target.style.borderColor='var(--jusai-border)'">
                <template x-if="logoPreview">
                    <img :src="logoPreview" alt="Logo" style="max-height:80px;max-width:100%;object-fit:contain;">
                </template>
                <template x-if="!logoPreview">
                    @if(Auth::user()->organization?->logo)
                        <img src="{{ asset('storage/' . Auth::user()->organization->logo) }}"
                             alt="Logotipo" style="max-height:80px;max-width:100%;object-fit:contain;">
                    @else
                        <i class="bi bi-image text-secondary" style="font-size: 2rem;"></i>
                        <span class="text-secondary small">{{ $canEdit ? 'Clique para carregar' : 'Sem logotipo' }}</span>
                    @endif
                </template>
            </div>
            @if($canEdit)
                <input type="file" wire:model="logoUpload" x-ref="logoInput"
                       accept="image/*" class="d-none"
                       @change="onLogoSelected($event, 'logo')">
                <div class="form-text">JPG, PNG, SVG ou WebP — máx. 3 MB</div>
            @endif
            @error('logoUpload') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Logo modo escuro --}}
        <div class="col-md-6">
            <label class="form-label fw-medium">Logotipo (modo escuro)
                <span class="text-secondary fw-normal small">(opcional)</span>
            </label>
            <div class="logo-upload-area rounded-3 d-flex flex-column align-items-center justify-content-center gap-2 p-3"
                 style="border: 2px dashed var(--jusai-border); min-height: 120px; background: #1a1a1a; cursor: {{ $canEdit ? 'pointer' : 'default' }};"
                 @if($canEdit) @click="$refs.logoDarkInput.click()" @endif>
                <template x-if="logoDarkPreview">
                    <img :src="logoDarkPreview" alt="Logo escuro" style="max-height:80px;max-width:100%;object-fit:contain;">
                </template>
                <template x-if="!logoDarkPreview">
                    @if(Auth::user()->organization?->logo_dark)
                        <img src="{{ asset('storage/' . Auth::user()->organization->logo_dark) }}"
                             alt="Logotipo modo escuro" style="max-height:80px;max-width:100%;object-fit:contain;">
                    @else
                        <i class="bi bi-image" style="font-size: 2rem; color: rgba(255,255,255,0.3);"></i>
                        <span class="small" style="color: rgba(255,255,255,0.35);">{{ $canEdit ? 'Versão para fundo escuro' : 'Sem logotipo escuro' }}</span>
                    @endif
                </template>
            </div>
            @if($canEdit)
                <input type="file" wire:model="logoDarkUpload" x-ref="logoDarkInput"
                       accept="image/*" class="d-none"
                       @change="onLogoSelected($event, 'dark')">
                <div class="form-text">Recomendado: versão branca ou clara do logotipo</div>
            @endif
            @error('logoDarkUpload') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Nome fantasia + Razão social --}}
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-medium" for="orgNome">
                <i class="bi bi-building me-1 text-secondary"></i>Nome fantasia <span class="text-danger">*</span>
            </label>
            <input type="text" id="orgNome" wire:model="name"
                   class="form-control rounded-3 @error('name') is-invalid @enderror"
                   placeholder="Como o escritório é conhecido"
                   @disabled(!$canEdit)>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-medium" for="orgRazao"><i class="bi bi-building me-1 text-secondary"></i>Razão social</label>
            <input type="text" id="orgRazao" wire:model="legalName"
                   class="form-control rounded-3 @error('legalName') is-invalid @enderror"
                   placeholder="Denominação formal"
                   @disabled(!$canEdit)>
            @error('legalName') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>


{{-- ============================================================
     CARD 2: Dados legais e contato
     ============================================================ --}}
<div class="settings-card">
    <div class="settings-card-title">Dados legais e contato</div>
    <div class="settings-card-description">CNPJ, telefone e email comercial do escritório.</div>

    <div class="row g-3">
        {{-- CNPJ --}}
        <div class="col-md-4">
            <label class="form-label fw-medium" for="orgCnpj">
                <i class="bi bi-receipt me-1 text-secondary"></i>CNPJ <span class="text-danger">*</span>
            </label>
            <input
                type="text"
                id="orgCnpj"
                wire:model="document"
                @input="let _v = maskCnpj($event.target.value); $event.target.value = _v"
                class="form-control rounded-3 @error('document') is-invalid @enderror"
                placeholder="12.345.678/0001-90"
                maxlength="18"
                @disabled(!$canEdit)
            >
            @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Telefone --}}
        <div class="col-md-4">
            <label class="form-label fw-medium" for="orgTelefone"><i class="bi bi-telephone me-1 text-secondary"></i>Telefone comercial</label>
            <input
                type="tel"
                id="orgTelefone"
                wire:model="phone"
                @input="let _v = maskPhone($event.target.value); $event.target.value = _v"
                class="form-control rounded-3 @error('phone') is-invalid @enderror"
                placeholder="(11) 3456-7890"
                maxlength="15"
                @disabled(!$canEdit)
            >
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Email --}}
        <div class="col-md-4">
            <label class="form-label fw-medium" for="orgEmail"><i class="bi bi-envelope me-1 text-secondary"></i>Email comercial</label>
            <input
                type="email"
                id="orgEmail"
                wire:model="email"
                class="form-control rounded-3 @error('email') is-invalid @enderror"
                placeholder="contato@escritorio.adv.br"
                @disabled(!$canEdit)
            >
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>


{{-- ============================================================
     CARD 3: Endereço (com auto-preenchimento via ViaCEP)
     ============================================================ --}}
<div class="settings-card">
    <div class="settings-card-title">Endereço</div>
    <div class="settings-card-description">Localização física do escritório.</div>

    <div class="row g-3">
        {{-- CEP --}}
        <div class="col-md-3">
            <label class="form-label fw-medium" for="orgCep"><i class="bi bi-geo-alt me-1 text-secondary"></i>CEP</label>
            <div class="input-group">
                <input
                    type="text"
                    id="orgCep"
                    wire:model="zipCode"
                    @input="let _v = maskCep($event.target.value); $event.target.value = _v; maybeFetchCep(_v)"
                    class="form-control rounded-start-3 @error('zipCode') is-invalid @enderror"
                    placeholder="00000-000"
                    maxlength="9"
                    @disabled(!$canEdit)
                >
                <span class="input-group-text" style="border-radius: 0 0.75rem 0.75rem 0;">
                    <span x-show="cepLoading" class="spinner-border spinner-border-sm text-secondary"></span>
                    <i x-show="!cepLoading && cepOk" class="bi bi-check-circle-fill text-success"></i>
                    <i x-show="!cepLoading && cepError" class="bi bi-x-circle-fill text-danger"></i>
                    <i x-show="!cepLoading && !cepOk && !cepError" class="bi bi-geo-alt text-secondary"></i>
                </span>
            </div>
            @error('zipCode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            <div class="form-text" x-show="cepError" style="color: var(--jusai-danger);">CEP não encontrado.</div>
        </div>

        {{-- Logradouro --}}
        <div class="col-md-6">
            <label class="form-label fw-medium" for="orgRua"><i class="bi bi-signpost me-1 text-secondary"></i>Logradouro</label>
            <input type="text" id="orgRua" wire:model="street"
                   class="form-control rounded-3 @error('street') is-invalid @enderror"
                   placeholder="Rua, Avenida, Alameda..."
                   @disabled(!$canEdit)>
            @error('street') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Número --}}
        <div class="col-md-3">
            <label class="form-label fw-medium" for="orgNumero"><i class="bi bi-123 me-1 text-secondary"></i>Número</label>
            <input type="text" id="orgNumero" wire:model="streetNumber"
                   x-ref="streetNumber"
                   class="form-control rounded-3 @error('streetNumber') is-invalid @enderror"
                   placeholder="123 ou S/N"
                   @disabled(!$canEdit)>
            @error('streetNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Complemento --}}
        <div class="col-md-4">
            <label class="form-label fw-medium" for="orgComplemento">
                <i class="bi bi-building-add me-1 text-secondary"></i>Complemento <span class="text-secondary fw-normal small">(opcional)</span>
            </label>
            <input type="text" id="orgComplemento" wire:model="complement"
                   class="form-control rounded-3"
                   placeholder="Sala 201, Andar 5..."
                   @disabled(!$canEdit)>
        </div>

        {{-- Bairro --}}
        <div class="col-md-4">
            <label class="form-label fw-medium" for="orgBairro"><i class="bi bi-map me-1 text-secondary"></i>Bairro</label>
            <input type="text" id="orgBairro" wire:model="neighborhood"
                   class="form-control rounded-3 @error('neighborhood') is-invalid @enderror"
                   placeholder="Preenchido pelo CEP"
                   @disabled(!$canEdit)>
            @error('neighborhood') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Cidade + UF --}}
        <div class="col-md-3">
            <label class="form-label fw-medium" for="orgCidade"><i class="bi bi-geo me-1 text-secondary"></i>Cidade</label>
            <input type="text" id="orgCidade" wire:model="city"
                   class="form-control rounded-3 @error('city') is-invalid @enderror"
                   placeholder="Cidade"
                   @disabled(!$canEdit)>
            @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-1">
            <label class="form-label fw-medium" for="orgEstado"><i class="bi bi-map-fill me-1 text-secondary"></i>UF</label>
            <select id="orgEstado" wire:model="state"
                    class="form-select rounded-3 @error('state') is-invalid @enderror"
                    @disabled(!$canEdit)>
                <option value=""></option>
                @foreach($ufs as $uf)
                    <option value="{{ $uf }}">{{ $uf }}</option>
                @endforeach
            </select>
            @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>


{{-- ============================================================
     CARD 4: Áreas de atuação
     ============================================================ --}}
<div class="settings-card">
    <div class="settings-card-title">Áreas de atuação</div>
    <div class="settings-card-description">
        Selecione as áreas do direito em que o escritório atua.
        @if($canEdit) Clique para marcar ou desmarcar. @endif
    </div>

    <div class="d-flex flex-wrap gap-2">
        @foreach($availablePracticeAreas as $area)
            <button
                type="button"
                wire:click="{{ $canEdit ? 'toggleArea(\'' . $area . '\')' : '' }}"
                class="area-chip {{ in_array($area, $practiceAreas) ? 'active' : '' }}"
                @disabled(!$canEdit)
                aria-pressed="{{ in_array($area, $practiceAreas) ? 'true' : 'false' }}"
            >
                @if(in_array($area, $practiceAreas))
                    <i class="bi bi-check me-1" aria-hidden="true"></i>
                @endif
                {{ $area }}
            </button>
        @endforeach
    </div>

    @if($canEdit && count($practiceAreas) > 0)
        <div class="mt-3 text-secondary small">
            <i class="bi bi-check-circle-fill text-success me-1"></i>
            {{ count($practiceAreas) }} {{ count($practiceAreas) === 1 ? 'área selecionada' : 'áreas selecionadas' }}
        </div>
    @endif
</div>


{{-- ============================================================
     BOTÃO SALVAR
     ============================================================ --}}
@if($canEdit)
    <div class="d-flex align-items-center gap-3 pb-2">
        <button
            type="button"
            wire:click="salvar"
            wire:loading.attr="disabled"
            :disabled="!hasChanges"
            class="btn btn-primary rounded-pill px-4"
        >
            <span wire:loading.remove wire:target="salvar">
                <i class="bi bi-check2 me-1"></i> Salvar alterações
            </span>
            <span wire:loading wire:target="salvar">
                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                Salvando...
            </span>
        </button>
        <span x-show="!hasChanges" class="text-secondary small" style="display:none;">
            Nenhuma alteração pendente
        </span>
    </div>
@endif


{{-- Estilos dos chips de área e logo upload --}}
@assets
<style>
/* Chips de área de atuação */
.area-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.9rem;
    border-radius: 999px;
    border: 1.5px solid var(--jusai-border, #d7dce5);
    background: transparent;
    color: var(--jusai-muted, #6b7280);
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 150ms ease;
    line-height: 1.4;
}

.area-chip:hover:not(:disabled) {
    border-color: var(--jusai-action, #2563eb);
    color: var(--jusai-action, #2563eb);
    background: rgba(37,99,235,0.05);
}

.area-chip.active {
    background: rgba(37,99,235,0.1);
    border-color: var(--jusai-action, #2563eb);
    color: var(--jusai-action, #2563eb);
    font-weight: 600;
}

.area-chip:disabled {
    opacity: 0.55;
    cursor: default;
}

/* Dark mode — chips */
[data-theme="dark"] .area-chip {
    border-color: rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.5);
}

[data-theme="dark"] .area-chip:hover:not(:disabled) {
    border-color: #60a5fa;
    color: #60a5fa;
    background: rgba(96,165,250,0.08);
}

[data-theme="dark"] .area-chip.active {
    background: rgba(96,165,250,0.12);
    border-color: #60a5fa;
    color: #93c5fd;
}

/* Dark mode — logo upload area */
[data-theme="dark"] .logo-upload-area {
    border-color: rgba(255,255,255,0.12) !important;
}
</style>
@endassets

</div>
