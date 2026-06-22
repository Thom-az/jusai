@extends('layouts.app')

@section('title', 'Assistente IA — ' . $caso->title)

@push('styles')
    @vite(['resources/css/modules/casos.css', 'resources/css/modules/documentos.css'])
@endpush

@section('content')
<div class="chat-page-layout" x-data="{ panelOpen: true }">

    {{-- ── Compact Header ─────────────────────────────────────────── --}}
    <div class="chat-page-header">
        <a href="{{ route('cases.show', $caso) }}" wire:navigate class="chat-back-btn" title="Voltar ao caso">
            <i class="bi bi-arrow-left"></i>
        </a>

        <div class="chat-header-info">
            <div class="chat-header-title">{{ $caso->title }}</div>
            <div class="chat-header-meta">
                <span class="chat-status-dot"></span>
                <span>Assistente IA</span>
                @if ($caso->area)
                    <span class="chat-meta-sep">·</span>
                    <span>{{ $caso->area }}</span>
                @endif
                @if ($caso->risk_level)
                    <span class="risk-badge risk-{{ $caso->risk_level }}">{{ ucfirst($caso->risk_level) }}</span>
                @endif
            </div>
        </div>

        <div class="chat-header-actions ms-auto">
            <button type="button"
                    class="chat-header-btn"
                    x-on:click="panelOpen = !panelOpen"
                    :class="{ 'is-active': panelOpen }"
                    title="Painel de contexto">
                <i class="bi bi-layout-sidebar-reverse"></i>
            </button>
        </div>
    </div>

    {{-- ── Body: Chat + Context Panel ─────────────────────────────── --}}
    <div class="chat-page-body">

        {{-- Chat --}}
        <div class="chat-page-main">
            <livewire:caso-chat :caso="$caso" />
        </div>

        {{-- Context Panel --}}
        <aside class="chat-context-panel" x-show="panelOpen">

            {{-- Case context --}}
            <div class="ctx-card">
                <div class="ctx-card-header">
                    <i class="bi bi-briefcase-fill"></i>
                    Contexto do Caso
                </div>
                <div class="ctx-card-body">
                    @if ($caso->client_name)
                        <div class="ctx-row">
                            <span class="ctx-label">Cliente</span>
                            <span class="ctx-value">{{ $caso->client_name }}</span>
                        </div>
                    @endif
                    <div class="ctx-row">
                        <span class="ctx-label">Área</span>
                        <span class="ctx-value">{{ $caso->area ?? '—' }}</span>
                    </div>
                    <div class="ctx-row">
                        <span class="ctx-label">Status</span>
                        <span class="ctx-value">{{ ucfirst(str_replace('_', ' ', $caso->status)) }}</span>
                    </div>
                    @if ($caso->risk_level)
                        <div class="ctx-row">
                            <span class="ctx-label">Risco</span>
                            <span class="ctx-value">
                                <span class="risk-badge risk-{{ $caso->risk_level }}">{{ ucfirst($caso->risk_level) }}</span>
                            </span>
                        </div>
                    @endif
                    @if ($caso->description)
                        <p class="ctx-summary">{{ Str::limit($caso->description, 110) }}</p>
                    @endif
                </div>
            </div>

            {{-- Document base --}}
            @php
                $chatDocs    = $caso->documents()
                    ->whereIn('status', ['ready', 'pending', 'processing'])
                    ->orderByDesc('created_at')
                    ->get(['id', 'title', 'original_filename', 'status']);
                $readyDocIds = $chatDocs->where('status', 'ready')->pluck('id')->values()->toArray();
                $readyCnt    = count($readyDocIds);
                $totalCnt    = $chatDocs->count();
            @endphp
            <div class="ctx-card"
                 x-data="{ selected: {{ json_encode($readyDocIds) }}, search: '' }">

                <div class="ctx-card-header">
                    <i class="bi bi-files"></i>
                    Base Documental
                    <span class="ctx-badge ms-auto">{{ $readyCnt }}/{{ $totalCnt }}</span>
                </div>

                @if ($chatDocs->isNotEmpty())
                    <div class="ctx-search-wrap">
                        <div class="ctx-search">
                            <i class="bi bi-search"></i>
                            <input type="text"
                                   x-model="search"
                                   placeholder="Buscar…"
                                   class="ctx-search-input">
                        </div>
                    </div>
                @endif

                <div class="ctx-docs-list">
                    @forelse ($chatDocs as $doc)
                        @php $name = $doc->title ?: $doc->original_filename; @endphp
                        <label class="ctx-doc-item"
                               x-show="!search || '{{ addslashes(strtolower($name)) }}'.includes(search.toLowerCase())">
                            @if ($doc->status === 'ready')
                                <input type="checkbox"
                                       class="ctx-doc-check"
                                       :checked="selected.includes('{{ $doc->id }}')"
                                       @change="
                                           const i = selected.indexOf('{{ $doc->id }}');
                                           i >= 0 ? selected.splice(i,1) : selected.push('{{ $doc->id }}');
                                           Livewire.dispatchTo('caso-chat','toggleChatDocument',{id:'{{ $doc->id }}'});
                                       ">
                            @else
                                <input type="checkbox" class="ctx-doc-check" disabled>
                            @endif
                            <div class="ctx-doc-info">
                                <span class="ctx-doc-name" title="{{ $name }}">{{ $name }}</span>
                                @if ($doc->status === 'ready')
                                    <span class="ctx-doc-badge ctx-doc-badge--ready"><i class="bi bi-check-circle-fill"></i> Indexado</span>
                                @elseif ($doc->status === 'processing')
                                    <span class="ctx-doc-badge ctx-doc-badge--processing"><i class="bi bi-hourglass-split"></i> Processando</span>
                                @else
                                    <span class="ctx-doc-badge ctx-doc-badge--pending"><i class="bi bi-clock"></i> Na fila</span>
                                @endif
                            </div>
                        </label>
                    @empty
                        <div class="ctx-empty">
                            <i class="bi bi-file-earmark-x"></i>
                            Nenhum documento neste caso.
                        </div>
                    @endforelse
                </div>

                <div class="ctx-card-footer">
                    <button type="button"
                            class="ctx-add-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#modalUploadChat">
                        <i class="bi bi-plus-lg"></i>
                        Adicionar Documento
                    </button>
                </div>
            </div>

        </aside>
    </div>
</div>

<x-upload-doc-modal :caso="$caso" modal-id="modalUploadChat" />
@endsection

@push('scripts')
<script>
function fillChat(text) {
    window.dispatchEvent(new CustomEvent('fill-chat', { detail: { text } }));
}
</script>
@endpush
