<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Smalot\PdfParser\Parser;

class AnthropicService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';
    private const MAX_TOKENS = 4096;
    private const PDF_TEXT_LIMIT = 30000;

    private const SYSTEM_BASE = 'Você é um assistente jurídico especializado no ordenamento jurídico brasileiro. Opera como copiloto de escritório de advocacia. Respostas objetivas, fundamentadas. Sempre indique que o resultado deve ser validado por profissional habilitado. Idioma: português do Brasil. Nunca invente leis, artigos ou jurisprudências.';

    public function __construct(
        private readonly string $apiKey,
        private readonly float  $temperature,
        private readonly string $modelFast,
        private readonly string $modelStrong,
    ) {}

    public function resumoCaso(string $documentText, string $caseTitle, string $caseArea): array
    {
        $system = self::SYSTEM_BASE . ' Produza um resumo executivo em 4 seções: 1) Síntese do caso, 2) Partes envolvidas, 3) Objeto da disputa/operação, 4) Pontos de atenção prioritários.';

        $user = "Caso: {$caseTitle}\nÁrea: {$caseArea}\n\nDocumento:\n{$documentText}";

        return $this->callAnthropic($this->modelFast, $system, $user);
    }

    public function analiseDocumento(string $documentText, string $caseTitle): array
    {
        $system = self::SYSTEM_BASE . ' Analise o documento e produza: 1) Cláusulas-chave, 2) Riscos identificados com classificação (alto/médio/baixo), 3) Prazos relevantes, 4) Recomendações práticas.';

        $user = "Caso: {$caseTitle}\n\nDocumento:\n{$documentText}";

        return $this->callAnthropic($this->modelStrong, $system, $user);
    }

    public function revisaoMinuta(string $draftContent, string $draftTitle, string $caseContext): array
    {
        $system = self::SYSTEM_BASE . ' Revise a minuta e identifique: 1) Inconsistências jurídicas, 2) Ambiguidades que geram risco, 3) Ausências relevantes, 4) Sugestões de melhoria com trechos citados do original.';

        $user = "Minuta: {$draftTitle}\nContexto do caso: {$caseContext}\n\nConteúdo da minuta:\n{$draftContent}";

        return $this->callAnthropic($this->modelStrong, $system, $user);
    }

    public function pesquisaJuridica(string $question, string $docsContext, string $caseTitle): array
    {
        $system = self::SYSTEM_BASE . ' Com base nos documentos do caso, fundamente a resposta em legislação, doutrina e jurisprudência do STF/STJ. Cite artigos de lei e números de decisões apenas se presentes nos documentos fornecidos.';

        $user = "Caso: {$caseTitle}\nPergunta: {$question}\n\nDocumentos do caso:\n{$docsContext}";

        return $this->callAnthropic($this->modelStrong, $system, $user);
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

    private function callAnthropic(string $model, string $system, string $user): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
            'content-type'      => 'application/json',
        ])->post(self::API_URL, [
            'model'       => $model,
            'max_tokens'  => self::MAX_TOKENS,
            'temperature' => $this->temperature,
            'system'      => $system,
            'messages'    => [
                ['role' => 'user', 'content' => $user],
            ],
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
