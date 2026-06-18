<div class="caso-chat d-flex flex-column" style="height:560px;">

    {{-- Histórico de mensagens --}}
    <div class="caso-chat__messages flex-grow-1 overflow-y-auto p-3 d-flex flex-column gap-2"
         id="chatMessages"
         x-data
         x-init="$el.scrollTop = $el.scrollHeight">

        @if ($messages->isEmpty() && !$thinking)
            <div class="text-center text-secondary py-5 small">
                <i class="bi bi-chat-dots fs-2 d-block mb-2 opacity-40"></i>
                Faça uma pergunta sobre este caso.<br>
                <span class="opacity-75">A IA irá responder com base no contexto disponível.</span>
            </div>
        @else
            @foreach ($messages as $message)
                @if ($message->role === 'user')
                    {{-- Balão do usuário — direita --}}
                    <div class="d-flex justify-content-end">
                        <div class="caso-chat__bubble caso-chat__bubble--user">
                            {{ $message->content }}
                        </div>
                    </div>
                @else
                    {{-- Balão da IA — esquerda --}}
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

            {{-- Indicador de "digitando" --}}
            @if ($thinking)
                <div class="d-flex align-items-end gap-2" wire:loading.remove wire:target="fetchAiResponse">
                    <div class="caso-chat__avatar flex-shrink-0">
                        <i class="bi bi-cpu text-white" style="font-size:.7rem;"></i>
                    </div>
                    <div class="caso-chat__bubble caso-chat__bubble--ai caso-chat__typing">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-2" wire:loading wire:target="fetchAiResponse">
                    <div class="caso-chat__avatar flex-shrink-0">
                        <i class="bi bi-cpu text-white" style="font-size:.7rem;"></i>
                    </div>
                    <div class="caso-chat__bubble caso-chat__bubble--ai caso-chat__typing">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Input --}}
    <div class="caso-chat__input-wrap border-top pt-3 px-3 pb-2">
        <form wire:submit="sendMessage" class="d-flex gap-2 align-items-end">
            <input type="text"
                   wire:model="input"
                   class="form-control form-control-sm rounded-pill"
                   placeholder="Mensagem…"
                   wire:loading.attr="disabled" wire:target="sendMessage,fetchAiResponse"
                   @keydown.enter.prevent="$wire.sendMessage()"
                   autocomplete="off"
                   style="padding-left:1rem;padding-right:1rem;">
            <button type="submit"
                    class="btn btn-primary btn-sm rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center"
                    style="width:2.15rem;height:2.15rem;padding:0;"
                    wire:loading.attr="disabled" wire:target="sendMessage,fetchAiResponse">
                <span wire:loading.remove wire:target="sendMessage,fetchAiResponse">
                    <i class="bi bi-send-fill" style="font-size:.8rem;"></i>
                </span>
                <span wire:loading wire:target="sendMessage,fetchAiResponse">
                    <span class="spinner-border" style="width:.7rem;height:.7rem;border-width:2px;"></span>
                </span>
            </button>
        </form>

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
    (() => {
        const scrollChat = () => {
            const el = document.getElementById('chatMessages');
            if (el) el.scrollTop = el.scrollHeight;
        };

        document.addEventListener('livewire:updated', scrollChat);
        document.addEventListener('livewire:navigated', scrollChat);
    })();
</script>
