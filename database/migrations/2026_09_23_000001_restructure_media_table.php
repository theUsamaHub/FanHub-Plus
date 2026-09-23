<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('uploaded_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('original_filename')->nullable()->after('path');
            $table->enum('media_type', ['image', 'video', 'audio', 'document'])->default('image')->after('mime_type');
            $table->unsignedBigInteger('size_bytes')->nullable()->after('media_type');
            $table->unsignedInteger('width_px')->nullable()->after('size_bytes');
            $table->unsignedInteger('height_px')->nullable()->after('width_px');
            $table->unsignedInteger('duration_seconds')->nullable()->after('height_px');
            $table->string('alt_text')->nullable()->after('duration_seconds');
            $table->string('path', 500)->change();
            $table->string('mime_type', 100)->nullable()->change();
            $table->string('disk', 50)->default('public')->change();
            $table->index('media_type', 'idx_media_type');
        });

        DB::table('media')->orderBy('id')->get()->each(function ($media) {
            DB::table('media')->where('id', $media->id)->update([
                'uploaded_by' => $media->created_by,
                'original_filename' => $media->original_name,
                'size_bytes' => $media->size,
            ]);
        });

        // Drop the foreign key first — MySQL refuses to drop an index backing an FK constraint
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        // Drop indexes that reference columns about to be removed (required on SQLite)
        foreach (Schema::getIndexes('media') as $index) {
            if (array_intersect($index['columns'], ['name', 'original_name', 'mediable_type', 'mediable_id', 'created_by'])) {
                Schema::table('media', function (Blueprint $table) use ($index) {
                    $table->dropIndex($index['name']);
                });
            }
        }

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['mediable_type', 'mediable_id', 'name', 'original_name', 'size', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('mediable_type')->nullable()->after('id');
            $table->unsignedBigInteger('mediable_id')->nullable()->after('mediable_type');
            $table->string('name')->after('mediable_id');
            $table->string('original_name')->nullable()->after('name');
            $table->unsignedBigInteger('size')->nullable()->after('original_name');
            $table->foreignId('created_by')->nullable()->after('disk')->constrained('users')->nullOnDelete();
            $table->index(['mediable_type', 'mediable_id']);
        });

        DB::table('media')->orderBy('id')->get()->each(function ($media) {
            DB::table('media')->where('id', $media->id)->update([
                'name' => $media->original_filename ?? 'file',
                'original_name' => $media->original_filename,
                'size' => $media->size_bytes,
                'created_by' => $media->uploaded_by,
            ]);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('idx_media_type');
            $table->dropColumn([
                'uploaded_by',
                'original_filename',
                'media_type',
                'size_bytes',
                'width_px',
                'height_px',
                'duration_seconds',
                'alt_text',
            ]);
            $table->string('path')->change();
            $table->string('mime_type')->change();
            $table->string('disk')->default('public')->change();
        });
    }
};
