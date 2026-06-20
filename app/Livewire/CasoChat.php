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
    public array $selectedDocumentIds = [];

    private ?AiConversation $conversation = null;

    public function mount(LegalCase $caso): void
    {
        $this->caso = $caso;

        $this->selectedDocumentIds = $caso->documents()
            ->where('status', 'ready')
            ->pluck('id')
            ->toArray();
    }

    public function toggleDocument(string $id): void
    {
        if (in_array($id, $this->selectedDocumentIds)) {
            $this->selectedDocumentIds = array_values(
                array_filter($this->selectedDocumentIds, fn ($v) => $v !== $id)
            );
        } else {
            $this->selectedDocumentIds[] = $id;
        }
    }

    #[On('toggleChatDocument')]
    public function toggleFromSidebar(string $id): void
    {
        $this->toggleDocument($id);
    }

    public function sendMessage(string $message = ''): void
    {
        if ($message !== '') {
            $this->input = $message;
        }

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

        // Try semantic search first
        $docChunks = $this->retrieveRelevantChunks($query);
        if ($docChunks !== '') {
            $context .= "\n\nTrechos relevantes dos documentos do caso:\n" . $docChunks;
            return $context;
        }

        // Fallback: include ai_summary for selected ready documents
        $selectedIds = $this->selectedDocumentIds;

        $readyDocs = $caso->documents()
            ->where('status', 'ready')
            ->when(! empty($selectedIds), fn ($q) => $q->whereIn('id', $selectedIds))
            ->whereNotNull('ai_summary')
            ->get(['id', 'title', 'original_filename', 'ai_summary']);

        if ($readyDocs->isNotEmpty()) {
            $context .= "\n\nResumos dos documentos do caso:";
            foreach ($readyDocs as $doc) {
                $name = $doc->title ?: $doc->original_filename;
                $context .= "\n\n**{$name}**\n" . mb_substr($doc->ai_summary, 0, 2000);
            }
        } else {
            $readyCount = $caso->documents()
                ->where('status', 'ready')
                ->when(! empty($selectedIds), fn ($q) => $q->whereIn('id', $selectedIds))
                ->count();

            if ($readyCount > 0) {
                $hasChunks = DB::table('document_chunks')
                    ->where('legal_case_id', $caso->id)
                    ->exists();
                $context .= $hasChunks
                    ? "\nDocumentos no caso: {$readyCount} arquivo(s) processado(s)"
                    : "\nDocumentos no caso: {$readyCount} arquivo(s) processado(s) — resumos disponíveis para consulta";
            }
        }

        $pendingCount = $caso->documents()
            ->whereIn('status', ['pending', 'processing'])
            ->count();
        if ($pendingCount > 0) {
            $context .= "\nDocumentos em processamento: {$pendingCount} arquivo(s)";
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
