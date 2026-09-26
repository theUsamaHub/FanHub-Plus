<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'icon_media_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('icon_media_id')->nullable()->constrained('media')->nullOnDelete();
            });
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('description', 500)->nullable()->change();
        });

        // Drop indexes that reference columns about to be removed (required on SQLite)
        foreach (Schema::getIndexes('categories') as $index) {
            if (array_intersect($index['columns'], ['is_active', 'sort_order', 'deleted_at', 'image', 'body'])) {
                Schema::table('categories', function (Blueprint $table) use ($index) {
                    $table->dropIndex($index['name']);
                });
            }
        }

        if (Schema::hasColumn('categories', 'deleted_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        $drop = array_filter(
            ['is_active', 'sort_order', 'image', 'body'],
            fn (string $column) => Schema::hasColumn('categories', $column)
        );

        if ($drop !== []) {
            Schema::table('categories', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }

        if (Schema::hasColumn('categories', 'created_by')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
            });
        }

        if (Schema::hasColumn('categories', 'updated_by')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropConstrainedForeignId('updated_by');
            });
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'icon_media_id')) {
                $table->dropConstrainedForeignId('icon_media_id');
            }
            $table->text('description')->nullable()->change();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('image')->nullable();
            $table->text('body')->nullable();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
