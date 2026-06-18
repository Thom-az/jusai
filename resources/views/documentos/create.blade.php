@extends('layouts.app')

@section('title', 'Enviar Documento')

@push('styles')
    @vite(['resources/css/modules/documentos.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('documents.index') }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <h2 class="fw-semibold mb-0">Enviar Documento</h2>
        </div>

        <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
            <i class="bi bi-cpu fs-5 mt-1"></i>
            <div>
                <div class="fw-semibold">Analise automatica de IA</div>
                <div class="small">PDFs vinculados a um caso serao analisados automaticamente. {{ config('jusai.ai.review_notice') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="surface-card p-4">
                        <h5 class="fw-semibold mb-4">Arquivo</h5>

                        <div class="mb-4">
                            <label for="file" class="form-label fw-semibold"><i class="bi bi-cloud-arrow-up me-1 text-secondary"></i>Selecionar arquivo <span class="text-danger">*</span></label>
                            <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.docx,.doc,.txt">
                            <div class="form-text">Formatos aceitos: PDF, DOCX, DOC, TXT. Tamanho maximo: 20 MB.</div>
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold"><i class="bi bi-type me-1 text-secondary"></i>Titulo <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Nome descritivo do documento">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label for="legal_case_id" class="form-label fw-semibold"><i class="bi bi-folder me-1 text-secondary"></i>Vincular a um caso</label>
                            <select id="legal_case_id" name="legal_case_id" class="form-select @error('legal_case_id') is-invalid @enderror">
                                <option value="">Nenhum caso (standalone)</option>
                                @foreach ($cases as $case)
                                    <option value="{{ $case->id }}" @selected(old('legal_case_id', $selectedCaseId) == $case->id)>{{ $case->title }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">PDFs vinculados a casos recebem analise automatica de IA.</div>
                            @error('legal_case_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="surface-card p-4 mb-4">
                        <h5 class="fw-semibold mb-3">O que acontece apos o envio</h5>
                        <ol class="ps-3 text-secondary small mb-0">
                            <li class="mb-2">Arquivo enviado para armazenamento seguro</li>
                            <li class="mb-2">Para PDFs com caso vinculado: analise de IA iniciada automaticamente</li>
                            <li class="mb-2">Resumo executivo gerado e disponivel na aba do caso</li>
                            <li>Revisao humana obrigatoria antes de uso externo</li>
                        </ol>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Enviar documento
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/modules/documentos-create.js'])
@endpush
