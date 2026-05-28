<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // uuid type rejects non-UUID values (e.g. BigInteger User IDs).
            // Changing to string accommodates both UUID (Organization) and BigInteger (User) subjects.
            $table->string('subject_id', 36)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->uuid('subject_id')->nullable()->change();
        });
    }
};
