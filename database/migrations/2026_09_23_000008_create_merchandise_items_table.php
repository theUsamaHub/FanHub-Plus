<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchandise_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->enum('tag', ['limited_edition', 'pre_order', 'collectible', 'standard'])->default('standard');
            $table->boolean('is_upcoming')->default(false);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
            $table->index('category_id', 'idx_merch_category');
            $table->index('is_upcoming', 'idx_merch_upcoming');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchandise_items');
    }
};
