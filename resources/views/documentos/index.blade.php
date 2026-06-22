@extends('layouts.app')

@section('title', 'Documentos')

@push('styles')
    @vite(['resources/css/modules/documentos.css'])
@endpush

@section('content')
@php
    $sortBy  = request('sort', 'created_at');
    $sortDir = request('direction', 'desc');
    $baseQ   = request()->except(['sort', 'direction', 'page']);

    $sortUrl = fn(string $col) => route('documents.index', array_merge(
        $baseQ,
        ['sort' => $col, 'direction' => ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc']
    ));
    $sortIcon = fn(string $col) => $sortBy === $col
        ? ($sortDir === 'asc' ? 'bi-sort-up' : 'bi-sort-down-alt')
        : 'bi-arrow-down-up';
@endphp

<div class="container-fluid px-0"
     x-data="{
         dragging: false,
         dragCount: 0,
         activeDoc: null,
         onDragEnter(e) {
             if (!e.dataTransfer?.types.includes('Files')) return;
             this.dragCount++;
             this.dragging = true;
         },
         onDragLeave() {
             this.dragCount = Math.max(0, this.dragCount - 1);
             if (this.dragCount === 0) this.dragging = false;
         },
         onDrop(e) {
             e.preventDefault();
             this.dragCount = 0;
             this.dragging = false;
             const modalEl = document.getElementById('modalEnviarDoc');
             bootstrap.Modal.getOrCreateInstance(modalEl).show();
             const file = e.dataTransfer?.files[0];
             if (file) {
                 modalEl.addEventListener('shown.bs.modal', () => {
                     window.dispatchEvent(new CustomEvent('doc-page-drop', { detail: { file } }));
                 }, { once: true });
             }
         }
     }"
     @dragenter.window="onDragEnter($event)"
     @dragleave.window="onDragLeave()"
     @dragover.window.prevent
     @drop.window="onDrop($event)">

    {{-- Overlay de drag & drop --}}
    <div x-show="dragging"
         x-transition:enter="transition-opacity ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="drop-overlay"
         aria-hidden="true">
        <div class="drop-overlay-inner">
            <i class="bi bi-cloud-arrow-up"></i>
            <h5 class="fw-semibold mb-1">Solte para enviar documentos</h5>
            <p class="small mb-0" style="opacity:.7">O formulário de envio será aberto</p>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-semibold mb-1">Documentos</h2>
            <p class="text-secondary mb-0 small">PDFs e documentos analisados por IA.</p>
        </div>
        <button type="button"
                class="btn btn-primary rounded-pill px-4"
                data-bs-toggle="modal"
                data-bs-target="#modalEnviarDoc">
            <i class="bi bi-cloud-arrow-up me-2"></i>Enviar documento
        </button>
    </div>

    {{-- Filtros --}}
    <div class="surface-card p-4 mb-4">
        <form method="GET" action="{{ route('documents.index') }}" class="row g-2 align-items-center">
            <div class="col-sm-8 col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-4 col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    @foreach (['uploading' => 'Enviando', 'processing' => 'Processando', 'ready' => 'Pronto', 'error' => 'Erro'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2 align-items-center">
                <select name="per_page" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    @foreach ([10, 25, 50] as $pp)
                        <option value="{{ $pp }}" @selected(request('per_page', 25) == $pp)>{{ $pp }} por pág.</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-primary btn-sm">Filtrar</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('documents.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm">Limpar</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="surface-card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 sort-th {{ $sortBy === 'title' ? 'active' : '' }}">
                            <a href="{{ $sortUrl('title') }}" wire:navigate>Documento <i class="bi {{ $sortIcon('title') }}"></i></a>
                        </th>
                        <th>Caso vinculado</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th class="sort-th {{ $sortBy === 'created_at' ? 'active' : '' }}">
                            <a href="{{ $sortUrl('created_at') }}" wire:navigate>Enviado <i class="bi {{ $sortIcon('created_at') }}"></i></a>
                        </th>
                        <th style="width:3.5rem"></th>
                    </tr>
                </thead>
                <tbody class="stagger-list">
                    @forelse ($documents as $doc)
                        @php
                            $docData = htmlspecialchars(json_encode([
                                'id'       => $doc->id,
                                'title'    => $doc->title,
                                'filename' => $doc->original_filename,
                                'size'     => number_format($doc->file_size / 1024, 0) . ' KB',
                                'mime'     => $doc->mime_type,
                                'status'   => ucfirst($doc->status),
                                'case'     => $doc->legalCase?->title,
                                'summary'  => \Str::limit($doc->ai_summary ?? '', 240),
                                'created'  => $doc->created_at->format('d/m/Y H:i'),
                                'url'      => route('documents.show', $doc),
                            ]), ENT_QUOTES, 'UTF-8');
                        @endphp
                        <tr style="cursor:pointer"
                            data-doc="{{ $docData }}"
                            @click="activeDoc = JSON.parse($el.dataset.doc); $dispatch('open-drawer', { id: 'drawerDocPreview' })">
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $doc->title }}</div>
                                <div class="text-secondary small">{{ $doc->original_filename }}</div>
                                @if ($doc->ai_summary)
                                    <div class="text-secondary small fst-italic">{{ \Str::limit($doc->ai_summary, 80) }}</div>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                @if ($doc->legalCase)
                                    <a href="{{ route('cases.show', $doc->legalCase) }}" wire:navigate
                                       class="text-decoration-none" @click.stop>
                                        {{ \Str::limit($doc->legalCase->title, 40) }}
                                    </a>
                                @else
                                    <span class="text-muted">Sem caso</span>
                                @endif
                            </td>
                            <td><span class="status-badge status-badge--secondary">{{ $doc->mime_type }}</span></td>
                            <td>
                                @php
                                    $statusClass = match($doc->status) {
                                        'ready'      => 'status-badge--success',
                                        'processing' => 'status-badge--warning',
                                        'error'      => 'status-badge--danger',
                                        default      => 'status-badge--secondary',
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ ucfirst($doc->status) }}</span>
                            </td>
                            <td class="text-secondary small">{{ $doc->created_at->diffForHumans() }}</td>
                            <td @click.stop class="text-end pe-3">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-secondary p-1 lh-1"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical fs-6"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                    data-preview-doc-id="{{ $doc->id }}"
                                                    data-preview-title="{{ $doc->title }}">
                                                <i class="bi bi-eye me-2 text-secondary"></i>Visualizar
                                            </button>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('documents.show', $doc) }}" wire:navigate>
                                                <i class="bi bi-box-arrow-up-right me-2 text-secondary"></i>Ver detalhes
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('documents.edit', $doc) }}" wire:navigate>
                                                <i class="bi bi-pencil me-2 text-secondary"></i>Editar
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('documents.destroy', $doc) }}">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item text-danger" type="submit"
                                                        data-confirm-delete="Excluir o documento &quot;{{ addslashes($doc->title) }}&quot; permanentemente?"
                                                        data-confirm-title="Excluir documento">
                                                    <i class="bi bi-trash me-2"></i>Excluir
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-0 border-0">
                                <div class="py-5">
                                    <x-empty-state
                                        icon="bi-file-earmark-text"
                                        title="Nenhum documento ainda"
                                        description="Envie PDFs e documentos para que a IA extraia resumos, identifique cláusulas e permita análises jurídicas contextualizadas."
                                        :primary-action="['label' => 'Enviar primeiro documento', 'modal' => '#modalEnviarDoc', 'icon' => 'bi-cloud-arrow-up']"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($documents->hasPages())
            <div class="px-4 py-3 d-flex align-items-center justify-content-between border-top" style="border-color:rgba(215,220,229,0.5) !important">
                <span class="text-secondary small">{{ $documents->total() }} documentos no total</span>
                <div>{{ $documents->appends(request()->query())->links() }}</div>
            </div>
        @endif
    </div>

    {{-- Drawer de preview do documento --}}
    <x-drawer id="drawerDocPreview" title="Detalhes do documento" width="lg">
        <div>
            <div class="mb-4">
                <div class="fw-semibold" style="font-size:1.05rem;line-height:1.35" x-text="activeDoc?.title"></div>
                <div class="text-secondary small mt-1" x-text="activeDoc?.filename"></div>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="badge text-bg-secondary" x-text="activeDoc?.mime"></span>
                <span class="badge text-bg-secondary" x-text="activeDoc?.size"></span>
                <span class="badge text-bg-primary" x-text="activeDoc?.status"></span>
            </div>
            <dl class="row small mb-0">
                <dt class="col-5 text-secondary fw-normal mb-2">Caso</dt>
                <dd class="col-7 mb-2" x-text="activeDoc?.case || 'Sem caso vinculado'"></dd>
                <dt class="col-5 text-secondary fw-normal mb-0">Enviado em</dt>
                <dd class="col-7 mb-0" x-text="activeDoc?.created"></dd>
            </dl>
            <template x-if="activeDoc?.summary">
                <div class="mt-4 pt-4" style="border-top:1px solid rgba(215,220,229,0.5)">
                    <div class="text-secondary mb-2" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.06em;font-weight:600">
                        <i class="bi bi-cpu me-1"></i>Resumo de IA
                    </div>
                    <div class="small" x-text="activeDoc?.summary" style="line-height:1.7"></div>
                </div>
            </template>
        </div>
        <x-slot name="footer">
            <button type="button"
                    class="btn btn-outline-primary rounded-pill px-4"
                    :data-preview-doc-id="activeDoc?.id"
                    :data-preview-title="activeDoc?.title">
                <i class="bi bi-eye me-2"></i>Visualizar arquivo
            </button>
            <a href="#" :href="activeDoc?.url ?? '#'" wire:navigate class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-box-arrow-up-right me-2"></i>Ver detalhes
            </a>
        </x-slot>
    </x-drawer>

</div>

{{-- Modal de envio de documento --}}
<x-upload-doc-modal :cases="$cases" />

@endsection
