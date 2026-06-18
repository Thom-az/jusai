@extends('layouts.app')

@section('title', $case->title)

@push('styles')
    @vite(['resources/css/modules/casos.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="{{ route('cases.index') }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div class="flex-grow-1">
                <h2 class="fw-semibold mb-1">{{ $case->title }}</h2>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge text-bg-primary">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</span>
                    <span class="badge text-bg-secondary">{{ ucfirst($case->area) }}</span>
                    @if ($case->risk_level)
                        @php
                            $riskClass = match($case->risk_level) {
                                'baixo'   => 'text-bg-success',
                                'medio'   => 'text-bg-warning text-dark',
                                'alto'    => 'text-bg-danger',
                                'critico' => 'text-bg-dark',
                                default   => 'text-bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $riskClass }}">Risco {{ ucfirst($case->risk_level) }}</span>
                    @endif
                    <span class="text-secondary small">Cliente: {{ $case->client_name }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('cases.chat', $case) }}" wire:navigate class="btn btn-primary rounded-pill px-3">
                    <i class="bi bi-chat-dots me-1"></i>Assistente IA
                </a>
                <button type="button"
                        class="btn btn-outline-secondary rounded-pill px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditCaso">
                    <i class="bi bi-pencil me-1"></i>Editar
                </button>
                <form method="POST" action="{{ route('cases.destroy', $case) }}" id="formDeleteCaso">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="btn btn-outline-danger rounded-pill px-3"
                            data-confirm-delete="Excluir o caso &quot;{{ $case->title }}&quot; permanentemente? Documentos e análises associados serão removidos."
                            data-confirm-title="Excluir caso">
                        <i class="bi bi-trash me-1"></i>Excluir
                    </button>
                </form>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4" id="caseTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#documentos">
                    <i class="bi bi-file-earmark-text me-1"></i>Documentos
                    <span class="badge text-bg-secondary ms-1">{{ $case->documents->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#analises">
                    <i class="bi bi-cpu me-1"></i>Análises de IA
                    <span class="badge text-bg-secondary ms-1">{{ $case->aiReviews->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#detalhes">
                    <i class="bi bi-info-circle me-1"></i>Detalhes
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="documentos">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold mb-0">Documentos do caso</h5>
                    <button type="button"
                            class="btn btn-primary rounded-pill"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEnviarDocCaso">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Enviar documento
                    </button>
                </div>
                @forelse ($case->documents as $doc)
                    <div class="surface-card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <a href="{{ route('documents.show', $doc) }}" wire:navigate class="fw-semibold text-decoration-none">{{ $doc->title }}</a>
                                <div class="text-secondary small">{{ $doc->original_filename }} &bull; {{ number_format($doc->file_size / 1024, 0) }} KB</div>
                                @if ($doc->ai_summary)
                                    <p class="text-secondary small mt-1 mb-0">{{ Str::limit($doc->ai_summary, 120) }}</p>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $statusClass = match($doc->status) {
                                        'ready'      => 'text-bg-success',
                                        'processing' => 'text-bg-warning text-dark',
                                        'error'      => 'text-bg-danger',
                                        default      => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($doc->status) }}</span>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-pill"
                                        data-preview-doc-id="{{ $doc->id }}"
                                        data-preview-title="{{ $doc->title }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('documents.show', $doc) }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill">Ver</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="surface-card p-4 text-center text-secondary">
                        <i class="bi bi-file-earmark fs-2 d-block mb-2"></i>
                        Nenhum documento enviado.
                        <button type="button"
                                class="d-block mt-1 btn btn-link btn-sm p-0 text-decoration-none"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEnviarDocCaso">
                            Enviar primeiro documento
                        </button>
                    </div>
                @endforelse
            </div>

            <div class="tab-pane fade" id="analises">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold mb-0">Análises de IA</h5>
                    <a href="{{ route('review.index') }}?case_id={{ $case->id }}" wire:navigate class="btn btn-primary rounded-pill">
                        <i class="bi bi-cpu me-2"></i>Nova análise
                    </a>
                </div>
                @forelse ($case->aiReviews->sortByDesc('created_at') as $review)
                    <div class="surface-card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $review->type)) }}</div>
                                <div class="text-secondary small">
                                    Por {{ $review->creator?->name ?? 'sistema' }} &bull; {{ $review->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $reviewStatus = match($review->status) {
                                        'concluido'   => 'text-bg-success',
                                        'processando' => 'text-bg-warning text-dark',
                                        'erro'        => 'text-bg-danger',
                                        default       => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $reviewStatus }}">{{ ucfirst($review->status) }}</span>
                                @if ($review->reviewed_at)
                                    <span class="badge text-bg-info">Revisado</span>
                                @endif
                                <a href="{{ route('review.show', $review) }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill">Ver</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="surface-card p-4 text-center text-secondary">
                        <i class="bi bi-cpu fs-2 d-block mb-2"></i>
                        Nenhuma análise de IA realizada ainda.
                    </div>
                @endforelse
            </div>

            <div class="tab-pane fade" id="detalhes">
                <div class="surface-card p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1"><i class="bi bi-person me-1"></i>Cliente</div>
                            <div>{{ $case->client_name }}</div>
                        </div>
                        @if ($case->client_email)
                            <div class="col-sm-6">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1"><i class="bi bi-envelope me-1"></i>E-mail</div>
                                <div>{{ $case->client_email }}</div>
                            </div>
                        @endif
                        @if ($case->client_phone)
                            <div class="col-sm-6">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1"><i class="bi bi-telephone me-1"></i>Telefone</div>
                                <div>{{ $case->client_phone }}</div>
                            </div>
                        @endif
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1"><i class="bi bi-person-badge me-1"></i>Responsável</div>
                            <div>{{ $case->assignedUser?->name ?? 'Não atribuído' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1"><i class="bi bi-person-circle me-1"></i>Criado por</div>
                            <div>{{ $case->creator?->name ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1"><i class="bi bi-calendar3 me-1"></i>Abertura</div>
                            <div>{{ $case->opened_at?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                        @if ($case->description)
                            <div class="col-12">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1"><i class="bi bi-text-paragraph me-1"></i>Descrição</div>
                                <div style="white-space: pre-wrap;">{{ $case->description }}</div>
                            </div>
                        @endif
                        @if ($case->internal_notes)
                            <div class="col-12">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1"><i class="bi bi-lock me-1"></i>Notas internas</div>
                                <div class="p-3 bg-warning bg-opacity-10 rounded" style="white-space: pre-wrap;">{{ $case->internal_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Modal: Editar caso --}}
<x-modal id="modalEditCaso" title="Editar caso" size="lg">
    <form method="POST" action="{{ route('cases.update', $case) }}" id="formEditCaso">
        @csrf @method('PATCH')

        <div class="row g-3 mb-3">
            <div class="col-12">
                <label for="edit_title" class="form-label fw-semibold"><i class="bi bi-briefcase me-1 text-secondary"></i>Título do caso <span class="text-danger">*</span></label>
                <input type="text" id="edit_title" name="title"
                       class="form-control @if($errors->has('title') && !$errors->has('file')) is-invalid @endif"
                       value="{{ old('title', $case->title) }}">
                @if($errors->has('title') && !$errors->has('file'))
                    <div class="invalid-feedback">{{ $errors->first('title') }}</div>
                @endif
            </div>
            <div class="col-sm-6">
                <label for="edit_area" class="form-label fw-semibold"><i class="bi bi-book me-1 text-secondary"></i>Área jurídica</label>
                <select id="edit_area" name="area" class="form-select @error('area') is-invalid @enderror">
                    @foreach (['civil' => 'Civil', 'criminal' => 'Criminal', 'trabalhista' => 'Trabalhista', 'tributario' => 'Tributário', 'empresarial' => 'Empresarial', 'familia' => 'Família', 'imobiliario' => 'Imobiliário', 'previdenciario' => 'Previdenciário', 'administrativo' => 'Administrativo', 'outro' => 'Outro'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('area', $case->area) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="edit_status" class="form-label fw-semibold"><i class="bi bi-circle-half me-1 text-secondary"></i>Status</label>
                <select id="edit_status" name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach (['triagem' => 'Triagem', 'em_andamento' => 'Em andamento', 'aguardando_cliente' => 'Aguardando cliente', 'aguardando_prazo' => 'Aguardando prazo', 'em_recurso' => 'Em recurso', 'encerrado' => 'Encerrado', 'arquivado' => 'Arquivado'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $case->status) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="edit_risk" class="form-label fw-semibold"><i class="bi bi-exclamation-triangle me-1 text-secondary"></i>Nível de risco</label>
                <select id="edit_risk" name="risk_level" class="form-select @error('risk_level') is-invalid @enderror">
                    <option value="">Não definido</option>
                    @foreach (['baixo' => 'Baixo', 'medio' => 'Médio', 'alto' => 'Alto', 'critico' => 'Crítico'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('risk_level', $case->risk_level) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('risk_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6">
                <label for="edit_opened_at" class="form-label fw-semibold"><i class="bi bi-calendar3 me-1 text-secondary"></i>Data de abertura</label>
                <input type="date" id="edit_opened_at" name="opened_at"
                       class="form-control @error('opened_at') is-invalid @enderror"
                       value="{{ old('opened_at', $case->opened_at?->format('Y-m-d')) }}">
                @error('opened_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label for="edit_assigned" class="form-label fw-semibold"><i class="bi bi-person-badge me-1 text-secondary"></i>Responsável</label>
                <select id="edit_assigned" name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                    <option value="">Não atribuído</option>
                    @foreach ($lawyers as $lawyer)
                        <option value="{{ $lawyer->id }}" @selected(old('assigned_to', $case->assigned_to) == $lawyer->id)>{{ $lawyer->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3 pt-3" style="border-top:1px solid rgba(215,220,229,0.5)">
            <div class="text-secondary small text-uppercase fw-semibold mb-2">Dados do cliente</div>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label for="edit_client_name" class="form-label fw-semibold"><i class="bi bi-person me-1 text-secondary"></i>Nome <span class="text-danger">*</span></label>
                    <input type="text" id="edit_client_name" name="client_name"
                           class="form-control @error('client_name') is-invalid @enderror"
                           value="{{ old('client_name', $case->client_name) }}">
                    @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label for="edit_client_email" class="form-label fw-semibold"><i class="bi bi-envelope me-1 text-secondary"></i>E-mail</label>
                    <input type="email" id="edit_client_email" name="client_email"
                           class="form-control @error('client_email') is-invalid @enderror"
                           value="{{ old('client_email', $case->client_email) }}">
                    @error('client_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label for="edit_client_phone" class="form-label fw-semibold"><i class="bi bi-telephone me-1 text-secondary"></i>Telefone</label>
                    <input type="text" id="edit_client_phone" name="client_phone"
                           class="form-control @error('client_phone') is-invalid @enderror"
                           value="{{ old('client_phone', $case->client_phone) }}">
                    @error('client_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mb-3 pt-3" style="border-top:1px solid rgba(215,220,229,0.5)">
            <label for="edit_description" class="form-label fw-semibold"><i class="bi bi-text-paragraph me-1 text-secondary"></i>Descrição</label>
            <textarea id="edit_description" name="description"
                      class="form-control @error('description') is-invalid @enderror"
                      rows="3">{{ old('description', $case->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-0">
            <label for="edit_notes" class="form-label fw-semibold"><i class="bi bi-lock me-1 text-secondary"></i>Notas internas</label>
            <textarea id="edit_notes" name="internal_notes"
                      class="form-control @error('internal_notes') is-invalid @enderror"
                      rows="2"
                      placeholder="Estratégia, observações confidenciais...">{{ old('internal_notes', $case->internal_notes) }}</textarea>
            @error('internal_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </form>
    <x-slot name="footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formEditCaso" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-check-circle me-2"></i>Salvar alterações
        </button>
    </x-slot>
</x-modal>

{{-- Modal: Enviar documento no caso --}}
<x-modal id="modalEnviarDocCaso" title="Enviar documento" size="md">
    <div x-data="{
        file: null,
        dragging: false,
        uploading: false,
        progress: 0,
        title: '{{ addslashes(old('title', '')) }}',
        error: null,
        handleDrop(e) {
            this.dragging = false;
            const f = e.dataTransfer.files[0];
            if (f) this.setFile(f);
        },
        setFile(f) {
            this.file = f;
            const dt = new DataTransfer();
            dt.items.add(f);
            this.$refs.fileInput.files = dt.files;
            if (!this.title.trim()) {
                this.title = f.name.replace(/\.[^/.]+$/, '').replace(/[-_.]+/g, ' ').trim();
            }
        },
        formatSize(bytes) {
            if (!bytes) return '';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
        submit() {
            if (!this.file || !this.title.trim()) return;
            const formEl = this.$refs.form;
            const fd = new FormData(formEl);
            fd.set('title', this.title);
            this.uploading = true;
            this.progress = 0;
            this.error = null;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', formEl.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) this.progress = Math.round(e.loaded / e.total * 100);
            });
            xhr.addEventListener('load', () => {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300 && data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    if (xhr.status === 422 && data.errors) {
                        const msgs = Object.values(data.errors).flat();
                        this.error = msgs[0] || 'Erro de validação.';
                    } else {
                        this.error = data.message || 'Erro ao enviar. Tente novamente.';
                    }
                } catch (e) {
                    this.error = 'Erro inesperado. Tente novamente.';
                }
                this.uploading = false;
            });
            xhr.addEventListener('error', () => {
                this.uploading = false;
                this.error = 'Erro de conexão. Tente novamente.';
            });
            xhr.send(fd);
        }
    }">
        <form method="POST"
              action="{{ route('documents.store') }}"
              enctype="multipart/form-data"
              x-ref="form">
            @csrf
            <input type="hidden" name="legal_case_id" value="{{ $case->id }}">
            <input type="file" x-ref="fileInput" name="file"
                   accept=".pdf,.docx,.doc,.txt"
                   class="d-none"
                   @change="if ($event.target.files[0]) setFile($event.target.files[0])">

            {{-- Dropzone --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Arquivo <span class="text-danger">*</span></label>
                <div class="doc-dropzone"
                     :class="{ 'doc-dropzone--dragging': dragging, 'doc-dropzone--has-file': file }"
                     @dragover.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="handleDrop($event)"
                     @click="$refs.fileInput.click()"
                     style="cursor:pointer">
                    <template x-if="!file">
                        <div class="text-center py-1">
                            <i class="bi bi-cloud-arrow-up" style="font-size:2rem;opacity:.45"></i>
                            <div class="fw-semibold mt-2 small">Solte aqui ou <span class="text-primary">clique para escolher</span></div>
                            <div class="text-secondary" style="font-size:.78rem;margin-top:.3rem">PDF, DOCX, DOC, TXT — máx. 100 MB</div>
                        </div>
                    </template>
                    <template x-if="file">
                        <div class="text-center py-1">
                            <i class="bi bi-file-earmark-check text-success" style="font-size:2rem"></i>
                            <div class="fw-semibold mt-2 small" x-text="file.name" style="word-break:break-all"></div>
                            <div class="text-secondary" style="font-size:.78rem;margin-top:.3rem" x-text="formatSize(file.size)"></div>
                            <button type="button"
                                    class="btn btn-sm btn-link text-danger p-0 mt-1"
                                    style="font-size:.78rem"
                                    @click.stop="file = null; $refs.fileInput.value = ''">Remover</button>
                        </div>
                    </template>
                </div>
                @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Título --}}
            <div class="mb-3">
                <label for="dc_title" class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                <input type="text" id="dc_title" name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       placeholder="Nome descritivo do documento"
                       x-model="title">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Progresso de upload --}}
            <div x-show="uploading" x-cloak class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-secondary">Enviando arquivo...</span>
                    <span class="fw-semibold" x-text="progress + '%'"></span>
                </div>
                <div class="progress" style="height:6px;border-radius:99px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                         role="progressbar"
                         :style="'width:' + progress + '%'"></div>
                </div>
            </div>

            <div x-show="error" x-cloak class="alert alert-danger py-2 small mb-3" x-text="error"></div>

            <div class="alert alert-info d-flex align-items-start gap-2 py-2 mb-0" x-show="!uploading">
                <i class="bi bi-cpu flex-shrink-0 mt-1"></i>
                <div class="small">PDFs enviados a este caso serão analisados automaticamente pela IA.</div>
            </div>
        </form>

        <div class="d-flex justify-content-end gap-2 mt-3 pt-3" style="border-top:1px solid rgba(215,220,229,0.35)">
            <button type="button"
                    class="btn btn-outline-secondary rounded-pill px-4"
                    data-bs-dismiss="modal"
                    :disabled="uploading">Cancelar</button>
            <button type="button"
                    class="btn btn-primary rounded-pill px-4"
                    @click="submit()"
                    :disabled="uploading || !file || !title.trim()">
                <template x-if="!uploading">
                    <span><i class="bi bi-cloud-arrow-up me-2"></i>Enviar</span>
                </template>
                <template x-if="uploading">
                    <span><span class="spinner-border spinner-border-sm me-2" role="status"></span>Enviando...</span>
                </template>
            </button>
        </div>
    </div>
</x-modal>

{{-- Reabre modal de edição apenas para campos exclusivos do formulário de edição de caso
     (file = upload de documento; area/internal_notes não existem no formulário de upload) --}}
@if (!$errors->has('file') && $errors->hasAny(['area', 'status', 'risk_level', 'opened_at', 'assigned_to', 'client_name', 'client_email', 'client_phone', 'description', 'internal_notes']))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('modalEditCaso');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endpush
@endif

{{-- Reabre modal de upload se o envio de documento falhou --}}
@if ($errors->has('file'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('modalEnviarDocCaso');
                if (el) new bootstrap.Modal(el).show();
            });
        </script>
    @endpush
@endif

@push('scripts')
    @vite(['resources/js/modules/casos-show.js'])
@endpush
