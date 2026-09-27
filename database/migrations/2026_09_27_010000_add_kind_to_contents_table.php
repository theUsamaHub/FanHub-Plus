<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contents') && !Schema::hasColumn('contents', 'kind')) {
            Schema::table('contents', function (Blueprint $table) {
                $table->string('kind', 40)->nullable()->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contents') && Schema::hasColumn('contents', 'kind')) {
            Schema::table('contents', function (Blueprint $table) {
                $table->dropColumn('kind');
            });
        }
    }
};
