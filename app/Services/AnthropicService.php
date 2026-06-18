<?php

namespace App\Services;

use App\Models\AiPrompt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Smalot\PdfParser\Parser;

class AnthropicService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';
    private const PDF_TEXT_LIMIT = 30000;

    // Limites por tipo de operação — evita gastar tokens desnecessários
    private const TOKENS_CHAT     = 1024;
    private const TOKENS_RESUMO   = 1024;
    private const TOKENS_ANALISE  = 2048;
    private const TOKENS_REVISAO  = 2048;
    private const TOKENS_PESQUISA = 2048;
    private const TOKENS_RASCUNHO = 4096;
    private const TOKENS_OCR      = 8192;

    // OCR via Vision — só ativado quando pdftotext retorna texto muito esparso
    private const OCR_MIN_CHARS_PER_PAGE  = 100;  // abaixo disso = PDF de imagem
    private const NATIVE_PDF_SIZE_LIMIT   = 32 * 1024 * 1024; // 32MB
    private const NATIVE_PDF_PAGE_LIMIT   = 100;
    private const OCR_BATCH_PAGES         = 20;   // páginas por chamada Vision

    public function __construct(
        private readonly string $apiKey,
        private readonly float  $temperature,
        private readonly string $modelFast,
        private readonly string $modelStrong,
        private readonly string $provider = 'mock',
    ) {}

    public function resumoCaso(string $documentText, string $caseTitle, string $caseArea): array
    {
        if ($this->isMock()) {
            return $this->mockResponse('resumo_caso');
        }

        $system = $this->systemBase() . "\n\n" . $this->prompt('system.resumo_caso');
        $user   = "Caso: {$caseTitle}\nÁrea: {$caseArea}\n\nDocumento:\n{$documentText}";

        return $this->callAnthropic($this->modelFast, $system, $user, [], self::TOKENS_RESUMO);
    }

    public function analiseDocumento(string $documentText, string $caseTitle): array
    {
        if ($this->isMock()) {
            return $this->mockResponse('analise_documento');
        }

        $system = $this->systemBase() . "\n\n" . $this->prompt('system.analise_documento');
        $user   = "Caso: {$caseTitle}\n\nDocumento:\n{$documentText}";

        return $this->callAnthropic($this->modelStrong, $system, $user, [], self::TOKENS_ANALISE);
    }

    public function revisaoMinuta(string $draftContent, string $draftTitle, string $caseContext): array
    {
        if ($this->isMock()) {
            return $this->mockResponse('revisao_minuta');
        }

        $system = $this->systemBase() . "\n\n" . $this->prompt('system.revisao_minuta');
        $user   = "Minuta: {$draftTitle}\nContexto do caso: {$caseContext}\n\nConteúdo da minuta:\n{$draftContent}";

        return $this->callAnthropic($this->modelStrong, $system, $user, [], self::TOKENS_REVISAO);
    }

    public function pesquisaJuridica(string $question, string $docsContext, string $caseTitle): array
    {
        if ($this->isMock()) {
            return $this->mockResponse('pesquisa_juridica');
        }

        $system = $this->systemBase() . "\n\n" . $this->prompt('system.pesquisa_juridica');
        $user   = "Caso: {$caseTitle}\nPergunta: {$question}\n\nDocumentos do caso:\n{$docsContext}";

        return $this->callAnthropic($this->modelStrong, $system, $user, [], self::TOKENS_PESQUISA);
    }

    public function rascunhoMinuta(string $instructions, string $type, string $caseTitle, string $caseArea = ''): array
    {
        if ($this->isMock()) {
            return $this->mockResponse('rascunho_minuta');
        }

        $typeLabel = $this->draftTypeLabel($type);
        $system    = $this->systemBase() . "\n\n" . $this->prompt('system.rascunho_minuta');
        $user      = "Tipo de documento: {$typeLabel}\nCaso vinculado: {$caseTitle}" .
                     ($caseArea ? "\nÁrea jurídica: {$caseArea}" : '') .
                     "\n\nInstruções para geração:\n{$instructions}";

        return $this->callAnthropic($this->modelStrong, $system, $user, [], self::TOKENS_RASCUNHO);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, string $caseContext = ''): array
    {
        if ($this->isMock()) {
            return $this->mockResponse('chat');
        }

        $systemSuffix = $caseContext
            ? "\n\nContexto do caso:\n{$caseContext}"
            : '';

        $system = $this->systemBase() . "\n\n" . $this->prompt('system.chat') . $systemSuffix;

        return $this->callAnthropic($this->modelFast, $system, '', $messages, self::TOKENS_CHAT);
    }

    public function extractPdfText(string $binary): string
    {
        $text = $this->extractWithOcrFallback($binary, allPages: false);
        return mb_substr($text, 0, self::PDF_TEXT_LIMIT);
    }

    /**
     * Extract ALL pages without character limit — for vectorization only.
     * The caller is responsible for chunking the result.
     */
    public function extractFullPdfText(string $binary): string
    {
        return $this->extractWithOcrFallback($binary, allPages: true);
    }

    /**
     * Hybrid extraction: pdftotext first (free). Falls back to Claude Vision
     * only when text density is below threshold (image-based PDF detected).
     */
    private function extractWithOcrFallback(string $binary, bool $allPages): string
    {
        $popplerText = $this->extractWithPoppler($binary, $allPages);
        $baseText    = $popplerText ?? $this->extractWithSmalot($binary);

        if ($this->isMock()) {
            return $baseText;
        }

        $pageCount = $this->getPdfPageCount($binary);
        if ($pageCount > 0 && mb_strlen(trim($baseText)) / $pageCount < self::OCR_MIN_CHARS_PER_PAGE) {
            $ocrText = $this->ocrWithVision($binary, $pageCount, $allPages);
            if (mb_strlen(trim($ocrText)) > mb_strlen(trim($baseText))) {
                return $ocrText;
            }
        }

        return $baseText;
    }

    // Streams through PDF page by page — handles 1000+ page documents without OOM.
    // allPages=false limits to first 100 pages for AI summary prompts.
    private function extractWithPoppler(string $binary, bool $allPages = false): ?string
    {
        $bin = trim((string) shell_exec('which pdftotext 2>/dev/null || where pdftotext 2>nul'));
        if (empty($bin)) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tmp, $binary);

        try {
            $pageLimit = $allPages ? '' : ' -f 1 -l 100';
            $cmd       = escapeshellarg($bin) . $pageLimit . ' -enc UTF-8 ' . escapeshellarg($tmp) . ' -';
            $output    = shell_exec($cmd);
            return $output !== null && strlen($output) > 0 ? $output : null;
        } finally {
            @unlink($tmp);
        }
    }

    private function getPdfPageCount(string $binary): int
    {
        $bin = trim((string) shell_exec('which pdfinfo 2>/dev/null || where pdfinfo 2>nul'));
        if (empty($bin)) {
            return 0;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tmp, $binary);

        try {
            $output = shell_exec(escapeshellarg($bin) . ' ' . escapeshellarg($tmp) . ' 2>/dev/null');
            if ($output && preg_match('/Pages:\s*(\d+)/i', $output, $m)) {
                return (int) $m[1];
            }
            return 0;
        } finally {
            @unlink($tmp);
        }
    }

    private function ocrWithVision(string $binary, int $pageCount, bool $allPages): string
    {
        $size     = strlen($binary);
        $maxPages = $allPages ? PHP_INT_MAX : self::NATIVE_PDF_PAGE_LIMIT;

        if ($size <= self::NATIVE_PDF_SIZE_LIMIT && $pageCount <= self::NATIVE_PDF_PAGE_LIMIT) {
            return $this->ocrNativePdf($binary);
        }

        return $this->ocrViaImages($binary, $pageCount, $maxPages);
    }

    // PDFs ≤32MB e ≤100 páginas: envia o PDF diretamente para a API Claude Vision.
    private function ocrNativePdf(string $binary): string
    {
        $response = Http::timeout(180)->withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
            'anthropic-beta'    => 'pdfs-2024-09-25',
            'content-type'      => 'application/json',
        ])->post(self::API_URL, [
            'model'      => $this->modelFast,
            'max_tokens' => self::TOKENS_OCR,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    [
                        'type'   => 'document',
                        'source' => [
                            'type'       => 'base64',
                            'media_type' => 'application/pdf',
                            'data'       => base64_encode($binary),
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Extraia TODO o texto deste documento. Retorne apenas o texto extraído, preservando parágrafos e estrutura. Não adicione comentários ou formatação extra.',
                    ],
                ],
            ]],
        ]);

        if ($response->failed()) {
            return '';
        }

        return $response->json('content.0.text', '');
    }

    // PDFs grandes: renderiza páginas com pdftoppm e processa em batches de 20 páginas.
    private function ocrViaImages(string $binary, int $totalPages, int $maxPages): string
    {
        $bin = trim((string) shell_exec('which pdftoppm 2>/dev/null || where pdftoppm 2>nul'));
        if (empty($bin)) {
            return '';
        }

        $tmp    = tempnam(sys_get_temp_dir(), 'pdf_');
        $outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pdf_ocr_' . uniqid();
        @mkdir($outDir);
        file_put_contents($tmp, $binary);

        $allText = '';
        $pages   = min($totalPages, $maxPages);

        try {
            for ($start = 1; $start <= $pages; $start += self::OCR_BATCH_PAGES) {
                $end = min($start + self::OCR_BATCH_PAGES - 1, $pages);

                shell_exec(
                    escapeshellarg($bin) .
                    " -jpeg -r 150 -f {$start} -l {$end} " .
                    escapeshellarg($tmp) . ' ' .
                    escapeshellarg($outDir . DIRECTORY_SEPARATOR . 'page')
                );

                $images = glob($outDir . DIRECTORY_SEPARATOR . 'page*.jpg') ?: [];
                sort($images);

                if (!empty($images)) {
                    $allText .= $this->ocrImages($images) . "\n";
                    foreach ($images as $img) {
                        @unlink($img);
                    }
                }
            }
        } finally {
            @unlink($tmp);
            @rmdir($outDir);
        }

        return $allText;
    }

    private function ocrImages(array $imagePaths): string
    {
        $content = [];

        foreach ($imagePaths as $path) {
            $content[] = [
                'type'   => 'image',
                'source' => [
                    'type'       => 'base64',
                    'media_type' => 'image/jpeg',
                    'data'       => base64_encode((string) file_get_contents($path)),
                ],
            ];
        }

        $content[] = [
            'type' => 'text',
            'text' => 'Extraia TODO o texto dessas páginas de documento jurídico. Retorne apenas o texto, preservando parágrafos e estrutura. Não adicione comentários.',
        ];

        $response = Http::timeout(120)->withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
            'content-type'      => 'application/json',
        ])->post(self::API_URL, [
            'model'      => $this->modelFast,
            'max_tokens' => self::TOKENS_OCR,
            'messages'   => [['role' => 'user', 'content' => $content]],
        ]);

        if ($response->failed()) {
            return '';
        }

        return $response->json('content.0.text', '');
    }

    private function extractWithSmalot(string $binary): string
    {
        $parser = new Parser();
        return $parser->parseContent($binary)->getText();
    }

    public function extractDocxText(string $binary): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'docx_');
        file_put_contents($tmp, $binary);

        $xml  = file_get_contents("zip://{$tmp}#word/document.xml");
        $text = $xml !== false ? strip_tags($xml) : '';

        unlink($tmp);

        return mb_substr($text, 0, self::PDF_TEXT_LIMIT);
    }

    private function isMock(): bool
    {
        return $this->provider !== 'anthropic';
    }

    private function systemBase(): string
    {
        return $this->prompt('system.base');
    }

    private function prompt(string $key): string
    {
        return Cache::remember("ai_prompt.{$key}", 600, function () use ($key) {
            $row = AiPrompt::where('key', $key)->where('is_active', true)->first();
            return $row ? $row->content : config("ai_prompts.{$key}", '');
        });
    }

    private function mockResponse(string $type): array
    {
        return [
            'content'       => $this->prompt("mock.{$type}") ?: "[Mock] Resposta não encontrada para o tipo: {$type}",
            'model'         => 'mock-legal-copilot',
            'input_tokens'  => 0,
            'output_tokens' => 0,
            'total_tokens'  => 0,
        ];
    }

    private function draftTypeLabel(string $type): string
    {
        return match ($type) {
            'peticao_inicial'          => 'Petição Inicial',
            'contestacao'              => 'Contestação',
            'recurso'                  => 'Recurso',
            'notificacao_extrajudicial' => 'Notificação Extrajudicial',
            'contrato'                 => 'Contrato',
            'parecer'                  => 'Parecer Jurídico',
            default                    => 'Documento Jurídico',
        };
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    private function callAnthropic(string $model, string $system, string $user, array $messages = [], int $maxTokens = self::TOKENS_ANALISE): array
    {
        if (empty($messages)) {
            $messages = [['role' => 'user', 'content' => $user]];
        }

        // Prompt caching: reduz custo em ~90% nas chamadas repetidas ao mesmo system prompt
        $systemPayload = [
            ['type' => 'text', 'text' => $system, 'cache_control' => ['type' => 'ephemeral']],
        ];

        $response = Http::timeout(120)->withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
            'anthropic-beta'    => 'prompt-caching-2024-07-31',
            'content-type'      => 'application/json',
        ])->post(self::API_URL, [
            'model'       => $model,
            'max_tokens'  => $maxTokens,
            'temperature' => $this->temperature,
            'system'      => $systemPayload,
            'messages'    => $messages,
        ]);

        if ($response->failed()) {
            throw new RuntimeException("Anthropic API error ({$response->status()}): {$response->body()}");
        }

        $data = $response->json();

        return [
            'content'       => $data['content'][0]['text'] ?? '',
            'model'         => $data['model'] ?? $model,
            'input_tokens'  => $data['usage']['input_tokens'] ?? 0,
            'output_tokens' => $data['usage']['output_tokens'] ?? 0,
            'total_tokens'  => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
        ];
    }
}
