@extends('layouts.app')

@section('title', 'Casos')

@push('styles')
    @vite(['resources/css/modules/casos.css'])
@endpush

@section('content')
@php
    $sortBy  = request('sort', 'updated_at');
    $sortDir = request('direction', 'desc');
    $baseQ   = request()->except(['sort', 'direction', 'page']);

    $sortUrl = fn(string $col) => route('cases.index', array_merge(
        $baseQ,
        ['sort' => $col, 'direction' => ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc']
    ));
    $sortIcon = fn(string $col) => $sortBy === $col
        ? ($sortDir === 'asc' ? 'bi-sort-up' : 'bi-sort-down-alt')
        : 'bi-arrow-down-up';

    $novoCasoInit = json_encode([
        'title'       => old('title', ''),
        'client'      => old('client_name', ''),
        'area'        => old('area', ''),
        'status'      => old('status', 'triagem'),
        'risk'        => old('risk_level', ''),
        'opened_at'   => old('opened_at', now()->format('Y-m-d')),
        'email'       => old('client_email', ''),
        'phone'       => old('client_phone', ''),
        'description' => old('description', ''),
    ]);
@endphp

<div class="container-fluid px-0"
     x-data="{
         selected: [],
         activeCaso: null,
         novoCasoForm: {{ $novoCasoInit }},
         allIds: {{ Js::from($cases->pluck('id')->toArray()) }},
         get selectedCount() { return this.selected.length; },
         get allSelected() { return this.allIds.length > 0 && this.selected.length >= this.allIds.length; },
         toggleAll(v) { this.selected = v ? this.allIds.map(id => String(id)) : []; },
         maskPhone(v) {
             v = v.replace(/\D/g, '').slice(0, 11);
             if (v.length === 0) return '';
             if (v.length <= 2)  return `(${v}`;
             if (v.length <= 6)  return `(${v.slice(0,2)}) ${v.slice(2)}`;
             if (v.length <= 10) return `(${v.slice(0,2)}) ${v.slice(2,6)}-${v.slice(6)}`;
             return `(${v.slice(0,2)}) ${v.slice(2,7)}-${v.slice(7)}`;
         },
     }">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-semibold mb-1">Casos</h2>
            <p class="text-secondary mb-0 small">Dossiês jurídicos do escritório.</p>
        </div>
        <button type="button"
                class="btn btn-primary rounded-pill px-4"
                data-bs-toggle="modal"
                data-bs-target="#modalNovoCaso">
            <i class="bi bi-folder-plus me-2"></i>Novo caso
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="surface-card p-4 mb-4">
        <form method="GET" action="{{ route('cases.index') }}" class="row g-2 align-items-center">
            <div class="col-sm-6 col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Buscar caso ou cliente..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    @foreach (['triagem' => 'Triagem', 'em_andamento' => 'Em andamento', 'aguardando_cliente' => 'Aguardando cliente', 'aguardando_prazo' => 'Aguardando prazo', 'em_recurso' => 'Em recurso', 'encerrado' => 'Encerrado', 'arquivado' => 'Arquivado'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <select name="area" class="form-select">
                    <option value="">Todas as áreas</option>
                    @foreach (['civil' => 'Civil', 'criminal' => 'Criminal', 'trabalhista' => 'Trabalhista', 'tributario' => 'Tributário', 'empresarial' => 'Empresarial', 'familia' => 'Família', 'imobiliario' => 'Imobiliário', 'previdenciario' => 'Previdenciário', 'administrativo' => 'Administrativo', 'outro' => 'Outro'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('area') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <select name="risk_level" class="form-select">
                    <option value="">Todos os riscos</option>
                    @foreach (['baixo' => 'Baixo', 'medio' => 'Médio', 'alto' => 'Alto', 'critico' => 'Crítico'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('risk_level') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2 align-items-center flex-wrap">
                <select name="per_page" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    @foreach ([10, 25, 50] as $pp)
                        <option value="{{ $pp }}" @selected(request('per_page', 25) == $pp)>{{ $pp }} por pág.</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-primary btn-sm">Filtrar</button>
                @if (request()->hasAny(['search', 'status', 'area', 'risk_level']))
                    <a href="{{ route('cases.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm">Limpar</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="surface-card p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:2.75rem">
                            <input type="checkbox" class="form-check-input"
                                   :checked="allSelected"
                                   :indeterminate="selectedCount > 0 && !allSelected"
                                   @change="toggleAll($event.target.checked)">
                        </th>
                        <th class="sort-th {{ $sortBy === 'title' ? 'active' : '' }}">
                            <a href="{{ $sortUrl('title') }}" wire:navigate>Caso <i class="bi {{ $sortIcon('title') }}"></i></a>
                        </th>
                        <th>Área</th>
                        <th class="sort-th {{ $sortBy === 'status' ? 'active' : '' }}">
                            <a href="{{ $sortUrl('status') }}" wire:navigate>Status <i class="bi {{ $sortIcon('status') }}"></i></a>
                        </th>
                        <th>Risco</th>
                        <th>Responsável</th>
                        <th class="sort-th {{ $sortBy === 'updated_at' ? 'active' : '' }}">
                            <a href="{{ $sortUrl('updated_at') }}" wire:navigate>Atualizado <i class="bi {{ $sortIcon('updated_at') }}"></i></a>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cases as $case)
                        @php
                            $casoData = htmlspecialchars(json_encode([
                                'title'       => $case->title,
                                'client'      => $case->client_name,
                                'area'        => ucfirst($case->area),
                                'status'      => ucfirst(str_replace('_', ' ', $case->status)),
                                'risk'        => $case->risk_level ? ucfirst($case->risk_level) : null,
                                'assignee'    => $case->assignedUser?->name,
                                'updated'     => $case->updated_at->diffForHumans(),
                                'description' => \Str::limit($case->description ?? '', 240),
                                'url'         => route('cases.show', $case),
                            ]), ENT_QUOTES, 'UTF-8');
                        @endphp
                        <tr class="caso-row"
                            style="cursor:pointer"
                            data-caso="{{ $casoData }}"
                            @click="activeCaso = JSON.parse($el.dataset.caso); $dispatch('open-drawer', { id: 'drawerCasoPreview' })">
                            <td class="ps-4" @click.stop>
                                <input type="checkbox" class="form-check-input"
                                       value="{{ $case->id }}"
                                       x-model="selected">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $case->title }}</div>
                                <div class="text-secondary small">{{ $case->client_name }}</div>
                            </td>
                            <td><span class="badge text-bg-secondary">{{ ucfirst($case->area) }}</span></td>
                            <td>
                                <span class="badge text-bg-primary">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</span>
                            </td>
                            <td>
                                @if ($case->risk_level)
                                    @php
                                        $riskClass = match($case->risk_level) {
                                            'baixo'   => 'text-bg-success',
                                            'medio'   => 'text-bg-warning text-dark',
                                            'alto'    => 'text-bg-danger',
                                            'critico' => 'text-bg-dark',
                                            default   => 'text-bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $riskClass }}">{{ ucfirst($case->risk_level) }}</span>
                                @else
                                    <span class="text-secondary small">—</span>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ $case->assignedUser?->name ?? '—' }}</td>
                            <td class="text-secondary small">{{ $case->updated_at->diffForHumans() }}</td>
                            <td @click.stop>
                                <a href="{{ route('cases.show', $case) }}" wire:navigate
                                   class="btn btn-sm btn-outline-primary rounded-pill">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-0 border-0">
                                <div class="py-5">
                                    <x-empty-state
                                        icon="bi-briefcase"
                                        title="Nenhum caso encontrado"
                                        description="Os dossiês jurídicos do escritório aparecem aqui. Cada caso reúne documentos, análises de IA e o histórico completo do processo."
                                        :primary-action="['label' => 'Criar primeiro caso', 'modal' => '#modalNovoCaso', 'icon' => 'bi-folder-plus']"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($cases->hasPages())
            <div class="px-4 py-3 d-flex align-items-center justify-content-between border-top" style="border-color:rgba(215,220,229,0.5) !important">
                <span class="text-secondary small">{{ $cases->total() }} casos no total</span>
                <div>{{ $cases->appends(request()->query())->links() }}</div>
            </div>
        @endif
    </div>

    {{-- Barra flutuante de ações em massa --}}
    <div class="bulk-action-bar"
         x-show="selectedCount > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         x-cloak>
        <span class="text-white-50 small me-1" x-text="selectedCount + ' selecionado(s)'"></span>
        <div class="vr mx-2" style="opacity:.25"></div>
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" @click="selected = []">Cancelar</button>
        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" disabled title="Em breve">
            <i class="bi bi-trash me-1"></i>Excluir
        </button>
    </div>

    {{-- Drawer de preview do caso --}}
    <x-drawer id="drawerCasoPreview" title="Detalhes do caso" width="lg">
        <div>
            <div class="mb-4">
                <div class="fw-semibold" style="font-size:1.05rem;line-height:1.35" x-text="activeCaso?.title"></div>
                <div class="text-secondary small mt-1" x-text="activeCaso?.client"></div>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="badge text-bg-secondary" x-text="activeCaso?.area"></span>
                <span class="badge text-bg-primary" x-text="activeCaso?.status"></span>
                <template x-if="activeCaso?.risk">
                    <span class="badge text-bg-warning text-dark" x-text="'Risco ' + activeCaso?.risk"></span>
                </template>
            </div>
            <dl class="row small mb-0">
                <dt class="col-5 text-secondary fw-normal mb-2">Responsável</dt>
                <dd class="col-7 fw-semibold mb-2" x-text="activeCaso?.assignee || 'Não atribuído'"></dd>
                <dt class="col-5 text-secondary fw-normal mb-0">Atualizado</dt>
                <dd class="col-7 mb-0" x-text="activeCaso?.updated"></dd>
            </dl>
            <template x-if="activeCaso?.description">
                <div class="mt-4 pt-4" style="border-top:1px solid rgba(215,220,229,0.5)">
                    <div class="text-secondary mb-2" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.06em;font-weight:600">Descrição</div>
                    <div class="small" x-text="activeCaso?.description" style="line-height:1.7"></div>
                </div>
            </template>
        </div>
        <x-slot name="footer">
            <a href="#" :href="activeCaso?.url ?? '#'" wire:navigate class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-box-arrow-up-right me-2"></i>Abrir dossiê completo
            </a>
        </x-slot>
    </x-drawer>

