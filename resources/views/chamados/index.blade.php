@extends('layouts.app')

@section('title', 'Chamados')

@push('styles')
    @vite(['resources/css/modules/chamados.css'])
@endpush

@php
$stages = [
    ['key' => 'aberto',             'label' => 'Novo',        'color' => '#a855f7'],
    ['key' => 'triagem',            'label' => 'Triagem',     'color' => '#6366f1'],
    ['key' => 'em_andamento',       'label' => 'Em andamento','color' => '#f59e0b'],
    ['key' => 'aguardando_cliente', 'label' => 'Aguardando',  'color' => '#3b82f6'],
    ['key' => 'resolvido',          'label' => 'Resolvido',   'color' => '#10b981'],
    ['key' => 'fechado',            'label' => 'Fechado',     'color' => '#6b7280'],
];

$statusLabels = [
    'aberto'             => 'Novo',
    'triagem'            => 'Triagem',
    'em_andamento'       => 'Em andamento',
    'aguardando_cliente' => 'Aguardando',
    'resolvido'          => 'Resolvido',
    'fechado'            => 'Fechado',
];

$prioLabels = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'critica' => 'Crítica'];

$prioIcons = [
    'baixa'   => 'bi-dash',
    'media'   => 'bi-circle-fill',
    'alta'    => 'bi-arrow-up',
    'critica' => 'bi-exclamation-triangle-fill',
];

$prioColors = [
    'baixa'   => '#6b7280',
    'media'   => '#3b82f6',
    'alta'    => '#f59e0b',
    'critica' => '#ef4444',
];
@endphp

