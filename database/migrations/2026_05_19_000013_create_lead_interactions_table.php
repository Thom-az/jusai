<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_interactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('lead_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['email', 'ligacao', 'reuniao', 'demo', 'proposta', 'linkedin', 'outros']);
            $table->text('notes')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_interactions');
    }
};
