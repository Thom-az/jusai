<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('subscription_id')->nullable();
            $table->string('reference_number')->unique();
            $table->integer('amount_cents');
            $table->char('currency', 3)->default('BRL');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'canceled'])->default('pending');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('gateway_invoice_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
