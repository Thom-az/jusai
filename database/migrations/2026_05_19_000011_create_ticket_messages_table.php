<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->boolean('is_internal')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ticket_id')->references('id')->on('support_tickets')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
