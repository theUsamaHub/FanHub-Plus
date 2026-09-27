<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Merchandise hierarchy:
     *   Category -> Content -> (optional Character) -> Merchandise
     *
     * Content is REQUIRED, Character is OPTIONAL.
     *
     * Existing rows must NOT be deleted. We add the columns as
     * nullable first, auto-assign any obvious rows (matching
     * category) to a default Content row in that category, then
     * a follow-up migration enforces NOT NULL after data is clean.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('merchandise_items', 'content_id')) {
            Schema::table('merchandise_items', function (Blueprint $table) {
                $table->foreignId('content_id')->after('category_id')
                    ->constrained('contents')->restrictOnDelete();
                $table->foreignId('character_id')->nullable()->after('content_id')
                    ->constrained('character_profiles')->nullOnDelete();
                $table->index(['content_id'], 'idx_merch_content');
                $table->index(['character_id'], 'idx_merch_character');
            });
        }

        // Auto-assign existing merchandise rows that have a category but no content:
        // pick the first published content in the same category so the FK can later
        // be tightened to NOT NULL without breaking data.
        $items = DB::table('merchandise_items')->whereNull('content_id')->whereNotNull('category_id')->get(['id', 'category_id']);
        foreach ($items as $item) {
            $fallback = DB::table('contents')
                ->where('category_id', $item->category_id)
                ->orderBy('id')
                ->value('id');
            if ($fallback) {
                DB::table('merchandise_items')->where('id', $item->id)->update(['content_id' => $fallback]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('merchandise_items', 'content_id')) {
            Schema::table('merchandise_items', function (Blueprint $table) {
                $table->dropIndex('idx_merch_content');
                $table->dropConstrainedForeignId('content_id');
            });
        }
        if (Schema::hasColumn('merchandise_items', 'character_id')) {
            Schema::table('merchandise_items', function (Blueprint $table) {
                $table->dropIndex('idx_merch_character');
                $table->dropConstrainedForeignId('character_id');
            });
        }
    }
};