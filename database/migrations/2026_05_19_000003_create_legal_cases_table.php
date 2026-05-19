<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('title');
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->enum('area', ['civil', 'trabalhista', 'empresarial', 'tributario', 'criminal', 'familia', 'outros']);
            $table->enum('status', ['triagem', 'em_andamento', 'aguardando_cliente', 'aguardando_tribunal', 'encerrado', 'arquivado'])->default('triagem');
            $table->enum('risk_level', ['baixo', 'medio', 'alto', 'critico'])->default('medio');
            $table->text('description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->date('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};
