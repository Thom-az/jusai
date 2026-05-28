<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Razão social (o campo `name` já existe como nome fantasia)
            $table->string('legal_name')->nullable()->after('name');

            // Endereço
            $table->string('zip_code', 9)->nullable()->after('document');  // 99999-999
            $table->string('street')->nullable()->after('zip_code');
            $table->string('street_number', 20)->nullable()->after('street');
            $table->string('complement', 100)->nullable()->after('street_number');
            $table->string('neighborhood')->nullable()->after('complement');
            $table->string('city')->nullable()->after('neighborhood');
            $table->char('state', 2)->nullable()->after('city');

            // Logotipos
            $table->string('logo')->nullable()->after('state');
            $table->string('logo_dark')->nullable()->after('logo');

            // Áreas de atuação (JSON array de strings)
            $table->json('practice_areas')->nullable()->after('logo_dark');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'zip_code', 'street', 'street_number', 'complement',
                'neighborhood', 'city', 'state',
                'logo', 'logo_dark',
                'practice_areas',
            ]);
        });
    }
};
