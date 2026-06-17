<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EmbeddingService
{
    private const API_URL = 'https://api.openai.com/v1/embeddings';
    private const MODEL   = 'text-embedding-3-small';
    private const DIMS    = 1536;

    public function __construct(private readonly string $apiKey) {}

    /**
     * Embed a single text and return the float vector.
     *
     * @return float[]
     */
    public function embed(string $text): array
    {
        return $this->embedBatch([$text])[0];
    }

    /**
     * Embed multiple texts in one API call (up to 2048 inputs).
     *
     * @param  string[]  $texts
     * @return float[][]
     */
    public function embedBatch(array $texts): array
    {
        $response = Http::timeout(60)->withToken($this->apiKey)->post(self::API_URL, [
            'model'      => self::MODEL,
            'input'      => $texts,
            'dimensions' => self::DIMS,
        ]);

        if ($response->failed()) {
            throw new RuntimeException("OpenAI Embeddings error ({$response->status()}): {$response->body()}");
        }

        $data = $response->json('data');

        usort($data, fn ($a, $b) => $a['index'] <=> $b['index']);

        return array_map(fn ($item) => $item['embedding'], $data);
    }

    /**
     * Convert a float vector to pgvector literal format: '[0.1,0.2,...]'
     *
     * @param  float[]  $vector
     */
    public function toSql(array $vector): string
    {
        return '[' . implode(',', $vector) . ']';
    }
}
