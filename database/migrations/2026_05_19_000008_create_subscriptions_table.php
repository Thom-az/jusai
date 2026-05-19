<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->enum('plan', ['starter', 'professional', 'enterprise']);
            $table->enum('status', ['trial', 'active', 'past_due', 'canceled', 'paused'])->default('trial');
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->integer('price_cents');
            $table->char('currency', 3)->default('BRL');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('canceled_at')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('gateway_subscription_id')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
