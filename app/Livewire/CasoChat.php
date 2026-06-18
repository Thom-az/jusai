<?php

namespace App\Livewire;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\LegalCase;
use App\Services\AnthropicService;
use App\Services\EmbeddingService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class CasoChat extends Component
{
    #[Locked]
    public LegalCase $caso;

    public string $input = '';
    public bool $thinking = false;

    private ?AiConversation $conversation = null;

    public function mount(LegalCase $caso): void
    {
        $this->caso = $caso;
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

        $this->thinking = true;

        $this->dispatch('triggerAiResponse');
    }

    #[On('triggerAiResponse')]
    public function fetchAiResponse(): void
    {
        $this->loadOrCreateConversation();

        $messagesForApi = $this->conversation->messages()
            ->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $lastUserContent = collect($messagesForApi)
            ->where('role', 'user')
            ->last()['content'] ?? '';

        try {
            $caseContext = $this->buildCaseContext($lastUserContent);
            $result = app(AnthropicService::class)->chat($messagesForApi, $caseContext);

            AiMessage::create([
                'conversation_id' => $this->conversation->id,
                'role'            => 'assistant',
                'content'         => $result['content'],
                'tokens_used'     => $result['total_tokens'],
            ]);
        } catch (\Throwable) {
            AiMessage::create([
                'conversation_id' => $this->conversation->id,
                'role'            => 'assistant',
                'content'         => 'Desculpe, ocorreu um erro ao processar sua mensagem. Tente novamente.',
            ]);
        }

        $this->thinking = false;
    }

    public function clearConversation(): void
    {
        $this->loadOrCreateConversation();
        $this->conversation->messages()->delete();
        $this->thinking = false;
    }

    public function render()
    {
        $this->loadOrCreateConversation();

        $messages = $this->conversation
            ? $this->conversation->messages()->get()
            : collect();

        return view('livewire.caso-chat', ['messages' => $messages]);
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

    private function buildCaseContext(string $query = ''): string
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

        $docChunks = $this->retrieveRelevantChunks($query);
        if ($docChunks !== '') {
            $context .= "\n\nTrechos relevantes dos documentos do caso:\n" . $docChunks;
        } else {
            $docCount = $caso->documents()->where('status', 'ready')->count();
            if ($docCount > 0) {
                $context .= "\nDocumentos no caso: {$docCount} arquivo(s) (vetorização pendente)";
            }
        }

        return $context;
    }

    private function retrieveRelevantChunks(string $query): string
    {
        if (empty(trim($query))) {
            return '';
        }

        $hasChunks = DB::table('document_chunks')
            ->where('legal_case_id', $this->caso->id)
            ->exists();

        if (! $hasChunks) {
            return '';
        }

        try {
            $embeddings = app(EmbeddingService::class);
            $vector     = $embeddings->embed($query);
            $sql        = $embeddings->toSql($vector);

            $rows = DB::select(
                'SELECT content FROM document_chunks
                 WHERE legal_case_id = ?
                 ORDER BY embedding <=> ?::vector
                 LIMIT 6',
                [$this->caso->id, $sql],
            );

            return implode("\n\n---\n\n", array_map(fn ($r) => $r->content, $rows));
        } catch (\Throwable) {
            return '';
        }
    }
}
