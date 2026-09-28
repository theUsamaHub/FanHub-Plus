<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'content_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('content_id')->nullable()->after('category_id')
                    ->constrained('contents')->nullOnDelete();
                $table->index(['content_id', 'start_at'], 'idx_events_content_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'content_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropIndex('idx_events_content_date');
                $table->dropConstrainedForeignId('content_id');
            });
        }
    }
};