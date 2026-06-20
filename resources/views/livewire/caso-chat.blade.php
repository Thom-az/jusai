<div class="caso-chat d-flex flex-column"
     x-data="{
         pending: '',
         isThinking: $wire.entangle('thinking'),
         showDocs: false,
         init() {
             this.$watch('isThinking', val => {
                 if (val === true) this.pending = '';
             });
         },
         sendMsg() {
             const inp = this.$refs.chatInput;
             const val = inp.value.trim();
             if (!val || this.isThinking) return;
             this.pending = val;
             inp.value = '';
             inp.focus();
             this.$nextTick(() => scrollChat());
             $wire.sendMessage(val);
         }
     }">

    {{-- Área de mensagens --}}
    <div class="caso-chat__messages flex-grow-1 overflow-y-auto p-3 d-flex flex-column gap-2"
         id="chatMessages">

        {{-- Estado vazio --}}
        @if ($messages->isEmpty())
            <div class="text-center text-secondary py-5 small"
                 x-show="!pending && !isThinking"
                 x-transition:leave="transition-opacity duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <i class="bi bi-chat-dots fs-2 d-block mb-2 opacity-40"></i>
                Faça uma pergunta sobre este caso.<br>
                <span class="opacity-75">A IA responderá com base nos documentos e contexto disponíveis.</span>
                @if (count($caseDocuments) > 0)
                    <div class="mt-3">
                        <span class="badge text-bg-success me-1">
                            <i class="bi bi-file-earmark-check me-1"></i>{{ count(array_filter($caseDocuments, fn($d) => $d['status'] === 'ready')) }} doc(s) disponível(is)
                        </span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Mensagens do servidor --}}
        @foreach ($messages as $message)
            @if ($message->role === 'user')
                <div class="d-flex justify-content-end">
                    <div class="caso-chat__bubble caso-chat__bubble--user">
                        {{ $message->content }}
                    </div>
                </div>
            @else
                <div class="d-flex align-items-end gap-2">
                    <div class="caso-chat__avatar flex-shrink-0">
                        <i class="bi bi-cpu text-white" style="font-size:.7rem;"></i>
                    </div>
                    <div class="caso-chat__bubble caso-chat__bubble--ai ai-body">
                        {!! (new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($message->content) !!}
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Mensagem otimista (aparece imediatamente ao enviar) --}}
        <template x-if="pending">
            <div class="d-flex justify-content-end">
                <div class="caso-chat__bubble caso-chat__bubble--user caso-chat__bubble--pending"
                     x-text="pending"></div>
            </div>
        </template>

        {{-- Indicador de "digitando" --}}
        @if ($thinking)
            <div class="d-flex align-items-end gap-2">
                <div class="caso-chat__avatar flex-shrink-0">
                    <i class="bi bi-cpu text-white" style="font-size:.7rem;"></i>
                </div>
                <div class="caso-chat__bubble caso-chat__bubble--ai caso-chat__typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        @endif
    </div>

    {{-- Painel de seleção de documentos (slide toggle) --}}
    @if (count($caseDocuments) > 0)
        <div class="caso-chat__doc-panel border-top px-3 py-2"
             x-show="showDocs"
             x-transition:enter="transition-all"
             x-transition:enter-start="opacity-0 max-h-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-all"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 max-h-0"
             x-cloak>
            <div class="d-flex flex-wrap gap-1 align-items-center">
                <span class="text-secondary me-1" style="font-size:.7rem;white-space:nowrap;">Contexto:</span>
                @foreach ($caseDocuments as $doc)
                    <button type="button"
                            wire:click="toggleDocument('{{ $doc['id'] }}')"
                            class="btn btn-sm rounded-pill px-2 py-0 {{ in_array($doc['id'], $selectedDocumentIds) ? 'btn-primary' : 'btn-outline-secondary' }}"
                            style="font-size:.7rem;line-height:1.8;"
                            title="{{ $doc['title'] }}">
                        <i class="bi {{ $doc['status'] === 'ready' ? 'bi-file-earmark-check' : 'bi-hourglass-split' }} me-1"></i>
                        {{ \Illuminate\Support\Str::limit($doc['title'], 22) }}
                    </button>
                @endforeach
                <a href="{{ route('cases.show', $caso) }}?tab=documentos"
                   wire:navigate
                   class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 ms-auto"
                   style="font-size:.7rem;line-height:1.8;"
                   title="Adicionar documento">
                    <i class="bi bi-plus-circle me-1"></i>Adicionar
                </a>
            </div>
        </div>
    @endif

    {{-- Área de input --}}
    <div class="caso-chat__input-wrap border-top pt-2 px-3 pb-2">
        <div class="d-flex gap-2 align-items-center">
            {{-- Botão de documentos --}}
            @if (count($caseDocuments) > 0)
                <button type="button"
                        @click="showDocs = !showDocs"
                        class="btn btn-sm rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center"
                        :class="showDocs ? 'btn-primary' : 'btn-outline-secondary'"
                        style="width:2.15rem;height:2.15rem;padding:0;"
                        title="Selecionar documentos para contexto">
                    <i class="bi bi-files" style="font-size:.85rem;"></i>
                </button>
            @endif

            {{-- Campo de texto --}}
            <input type="text"
                   x-ref="chatInput"
                   class="form-control form-control-sm rounded-pill flex-grow-1"
                   placeholder="Mensagem…"
                   :disabled="isThinking"
                   @keydown.enter.prevent="sendMsg()"
                   autocomplete="off"
                   style="padding-left:1rem;padding-right:1rem;">

            {{-- Botão enviar --}}
            <button type="button"
                    @click="sendMsg()"
                    class="btn btn-primary btn-sm rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center"
                    style="width:2.15rem;height:2.15rem;padding:0;"
                    :disabled="isThinking">
                <template x-if="!isThinking">
                    <i class="bi bi-send-fill" style="font-size:.8rem;"></i>
                </template>
                <template x-if="isThinking">
                    <span class="spinner-border" style="width:.7rem;height:.7rem;border-width:2px;"></span>
                </template>
            </button>
        </div>

        @error('input')
            <div class="text-danger small mt-1 ps-1">{{ $message }}</div>
        @enderror

        <div class="d-flex justify-content-between align-items-center mt-1 px-1">
            <span class="text-secondary" style="font-size:.65rem;">
                <i class="bi bi-exclamation-triangle me-1"></i>Revisar com advogado habilitado
            </span>
            @if ($messages->isNotEmpty())
                <button type="button"
                        wire:click="clearConversation"
                        wire:confirm="Limpar todo o histórico desta conversa?"
                        class="btn btn-link btn-sm text-secondary p-0"
                        style="font-size:.65rem;">
                    Limpar conversa
                </button>
            @endif
        </div>
    </div>
</div>

<script>
    function scrollChat() {
        const el = document.getElementById('chatMessages');
        if (el) el.scrollTop = el.scrollHeight;
    }

    (() => {
        document.addEventListener('livewire:updated', scrollChat);
        document.addEventListener('livewire:navigated', scrollChat);
    })();
</script>
