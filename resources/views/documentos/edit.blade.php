@extends('layouts.app')

@section('title', 'Editar Documento')

@push('styles')
    @vite(['resources/css/modules/documentos.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="{{ route('documents.show', $document) }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div class="flex-grow-1">
                <h2 class="fw-semibold mb-1">Editar documento</h2>
                <p class="text-secondary mb-0 small">{{ $document->original_filename }}</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="surface-card p-4">
                    <form method="POST" action="{{ route('documents.update', $document) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">
                                Título <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $document->title) }}"
                                   required
                                   maxlength="255">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="legal_case_id" class="form-label fw-semibold">Caso vinculado</label>
                            <select id="legal_case_id" name="legal_case_id"
                                    class="form-select @error('legal_case_id') is-invalid @enderror">
                                <option value="">— Nenhum caso —</option>
                                @foreach ($cases as $case)
                                    <option value="{{ $case->id }}"
                                        @selected(old('legal_case_id', $document->legal_case_id) == $case->id)>
                                        {{ $case->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('legal_case_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
                                <i class="bi bi-check-lg me-2"></i>Salvar alterações
                            </button>
                            <a href="{{ route('documents.show', $document) }}" wire:navigate
                               class="btn btn-outline-secondary rounded-pill px-4 py-2">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="surface-card p-4">
                    <h6 class="fw-semibold mb-3">Informações do arquivo</h6>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-secondary text-uppercase">Arquivo</dt>
                        <dd class="col-7 text-truncate">{{ $document->original_filename }}</dd>

                        <dt class="col-5 text-secondary text-uppercase">Tipo</dt>
                        <dd class="col-7">{{ $document->mime_type }}</dd>

                        <dt class="col-5 text-secondary text-uppercase">Tamanho</dt>
                        <dd class="col-7">{{ number_format($document->file_size / 1024, 0) }} KB</dd>

                        <dt class="col-5 text-secondary text-uppercase">Status</dt>
                        <dd class="col-7">
                            @php
                                $statusClass = match($document->status) {
                                    'ready'      => 'text-bg-success',
                                    'processing' => 'text-bg-warning text-dark',
                                    'error'      => 'text-bg-danger',
                                    default      => 'text-bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($document->status) }}</span>
                        </dd>

                        <dt class="col-5 text-secondary text-uppercase">Enviado em</dt>
                        <dd class="col-7">{{ $document->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
