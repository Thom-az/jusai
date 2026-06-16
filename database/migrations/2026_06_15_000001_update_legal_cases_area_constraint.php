<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE legal_cases DROP CONSTRAINT IF EXISTS legal_cases_area_check');
        DB::statement("ALTER TABLE legal_cases ADD CONSTRAINT legal_cases_area_check CHECK (area IN ('civil', 'trabalhista', 'empresarial', 'tributario', 'criminal', 'familia', 'outros', 'outro', 'imobiliario', 'previdenciario', 'administrativo'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE legal_cases DROP CONSTRAINT IF EXISTS legal_cases_area_check');
        DB::statement("ALTER TABLE legal_cases ADD CONSTRAINT legal_cases_area_check CHECK (area IN ('civil', 'trabalhista', 'empresarial', 'tributario', 'criminal', 'familia', 'outros'))");
    }
};
