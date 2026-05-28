<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Telefone com máscara BR: (11) 91234-5678
            $table->string('phone', 20)->nullable()->after('email');

            // Número da OAB — somente os dígitos (ex: 123456)
            $table->string('oab_number', 10)->nullable()->after('phone');

            // UF do registro OAB (ex: SP)
            $table->char('oab_uf', 2)->nullable()->after('oab_number');

            // Path do avatar relativo ao disco 'public' (ex: avatars/abc123.jpg)
            $table->string('avatar')->nullable()->after('oab_uf');

            // Cargo no escritório (ex: Advogado Sênior, Estagiário...)
            $table->string('job_title', 100)->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'oab_number', 'oab_uf', 'avatar', 'job_title']);
        });
    }
};
