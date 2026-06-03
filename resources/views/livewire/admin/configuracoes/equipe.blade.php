<div>

    {{-- =====================================================================
         Toast de notificação (sticky, topo da seção)
         ===================================================================== --}}
    <div
        x-show="toast.show"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        :class="{
            'alert-success border-success-subtle': toast.type === 'success',
            'alert-warning border-warning-subtle': toast.type === 'warning',
            'alert-danger  border-danger-subtle':  toast.type === 'danger',
        }"
        class="alert d-flex align-items-center gap-2 rounded-3 shadow-sm mb-3"
        role="alert"
        style="position: sticky; top: 1rem; z-index: 100;"
    >
        <i class="bi fs-6" :class="{
            'bi-check-circle-fill text-success': toast.type === 'success',
            'bi-exclamation-triangle-fill text-warning': toast.type === 'warning',
            'bi-x-circle-fill text-danger': toast.type === 'danger',
        }"></i>
        <span x-text="toast.message" class="flex-grow-1 small fw-semibold"></span>
        <button type="button" class="btn-close btn-close-sm" @click="toast.show = false" aria-label="Fechar"></button>
    </div>

    {{-- =====================================================================
         Estatísticas
         ===================================================================== --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="team-stat-card">
                <div class="team-stat-number">{{ $this->stats['total'] }}</div>
                <div class="team-stat-label">Total de membros</div>
            </div>
        </div>
        <div class="col-4">
            <div class="team-stat-card">
                <div class="team-stat-number" style="color: #059669;">{{ $this->stats['active'] }}</div>
                <div class="team-stat-label">Ativos</div>
            </div>
        </div>
        <div class="col-4">
            <div class="team-stat-card">
                <div class="team-stat-number text-secondary">{{ $this->stats['inactive'] }}</div>
                <div class="team-stat-label">Inativos</div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         Toolbar
         ===================================================================== --}}
    <div class="settings-card mb-3" style="padding: 0.875rem 1.25rem;">
        <div class="d-flex align-items-center gap-2 flex-wrap">

            {{-- Busca --}}
            <div class="flex-grow-1" style="min-width: 180px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text border-end-0 bg-transparent ps-2">
                        <i class="bi bi-search text-secondary" style="font-size:.8rem;"></i>
                    </span>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        class="form-control form-control-sm border-start-0 ps-1"
                        placeholder="Buscar por nome ou e-mail…"
                        autocomplete="off"
                    >
                </div>
            </div>

            {{-- Filtro: Perfil --}}
            <div style="min-width: 145px;">
                <select wire:model.live="filterRole" class="form-select form-select-sm">
                    <option value="">Todos os perfis</option>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filtro: Status --}}
            <div style="min-width: 120px;">
                <select wire:model.live="filterStatus" class="form-select form-select-sm">
                    <option value="">Todos status</option>
                    <option value="active">Ativos</option>
                    <option value="inactive">Inativos</option>
                </select>
            </div>

            {{-- Indicador de carregamento --}}
            <div wire:loading wire:target="search,filterRole,filterStatus" class="spinner-border spinner-border-sm text-secondary" role="status"></div>

            {{-- Botão convidar --}}
            @if($canManage)
                <button
                    type="button"
                    wire:click="openInvite()"
                    class="btn btn-primary btn-sm rounded-pill d-inline-flex align-items-center gap-1 flex-shrink-0"
                >
                    <i class="bi bi-person-plus"></i>
                    <span>Convidar membro</span>
                </button>
            @endif
        </div>
    </div>

    {{-- =====================================================================
         Lista de usuários
         ===================================================================== --}}
    <div class="settings-card p-0 overflow-hidden">
        @forelse($this->users as $user)
            @php
                $role      = $user->roles->first()?->name ?? 'default';
                $roleLabel = $roles[$role] ?? ucfirst($role);
                $initials  = collect(explode(' ', $user->name))
                    ->filter()
                    ->take(2)
                    ->map(fn ($w) => strtoupper($w[0]))
                    ->implode('');
            @endphp

            <div
                class="settings-user-row team-user-row px-4"
                wire:key="user-{{ $user->id }}"
                wire:click="openDrawer('{{ $user->id }}')"
                @click="$dispatch('open-drawer', { id: 'drawerUsuario' })"
                role="button"
                tabindex="0"
                @keydown.enter="$el.click()"
                title="Ver detalhes de {{ $user->name }}"
            >
                {{-- Avatar --}}
                <div class="team-avatar role--{{ $role }}">
                    @if($user->avatar)
                        <img
                            src="{{ Storage::url($user->avatar) }}"
                            alt="{{ $user->name }}"
                            loading="lazy"
                        >
                    @else
                        {{ $initials }}
                    @endif
                </div>

                {{-- Nome + Email --}}
                <div class="flex-grow-1 min-width-0">
                    <div class="fw-semibold text-truncate" style="font-size:.875rem; line-height:1.3;">
                        {{ $user->name }}
                    </div>
                    <div class="text-secondary text-truncate" style="font-size:.8rem;">
                        {{ $user->email }}
                    </div>
                </div>

                {{-- Cargo (médio+) --}}
                @if($user->job_title)
                    <div class="d-none d-lg-block text-secondary text-truncate flex-shrink-0"
                         style="font-size:.78rem; max-width: 130px;">
                        {{ $user->job_title }}
                    </div>
                @endif

                {{-- Perfil badge --}}
                <div class="d-none d-sm-flex flex-shrink-0">
                    <span class="team-role-badge role--{{ $role }}">{{ $roleLabel }}</span>
                </div>

                {{-- Status --}}
                <div class="flex-shrink-0">
                    @if($user->is_active)
                        <span class="team-status-badge status--active">
                            <span class="team-status-dot"></span>Ativo
                        </span>
                    @else
                        <span class="team-status-badge status--inactive">
                            <span class="team-status-dot"></span>Inativo
                        </span>
                    @endif
                </div>

                {{-- Seta --}}
                <div class="flex-shrink-0 text-secondary" style="opacity: .35; font-size: .75rem;">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        @empty
            <div class="py-5 px-4">
                <x-empty-state
                    icon="bi-people"
                    title="{{ ($search || $filterRole || $filterStatus) ? 'Nenhum membro encontrado' : 'Nenhum membro na equipe' }}"
                    description="{{ ($search || $filterRole || $filterStatus) ? 'Tente ajustar os filtros.' : ($canManage ? 'Convide colegas para colaborar nos casos e documentos.' : 'Nenhum membro cadastrado ainda.') }}"
                />
                @if(!$search && !$filterRole && !$filterStatus && $canManage)
                    <div class="text-center mt-3">
                        <button
                            type="button"
                            wire:click="openInvite()"
                            class="btn btn-primary rounded-pill btn-sm d-inline-flex align-items-center gap-1"
                        >
                            <i class="bi bi-person-plus"></i> Convidar membro
                        </button>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    @if($this->users->hasPages())
        <div class="mt-3">
            {{ $this->users->links('pagination::bootstrap-5') }}
        </div>
    @endif

    {{-- =====================================================================
         Modal de Convite
         ===================================================================== --}}
    <div
        class="modal fade"
        id="modalConvite"
        x-ref="modalConvite"
        tabindex="-1"
        aria-labelledby="modalConviteLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content jusai-modal-content">

                {{-- Header --}}
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold fs-6" id="modalConviteLabel">
                        @if($inviteDone)
                            <i class="bi bi-person-check-fill text-success me-1"></i>
                            Membro adicionado com sucesso
                        @else
                            <i class="bi bi-person-plus me-1"></i>
                            Convidar novo membro
                        @endif
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                        wire:click="resetInvite()"
                    ></button>
                </div>

                {{-- Body --}}
                <div class="modal-body pt-3 pb-2">

                    {{-- ─── Formulário ────────────────────────────────── --}}
                    @if(!$inviteDone)

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Nome completo <span class="text-danger">*</span>
                            </label>
                            <input
                                wire:model="inviteName"
                                type="text"
                                class="form-control @error('inviteName') is-invalid @enderror"
                                placeholder="Ex.: Maria Santos"
                                autocomplete="off"
                            >
                            @error('inviteName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                E-mail profissional <span class="text-danger">*</span>
                            </label>
                            <input
                                wire:model="inviteEmail"
                                type="email"
                                class="form-control @error('inviteEmail') is-invalid @enderror"
                                placeholder="nome@escritorio.com.br"
                                autocomplete="off"
                            >
                            @error('inviteEmail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-7">
                                <label class="form-label small fw-semibold">
                                    Perfil de acesso <span class="text-danger">*</span>
                                </label>
                                <select
                                    wire:model.live="inviteRole"
                                    class="form-select @error('inviteRole') is-invalid @enderror"
                                >
                                    @foreach($roles as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('inviteRole')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-semibold">
                                    Cargo <span class="text-secondary fw-normal">(opcional)</span>
                                </label>
                                <input
                                    wire:model="inviteJobTitle"
                                    type="text"
                                    class="form-control"
                                    placeholder="Ex.: Associado"
                                    maxlength="100"
                                >
                            </div>
                        </div>

                        {{-- Descrição do perfil --}}
                        <div class="invite-role-hint">
                            @switch($inviteRole)
                                @case('admin')
                                    <i class="bi bi-shield-check text-danger me-1"></i>
                                    Acesso <strong>completo</strong> — gerencia equipe, escritório, plano e configurações.
                                    @break
                                @case('socio')
                                    <i class="bi bi-star text-purple me-1" style="color:#7c3aed;"></i>
                                    Acesso a casos, documentos, análise de IA e visualização de equipe e plano.
                                    @break
                                @case('advogado')
                                    <i class="bi bi-briefcase text-primary me-1"></i>
                                    Gerencia casos e documentos próprios e usa análise de IA.
                                    @break
                                @case('estagiario')
                                    <i class="bi bi-mortarboard text-info me-1"></i>
                                    Colabora em casos próprios com acesso limitado à análise de IA.
                                    @break
                                @case('secretario')
                                    <i class="bi bi-calendar3 text-success me-1"></i>
                                    Gerencia agenda e atribuição de casos; visualiza lista de membros.
                                    @break
                                @case('financeiro')
                                    <i class="bi bi-receipt text-warning me-1"></i>
                                    Acesso exclusivo ao plano e ao faturamento do escritório.
                                    @break
                                @default
                                    <i class="bi bi-person me-1"></i>
                                    Selecione um perfil para ver as permissões.
                            @endswitch
                        </div>

                    {{-- ─── Sucesso: credenciais ──────────────────────── --}}
                    @else

                        <div class="text-center py-2 mb-3">
                            <div class="invite-success-icon mx-auto mb-3">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <h6 class="fw-semibold mb-1">{{ $inviteName }} foi adicionado!</h6>
                            <p class="text-secondary small mb-0">
                                Compartilhe as credenciais de acesso de forma segura com o novo membro.
                            </p>
                        </div>

                        <div class="mb-2">
                            <div class="small fw-semibold mb-1 text-secondary">E-mail de acesso</div>
                            <div class="temp-password-box">
                                <code class="flex-grow-1" style="font-size:.85rem; word-break:break-all;">
                                    {{ $inviteEmail }}
                                </code>
                            </div>
                        </div>

                        <div class="mb-3" x-data="{ copied: false }">
                            <div class="small fw-semibold mb-1 text-secondary">Senha temporária</div>
                            <div class="temp-password-box">
                                <code style="font-size:.875rem; letter-spacing:.08em; flex-grow:1;">
                                    {{ $tempPassword }}
                                </code>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 flex-shrink-0"
                                    style="font-size:.72rem;"
                                    @click="navigator.clipboard.writeText('{{ $tempPassword }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                >
                                    <i class="bi" :class="copied ? 'bi-check2' : 'bi-clipboard'"></i>
                                    <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
                                </button>
                            </div>
                        </div>

                        <p class="text-secondary mb-0" style="font-size:.75rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Peça que o membro altere a senha após o primeiro acesso.
                        </p>

                    @endif
                </div>

                {{-- Footer --}}
                <div class="modal-footer border-0 pt-0 gap-2">
                    @if(!$inviteDone)
                        <button
                            type="button"
                            class="btn btn-outline-secondary rounded-pill btn-sm"
                            data-bs-dismiss="modal"
                            wire:click="resetInvite()"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary rounded-pill btn-sm d-inline-flex align-items-center gap-1"
                            wire:click="convidar()"
                            wire:loading.attr="disabled"
                            wire:target="convidar"
                        >
                            <span wire:loading.remove wire:target="convidar">
                                <i class="bi bi-person-plus me-1"></i>Adicionar membro
                            </span>
                            <span wire:loading wire:target="convidar" class="d-inline-flex align-items-center gap-1">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Adicionando…
                            </span>
                        </button>
                    @else
                        <button
                            type="button"
                            class="btn btn-outline-secondary rounded-pill btn-sm d-inline-flex align-items-center gap-1"
                            wire:click="resetInvite()"
                        >
                            <i class="bi bi-person-plus"></i>Adicionar outro
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary rounded-pill btn-sm"
                            data-bs-dismiss="modal"
                            wire:click="resetInvite()"
                        >
                            Concluir
                        </button>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- =====================================================================
         Drawer de Usuário
         ===================================================================== --}}
    <div x-data @close-user-drawer.window="$dispatch('close-drawer', { id: 'drawerUsuario' })">

        @php $su = $this->selectedUser; @endphp

        <x-drawer
            id="drawerUsuario"
            :title="$su?->name ?? 'Detalhes do membro'"
            :subtitle="$su?->email"
            width="md"
        >
            @if($su)

                @php
                    $suRole      = $su->roles->first()?->name ?? 'default';
                    $suRoleLabel = $roles[$suRole] ?? ucfirst($suRole);
                    $suInitials  = collect(explode(' ', $su->name))
                        ->filter()
                        ->take(2)
                        ->map(fn ($w) => strtoupper($w[0]))
                        ->implode('');
                @endphp

                {{-- Cabeçalho do perfil --}}
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="team-avatar role--{{ $suRole }}" style="width:50px;height:50px;font-size:1.05rem;">
                        @if($su->avatar)
                            <img src="{{ Storage::url($su->avatar) }}" alt="{{ $su->name }}">
                        @else
                            {{ $suInitials }}
                        @endif
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-truncate">{{ $su->name }}</div>
                        @if($su->job_title)
                            <div class="text-secondary" style="font-size:.78rem;">{{ $su->job_title }}</div>
                        @endif
                    </div>
                    <span class="team-role-badge role--{{ $suRole }} flex-shrink-0">{{ $suRoleLabel }}</span>
                </div>

                {{-- Linha de detalhes --}}
                @php
                    $detailRows = array_filter([
                        ['Status', $su->is_active
                            ? '<span class="team-status-badge status--active"><span class="team-status-dot"></span>Ativo</span>'
                            : '<span class="team-status-badge status--inactive"><span class="team-status-dot"></span>Inativo</span>'],
                        $su->phone ? ['Telefone', e($su->phone)] : null,
                        $su->oab_number ? ['OAB', e($su->oab_number) . ($su->oab_uf ? '/' . e($su->oab_uf) : '')] : null,
                    ]);
                @endphp

                @foreach($detailRows as [$dLabel, $dValue])
                    <div class="drawer-detail-row">
                        <span class="drawer-detail-label">{{ $dLabel }}</span>
                        <span class="drawer-detail-value">{!! $dValue !!}</span>
                    </div>
                @endforeach

                {{-- Edição de perfil (somente manage-team) --}}
                @if($canManage)
                    <div class="mt-4 mb-1">
                        <div class="drawer-section-label">Perfil de acesso</div>

                        <div class="d-flex gap-2 align-items-start">
                            <div class="flex-grow-1">
                                <select
                                    wire:model="editRole"
                                    class="form-select form-select-sm @error('editRole') is-invalid @enderror"
                                >
                                    @foreach($roles as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('editRole')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary btn-sm rounded-pill flex-shrink-0"
                                wire:click="updateRole()"
                                wire:loading.attr="disabled"
                                wire:target="updateRole"
                                {{ $editRole === $suRole ? 'disabled' : '' }}
                            >
                                <span wire:loading.remove wire:target="updateRole">Salvar</span>
                                <span wire:loading wire:target="updateRole">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Ações --}}
                    <div class="mt-4">
                        <div class="drawer-section-label">Ações</div>

                        {{-- Ativar / Desativar --}}
                        <button
                            type="button"
                            class="btn btn-sm rounded-pill w-100 mb-2 d-flex align-items-center gap-2
                                   {{ $su->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                            wire:click="toggleActive()"
                            wire:loading.attr="disabled"
                            wire:target="toggleActive"
                        >
                            <span wire:loading.remove wire:target="toggleActive">
                                <i class="bi {{ $su->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                {{ $su->is_active ? 'Desativar acesso' : 'Reativar acesso' }}
                            </span>
                            <span wire:loading wire:target="toggleActive" class="d-inline-flex align-items-center gap-1 w-100 justify-content-center">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Aguarde…
                            </span>
                        </button>

                        {{-- Remover do escritório --}}
                        @if(!$showRemoveConfirm)
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger rounded-pill w-100 d-flex align-items-center gap-2"
                                wire:click="$set('showRemoveConfirm', true)"
                            >
                                <i class="bi bi-person-dash"></i>
                                Remover do escritório
                            </button>
                        @else
                            <div class="remove-confirm-box mt-2">
                                <p class="small fw-semibold mb-1" style="color:#b91c1c;">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Confirmar remoção?
                                </p>
                                <p class="small text-secondary mb-3">
                                    <strong>{{ $su->name }}</strong> perderá acesso ao escritório imediatamente.
                                    A conta não é excluída.
                                </p>
                                <div class="d-flex gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-pill flex-grow-1"
                                        wire:click="$set('showRemoveConfirm', false)"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger rounded-pill flex-grow-1"
                                        wire:click="removeUser()"
                                        wire:loading.attr="disabled"
                                        wire:target="removeUser"
                                    >
                                        <span wire:loading.remove wire:target="removeUser">Remover</span>
                                        <span wire:loading wire:target="removeUser">
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

            @else
                {{-- Loading enquanto Livewire responde --}}
                <div class="d-flex align-items-center justify-content-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando…</span>
                    </div>
                </div>
            @endif

            <x-slot name="footer">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary rounded-pill"
                    @click="$dispatch('close-drawer', { id: 'drawerUsuario' })"
                    wire:click="closeDrawer()"
                >
                    Fechar
                </button>
                @if($su && $canManage)
                    <span class="text-secondary ms-auto" style="font-size:.72rem;">
                        Membro desde {{ $su->created_at?->format('d/m/Y') ?? '—' }}
                    </span>
                @endif
            </x-slot>
        </x-drawer>

    </div>{{-- /drawer wrapper --}}

</div>{{-- /x-data --}}
