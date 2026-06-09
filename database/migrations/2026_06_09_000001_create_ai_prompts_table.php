<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('key')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prompts');
    }

    private function seedDefaults(): void
    {
        $prompts = config('ai_prompts');
        $now     = now();

        $rows = [];

        $systemLabels = [
            'base'              => ['Base do sistema',                'Prompt base injetado em todas as chamadas de IA'],
            'resumo_caso'       => ['Resumo de caso',                 'Instrução para geração de resumo executivo estruturado'],
            'analise_documento' => ['Análise de documento',           'Instrução para análise de cláusulas e riscos'],
            'revisao_minuta'    => ['Revisão de minuta',              'Instrução para revisão e identificação de problemas jurídicos'],
            'pesquisa_juridica' => ['Pesquisa jurídica',              'Instrução para responder questões jurídicas com fundamento'],
            'rascunho_minuta'   => ['Rascunho de minuta',             'Instrução para geração de documentos jurídicos completos'],
            'chat'              => ['Chat jurídico',                  'Instrução para o assistente conversacional por caso'],
        ];

        $mockLabels = [
            'resumo_caso'       => ['Mock — Resumo de caso',          'Resposta de demonstração para resumo de caso'],
            'analise_documento' => ['Mock — Análise de documento',    'Resposta de demonstração para análise de documento'],
            'revisao_minuta'    => ['Mock — Revisão de minuta',       'Resposta de demonstração para revisão de minuta'],
            'pesquisa_juridica' => ['Mock — Pesquisa jurídica',       'Resposta de demonstração para pesquisa jurídica'],
            'rascunho_minuta'   => ['Mock — Rascunho de minuta',      'Resposta de demonstração para rascunho de minuta'],
            'chat'              => ['Mock — Chat jurídico',           'Primeira resposta de demonstração do assistente de chat'],
        ];

        foreach ($systemLabels as $suffix => [$label, $desc]) {
            $rows[] = [
                'id'          => \Illuminate\Support\Str::uuid(),
                'key'         => "system.{$suffix}",
                'label'       => $label,
                'description' => $desc,
                'content'     => $prompts['system'][$suffix] ?? '',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        foreach ($mockLabels as $suffix => [$label, $desc]) {
            $rows[] = [
                'id'          => \Illuminate\Support\Str::uuid(),
                'key'         => "mock.{$suffix}",
                'label'       => $label,
                'description' => $desc,
                'content'     => $prompts['mock'][$suffix] ?? '',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        DB::table('ai_prompts')->insert($rows);
    }
};
