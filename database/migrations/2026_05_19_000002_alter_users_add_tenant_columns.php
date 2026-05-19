<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable()->after('id');
            $table->enum('role', ['super_admin', 'org_admin', 'lawyer', 'assistant'])->default('lawyer')->after('organization_id');
            $table->boolean('is_active')->default(true)->after('role');

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['organization_id', 'role', 'is_active']);
        });
    }
};
