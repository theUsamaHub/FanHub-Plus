<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tags', 'color')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tags', 'color')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->string('color')->default('#4f46e5');
            });
        }
    }
};
