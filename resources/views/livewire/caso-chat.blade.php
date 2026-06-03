<div class="caso-chat d-flex flex-column" style="height: 520px;">
    {{-- Histórico de mensagens --}}
    <div class="caso-chat__messages flex-grow-1 overflow-y-auto p-3 d-flex flex-column gap-3"
         id="chatMessages"
         x-data
         x-init="$el.scrollTop = $el.scrollHeight"
         x-on:livewire:navigated.window="$el.scrollTop = $el.scrollHeight">

        @if ($messages->isEmpty())
            <div class="text-center text-secondary py-5 small">
                <i class="bi bi-chat-dots fs-3 d-block mb-2 opacity-50"></i>
                Faça uma pergunta sobre este caso. A IA irá responder com base no contexto disponível.
            </div>
        @else
            @foreach ($messages as $message)
                @if ($message->role === 'user')
                    <div class="d-flex justify-content-end">
                        <div class="caso-chat__bubble caso-chat__bubble--user rounded-3 px-3 py-2 small">
                            {{ $message->content }}
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-start gap-2">
                        <div class="flex-shrink-0 rounded-circle bg-primary d-flex align-items-center justify-content-center"
                             style="width:1.75rem;height:1.75rem;">
                            <i class="bi bi-cpu text-white" style="font-size:.7rem;"></i>
                        </div>
                        <div class="caso-chat__bubble caso-chat__bubble--ai rounded-3 px-3 py-2 small"
                             style="white-space: pre-wrap; max-width: 85%;">{{ $message->content }}</div>
                    </div>
                @endif
            @endforeach

            @if ($isTyping)
                <div class="d-flex justify-content-start gap-2">
                    <div class="flex-shrink-0 rounded-circle bg-primary d-flex align-items-center justify-content-center"
                         style="width:1.75rem;height:1.75rem;">
                        <i class="bi bi-cpu text-white" style="font-size:.7rem;"></i>
                    </div>
                    <div class="caso-chat__bubble caso-chat__bubble--ai rounded-3 px-3 py-2 small">
                        <span class="spinner-border spinner-border-sm me-1" style="width:.65rem;height:.65rem;"></span>
                        Processando…
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Input --}}
    <div class="border-top p-3">
        <form wire:submit="sendMessage" class="d-flex gap-2">
            <input type="text"
                   wire:model="input"
                   class="form-control form-control-sm rounded-pill"
                   placeholder="Faça uma pergunta jurídica sobre este caso…"
                   @if($isTyping) disabled @endif
                   autocomplete="off">
            <button type="submit"
                    class="btn btn-primary btn-sm rounded-pill px-3"
                    @if($isTyping) disabled @endif>
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
        @error('input')
            <div class="text-danger small mt-1 ps-2">{{ $message }}</div>
        @enderror
        <div class="d-flex justify-content-between align-items-center mt-2 px-1">
            <span class="text-secondary" style="font-size:.7rem;">
                <i class="bi bi-exclamation-triangle me-1"></i>Respostas de IA — revisar com advogado habilitado
            </span>
            @if ($messages->isNotEmpty())
                <button type="button"
                        wire:click="clearConversation"
                        wire:confirm="Limpar todo o histórico desta conversa?"
                        class="btn btn-link btn-sm text-secondary p-0"
                        style="font-size:.7rem;">
                    Limpar conversa
                </button>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:updated', () => {
        const msgs = document.getElementById('chatMessages');
        if (msgs) msgs.scrollTop = msgs.scrollHeight;
    });
</script>
