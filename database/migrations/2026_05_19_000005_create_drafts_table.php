<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('legal_case_id')->nullable();
            $table->string('title');
            $table->enum('type', ['notificacao_extrajudicial', 'contrato', 'peticao_inicial', 'contestacao', 'recurso', 'parecer', 'outros']);
            $table->text('content');
            $table->enum('status', ['rascunho', 'em_revisao', 'aprovado', 'rejeitado', 'publicado'])->default('rascunho');
            $table->smallInteger('version')->default(1);
            $table->boolean('generated_by_ai')->default(false);
            $table->string('ai_model_used')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};
