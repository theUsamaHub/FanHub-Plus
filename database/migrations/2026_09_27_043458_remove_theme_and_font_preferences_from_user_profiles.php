<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['theme_preference', 'font_size_preference']);
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->enum('theme_preference', ['light', 'dark', 'system'])->default('system');
            $table->enum('font_size_preference', ['small', 'medium', 'large'])->default('medium');
        });
    }
};
