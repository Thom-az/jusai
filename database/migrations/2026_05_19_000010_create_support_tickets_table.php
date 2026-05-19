<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->unsignedBigInteger('opened_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['aberto', 'em_andamento', 'aguardando_cliente', 'resolvido', 'fechado'])->default('aberto');
            $table->enum('priority', ['baixa', 'media', 'alta', 'critica'])->default('media');
            $table->enum('category', ['tecnico', 'financeiro', 'duvida', 'sugestao', 'bug', 'outros']);
            $table->text('resolution_notes')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('opened_by')->references('id')->on('users');
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
