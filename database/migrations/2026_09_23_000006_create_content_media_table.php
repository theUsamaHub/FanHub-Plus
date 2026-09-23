<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->enum('role', ['cover', 'gallery', 'trailer', 'audio_clip', 'attachment'])->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['content_id', 'media_id', 'role'], 'uq_content_media_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_media');
    }
};
