<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE legal_cases DROP CONSTRAINT IF EXISTS legal_cases_status_check');
        DB::statement("ALTER TABLE legal_cases ADD CONSTRAINT legal_cases_status_check CHECK (status IN ('triagem', 'em_andamento', 'aguardando_cliente', 'aguardando_tribunal', 'aguardando_prazo', 'em_recurso', 'encerrado', 'arquivado'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE legal_cases DROP CONSTRAINT IF EXISTS legal_cases_status_check');
        DB::statement("ALTER TABLE legal_cases ADD CONSTRAINT legal_cases_status_check CHECK (status IN ('triagem', 'em_andamento', 'aguardando_cliente', 'aguardando_tribunal', 'encerrado', 'arquivado'))");
    }
};
