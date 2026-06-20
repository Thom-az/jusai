@extends('layouts.app')

@section('title', 'Chat — ' . $caso->title)

@section('content')
    <div class="container-fluid px-0">

        {{-- Cabeçalho da página --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('cases.show', $caso) }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar ao caso
            </a>
            <div>
                <h2 class="fw-semibold mb-1">Assistente Jurídico</h2>
                <p class="text-secondary mb-0 small">
                    <i class="bi bi-briefcase me-1"></i>{{ $caso->title }}
                </p>
            </div>
            <span class="badge text-bg-secondary ms-auto">
                <i class="bi bi-cpu me-1"></i>IA
            </span>
        </div>

        <div class="row g-4">

            {{-- Chat --}}
            <div class="col-lg-8">
                <div class="surface-card p-0 overflow-hidden">
                    <livewire:caso-chat :caso="$caso" />
                </div>
            </div>

            {{-- Sidebar com 3 cards --}}
            <div class="col-lg-4 d-flex flex-column gap-3">

                {{-- Card 1: Detalhes do caso --}}
                <div class="surface-card p-4">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-briefcase me-2 text-primary"></i>Contexto do caso
                    </h6>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-secondary fw-normal" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">Área</dt>
                        <dd class="col-7 mb-2">{{ $caso->area ?? '—' }}</dd>

                        <dt class="col-5 text-secondary fw-normal" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">Status</dt>
                        <dd class="col-7 mb-2">{{ ucfirst(str_replace('_', ' ', $caso->status)) }}</dd>

                        @if ($caso->client_name)
                            <dt class="col-5 text-secondary fw-normal" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">Cliente</dt>
                            <dd class="col-7 mb-2">{{ $caso->client_name }}</dd>
                        @endif

                        @if ($caso->risk_level)
                            <dt class="col-5 text-secondary fw-normal" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">Risco</dt>
                            <dd class="col-7 mb-0">
                                <span class="risk-badge risk-{{ $caso->risk_level }}">{{ ucfirst($caso->risk_level) }}</span>
                            </dd>
                        @endif
                    </dl>
                </div>

                {{-- Card 2: Base documental --}}
                @php
                    $chatDocs = $caso->documents()
                        ->whereIn('status', ['ready', 'pending', 'processing'])
                        ->orderByDesc('created_at')
                        ->get(['id', 'title', 'original_filename', 'status']);
                    $readyDocIds = $chatDocs->where('status', 'ready')->pluck('id')->values()->toArray();
                @endphp
                <div class="surface-card p-4"
                     x-data="{ selected: {{ json_encode($readyDocIds) }} }">
                    <h6 class="fw-semibold mb-1">
                        <i class="bi bi-files me-2 text-primary"></i>Base documental
                    </h6>
                    <p class="text-secondary mb-3" style="font-size:.75rem;">
                        Ative os documentos que a IA deve consultar.
                    </p>

                    @if ($chatDocs->isEmpty())
                        <p class="text-secondary small mb-0">
                            <i class="bi bi-info-circle me-1"></i>Nenhum documento neste caso.
                        </p>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach ($chatDocs as $doc)
                                @php $docName = $doc->title ?: $doc->original_filename; @endphp
                                <div class="d-flex align-items-center gap-2">
                                    {{-- Toggle switch --}}
                                    <div class="form-check form-switch mb-0 flex-shrink-0">
                                        @if ($doc->status === 'ready')
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   role="switch"
                                                   :checked="selected.includes('{{ $doc->id }}')"
                                                   style="cursor:pointer;"
                                                   @change="
                                                       const idx = selected.indexOf('{{ $doc->id }}');
                                                       idx >= 0 ? selected.splice(idx, 1) : selected.push('{{ $doc->id }}');
                                                       Livewire.dispatchTo('caso-chat', 'toggleChatDocument', { id: '{{ $doc->id }}' });
                                                   ">
                                        @else
                                            <input class="form-check-input" type="checkbox" role="switch" disabled>
                                        @endif
                                    </div>

                                    {{-- Info do documento --}}
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="fw-medium text-truncate" style="font-size:.8rem;" title="{{ $docName }}">
                                            {{ $docName }}
                                        </div>
                                        <div class="text-secondary" style="font-size:.68rem;">
                                            @if ($doc->status === 'ready')
                                                <i class="bi bi-check-circle-fill text-success me-1"></i>Pronto para consulta
                                            @elseif ($doc->status === 'processing')
                                                <i class="bi bi-hourglass-split text-warning me-1"></i>Processando…
                                            @else
                                                <i class="bi bi-clock text-secondary me-1"></i>Na fila
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-3 pt-3 border-top">
                        <a href="{{ route('cases.show', $caso) }}"
                           wire:navigate
                           class="btn btn-outline-secondary btn-sm rounded-pill w-100"
                           style="font-size:.75rem;">
                            <i class="bi bi-cloud-upload me-1"></i>Adicionar documento
                        </a>
                    </div>
                </div>

                {{-- Card 3: Sugestões de perguntas --}}
                <div class="surface-card p-4">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-lightbulb me-2 text-warning"></i>Sugestões de perguntas
                    </h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-1">
                        <li class="chat-suggestion"
                            onclick="fillChat('Qual é o prazo processual mais urgente neste caso?')">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>Qual é o prazo processual mais urgente?
                        </li>
                        <li class="chat-suggestion"
                            onclick="fillChat('Quais são os principais riscos jurídicos deste caso?')">
                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Quais são os principais riscos jurídicos?
                        </li>
                        <li class="chat-suggestion"
                            onclick="fillChat('Quais documentos ainda precisamos reunir para este caso?')">
                            <i class="bi bi-file-earmark-plus me-2 text-info"></i>Quais documentos ainda precisamos reunir?
                        </li>
                        <li class="chat-suggestion"
                            onclick="fillChat('Qual é a tese jurídica mais sólida para este caso?')">
                            <i class="bi bi-search me-2 text-success"></i>Qual é a tese jurídica mais sólida?
                        </li>
                        <li class="chat-suggestion"
                            onclick="fillChat('Que estratégia você recomendaria para este caso?')">
                            <i class="bi bi-lightbulb me-2 text-warning"></i>Que estratégia você recomendaria?
                        </li>
                    </ul>
                </div>

            </div>{{-- /col-lg-4 --}}
        </div>{{-- /row --}}
    </div>
@endsection

@push('scripts')
<script>
function fillChat(text) {
    window.dispatchEvent(new CustomEvent('fill-chat', { detail: { text } }));
}
</script>
@endpush
