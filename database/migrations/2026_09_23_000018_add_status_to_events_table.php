<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'status')) {
            Schema::table('events', function (Blueprint $table) {
                $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft')->after('ticket_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'status')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