@section('content')
<div class="container-fluid px-0">

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-semibold mb-1">Chamados</h2>
            <p class="text-secondary mb-0 small">Acompanhe e abra chamados de suporte</p>
        </div>
        <button type="button" class="btn btn-primary rounded-pill px-4"
                data-bs-toggle="modal" data-bs-target="#modalNovoChamado">
            <i class="bi bi-plus-lg me-1"></i>Novo chamado
        </button>
    </div>

    {{-- Metrics strip --}}
    <div class="cham-metrics">
        @foreach ($stages as $i => $stage)
            <div class="cham-metric {{ request('status') === $stage['key'] ? 'is-active' : '' }}"
                 style="--stage-color: {{ $stage['color'] }}"
                 role="button"
                 tabindex="0"
                 data-filter-status="{{ $stage['key'] }}"
                 aria-label="Filtrar por {{ $stage['label'] }}">
                <span class="cham-metric-count">{{ $counts[$stage['key']] ?? 0 }}</span>
                <span class="cham-metric-sep"></span>
                <span class="cham-metric-label">{{ $stage['label'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Command bar --}}
    <form method="GET" action="{{ route('tickets.index') }}" id="filterForm">
        <input type="hidden" name="status" id="statusHiddenInput" value="{{ request('status') }}">

        <div class="cham-command mb-4">

            {{-- Search --}}
            <div class="cham-search">
                <i class="bi bi-search cham-search-icon"></i>
                <input type="text"
                       name="search"
                       id="chamSearchInput"
                       class="cham-search-input"
                       placeholder="Buscar protocolo ou título…"
                       value="{{ request('search') }}"
                       autocomplete="off">
                <span class="cham-kbd">/</span>
            </div>

            <div class="cham-cmd-sep d-none d-sm-block"></div>

            {{-- Priority filter --}}
            <select name="priority" class="cham-filter-select" id="chamPrioFilter">
                <option value="">Prioridade</option>
                @foreach ($prioLabels as $val => $label)
                    <option value="{{ $val }}" @selected(request('priority') === $val)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="cham-cmd-actions">

                {{-- Clear filters --}}
                @if (request()->hasAny(['search', 'status', 'priority']))
                    <a href="{{ route('tickets.index') }}" wire:navigate class="cham-clear-btn">
                        <i class="bi bi-x"></i>Limpar
                    </a>
                @endif

                {{-- Density toggle --}}
                <button type="button" class="cham-density-btn" id="chamDensityBtn"
                        title="Alternar densidade (D)">
                    <i class="bi bi-layout-split" id="chamDensityIcon"></i>
                </button>

                {{-- View toggle --}}
                <button type="button" class="cham-view-btn" data-view="lista"
                        title="Lista (L)">
                    <i class="bi bi-list-ul"></i>
                </button>
                <button type="button" class="cham-view-btn" data-view="kanban"
                        title="Kanban (K)">
                    <i class="bi bi-kanban"></i>
                </button>
            </div>
        </div>
    </form>

    {{-- Skeleton loader (shown by JS during filter submit) --}}
    <div id="ticketsLoading" class="d-none">
        <div class="cham-list-wrap">
            @for ($i = 0; $i < 6; $i++)
                <div class="cham-skel-row">
                    <div class="skeleton skeleton-text" style="width:100px"></div>
                    <div class="skeleton skeleton-text" style="width:220px"></div>
                    <div class="skeleton skeleton-badge ms-auto" style="width:80px"></div>
                    <div class="skeleton skeleton-badge" style="width:62px"></div>
                    <div class="skeleton skeleton-text" style="width:68px"></div>
                </div>
            @endfor
        </div>
    </div>

    {{-- Vista: Lista --}}
    <div id="viewLista">
        <div class="cham-list-wrap">
            <table class="cham-list" id="chamListTable">
                <thead class="cham-list-head">
                    <tr>
                        <th>Protocolo</th>
                        <th style="width:40%">Chamado</th>
                        <th>Etapa</th>
                        <th>Prioridade</th>
                        <th>Atualizado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        @php
                            $hours     = $ticket->updated_at->diffInHours(now());
                            $timeClass = $hours > 48 ? 't-urgent' : ($hours > 12 ? 't-aged' : '');
                        @endphp
                        <tr class="cham-row">
                            <td>
                                <span class="cham-protocol-tag">{{ $ticket->protocol }}</span>
                            </td>
                            <td>
                                <div class="cham-title-wrap">
                                    <div class="cham-title-main">{{ $ticket->title }}</div>
                                    <div class="cham-title-sub">{{ Str::limit($ticket->description, 72) }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="cham-status" data-status="{{ $ticket->status }}">
                                    <span class="cham-status-dot"></span>
                                    {{ $statusLabels[$ticket->status] ?? ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="cham-prio" data-prio="{{ $ticket->priority }}">
                                    <i class="bi {{ $prioIcons[$ticket->priority] ?? 'bi-dash' }}"></i>
                                    {{ $prioLabels[$ticket->priority] ?? ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="cham-time {{ $timeClass }}">
                                    {{ $ticket->updated_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                <span class="cham-row-action">Ver</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="cham-empty">
                                    <div class="cham-empty-icon">
                                        <i class="bi bi-headset"></i>
                                    </div>
                                    <div class="cham-empty-title">Nenhum chamado encontrado</div>
                                    <div class="cham-empty-sub">
                                        @if (request()->hasAny(['search', 'status', 'priority']))
                                            Tente ajustar os filtros ou
                                            <a href="{{ route('tickets.index') }}" wire:navigate class="text-primary">limpar a busca</a>.
                                        @else
                                            Você ainda não abriu nenhum chamado de suporte.
                                            <a href="#" class="text-primary d-block mt-2"
                                               data-bs-toggle="modal" data-bs-target="#modalNovoChamado">
                                                Abrir primeiro chamado →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($tickets->hasPages())
                <div class="cham-pagination">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Vista: Kanban --}}
    <div id="viewKanban" class="d-none">
        <div class="cham-kanban">
            @foreach ($stages as $stage)
                @php $colTickets = $tickets->getCollection()->where('status', $stage['key']); @endphp
                <div class="cham-kcol">
                    <div class="cham-kcol-head" style="--stage-color: {{ $stage['color'] }}">
                        <span class="cham-kcol-title">{{ $stage['label'] }}</span>
                        <span class="cham-kcol-count">{{ $counts[$stage['key']] ?? 0 }}</span>
                    </div>
                    <div class="cham-kcol-body">
                        @forelse ($colTickets as $ticket)
                            <div class="cham-kcard"
                                 style="--prio-color: {{ $prioColors[$ticket->priority] ?? '#6b7280' }}">
                                <div class="cham-kcard-proto">{{ $ticket->protocol }}</div>
                                <div class="cham-kcard-title">{{ Str::limit($ticket->title, 60) }}</div>
                                <div class="cham-kcard-foot">
                                    <span class="cham-prio" data-prio="{{ $ticket->priority }}">
                                        <i class="bi {{ $prioIcons[$ticket->priority] ?? 'bi-dash' }}"></i>
                                        {{ $prioLabels[$ticket->priority] ?? '' }}
                                    </span>
                                    <span class="cham-time">
                                        {{ $ticket->updated_at->diffForHumans(null, true, true) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="cham-kempty">
                                <i class="bi bi-inbox" style="font-size:1.15rem; opacity:0.4;"></i>
                                <span>Vazio</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

{{-- =================== MODAL: Novo Chamado (Stepper) =================== --}}
<div class="modal fade" id="modalNovoChamado" tabindex="-1"
     aria-labelledby="modalNovoChamadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ticket-modal">

            <div class="modal-header border-0 pb-0 pe-3">
                <div class="flex-grow-1">
                    <h5 class="modal-title fw-semibold mb-1" id="modalNovoChamadoLabel">
                        Abrir chamado
                    </h5>
                    <p class="text-secondary small mb-3">
                        Preencha as informações para abrir seu chamado de suporte.
                    </p>

                    {{-- Stepper --}}
                    <div class="ticket-stepper">
                        @php
                        $steps = [
                            ['num' => 1, 'label' => 'Título e descrição'],
                            ['num' => 2, 'label' => 'Categoria e prioridade'],
                            ['num' => 3, 'label' => 'Anexos'],
                            ['num' => 4, 'label' => 'Confirmação'],
                        ];
                        @endphp
                        @foreach ($steps as $step)
                            <div class="stepper-step {{ $step['num'] === 1 ? 'active' : '' }}"
                                 data-step="{{ $step['num'] }}">
                                <div class="stepper-dot">
                                    <span class="stepper-dot-num">{{ $step['num'] }}</span>
                                    <i class="bi bi-check stepper-dot-check d-none"></i>
                                </div>
                                <div class="stepper-label d-none d-sm-block">{{ $step['label'] }}</div>
                            </div>
                            @if (! $loop->last)
                                <div class="stepper-line"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn-close align-self-start ms-3"
                        data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body pt-3">
                <form id="formNovoChamado" novalidate>
                    @csrf

                    {{-- Etapa 1: Título e descrição --}}
                    <div class="stepper-panel" data-panel="1">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Título do chamado <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control"
                                   placeholder="Ex: Erro ao gerar minuta em PDF…"
                                   maxlength="255" required>
                            <div class="invalid-feedback">Informe um título para o chamado.</div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold">
                                Descrição <span class="text-danger">*</span>
                            </label>
                            <textarea name="description" class="form-control" rows="6"
                                      placeholder="Descreva em detalhes o problema ou solicitação. Quanto mais informações, mais rápido poderemos ajudar…"
                                      maxlength="5000" required></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <div class="invalid-feedback d-block" id="descError"></div>
                                <small class="text-secondary ms-auto desc-counter">0 / 5000</small>
                            </div>
                        </div>
                    </div>

                    {{-- Etapa 2: Categoria e prioridade --}}
                    <div class="stepper-panel d-none" data-panel="2">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Categoria <span class="text-danger">*</span>
                            </label>
                            <div class="category-grid">
                                @foreach ([
                                    'tecnico'    => ['Técnico',    'bi-cpu'],
                                    'financeiro' => ['Financeiro', 'bi-currency-dollar'],
                                    'duvida'     => ['Dúvida',     'bi-question-circle'],
                                    'sugestao'   => ['Sugestão',   'bi-lightbulb'],
                                    'bug'        => ['Bug',        'bi-bug'],
                                    'outros'     => ['Outros',     'bi-three-dots'],
                                ] as $val => [$label, $icon])
                                    <label class="category-option">
                                        <input type="radio" name="category" value="{{ $val }}" class="d-none">
                                        <div class="category-card">
                                            <i class="bi {{ $icon }} fs-4 mb-1"></i>
                                            <span>{{ $label }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="text-danger small mt-2 d-none" id="categoryError">
                                Selecione uma categoria.
                            </div>
                        </div>

                        <div class="mb-1">
                            <label class="form-label fw-semibold">
                                Prioridade <span class="text-danger">*</span>
                            </label>
                            <div class="priority-list">
                                @foreach ([
                                    'baixa'   => ['Baixa',   'bi-dash',                    'prio-baixa',   'Sem urgência, pode aguardar normalmente'],
                                    'media'   => ['Média',   'bi-circle-fill',             'prio-media',   'Importante mas sem impacto imediato'],
                                    'alta'    => ['Alta',    'bi-arrow-up',               'prio-alta',    'Precisa de atenção rápida'],
                                    'critica' => ['Crítica', 'bi-exclamation-triangle-fill','prio-critica', 'Impacto crítico na operação do escritório'],
                                ] as $val => [$label, $icon, $cls, $desc])
                                    <label class="priority-option">
                                        <input type="radio" name="priority" value="{{ $val }}" class="d-none">
                                        <div class="priority-card">
                                            <div class="priority-indicator {{ $cls }}">
                                                <i class="bi {{ $icon }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold small">{{ $label }}</div>
                                                <div class="text-secondary" style="font-size:0.79rem;">{{ $desc }}</div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="text-danger small mt-2 d-none" id="priorityError">
                                Selecione uma prioridade.
                            </div>
                        </div>
                    </div>

                    {{-- Etapa 3: Anexos --}}
                    <div class="stepper-panel d-none" data-panel="3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Anexos <span class="text-secondary fw-normal">(opcional)</span>
                            </label>
                            <input type="file" name="attachments[]" multiple
                                   accept=".pdf,.png,.jpg,.jpeg,.docx,.txt"
                                   class="form-control">
                            <p class="text-secondary small mt-2 mb-0">
                                Formatos aceitos: PDF, PNG, JPG, DOCX, TXT &mdash; máximo 10 MB por arquivo.
                            </p>
                        </div>
                        <div id="filePreview" class="file-preview-list"></div>
                    </div>

                    {{-- Etapa 4: Confirmação --}}
                    <div class="stepper-panel d-none" data-panel="4">
                        <div class="surface-card p-4 mb-3">
                            <h6 class="fw-semibold mb-3 text-secondary small text-uppercase"
                                style="letter-spacing:0.07em;">
                                Resumo do chamado
                            </h6>
                            <div class="confirm-row">
                                <span class="confirm-label">Título</span>
                                <span class="confirm-value" id="confirmTitle">—</span>
                            </div>
                            <div class="confirm-row">
                                <span class="confirm-label">Descrição</span>
                                <span class="confirm-value confirm-desc" id="confirmDesc">—</span>
                            </div>
                            <div class="confirm-row">
                                <span class="confirm-label">Categoria</span>
                                <span class="confirm-value" id="confirmCategory">—</span>
                            </div>
                            <div class="confirm-row">
                                <span class="confirm-label">Prioridade</span>
                                <span class="confirm-value" id="confirmPriority">—</span>
                            </div>
                            <div class="confirm-row">
                                <span class="confirm-label">Anexos</span>
                                <span class="confirm-value" id="confirmAttach">Nenhum</span>
                            </div>
                        </div>
                        <div class="alert border-0 mb-0" style="background:rgba(37,99,235,0.06);">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            <span class="small">Após a abertura você receberá um número de protocolo para acompanhamento.</span>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        id="btnStepperBack" style="display:none">
                    <i class="bi bi-chevron-left me-1"></i>Voltar
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnStepperNext">
                    Próximo <i class="bi bi-chevron-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-success rounded-pill px-4 d-none" id="btnStepperSubmit">
                    <i class="bi bi-check-lg me-1"></i>Abrir chamado
                </button>
            </div>

        </div>
    </div>
</div>

{{-- =================== MODAL: Sucesso =================== --}}
<div class="modal fade" id="modalSucesso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4 ticket-modal">
            <div class="mx-auto mb-3">
                <i class="bi bi-check-circle-fill text-success" style="font-size:2.8rem;"></i>
            </div>
            <h5 class="fw-semibold mb-1">Chamado aberto!</h5>
            <p class="text-secondary small mb-3">Seu número de protocolo é:</p>
            <div class="ticket-protocol-display mb-4" id="successProtocol">TKT-2026-XXXXXXXX</div>
            <button type="button" class="btn btn-primary rounded-pill px-4"
                    onclick="window.location.reload()">
                Ver meus chamados
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/modules/chamados-index.js'])
@endpush
