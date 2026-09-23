<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop any indexes that reference the columns before removing them (required on SQLite)
        foreach (Schema::getIndexes('categories') as $index) {
            if (array_intersect($index['columns'], ['published_at', 'unpublish_at'])) {
                Schema::table('categories', function (Blueprint $table) use ($index) {
                    $table->dropIndex($index['name']);
                });
            }
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'unpublish_at']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('body');
            $table->timestamp('unpublish_at')->nullable()->after('published_at');
            $table->index(['is_active', 'published_at']);
            $table->index(['is_active', 'unpublish_at']);
            $table->index('published_at');
            $table->index('unpublish_at');
        });
    }
};
