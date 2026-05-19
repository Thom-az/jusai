<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('legal_case_id')->nullable();
            $table->uuid('document_id')->nullable();
            $table->uuid('draft_id')->nullable();
            $table->enum('type', ['analise_documento', 'revisao_minuta', 'pesquisa_juridica', 'resumo_caso']);
            $table->text('prompt_used');
            $table->text('result');
            $table->enum('status', ['processando', 'concluido', 'erro', 'cancelado'])->default('processando');
            $table->string('ai_model_used');
            $table->integer('tokens_used')->nullable();
            $table->decimal('confidence_score', 3, 2)->nullable();
            $table->boolean('requires_human_review')->default(true);
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->nullOnDelete();
            $table->foreign('document_id')->references('id')->on('documents')->nullOnDelete();
            $table->foreign('draft_id')->references('id')->on('drafts')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_reviews');
    }
};
