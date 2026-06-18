@extends('layouts.app')

@section('title', 'Editar Minuta')

@push('styles')
    @vite(['resources/css/modules/minutas.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('drafts.show', $draft) }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div>
                <h2 class="fw-semibold mb-0">Editar minuta</h2>
                <p class="text-secondary mb-0 small">{{ $draft->title }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('drafts.update', $draft) }}">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="surface-card p-4 mb-4">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-medium"><i class="bi bi-type me-1 text-secondary"></i>Título <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $draft->title) }}"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label fw-medium"><i class="bi bi-file-text me-1 text-secondary"></i>Conteúdo <span class="text-danger">*</span></label>
                            <textarea id="content"
                                      name="content"
                                      class="form-control font-monospace @error('content') is-invalid @enderror"
                                      rows="24"
                                      required>{{ old('content', $draft->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Edite o conteúdo gerado pela IA. Use [PREENCHER: ...] como marcadores para dados que precisam ser completados.</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="surface-card p-4 mb-4">
                        <h6 class="fw-semibold mb-3">Configurações</h6>

                        <div class="mb-3">
                            <label for="status" class="form-label fw-medium"><i class="bi bi-circle-half me-1 text-secondary"></i>Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="rascunho"   {{ old('status', $draft->status) === 'rascunho' ? 'selected' : '' }}>Rascunho</option>
                                <option value="em_revisao" {{ old('status', $draft->status) === 'em_revisao' ? 'selected' : '' }}>Em revisão</option>
                                <option value="aprovado"   {{ old('status', $draft->status) === 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                                <option value="rejeitado"  {{ old('status', $draft->status) === 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                                <option value="publicado"  {{ old('status', $draft->status) === 'publicado' ? 'selected' : '' }}>Publicado</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="legal_case_id" class="form-label fw-medium"><i class="bi bi-folder me-1 text-secondary"></i>Caso vinculado</label>
                            <select id="legal_case_id" name="legal_case_id" class="form-select @error('legal_case_id') is-invalid @enderror">
                                <option value="">Nenhum</option>
                                @foreach ($cases as $case)
                                    <option value="{{ $case->id }}" {{ old('legal_case_id', $draft->legal_case_id) === $case->id ? 'selected' : '' }}>
                                        {{ $case->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('legal_case_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">
                            <i class="bi bi-floppy me-2"></i>Salvar alterações
                        </button>
                        <a href="{{ route('drafts.show', $draft) }}" wire:navigate class="btn btn-outline-secondary rounded-pill py-2">
                            Cancelar
                        </a>
                    </div>

                    @if ($draft->generated_by_ai)
                        <div class="mt-4">
                            <x-ai-disclaimer variant="banner" />
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection
