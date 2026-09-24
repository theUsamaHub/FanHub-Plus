<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchandise_items', function (Blueprint $table) {
            $table->date('release_date')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('merchandise_items', function (Blueprint $table) {
            $table->dropColumn('release_date');
        });
    }
};
