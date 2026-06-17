<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\AnthropicService;
use App\Services\EmbeddingService;
use App\Services\SupabaseStorageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VectorizeDocument implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 600;

    public function __construct(public readonly Document $document) {}

    public function handle(
        EmbeddingService      $embeddings,
        AnthropicService      $anthropic,
        SupabaseStorageService $storage,
    ): void {
        ini_set('memory_limit', '512M');

        DocumentChunk::where('document_id', $this->document->id)->delete();

        $binary = $storage->download('case-documents', $this->document->storage_path);
        $text   = $this->extractText($anthropic, $binary, $this->document->mime_type);

        if (empty(trim($text))) {
            return;
        }

        $chunks = $this->chunkText($text);

        foreach (array_chunk($chunks, 100) as $batch) {
            $vectors = $embeddings->embedBatch($batch);

            $rows = [];
            foreach ($batch as $i => $content) {
                $rows[] = [
                    'id'              => (string) Str::uuid(),
                    'organization_id' => $this->document->organization_id,
                    'legal_case_id'   => $this->document->legal_case_id,
                    'document_id'     => $this->document->id,
                    'chunk_index'     => count($rows),
                    'content'         => $content,
                    'embedding'       => $embeddings->toSql($vectors[$i]),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            DB::table('document_chunks')->insert($rows);
        }
    }

    private function extractText(AnthropicService $anthropic, string $binary, string $mimeType): string
    {
        return match (true) {
            str_contains($mimeType, 'pdf')  => $anthropic->extractFullPdfText($binary),
            str_contains($mimeType, 'word'),
            str_contains($mimeType, 'docx') => $anthropic->extractDocxText($binary),
            default                          => $binary,
        };
    }

    /**
     * Split text into overlapping chunks of ~800 chars.
     *
     * @return string[]
     */
    private function chunkText(string $text, int $size = 800, int $overlap = 100): array
    {
        $chunks = [];
        $len    = mb_strlen($text);
        $step   = $size - $overlap;
        $start  = 0;

        while ($start < $len) {
            $chunk = trim(mb_substr($text, $start, $size));
            if ($chunk !== '') {
                $chunks[] = $chunk;
            }
            $start += $step;
        }

        return $chunks;
    }
}
