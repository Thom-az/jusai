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
                        <div class="ai-body">
                            {!! (new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($review->result) !!}
                        </div>
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
                        <div class="d-flex align-items-center gap-2 p-3 rounded small"
                             style="background:rgba(25,135,84,0.08);border:1px solid rgba(25,135,84,0.2)">
                            <i class="bi bi-check-circle-fill text-success flex-shrink-0"></i>
                            <span class="text-success-emphasis">
                                Revisado por <strong>{{ $review->reviewer?->name }}</strong>
                                em {{ $review->reviewed_at->format('d/m/Y \a\s H:i') }}.
                            </span>
                        </div>
                    @endif

                    {{-- Feedback --}}
                    <div class="mt-3 p-3 border rounded" x-data="{ open: {{ $review->feedback_rating ? 'false' : 'true' }} }">
                        <div class="d-flex align-items-center gap-2 cursor-pointer" @click="open = !open" role="button">
                            <i class="bi bi-hand-thumbs-up text-secondary"></i>
                            <span class="small fw-medium">
                                @if ($review->feedback_rating)
                                    Feedback enviado — {{ $review->feedback_rating }}/5 estrelas
                                @else
                                    Avaliar esta análise
                                @endif
                            </span>
                            <i class="bi ms-auto small text-secondary" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </div>
                        <div x-show="open" x-collapse class="mt-3">
                            <form method="POST" action="{{ route('review.feedback', $review) }}">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Qualidade da análise</label>
                                    <div class="d-flex gap-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="feedback_rating"
                                                       id="rating{{ $i }}" value="{{ $i }}"
                                                       {{ $review->feedback_rating == $i ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="rating{{ $i }}">
                                                    {{ $i }}★
                                                </label>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <textarea name="feedback_comment" class="form-control form-control-sm" rows="2"
                                              placeholder="Comentário opcional (o que poderia melhorar?)">{{ $review->feedback_comment }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    Enviar feedback
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="surface-card p-4">
                        <h5 class="fw-semibold mb-3">Detalhes</h5>
                        <dl class="row mb-0">
                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-cpu me-1"></i>Tipo</dt>
                            <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $review->type)) }}</dd>

                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-circle-half me-1"></i>Status</dt>
                            <dd class="col-7"><span class="badge {{ $statusClass }}">{{ ucfirst($review->status) }}</span></dd>

                            @if ($review->tokens_used)
                                <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-lightning me-1"></i>Tokens</dt>
                                <dd class="col-7">{{ number_format($review->tokens_used) }}</dd>
                            @endif

                            @if ($review->ai_model_used)
                                <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-cpu me-1"></i>Modelo</dt>
                                <dd class="col-7 small">{{ $review->ai_model_used }}</dd>
                            @endif

                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-calendar3 me-1"></i>Criado</dt>
                            <dd class="col-7 small">{{ $review->created_at->format('d/m/Y H:i') }}</dd>

                            <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-person-circle me-1"></i>Por</dt>
                            <dd class="col-7 small">{{ $review->creator?->name ?? 'Sistema' }}</dd>

                            @if ($review->legalCase)
                                <dt class="col-5 text-secondary small text-uppercase"><i class="bi bi-folder me-1"></i>Caso</dt>
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
