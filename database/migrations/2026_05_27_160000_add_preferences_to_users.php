<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Aparência — light | dark | system
            $table->string('theme', 10)->default('system')->after('job_title');

            // Fuso horário
            $table->string('timezone', 50)->default('America/Sao_Paulo')->after('theme');

            // Preferências de notificação (JSON)
            // Estrutura: { channels: {email, browser}, quiet: {enabled, start, end}, events: {...} }
            $table->jsonb('notification_prefs')->nullable()->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme', 'timezone', 'notification_prefs']);
        });
    }
};
