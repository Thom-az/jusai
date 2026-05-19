@extends('layouts.app')

@section('title', 'Revisor Juridico')

@push('styles')
    @vite(['resources/css/modules/revisor.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Revisor Juridico</h2>
                <p class="text-secondary mb-0 small">Analise de documentos e pecas com suporte de IA.</p>
            </div>
        </div>

        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="surface-card p-4">
                    <h5 class="fw-semibold mb-4">Nova analise de IA</h5>

                    <form method="POST" action="{{ route('review.store') }}" id="reviewForm">
                        @csrf

                        <div class="mb-3">
                            <label for="type" class="form-label fw-semibold">Tipo de analise <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">Selecionar...</option>
                                <option value="resumo_caso" @selected(old('type') === 'resumo_caso')>Resumo do caso</option>
                                <option value="analise_documento" @selected(old('type') === 'analise_documento')>Analise de documento</option>
                                <option value="revisao_minuta" @selected(old('type') === 'revisao_minuta')>Revisao de minuta</option>
                                <option value="pesquisa_juridica" @selected(old('type') === 'pesquisa_juridica')>Pesquisa juridica</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="legal_case_id" class="form-label fw-semibold">Caso <span class="text-danger">*</span></label>
                            <select id="legal_case_id" name="legal_case_id" class="form-select @error('legal_case_id') is-invalid @enderror">
                                <option value="">Selecionar caso...</option>
                                @foreach ($cases as $case)
                                    <option value="{{ $case->id }}" @selected(old('legal_case_id', request('case_id')) == $case->id)>{{ $case->title }}</option>
                                @endforeach
                            </select>
                            @error('legal_case_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div id="fieldDocumento" class="mb-3" style="display:none;">
                            <label for="document_id" class="form-label fw-semibold">Documento</label>
                            <select id="document_id" name="document_id" class="form-select @error('document_id') is-invalid @enderror">
                                <option value="">Selecionar documento...</option>
                                @foreach ($documents as $doc)
                                    <option value="{{ $doc->id }}" @selected(old('document_id') == $doc->id)>{{ $doc->title }}</option>
                                @endforeach
                            </select>
                            @error('document_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div id="fieldMinuta" class="mb-3" style="display:none;">
                            <label for="draft_id" class="form-label fw-semibold">Minuta</label>
                            <select id="draft_id" name="draft_id" class="form-select @error('draft_id') is-invalid @enderror">
                                <option value="">Selecionar minuta...</option>
                                @foreach ($drafts as $draft)
                                    <option value="{{ $draft->id }}" @selected(old('draft_id') == $draft->id)>{{ $draft->title }}</option>
                                @endforeach
                            </select>
                            @error('draft_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div id="fieldPergunta" class="mb-3" style="display:none;">
                            <label for="question" class="form-label fw-semibold">Pergunta juridica <span class="text-danger">*</span></label>
                            <textarea id="question" name="question" class="form-control @error('question') is-invalid @enderror" rows="3" placeholder="Ex: Quais os riscos de rescisao sem justa causa neste contrato?">{{ old('question') }}</textarea>
                            @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="alert alert-warning small mb-4">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ config('jusai.ai.review_notice') }}
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                            <i class="bi bi-cpu me-2"></i>Iniciar analise
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="surface-card p-4">
                    <h5 class="fw-semibold mb-4">Analises recentes</h5>

                    @forelse ($reviews as $review)
                        <div class="list-item mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $review->type)) }}</div>
                                    <div class="text-secondary small">
                                        {{ Str::limit($review->legalCase?->title ?? '—', 50) }}
                                        &bull; {{ $review->created_at->diffForHumans() }}
                                    </div>
                                    @if ($review->document)
                                        <div class="text-secondary small">Doc: {{ $review->document->title }}</div>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    @php
                                        $statusClass = match($review->status) {
                                            'concluido'   => 'text-bg-success',
                                            'processando' => 'text-bg-warning text-dark',
                                            'erro'        => 'text-bg-danger',
                                            default       => 'text-bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($review->status) }}</span>
                                    @if ($review->reviewed_at)
                                        <span class="badge text-bg-info">Revisado</span>
                                    @endif
                                    <a href="{{ route('review.show', $review) }}" class="btn btn-sm btn-outline-primary rounded-pill">Ver</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-secondary py-4">
                            <i class="bi bi-cpu fs-2 d-block mb-2"></i>
                            Nenhuma analise realizada ainda.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/modules/revisor-index.js'])
@endpush
