@extends('layouts.admin')

@section('title', 'Prompts de IA')

@push('styles')
    @vite(['resources/css/modules/admin/prompts.css'])
@endpush

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-semibold mb-1">Prompts de IA</h2>
            <p class="text-secondary mb-0 small">Gerencie as instruções enviadas aos modelos de linguagem. Alterações entram em vigor imediatamente.</p>
        </div>
        <span class="badge text-bg-primary rounded-pill px-3 py-2">
            <i class="bi bi-braces me-1"></i>{{ $prompts->flatten()->count() }} prompts
        </span>
    </div>

    @foreach ([
        'system' => ['Prompts do Sistema', 'Instruções enviadas à IA real (Anthropic Claude). Afetam qualidade e comportamento das análises.', 'bi-cpu', 'text-primary'],
        'mock'   => ['Respostas Mock', 'Textos retornados quando AI_PROVIDER=mock. Usados para testes sem custo de API.', 'bi-flask', 'text-warning'],
    ] as $group => [$title, $subtitle, $icon, $iconColor])
        @if ($prompts->has($group))
        <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi {{ $icon }} {{ $iconColor }} fs-5"></i>
                <div>
                    <h5 class="fw-semibold mb-0">{{ $title }}</h5>
                    <p class="text-secondary small mb-0">{{ $subtitle }}</p>
                </div>
            </div>

            <div class="surface-card p-0 overflow-hidden">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary small text-uppercase fw-semibold" style="font-size:.72rem;letter-spacing:.05em;">Prompt</th>
                            <th class="py-3 text-secondary small text-uppercase fw-semibold d-none d-md-table-cell" style="font-size:.72rem;letter-spacing:.05em;">Chave</th>
                            <th class="py-3 text-secondary small text-uppercase fw-semibold d-none d-lg-table-cell" style="font-size:.72rem;letter-spacing:.05em;">Última edição</th>
                            <th class="py-3 pe-4 text-end" style="width:200px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prompts[$group] as $prompt)
                        <tr data-prompt-key="{{ $prompt->key }}">
                            <td class="ps-4 py-3">
                                <div class="fw-semibold small">{{ $prompt->label }}</div>
                                @if ($prompt->description)
                                    <div class="text-secondary" style="font-size:.78rem;">{{ $prompt->description }}</div>
                                @endif
                            </td>
                            <td class="py-3 d-none d-md-table-cell">
                                <code class="prompt-key-badge">{{ $prompt->key }}</code>
                            </td>
                            <td class="py-3 d-none d-lg-table-cell text-secondary small">
                                <span class="prompt-updated-at" data-key="{{ $prompt->key }}">
                                    @if ($prompt->updated_by)
                                        {{ $prompt->updatedBy?->name }} &bull; {{ $prompt->updated_at->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted fst-italic">padrão</span>
                                    @endif
                                </span>
                            </td>
                            <td class="py-3 pe-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalPreviewPrompt"
                                            data-key="{{ $prompt->key }}"
                                            data-label="{{ $prompt->label }}"
                                            data-content="{{ htmlspecialchars($prompt->content) }}">
                                        <i class="bi bi-eye me-1"></i>Ver
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-primary rounded-pill px-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditPrompt"
                                            data-key="{{ $prompt->key }}"
                                            data-label="{{ $prompt->label }}"
                                            data-content="{{ htmlspecialchars($prompt->content) }}">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endforeach

</div>

{{-- ============================================================ --}}
{{-- MODAL: Preview (read-only)                                    --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalPreviewPrompt" tabindex="-1" aria-labelledby="previewPromptLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="previewPromptLabel">
                    <i class="bi bi-eye me-2 text-secondary"></i><span id="previewLabel">Visualizar prompt</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="px-4 py-2 border-bottom d-flex align-items-center gap-2" style="background:rgba(0,0,0,.025);">
                    <code class="small text-secondary" id="previewKey"></code>
                    <span class="badge text-bg-secondary ms-auto" id="previewChars"></span>
                </div>
                <pre id="previewContent" class="prompt-pre p-4 mb-0"></pre>
            </div>
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-primary rounded-pill px-4"
                        id="btnPreviewToEdit">
                    <i class="bi bi-pencil me-1"></i>Editar este prompt
                </button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: Editar                                                  --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalEditPrompt" tabindex="-1" aria-labelledby="editPromptLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="editPromptLabel">
                    <i class="bi bi-pencil me-2 text-primary"></i><span id="editLabel">Editar prompt</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <code class="small text-secondary" id="editKey"></code>
                    <span class="badge text-bg-secondary ms-auto" id="editChars"></span>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold text-secondary text-uppercase" style="font-size:.72rem;letter-spacing:.05em;">Conteúdo do prompt</label>
                    <textarea id="editContent"
                              class="form-control prompt-textarea font-monospace"
                              rows="20"
                              spellcheck="false"
                              placeholder="Digite o conteúdo do prompt..."></textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <div class="text-danger small d-none" id="editError"></div>
                        <small class="text-secondary ms-auto" id="editCharCount">0 caracteres</small>
                    </div>
                </div>
                <div class="p-3 rounded" style="background:rgba(37,99,235,.04);border:1px solid rgba(37,99,235,.1);">
                    <div class="small text-secondary">
                        <i class="bi bi-info-circle me-1 text-primary"></i>
                        O prompt entra em vigor imediatamente após salvar (cache de 10 min).
                        Use <strong>[PREENCHER: ...]</strong> para campos variáveis que a IA deve preencher com dados do caso.
                    </div>
                </div>
            </div>
            <div class="modal-footer gap-2">
                <button type="button"
                        class="btn btn-outline-danger rounded-pill px-4 me-auto"
                        id="btnResetPrompt">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar padrão
                </button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button"
                        class="btn btn-primary rounded-pill px-4"
                        id="btnSavePrompt">
                    <i class="bi bi-check-lg me-1"></i>Salvar prompt
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: Confirmar reset                                         --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalConfirmReset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Restaurar padrão?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary mb-0">
                    O conteúdo atual do prompt <strong id="resetKeyLabel"></strong> será substituído pela versão original do sistema. Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button"
                        class="btn btn-danger rounded-pill px-4"
                        id="btnConfirmReset">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Sim, restaurar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/modules/admin/prompts.js'])
@endpush
