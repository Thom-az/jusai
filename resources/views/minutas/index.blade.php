@extends('layouts.app')

@section('title', 'Minutas')

@push('styles')
    @vite(['resources/css/modules/minutas.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Minutas</h2>
                <p class="text-secondary mb-0 small">Rascunhos jurídicos gerados com IA.</p>
            </div>
            <button type="button"
                    class="btn btn-primary rounded-pill px-4"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNovaMinuta">
                <i class="bi bi-journal-plus me-2"></i>Nova minuta
            </button>
        </div>

        @if ($drafts->isEmpty())
            <div class="surface-card p-5">
                <x-empty-state
                    icon="bi-journal-richtext"
                    title="Nenhuma minuta ainda"
                    description="Crie sua primeira minuta jurídica gerada por IA. Descreva o documento que precisa e a IA irá redigir um rascunho completo para revisão."
                    size="lg"
                />
                <div class="text-center mt-4">
                    <button type="button"
                            class="btn btn-primary rounded-pill px-4"
                            data-bs-toggle="modal"
                            data-bs-target="#modalNovaMinuta">
                        <i class="bi bi-journal-plus me-2"></i>Criar primeira minuta
                    </button>
                </div>
            </div>
        @else
            <div class="surface-card p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Título</th>
                                <th>Tipo</th>
                                <th>Caso</th>
                                <th>Status</th>
                                <th>Criado por</th>
                                <th>Data</th>
                                <th style="width:3.5rem"></th>
                            </tr>
                        </thead>
                        <tbody class="stagger-list">
                            @foreach ($drafts as $draft)
                                @php
                                    $isGenerating = $draft->generated_by_ai && $draft->content === '';
                                    $statusClass = match($draft->status) {
                                        'aprovado'   => 'status-badge--success',
                                        'publicado'  => 'status-badge--info',
                                        'em_revisao' => 'status-badge--warning',
                                        'rejeitado'  => 'status-badge--danger',
                                        default      => 'status-badge--secondary',
                                    };
                                    $typeLabel = match($draft->type) {
                                        'peticao_inicial'           => 'Petição Inicial',
                                        'contestacao'               => 'Contestação',
                                        'recurso'                   => 'Recurso',
                                        'notificacao_extrajudicial' => 'Notificação',
                                        'contrato'                  => 'Contrato',
                                        'parecer'                   => 'Parecer',
                                        default                     => 'Outros',
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('drafts.show', $draft) }}" wire:navigate class="fw-medium text-decoration-none">
                                            {{ $draft->title }}
                                        </a>
                                        @if ($isGenerating)
                                            <span class="ms-2 badge text-bg-warning text-dark small">
                                                <span class="spinner-border spinner-border-sm me-1" style="width:.65rem;height:.65rem;"></span>Gerando…
                                            </span>
                                        @endif
                                        @if ($draft->generated_by_ai)
                                            <i class="bi bi-cpu ms-1 text-primary small" title="Gerado por IA"></i>
                                        @endif
                                    </td>
                                    <td><span class="badge text-bg-light border text-dark small">{{ $typeLabel }}</span></td>
                                    <td class="small text-secondary">
                                        @if ($draft->legalCase)
                                            <a href="{{ route('cases.show', $draft->legalCase) }}" wire:navigate class="text-decoration-none text-secondary">
                                                {{ Str::limit($draft->legalCase->title, 30) }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td><span class="status-badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $draft->status)) }}</span></td>
                                    <td class="small text-secondary">{{ $draft->creator?->name ?? '—' }}</td>
                                    <td class="small text-secondary">{{ $draft->updated_at->format('d/m/Y') }}</td>
                                    <td class="pe-3 text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-secondary p-1 lh-1"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical fs-6"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('drafts.show', $draft) }}" wire:navigate>
                                                        <i class="bi bi-eye me-2 text-secondary"></i>Ver
                                                    </a>
                                                </li>
                                                @if (!$isGenerating)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('drafts.edit', $draft) }}" wire:navigate>
                                                        <i class="bi bi-pencil me-2 text-secondary"></i>Editar
                                                    </a>
                                                </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('drafts.destroy', $draft) }}">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit"
                                                                data-confirm-delete="Excluir a minuta &quot;{{ addslashes($draft->title) }}&quot; permanentemente?"
                                                                data-confirm-title="Excluir minuta">
                                                            <i class="bi bi-trash me-2"></i>Excluir
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($drafts->hasPages())
                    <div class="px-4 py-3 border-top">
                        {{ $drafts->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection

{{-- Modal: Nova minuta --}}
<x-modal id="modalNovaMinuta" title="Nova minuta" size="lg">
    <form method="POST" action="{{ route('drafts.store') }}" id="formNovaMinuta">
        @csrf

        <div class="mb-3">
            <label for="nm_title" class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
            <input type="text" id="nm_title" name="title"
                   class="form-control @error('title') is-invalid @enderror"
                   placeholder="Ex: Petição Inicial — Cobrança de Honorários"
                   value="{{ old('title') }}">
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="nm_type" class="form-label fw-semibold">Tipo de documento <span class="text-danger">*</span></label>
                <select id="nm_type" name="type" class="form-select @error('type') is-invalid @enderror">
                    <option value="">Selecione o tipo…</option>
                    <option value="peticao_inicial"           @selected(old('type') === 'peticao_inicial')>Petição Inicial</option>
                    <option value="contestacao"               @selected(old('type') === 'contestacao')>Contestação</option>
                    <option value="recurso"                   @selected(old('type') === 'recurso')>Recurso</option>
                    <option value="notificacao_extrajudicial" @selected(old('type') === 'notificacao_extrajudicial')>Notificação Extrajudicial</option>
                    <option value="contrato"                  @selected(old('type') === 'contrato')>Contrato</option>
                    <option value="parecer"                   @selected(old('type') === 'parecer')>Parecer Jurídico</option>
                    <option value="outros"                    @selected(old('type') === 'outros')>Outros</option>
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="nm_case" class="form-label fw-semibold">Caso vinculado</label>
                <select id="nm_case" name="legal_case_id" class="form-select @error('legal_case_id') is-invalid @enderror">
                    <option value="">Nenhum caso específico</option>
                    @foreach ($cases as $case)
                        <option value="{{ $case->id }}" @selected(old('legal_case_id') === $case->id)>{{ $case->title }}</option>
                    @endforeach
                </select>
                @error('legal_case_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="nm_instructions" class="form-label fw-semibold">Instruções para a IA <span class="text-danger">*</span></label>
            <textarea id="nm_instructions" name="instructions"
                      class="form-control @error('instructions') is-invalid @enderror"
                      rows="6"
                      placeholder="Descreva o documento que precisa. Quanto mais detalhes você fornecer, melhor será o resultado.

Ex. petição de cobrança: informe as partes, o valor devido, a origem da dívida e o pedido.
Ex. contrato: informe as partes, o objeto, duração, valor e forma de pagamento.">{{ old('instructions') }}</textarea>
            <div class="form-text">Mínimo de 10 caracteres. Seja específico para melhores resultados.</div>
            @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <x-ai-disclaimer class="mb-0" />
    </form>
    <x-slot name="footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formNovaMinuta" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-cpu me-2"></i>Gerar minuta com IA
        </button>
    </x-slot>
</x-modal>

@if ($errors->hasAny(['title', 'type', 'instructions', 'legal_case_id']))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('modalNovaMinuta');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endpush
@endif
