<?php

namespace App\Jobs;

use App\Models\AiReview;
use App\Models\Document;
use App\Services\AnthropicService;
use App\Services\SupabaseStorageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAiReview implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly AiReview $aiReview) {}

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function handle(AnthropicService $anthropic, SupabaseStorageService $storage): void
    {
        $review = $this->aiReview->fresh(['legalCase', 'document', 'draft']);

        $documentText = '';

        if ($review->document) {
            $binary       = $storage->download('case-documents', $review->document->storage_path);
            $documentText = $this->extractText($anthropic, $binary, $review->document->mime_type);
        }

        $result = match ($review->type) {
            'resumo_caso' => $anthropic->resumoCaso(
                $documentText,
                $review->legalCase->title,
                $review->legalCase->area,
            ),
            'analise_documento' => $anthropic->analiseDocumento(
                $documentText,
                $review->legalCase->title,
            ),
            'revisao_minuta' => $anthropic->revisaoMinuta(
                $review->draft->content ?? '',
                $review->draft->title ?? '',
                $review->legalCase->title,
            ),
            'pesquisa_juridica' => $anthropic->pesquisaJuridica(
                $review->prompt_used ?? '',
                $this->buildDocsContext($review->legalCase->id, $storage, $anthropic),
                $review->legalCase->title,
            ),
            default => throw new \RuntimeException("Tipo de revisão desconhecido: {$review->type}"),
        };

        $review->update([
            'result'        => $result['content'],
            'status'        => 'concluido',
            'ai_model_used' => $result['model'],
            'tokens_used'   => $result['total_tokens'],
        ]);

        if (in_array($review->type, ['analise_documento', 'resumo_caso']) && $review->document) {
            $review->document->update([
                'ai_summary'      => mb_substr($result['content'], 0, 1000),
                'ai_extracted_at' => now(),
                'status'          => 'ready',
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        $review = $this->aiReview->fresh(['document']);

        $review->update([
            'status' => 'erro',
            'result' => 'Erro ao processar: ' . $e->getMessage(),
        ]);

        if ($review->document_id && $review->document) {
            $review->document->update(['status' => 'error']);
        }
    }

    private function extractText(AnthropicService $anthropic, string $binary, string $mimeType): string
    {
        return match (true) {
            str_contains($mimeType, 'pdf')  => $anthropic->extractPdfText($binary),
            str_contains($mimeType, 'word'),
            str_contains($mimeType, 'docx') => $anthropic->extractDocxText($binary),
            default                          => mb_substr($binary, 0, 30000),
        };
    }

    private function buildDocsContext(string $caseId, SupabaseStorageService $storage, AnthropicService $anthropic): string
    {
        $documents = \App\Models\Document::where('legal_case_id', $caseId)
            ->where('status', 'ready')
            ->get();

        $parts     = [];
        $totalLen  = 0;
        $limit     = 25000;

        foreach ($documents as $doc) {
            if ($totalLen >= $limit) {
                break;
            }

            try {
                $binary  = $storage->download('case-documents', $doc->storage_path);
                $text    = $this->extractText($anthropic, $binary, $doc->mime_type);
                $excerpt = mb_substr($text, 0, $limit - $totalLen);
                $parts[] = "=== {$doc->title} ===\n{$excerpt}";
                $totalLen += mb_strlen($excerpt);
            } catch (\Throwable) {
                // skip unreadable document
            }
        }

        return implode("\n\n", $parts);
    }
}
