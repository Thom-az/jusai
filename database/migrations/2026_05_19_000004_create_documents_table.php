<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('legal_case_id')->nullable();
            $table->string('title');
            $table->string('original_filename');
            $table->string('storage_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->enum('status', ['uploading', 'processing', 'ready', 'error'])->default('uploading');
            $table->text('ai_summary')->nullable();
            $table->timestamp('ai_extracted_at')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->nullOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
