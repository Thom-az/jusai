<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // legal_cases — listagem paginada e contagem no dashboard
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->index(['organization_id', 'updated_at'], 'idx_legal_cases_org_updated');
            $table->index(['organization_id', 'status'],     'idx_legal_cases_org_status');
        });

        // documents — listagem paginada, contagem de 'ready' no dashboard
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['organization_id', 'updated_at'], 'idx_documents_org_updated');
            $table->index(['organization_id', 'status'],     'idx_documents_org_status');
            $table->index(['legal_case_id', 'status'],       'idx_documents_case_status');
        });

        // ai_reviews — contador de revisões pendentes no dashboard (4 condições)
        Schema::table('ai_reviews', function (Blueprint $table) {
            $table->index(['organization_id', 'status'],                       'idx_ai_reviews_org_status');
            $table->index(['organization_id', 'status', 'requires_human_review'], 'idx_ai_reviews_org_status_review');
        });

        // activity_logs — feed do dashboard e tela de caso
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['organization_id', 'created_at'], 'idx_activity_logs_org_created');
        });
    }

    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropIndex('idx_legal_cases_org_updated');
            $table->dropIndex('idx_legal_cases_org_status');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_documents_org_updated');
            $table->dropIndex('idx_documents_org_status');
            $table->dropIndex('idx_documents_case_status');
        });

        Schema::table('ai_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_ai_reviews_org_status');
            $table->dropIndex('idx_ai_reviews_org_status_review');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_activity_logs_org_created');
        });
    }
};
