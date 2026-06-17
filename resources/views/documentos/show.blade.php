@extends('layouts.app')

@section('title', $document->title)

@push('styles')
    @vite(['resources/css/modules/documentos.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="{{ $document->legalCase ? route('cases.show', $document->legalCase) : route('documents.index') }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div class="flex-grow-1">
                <h2 class="fw-semibold mb-1">{{ $document->title }}</h2>
                <div class="d-flex flex-wrap gap-2 align-items-center text-secondary small">
                    <span>{{ $document->original_filename }}</span>
                    <span>&bull;</span>
                    <span>{{ number_format($document->file_size / 1024, 0) }} KB</span>
                    <span>&bull;</span>
                    @php
                        $statusClass = match($document->status) {
                            'ready'      => 'text-bg-success',
                            'processing' => 'text-bg-warning text-dark',
                            'error'      => 'text-bg-danger',
                            default      => 'text-bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($document->status) }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button"
                        class="btn btn-sm btn-primary rounded-pill px-3"
                        data-preview-doc-id="{{ $document->id }}"
                        data-preview-title="{{ $document->title }}">
                    <i class="bi bi-eye me-1"></i>Visualizar
                </button>
                @if ($downloadUrl)
                    <a href="{{ $downloadUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-download me-1"></i>Baixar
                    </a>
                @endif
                <form method="POST" action="{{ route('documents.destroy', $document) }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="btn btn-sm btn-outline-danger rounded-pill px-3"
                            data-confirm-delete="Excluir o documento &quot;{{ $document->title }}&quot; permanentemente?"
                            data-confirm-title="Excluir documento">
                        <i class="bi bi-trash me-1"></i>Excluir
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                @if ($document->ai_summary)
                    <div class="surface-card p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-cpu text-primary"></i>
                            <h5 class="fw-semibold mb-0">Resumo gerado por IA</h5>
                            @if ($document->ai_extracted_at)
                                <span class="badge text-bg-success ms-auto">Processado</span>
                            @endif
                        </div>
                        <div class="ai-summary-body">
                            {!! \League\CommonMark\CommonMarkConverter::create()->convert($document->ai_summary) !!}
                        </div>
                        <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded small text-warning-emphasis">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ config('jusai.ai.review_notice') }}
                        </div>
                    </div>
                @elseif ($document->status === 'processing')
                    <div class="surface-card p-4 mb-4 text-center" id="docProcessingCard"
                         data-status-url="{{ route('documents.status', $document) }}">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <div class="fw-semibold">Processando análise de IA...</div>
                        <div class="text-secondary small">O resumo será gerado em breve. Esta página será atualizada automaticamente.</div>
                    </div>
                @endif

                <div class="surface-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold mb-0">Análises de IA</h5>
                        <a href="{{ route('review.index') }}?case_id={{ $document->legal_case_id }}" wire:navigate class="btn btn-outline-primary rounded-pill btn-sm">
                            <i class="bi bi-plus me-1"></i>Nova análise
                        </a>
                    </div>
                    @forelse ($document->aiReviews->sortByDesc('created_at') as $review)
                        <div class="list-item mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $review->type)) }}</div>
                                    <div class="text-secondary small">{{ $review->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $rStatusClass = match($review->status) {
                                            'concluido'   => 'text-bg-success',
                                            'processando' => 'text-bg-warning text-dark',
                                            'erro'        => 'text-bg-danger',
                                            default       => 'text-bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $rStatusClass }}">{{ ucfirst($review->status) }}</span>
                                    <a href="{{ route('review.show', $review) }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill">Ver</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary small">Nenhuma análise iniciada para este documento.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4">
                <div class="surface-card p-4">
                    <h5 class="fw-semibold mb-3">Informações</h5>
                    <dl class="row mb-0">
                        <dt class="col-5 text-secondary small text-uppercase">Tipo</dt>
                        <dd class="col-7">{{ $document->mime_type }}</dd>

                        <dt class="col-5 text-secondary small text-uppercase">Tamanho</dt>
                        <dd class="col-7">{{ number_format($document->file_size / 1024, 0) }} KB</dd>

                        <dt class="col-5 text-secondary small text-uppercase">Enviado</dt>
                        <dd class="col-7">{{ $document->created_at->format('d/m/Y H:i') }}</dd>

                        @if ($document->ai_extracted_at)
                            <dt class="col-5 text-secondary small text-uppercase">Analisado</dt>
                            <dd class="col-7">{{ $document->ai_extracted_at->format('d/m/Y H:i') }}</dd>
                        @endif

                        @if ($document->legalCase)
                            <dt class="col-5 text-secondary small text-uppercase">Caso</dt>
                            <dd class="col-7">
                                <a href="{{ route('cases.show', $document->legalCase) }}" wire:navigate class="text-decoration-none small">{{ Str::limit($document->legalCase->title, 30) }}</a>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($document->status === 'processing')
@push('scripts')
    @vite(['resources/js/modules/documento-show.js'])
@endpush
@endif
