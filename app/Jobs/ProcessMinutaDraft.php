<?php

namespace App\Jobs;

use App\Models\Draft;
use App\Services\AnthropicService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMinutaDraft implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 180;

    public function __construct(public readonly Draft $draft) {}

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function handle(AnthropicService $anthropic): void
    {
        $draft = $this->draft->fresh(['legalCase']);

        $caseTitle = $draft->legalCase?->title ?? '';
        $caseArea  = $draft->legalCase?->area ?? '';

        $result = $anthropic->rascunhoMinuta(
            instructions: $draft->instructions ?? '',
            type:         $draft->type,
            caseTitle:    $caseTitle,
            caseArea:     $caseArea,
        );

        $draft->update([
            'content'      => $result['content'],
            'ai_model_used' => $result['model'],
        ]);
    }

    public function failed(\Throwable $e): void
    {
        $draft = $this->draft->fresh();

        $draft->update([
            'content' => '[ERRO NA GERAÇÃO] ' . $e->getMessage(),
        ]);
    }
}
