@extends('layouts.app')

@section('title', 'Analise de IA')

@push('styles')
    @vite(['resources/css/modules/revisor.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="{{ route('review.index') }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div class="flex-grow-1">
                <h2 class="fw-semibold mb-1">{{ ucfirst(str_replace('_', ' ', $review->type)) }}</h2>
                <div class="text-secondary small">
                    {{ $review->legalCase?->title ?? '—' }}
                    @if ($review->document)
                        &bull; {{ $review->document->title }}
                    @endif
                    &bull; {{ $review->created_at->diffForHumans() }}
                </div>
            </div>
            @php
                $statusClass = match($review->status) {
                    'concluido'   => 'text-bg-success',
                    'processando' => 'text-bg-warning text-dark',
                    'erro'        => 'text-bg-danger',
                    default       => 'text-bg-secondary',
                };
            @endphp
            <span class="badge {{ $statusClass }} fs-6 px-3 py-2">{{ ucfirst($review->status) }}</span>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($isProcessing)
            <div class="surface-card p-4 mb-4" id="processingCard" data-status-url="{{ route('review.status', $review) }}">
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
                    <div class="spinner-border spinner-border-sm text-primary flex-shrink-0" role="status" aria-label="Processando"></div>
                    A IA esta analisando o documento. Esta pagina sera atualizada automaticamente.
                </div>
            </div>
        @elseif ($review->status === 'erro')
            <div class="alert alert-danger mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Erro ao processar:</strong> {{ $review->result }}
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="surface-card p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-cpu text-primary fs-5"></i>
                            <h5 class="fw-semibold mb-0">Resultado da analise</h5>
                            @if ($review->ai_model_used)
                                <span class="badge text-bg-secondary ms-auto">{{ $review->ai_model_used }}</span>
                            @endif
                        </div>
                        <div class="result-content" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.7;">{{ $review->result }}</div>
                        <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded small text-warning-emphasis">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ config('jusai.ai.review_notice') }}
                        </div>
                    </div>

                    @if (!$review->reviewed_at)
                        <form method="POST" action="{{ route('review.approve', $review) }}">
                            @csrf
                            <button type="submit" class="btn btn-success rounded-pill px-4 py-2">
                                <i class="bi bi-check-circle me-2"></i>Confirmar revisao humana
                            </button>
                        </form>
                    @else
                        <div class="alert alert-success d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                Revisado por <strong>{{ $review->reviewer?->name }}</strong>
                                em {{ $review->reviewed_at->format('d/m/Y \a\s H:i') }}.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="surface-card p-4">
                        <h5 class="fw-semibold mb-3">Detalhes</h5>
                        <dl class="row mb-0">
                            <dt class="col-5 text-secondary small text-uppercase">Tipo</dt>
                            <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $review->type)) }}</dd>

                            <dt class="col-5 text-secondary small text-uppercase">Status</dt>
                            <dd class="col-7"><span class="badge {{ $statusClass }}">{{ ucfirst($review->status) }}</span></dd>

                            @if ($review->tokens_used)
                                <dt class="col-5 text-secondary small text-uppercase">Tokens</dt>
                                <dd class="col-7">{{ number_format($review->tokens_used) }}</dd>
                            @endif

                            @if ($review->ai_model_used)
                                <dt class="col-5 text-secondary small text-uppercase">Modelo</dt>
                                <dd class="col-7 small">{{ $review->ai_model_used }}</dd>
                            @endif

                            <dt class="col-5 text-secondary small text-uppercase">Criado</dt>
                            <dd class="col-7 small">{{ $review->created_at->format('d/m/Y H:i') }}</dd>

                            <dt class="col-5 text-secondary small text-uppercase">Por</dt>
                            <dd class="col-7 small">{{ $review->creator?->name ?? 'Sistema' }}</dd>

                            @if ($review->legalCase)
                                <dt class="col-5 text-secondary small text-uppercase">Caso</dt>
                                <dd class="col-7">
                                    <a href="{{ route('cases.show', $review->legalCase) }}" wire:navigate class="small text-decoration-none">{{ Str::limit($review->legalCase->title, 30) }}</a>
                                </dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@if ($isProcessing)
@push('scripts')
    @vite(['resources/js/modules/revisor-show.js'])
@endpush
@endif
