<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug', 240)->nullable()->unique();
            $table->string('event_type', 40)->nullable();
            $table->string('short_description', 400)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('popularity_score')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->index(['status', 'is_featured', 'start_at'], 'events_public_featured');
            $table->index(['status', 'event_type', 'start_at'], 'events_public_type');
        });
        // Preserve existing records and give each an unambiguous public URL.
        DB::table('events')->orderBy('id')->chunkById(200, function ($events) {
            foreach ($events as $event) {
                DB::table('events')->where('id', $event->id)->update([
                    'slug' => (Str::slug($event->title) ?: 'event').'-'.$event->id,
                ]);
            }
        });
        Schema::create('event_media', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->primary(['event_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_media');
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_public_featured');
            $table->dropIndex('events_public_type');
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'event_type', 'short_description', 'is_featured', 'popularity_score', 'view_count']);
        });
    }
};
