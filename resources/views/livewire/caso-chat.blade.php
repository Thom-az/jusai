<div class="caso-chat"
     x-data="{
         pending: '',
         isThinking: $wire.entangle('thinking'),
         init() {
             this.$watch('isThinking', val => {
                 if (val === true) this.pending = '';
             });
             window.addEventListener('fill-chat', e => {
                 const el = this.$refs.chatInput;
                 if (!el) return;
                 el.value = e.detail.text;
                 el.style.height = 'auto';
                 el.style.height = Math.min(el.scrollHeight, 160) + 'px';
                 el.focus();
             });
         },
         sendMsg() {
             const el = this.$refs.chatInput;
             const val = el ? el.value.trim() : '';
             if (!val || this.isThinking) return;
             this.pending = val;
             el.value = '';
             el.style.height = 'auto';
             el.focus();
             this.$nextTick(() => scrollChat());
             $wire.sendMessage(val);
         },
         chip(text) {
             const el = this.$refs.chatInput;
             if (!el) return;
             el.value = text;
             el.style.height = 'auto';
             el.style.height = Math.min(el.scrollHeight, 160) + 'px';
             el.focus();
         }
     }">

    {{-- ── Messages ────────────────────────────────────────── --}}
    <div class="caso-chat__messages"
         id="chatMessages"
         x-init="$el.scrollTop = $el.scrollHeight">

        {{-- Empty state --}}
        @if ($messages->isEmpty())
            <div class="chat-empty" x-show="!pending && !isThinking">
                <div class="chat-empty__icon">
                    <i class="bi bi-cpu-fill"></i>
                </div>
                <p class="chat-empty__title">Assistente Jurídico IA</p>
                <p class="chat-empty__sub">
                    Faça uma pergunta sobre este caso.<br>
                    Respondo com base nos documentos e no contexto disponíveis.
                </p>
            </div>
        @endif

        {{-- Message list --}}
        <div class="chat-msgs">

            @foreach ($messages as $msg)
                @if ($msg->role === 'user')
                    <div class="chat-msg chat-msg--user">
                        <div class="chat-bubble chat-bubble--user">{{ $msg->content }}</div>
                        <div class="chat-avatar chat-avatar--user">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                    </div>
                @else
                    <div class="chat-msg chat-msg--ai">
                        <div class="chat-avatar chat-avatar--ai">
                            <i class="bi bi-cpu-fill"></i>
                        </div>
                        <div class="chat-bubble chat-bubble--ai ai-body">
                            {!! (new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($msg->content) !!}
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Optimistic --}}
            <template x-if="pending">
                <div class="chat-msg chat-msg--user">
                    <div class="chat-bubble chat-bubble--user chat-bubble--pending" x-text="pending"></div>
                    <div class="chat-avatar chat-avatar--user">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            @if ($thinking)
                <div class="chat-msg chat-msg--ai">
                    <div class="chat-avatar chat-avatar--ai">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <div class="chat-bubble chat-bubble--ai chat-typing">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ── Input Area ──────────────────────────────────────── --}}
    <div class="chat-input-area">

        {{-- Chips — visible when conversation is empty --}}
        @if ($messages->isEmpty())
            <div class="chat-chips" x-show="!pending && !isThinking">
                <button type="button" class="chip" @click="chip('Qual é o prazo processual mais urgente neste caso?')">
                    <i class="bi bi-calendar-check"></i> Prazo processual
                </button>
                <button type="button" class="chip" @click="chip('Quais são os principais riscos jurídicos deste caso?')">
                    <i class="bi bi-shield-exclamation"></i> Riscos jurídicos
                </button>
                <button type="button" class="chip" @click="chip('Qual é a melhor estratégia jurídica para este caso?')">
                    <i class="bi bi-lightbulb"></i> Estratégia
                </button>
                <button type="button" class="chip" @click="chip('Quais documentos ainda precisamos reunir para este caso?')">
                    <i class="bi bi-file-earmark-plus"></i> Docs pendentes
                </button>
                <button type="button" class="chip" @click="chip('Quais são os próximos passos recomendados neste caso?')">
                    <i class="bi bi-arrow-right-circle"></i> Próximos passos
                </button>
            </div>
        @endif

        {{-- Input box --}}
        <div class="chat-input-wrap">
            <div class="chat-input-box" :class="{ 'is-loading': isThinking }">
                <textarea
                    x-ref="chatInput"
                    class="chat-input-field"
                    placeholder="Mensagem ao Assistente Jurídico…"
                    :disabled="isThinking"
                    @keydown.enter.prevent="!$event.shiftKey && sendMsg()"
                    @input="$el.style.height='auto'; $el.style.height = Math.min($el.scrollHeight,160)+'px'"
                    rows="1"
                    autocomplete="off"></textarea>

                <button type="button"
                        class="chat-send-btn"
                        @click="sendMsg()"
                        :disabled="isThinking"
                        title="Enviar (Enter)">
                    <template x-if="!isThinking">
                        <i class="bi bi-arrow-up-circle-fill"></i>
                    </template>
                    <template x-if="isThinking">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </template>
                </button>
            </div>

            @error('input')
                <div class="text-danger small mt-1 px-1">{{ $message }}</div>
            @enderror

            <div class="chat-input-meta">
                <span class="chat-disclaimer">
                    <i class="bi bi-shield-check"></i>
                    Revise sempre com um advogado habilitado
                </span>
                @if ($messages->isNotEmpty())
                    <button type="button"
                            wire:click="clearConversation"
                            wire:confirm="Limpar todo o histórico desta conversa?"
                            class="chat-clear-btn">
                        Limpar conversa
                    </button>
                @endif
            </div>
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
