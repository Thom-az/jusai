<div class="caso-chat d-flex flex-column"
     x-data="{
         pending: '',
         isThinking: $wire.entangle('thinking'),
         init() {
             this.$watch('isThinking', val => {
                 if (val === true) this.pending = '';
             });
             window.addEventListener('fill-chat', e => {
                 const inp = this.$refs.chatInput;
                 if (inp) { inp.value = e.detail.text; inp.focus(); }
             });
         },
         sendMsg() {
             const inp = this.$refs.chatInput;
             const val = inp ? inp.value.trim() : '';
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
         id="chatMessages"
         x-init="$el.scrollTop = $el.scrollHeight">

        {{-- Estado vazio --}}
        @if ($messages->isEmpty())
            <div class="text-center text-secondary py-5 small"
                 x-show="!pending && !isThinking">
                <i class="bi bi-chat-dots fs-2 d-block mb-2 opacity-40"></i>
                Faça uma pergunta sobre este caso.<br>
                <span class="opacity-75">A IA irá responder com base no contexto disponível.</span>
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

        {{-- Mensagem otimista: aparece imediatamente ao enviar --}}
        <template x-if="pending">
            <div class="d-flex justify-content-end">
                <div class="caso-chat__bubble caso-chat__bubble--user caso-chat__bubble--pending"
                     x-text="pending"></div>
            </div>
        </template>

        {{-- Indicador de digitação --}}
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

    {{-- Input --}}
    <div class="caso-chat__input-wrap border-top pt-3 px-3 pb-2">
        <div class="d-flex gap-2 align-items-end">
            <input type="text"
                   x-ref="chatInput"
                   class="form-control form-control-sm rounded-pill"
                   placeholder="Mensagem…"
                   :disabled="isThinking"
                   @keydown.enter.prevent="sendMsg()"
                   autocomplete="off"
                   style="padding-left:1rem;padding-right:1rem;">

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

        <div class="d-flex justify-content-between align-items-center mt-2 px-1">
            <span class="text-secondary" style="font-size:.68rem;">
                <i class="bi bi-exclamation-triangle me-1"></i>Revisar com advogado habilitado
            </span>
            @if ($messages->isNotEmpty())
                <button type="button"
                        wire:click="clearConversation"
                        wire:confirm="Limpar todo o histórico desta conversa?"
                        class="btn btn-link btn-sm text-secondary p-0"
                        style="font-size:.68rem;">
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
