@extends('layouts.app')

@section('title', $draft->title)

@push('styles')
    @vite(['resources/css/modules/minutas.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="{{ route('drafts.index') }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div class="flex-grow-1">
                <h2 class="fw-semibold mb-1">{{ $draft->title }}</h2>
                <div class="text-secondary small">
                    @php
                        $typeLabel = match($draft->type) {
                            'peticao_inicial'           => 'Petição Inicial',
                            'contestacao'               => 'Contestação',
                            'recurso'                   => 'Recurso',
                            'notificacao_extrajudicial' => 'Notificação Extrajudicial',
                            'contrato'                  => 'Contrato',
                            'parecer'                   => 'Parecer Jurídico',
                            default                     => 'Outros',
                        };
                    @endphp
                    {{ $typeLabel }}
                    @if ($draft->legalCase)
                        &bull; <a href="{{ route('cases.show', $draft->legalCase) }}" wire:navigate class="text-secondary text-decoration-none">{{ $draft->legalCase->title }}</a>
                    @endif
                    &bull; {{ $draft->created_at->diffForHumans() }}
                </div>
            </div>
            @php
                $statusClass = match($draft->status) {
                    'aprovado'   => 'text-bg-success',
                    'publicado'  => 'text-bg-primary',
                    'em_revisao' => 'text-bg-warning text-dark',
                    'rejeitado'  => 'text-bg-danger',
                    default      => 'text-bg-secondary',
                };
            @endphp
            <span class="badge {{ $statusClass }} fs-6 px-3 py-2">{{ ucfirst(str_replace('_', ' ', $draft->status)) }}</span>
        </div>

        @if ($isGenerating)
            <div class="surface-card p-4 mb-4" id="processingCard" data-status-url="{{ route('drafts.status', $draft) }}">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="skeleton skeleton-circle flex-shrink-0" style="width:2.6rem;height:2.6rem;"></div>
                    <div class="flex-grow-1">
                        <div class="skeleton skeleton-heading mb-2" style="width:45%;"></div>
                        <div class="skeleton skeleton-text" style="width:65%;"></div>
                    </div>
                    <div class="skeleton skeleton-badge ms-auto" style="width:80px;"></div>
                </div>
                <div class="skeleton skeleton-text mb-2" style="width:100%;"></div>
                <div class="skeleton skeleton-text mb-2" style="width:97%;"></div>
                <div class="skeleton skeleton-text mb-2" style="width:91%;"></div>
                <div class="skeleton skeleton-text mb-2" style="width:94%;"></div>
                <div class="skeleton skeleton-text mb-2" style="width:88%;"></div>
                <div class="skeleton skeleton-text mb-4" style="width:72%;"></div>
                <div class="skeleton skeleton-text mb-2" style="width:100%;"></div>
                <div class="skeleton skeleton-text mb-2" style="width:95%;"></div>
                <div class="skeleton skeleton-text mb-4" style="width:80%;"></div>
                <div class="d-flex align-items-center gap-2 text-secondary small pt-2" style="border-top:1px solid rgba(215,220,229,0.6);">
                    <div class="spinner-border spinner-border-sm text-primary flex-shrink-0" role="status" aria-label="Gerando"></div>
                    A IA está redigindo o documento. Esta página será atualizada automaticamente.
                </div>
            </div>
        @elseif (str_starts_with($draft->content, '[ERRO NA GERAÇÃO]'))
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Erro ao gerar:</strong> {{ substr($draft->content, 18) }}
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="surface-card p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-journal-richtext text-primary fs-5"></i>
                            <h5 class="fw-semibold mb-0">Conteúdo da minuta</h5>
                            @if ($draft->generated_by_ai)
                                <i class="bi bi-cpu text-secondary ms-1 small" title="Gerado por IA"></i>
                            @endif
                            @if ($draft->ai_model_used)
                                <span class="badge text-bg-secondary ms-auto">{{ $draft->ai_model_used }}</span>
                            @endif
                        </div>
                        <div class="ai-body">
                            {!! (new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($draft->content) !!}
                        </div>
                        @if ($draft->generated_by_ai)
                            <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded small text-warning-emphasis">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ config('jusai.ai.draft_notice') }}
                            </div>
                        @endif
                    </div>

                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('drafts.edit', $draft) }}" wire:navigate class="btn btn-primary rounded-pill px-4 py-2">
                            <i class="bi bi-pencil me-2"></i>Editar minuta
                        </a>
                        <form method="POST" action="{{ route('drafts.destroy', $draft) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-outline-danger rounded-pill px-4 py-2"
                                    data-confirm-delete="Excluir a minuta &quot;{{ $draft->title }}&quot; definitivamente?"
                                    data-confirm-title="Excluir minuta">
                                <i class="bi bi-trash me-2"></i>Excluir
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="surface-card p-4">
                        <h5 class="fw-semibold mb-3">Detalhes</h5>
                        <dl class="row mb-0">
                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-file-text me-1"></i>Tipo</dt>
                            <dd class="col-7 small">{{ $typeLabel }}</dd>

                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-circle-half me-1"></i>Status</dt>
                            <dd class="col-7"><span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $draft->status)) }}</span></dd>

                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-tags me-1"></i>Versão</dt>
                            <dd class="col-7 small">v{{ $draft->version }}</dd>

                            @if ($draft->ai_model_used)
                                <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-cpu me-1"></i>Modelo</dt>
                                <dd class="col-7 small">{{ $draft->ai_model_used }}</dd>
                            @endif

                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-calendar3 me-1"></i>Criado</dt>
                            <dd class="col-7 small">{{ $draft->created_at->format('d/m/Y H:i') }}</dd>

                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-person-circle me-1"></i>Por</dt>
                            <dd class="col-7 small">{{ $draft->creator?->name ?? 'Sistema' }}</dd>

                            @if ($draft->legalCase)
                                <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-folder me-1"></i>Caso</dt>
                                <dd class="col-7">
                                    <a href="{{ route('cases.show', $draft->legalCase) }}" wire:navigate class="small text-decoration-none">
                                        {{ Str::limit($draft->legalCase->title, 30) }}
                                    </a>
                                </dd>
                            @endif

                            @if ($draft->instructions)
                                <dt class="col-12 text-secondary small text-uppercase mt-2"><i class="bi bi-chat-left-text me-1"></i>Instruções originais</dt>
                                <dd class="col-12 small text-secondary mt-1" style="font-style:italic;">
                                    {{ Str::limit($draft->instructions, 200) }}
                                </dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@if ($isGenerating)
@push('scripts')
    @vite(['resources/js/modules/minuta-show.js'])
@endpush
@endif
