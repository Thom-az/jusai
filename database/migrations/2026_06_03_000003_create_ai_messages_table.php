<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('ai_conversations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
