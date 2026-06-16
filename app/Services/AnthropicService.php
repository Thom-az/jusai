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
        $parser = new Parser();
        $pdf    = $parser->parseContent($binary);
        $text   = $pdf->getText();

        return mb_substr($text, 0, self::PDF_TEXT_LIMIT);
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
