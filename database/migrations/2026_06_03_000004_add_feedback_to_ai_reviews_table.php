<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_reviews', function (Blueprint $table) {
            $table->unsignedTinyInteger('feedback_rating')->nullable()->after('reviewed_at');
            $table->text('feedback_comment')->nullable()->after('feedback_rating');
        });
    }

    public function down(): void
    {
        Schema::table('ai_reviews', function (Blueprint $table) {
            $table->dropColumn(['feedback_rating', 'feedback_comment']);
        });
    }
};
