<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('organization_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event');
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
