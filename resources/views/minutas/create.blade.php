@extends('layouts.app')

@section('title', 'Nova Minuta')

@push('styles')
    @vite(['resources/css/modules/minutas.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('drafts.index') }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div>
                <h2 class="fw-semibold mb-0">Nova minuta</h2>
                <p class="text-secondary mb-0 small">A IA irá gerar um rascunho completo com base nas suas instruções.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('drafts.store') }}">
                    @csrf
                    <div class="surface-card p-4 mb-4">
                        <h5 class="fw-semibold mb-4">Informações do documento</h5>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-medium">Título <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}"
                                   placeholder="Ex: Petição Inicial — Cobrança de Honorários"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label fw-medium">Tipo de documento <span class="text-danger">*</span></label>
                                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Selecione o tipo…</option>
                                    <option value="peticao_inicial"           {{ old('type') === 'peticao_inicial' ? 'selected' : '' }}>Petição Inicial</option>
                                    <option value="contestacao"               {{ old('type') === 'contestacao' ? 'selected' : '' }}>Contestação</option>
                                    <option value="recurso"                   {{ old('type') === 'recurso' ? 'selected' : '' }}>Recurso</option>
                                    <option value="notificacao_extrajudicial" {{ old('type') === 'notificacao_extrajudicial' ? 'selected' : '' }}>Notificação Extrajudicial</option>
                                    <option value="contrato"                  {{ old('type') === 'contrato' ? 'selected' : '' }}>Contrato</option>
                                    <option value="parecer"                   {{ old('type') === 'parecer' ? 'selected' : '' }}>Parecer Jurídico</option>
                                    <option value="outros"                    {{ old('type') === 'outros' ? 'selected' : '' }}>Outros</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="legal_case_id" class="form-label fw-medium">Caso vinculado</label>
                                <select id="legal_case_id" name="legal_case_id" class="form-select @error('legal_case_id') is-invalid @enderror">
                                    <option value="">Nenhum caso específico</option>
                                    @foreach ($cases as $case)
                                        <option value="{{ $case->id }}" {{ old('legal_case_id') === $case->id ? 'selected' : '' }}>
                                            {{ $case->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('legal_case_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="instructions" class="form-label fw-medium">
                                Instruções para a IA <span class="text-danger">*</span>
                            </label>
                            <textarea id="instructions"
                                      name="instructions"
                                      class="form-control @error('instructions') is-invalid @enderror"
                                      rows="8"
                                      placeholder="Descreva o documento que precisa. Quanto mais detalhes você fornecer, melhor será o resultado.

Ex. petição de cobrança: informe as partes, o valor devido, a origem da dívida e o pedido.
Ex. contrato: informe as partes, o objeto, duração, valor e forma de pagamento.
Ex. notificação: informe quem notifica, quem é notificado, o que exige e o prazo."
                                      required>{{ old('instructions') }}</textarea>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Mínimo de 10 caracteres. Seja específico para melhores resultados.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 align-items-center">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
                            <i class="bi bi-cpu me-2"></i>Gerar minuta com IA
                        </button>
                        <a href="{{ route('drafts.index') }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="surface-card p-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Como funciona</h6>
                    <ol class="small text-secondary ps-3 mb-4">
                        <li class="mb-2">Preencha o título, tipo e as instruções detalhadas</li>
                        <li class="mb-2">A IA irá gerar um rascunho completo em segundos</li>
                        <li class="mb-2">Você poderá editar o conteúdo livremente</li>
                        <li>Salve e encaminhe para revisão do advogado responsável</li>
                    </ol>
                    <x-ai-disclaimer variant="banner" />
                </div>
            </div>
        </div>
    </div>
@endsection
