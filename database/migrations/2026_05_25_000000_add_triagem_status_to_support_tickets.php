<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE support_tickets DROP CONSTRAINT IF EXISTS support_tickets_status_check");
        DB::statement("ALTER TABLE support_tickets ADD CONSTRAINT support_tickets_status_check CHECK (status IN ('aberto','triagem','em_andamento','aguardando_cliente','resolvido','fechado'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE support_tickets DROP CONSTRAINT IF EXISTS support_tickets_status_check");
        DB::statement("ALTER TABLE support_tickets ADD CONSTRAINT support_tickets_status_check CHECK (status IN ('aberto','em_andamento','aguardando_cliente','resolvido','fechado'))");
    }
};
