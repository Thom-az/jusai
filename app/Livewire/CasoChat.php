<?php

namespace App\Livewire;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\LegalCase;
use App\Services\AnthropicService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CasoChat extends Component
{
    #[Locked]
    public LegalCase $caso;

    public string $input = '';

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $messages;

    private ?AiConversation $conversation = null;

    public function mount(LegalCase $caso): void
    {
        $this->caso = $caso;
        $this->loadOrCreateConversation();
        $this->messages = $this->conversation->messages;
    }

    public function sendMessage(): void
    {
        $this->validate(['input' => ['required', 'string', 'min:2', 'max:2000']]);

        $userText = trim($this->input);
        $this->input = '';

        $this->loadOrCreateConversation();

        AiMessage::create([
            'conversation_id' => $this->conversation->id,
            'role'            => 'user',
            'content'         => $userText,
        ]);

        $this->messages = $this->conversation->messages()->get();

        $messagesForApi = $this->messages->map(fn ($m) => [
            'role'    => $m->role,
            'content' => $m->content,
        ])->toArray();

        try {
            $caseContext = $this->buildCaseContext();
            $result = app(AnthropicService::class)->chat($messagesForApi, $caseContext);

            AiMessage::create([
                'conversation_id' => $this->conversation->id,
                'role'            => 'assistant',
                'content'         => $result['content'],
                'tokens_used'     => $result['total_tokens'],
            ]);
        } catch (\Throwable $e) {
            AiMessage::create([
                'conversation_id' => $this->conversation->id,
                'role'            => 'assistant',
                'content'         => 'Desculpe, ocorreu um erro ao processar sua mensagem. Tente novamente.',
            ]);
        }

        $this->messages = $this->conversation->messages()->get();
    }

    public function clearConversation(): void
    {
        $this->loadOrCreateConversation();
        $this->conversation->messages()->delete();
        $this->messages = collect();
    }

    public function render()
    {
        return view('livewire.caso-chat');
    }

    private function loadOrCreateConversation(): void
    {
        if ($this->conversation) {
            return;
        }

        $this->conversation = AiConversation::firstOrCreate([
            'legal_case_id' => $this->caso->id,
            'user_id'       => auth()->id(),
        ], [
            'organization_id' => auth()->user()->organization_id,
            'legal_case_id'   => $this->caso->id,
            'user_id'         => auth()->id(),
        ]);
    }

    private function buildCaseContext(): string
    {
        $caso = $this->caso;

        $context = "Título: {$caso->title}";
        $context .= "\nÁrea: {$caso->area}";
        $context .= "\nStatus: {$caso->status}";

        if ($caso->client_name) {
            $context .= "\nCliente: {$caso->client_name}";
        }
        if ($caso->description) {
            $context .= "\nDescrição: " . mb_substr($caso->description, 0, 500);
        }

        $docCount = $caso->documents()->count();
        if ($docCount > 0) {
            $context .= "\nDocumentos no caso: {$docCount} arquivo(s)";
        }

        return $context;
    }
}
