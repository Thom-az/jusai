@props([
    'modalId' => 'modalEnviarDoc',
    'caso'    => null,
    'cases'   => collect(),
])

@php $chatMode = $caso !== null; @endphp

<x-modal :id="$modalId" title="Enviar documento" size="md">
    <div x-data="{
        file: null,
        dragging: false,
        uploading: false,
        progress: 0,
        processing: false,
        title: '',
        error: null,
        init() {
            window.addEventListener('doc-page-drop', (e) => { this.setFile(e.detail.file); });
            const modalEl = document.getElementById('{{ $modalId }}');
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', () => {
                    this.file = null;
                    this.title = '';
                    this.error = null;
                    this.uploading = false;
                    this.processing = false;
                    this.progress = 0;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                });
            }
        },
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
            this.processing = false;
            this.progress = 0;
            this.error = null;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', formEl.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    this.progress = Math.min(85, Math.round(e.loaded / e.total * 85));
                }
            });
            xhr.upload.addEventListener('load', () => {
                this.processing = true;
                this.progress = 85;
            });
            xhr.addEventListener('load', () => {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300 && data.redirect) {
                        this.progress = 100;
                        @if ($chatMode)
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('{{ $modalId }}'))?.hide();
                            window.dispatchEvent(new CustomEvent('app:toast', {
                                detail: { message: 'Documento enviado com sucesso!', type: 'success' }
                            }));
                            setTimeout(() => Livewire.navigate(window.location.href), 400);
                        }, 300);
                        @else
                        setTimeout(() => { window.location.href = data.redirect; }, 300);
                        @endif
                        return;
                    }
                    if (xhr.status === 422 && data.errors) {
                        this.error = Object.values(data.errors).flat()[0] || 'Erro de validação.';
                    } else {
                        this.error = data.message || 'Erro ao enviar. Tente novamente.';
                    }
                } catch (e) {
                    this.error = 'Erro inesperado. Tente novamente.';
                }
                this.uploading = false;
                this.processing = false;
            });
            xhr.addEventListener('error', () => {
                this.uploading = false;
                this.processing = false;
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

            @if ($chatMode)
                <input type="hidden" name="legal_case_id" value="{{ $caso->id }}">
            @endif

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
            </div>

            {{-- Título --}}
            <div class="mb-3">
                <label for="{{ $modalId }}_title" class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                <input type="text" id="{{ $modalId }}_title" name="title"
                       class="form-control"
                       placeholder="Nome descritivo do documento"
                       x-model="title">
            </div>

            @if (!$chatMode && $cases->isNotEmpty())
                <div class="mb-3">
                    <label for="{{ $modalId }}_case" class="form-label fw-semibold">Vincular ao caso</label>
                    <select id="{{ $modalId }}_case" name="legal_case_id" class="form-select">
                        <option value="">Nenhum caso (autônomo)</option>
                        @foreach ($cases as $case)
                            <option value="{{ $case->id }}">{{ $case->title }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">PDFs vinculados a casos recebem análise automática de IA.</div>
                </div>
            @endif

            @if ($chatMode)
                <p class="text-secondary small mb-3">
                    <i class="bi bi-briefcase me-1"></i>
                    Documento será vinculado ao caso <strong>{{ $caso->title }}</strong>.
                </p>
            @endif

            {{-- Progresso de upload --}}
            <div x-show="uploading" x-cloak class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-secondary" x-text="processing ? 'Salvando no servidor...' : 'Enviando arquivo...'"></span>
                    <span class="fw-semibold" x-text="progress + '%'"></span>
                </div>
                <div class="progress" style="height:6px;border-radius:99px">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                         role="progressbar"
                         :style="'width:' + progress + '%'"></div>
                </div>
            </div>

            <div x-show="error" x-cloak class="alert alert-danger py-2 small mb-3" x-text="error"></div>

            <x-ai-disclaimer class="mb-3" />
        </form>

        <div class="d-flex justify-content-end gap-2 mt-1">
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
                <template x-if="uploading && !processing">
                    <span><span class="spinner-border spinner-border-sm me-2"></span>Enviando <span x-text="progress + '%'"></span></span>
                </template>
                <template x-if="processing">
                    <span><span class="spinner-border spinner-border-sm me-2"></span>Salvando...</span>
                </template>
            </button>
        </div>
    </div>
</x-modal>
