<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_reviews', function (Blueprint $table) {
            $table->text('prompt_used')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ai_reviews', function (Blueprint $table) {
            $table->text('prompt_used')->nullable(false)->change();
        });
    }
};
