<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->enum('company_size', ['pequeno', 'medio', 'grande'])->nullable();
            $table->text('area_of_interest')->nullable();
            $table->enum('source', ['website', 'indicacao', 'linkedin', 'evento', 'google_ads', 'cold_outreach', 'outros']);
            $table->enum('status', ['novo', 'contatado', 'qualificado', 'demo_agendada', 'proposta_enviada', 'negociando', 'ganho', 'perdido', 'inativo'])->default('novo');
            $table->string('lost_reason')->nullable();
            $table->integer('estimated_value_cents')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->uuid('converted_organization_id')->nullable();
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('converted_organization_id')->references('id')->on('organizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