</div>

{{-- Modal de criação de caso (stepper 4 passos) --}}
<form method="POST" action="{{ route('cases.store') }}" id="formNovoCaso">
    @csrf
    <x-modal-stepper
        id="modalNovoCaso"
        title="Novo caso"
        subtitle="Preencha os dados para criar um dossiê jurídico."
        :steps="[
            ['label' => 'Identificação'],
            ['label' => 'Classificação'],
            ['label' => 'Cliente'],
            ['label' => 'Confirmar'],
        ]"
        size="lg"
    >
        {{-- Passo 1 — Identificação --}}
        <x-slot name="step_1">
            <div class="mb-3">
                <label for="ms_title" class="form-label fw-semibold">Título do caso <span class="text-danger">*</span></label>
                <input type="text" id="ms_title" name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       placeholder="Ex: Ação de cobrança — Empresa XYZ"
                       x-model="novoCasoForm.title"
                       value="{{ old('title') }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="ms_client_name" class="form-label fw-semibold">Nome do cliente <span class="text-danger">*</span></label>
                <input type="text" id="ms_client_name" name="client_name"
                       class="form-control @error('client_name') is-invalid @enderror"
                       placeholder="Nome completo ou razão social"
                       x-model="novoCasoForm.client"
                       value="{{ old('client_name') }}">
                @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-0">
                <label for="ms_description" class="form-label fw-semibold">Descrição</label>
                <textarea id="ms_description" name="description"
                          class="form-control @error('description') is-invalid @enderror"
                          rows="3"
                          placeholder="Contexto e histórico do caso..."
                          x-model="novoCasoForm.description">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-slot>

        {{-- Passo 2 — Classificação --}}
        <x-slot name="step_2">
            <div class="row g-3">
                <div class="col-sm-6">
                    <label for="ms_area" class="form-label fw-semibold">Área jurídica <span class="text-danger">*</span></label>
                    <select id="ms_area" name="area"
                            class="form-select @error('area') is-invalid @enderror"
                            x-model="novoCasoForm.area">
                        <option value="">Selecionar...</option>
                        @foreach (['civil' => 'Civil', 'criminal' => 'Criminal', 'trabalhista' => 'Trabalhista', 'tributario' => 'Tributário', 'empresarial' => 'Empresarial', 'familia' => 'Família', 'imobiliario' => 'Imobiliário', 'previdenciario' => 'Previdenciário', 'administrativo' => 'Administrativo', 'outro' => 'Outro'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('area') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label for="ms_status" class="form-label fw-semibold">Status</label>
                    <select id="ms_status" name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            x-model="novoCasoForm.status">
                        @foreach (['triagem' => 'Triagem', 'em_andamento' => 'Em andamento', 'aguardando_cliente' => 'Aguardando cliente', 'aguardando_prazo' => 'Aguardando prazo'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', 'triagem') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label for="ms_risk" class="form-label fw-semibold">Nível de risco</label>
                    <select id="ms_risk" name="risk_level"
                            class="form-select @error('risk_level') is-invalid @enderror"
                            x-model="novoCasoForm.risk">
                        <option value="">Não definido</option>
                        @foreach (['baixo' => 'Baixo', 'medio' => 'Médio', 'alto' => 'Alto', 'critico' => 'Crítico'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('risk_level') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('risk_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label for="ms_opened_at" class="form-label fw-semibold">Data de abertura <span class="text-danger">*</span></label>
                    <input type="date" id="ms_opened_at" name="opened_at"
                           class="form-control @error('opened_at') is-invalid @enderror"
                           x-model="novoCasoForm.opened_at"
                           value="{{ old('opened_at', now()->format('Y-m-d')) }}">
                    @error('opened_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-slot>

        {{-- Passo 3 — Dados do cliente --}}
        <x-slot name="step_3">
            <div class="mb-3">
                <label for="ms_email" class="form-label fw-semibold">E-mail do cliente</label>
                <input type="email" id="ms_email" name="client_email"
                       class="form-control @error('client_email') is-invalid @enderror"
                       placeholder="cliente@exemplo.com"
                       x-model="novoCasoForm.email"
                       value="{{ old('client_email') }}">
                @error('client_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-0">
                <label for="ms_phone" class="form-label fw-semibold">Telefone do cliente</label>
                <input type="text" id="ms_phone" name="client_phone"
                       class="form-control @error('client_phone') is-invalid @enderror"
                       placeholder="(11) 90000-0000"
                       x-model="novoCasoForm.phone"
                       @input="let _v = maskPhone($event.target.value); $event.target.value = _v; novoCasoForm.phone = _v"
                       value="{{ old('client_phone') }}">
                @error('client_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mt-3 pt-3" style="border-top:1px solid rgba(215,220,229,0.5)">
                <div class="text-secondary small">
                    <i class="bi bi-info-circle me-1"></i>
                    Para atribuir um responsável, abra o caso após criá-lo e edite os dados.
                </div>
            </div>
        </x-slot>

        {{-- Passo 4 — Confirmar --}}
        <x-slot name="step_4">
            <h6 class="fw-semibold mb-3">Revise antes de criar</h6>
            <dl class="row small mb-4">
                <dt class="col-4 text-secondary fw-normal mb-2">Título</dt>
                <dd class="col-8 fw-semibold mb-2" x-text="novoCasoForm.title || '—'"></dd>

                <dt class="col-4 text-secondary fw-normal mb-2">Cliente</dt>
                <dd class="col-8 mb-2" x-text="novoCasoForm.client || '—'"></dd>

                <dt class="col-4 text-secondary fw-normal mb-2">E-mail</dt>
                <dd class="col-8 mb-2" x-text="novoCasoForm.email || '—'"></dd>

                <dt class="col-4 text-secondary fw-normal mb-2">Telefone</dt>
                <dd class="col-8 mb-2" x-text="novoCasoForm.phone || '—'"></dd>

                <dt class="col-4 text-secondary fw-normal mb-2">Área</dt>
                <dd class="col-8 mb-2" x-text="novoCasoForm.area || 'Não selecionada'"></dd>

                <dt class="col-4 text-secondary fw-normal mb-2">Status</dt>
                <dd class="col-8 mb-2" x-text="novoCasoForm.status"></dd>

                <dt class="col-4 text-secondary fw-normal mb-2">Risco</dt>
                <dd class="col-8 mb-2" x-text="novoCasoForm.risk || 'Não definido'"></dd>

                <dt class="col-4 text-secondary fw-normal mb-0">Abertura</dt>
                <dd class="col-8 mb-0" x-text="novoCasoForm.opened_at || '—'"></dd>
            </dl>

            <x-ai-disclaimer />
        </x-slot>

        {{-- Botão de submissão (aparece só no último passo) --}}
        <x-slot name="submit">
            <button type="submit" form="formNovoCaso" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-folder-plus me-2"></i>Criar caso
            </button>
        </x-slot>
    </x-modal-stepper>
</form>

@if ($errors->hasAny(['title', 'client_name', 'area', 'status', 'risk_level', 'opened_at', 'description', 'client_email', 'client_phone']))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('modalNovoCaso');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endpush
@endif

@endsection
