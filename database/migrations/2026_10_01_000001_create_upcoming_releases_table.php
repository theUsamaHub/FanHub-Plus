<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upcoming_releases', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('kind', 40)->default('anime'); // anime | event | movie | series | game | merchandise
            $table->date('release_date')->nullable();
            $table->string('release_label', 60)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'release_date'], 'idx_upcoming_pub_date');
            $table->index('category_id', 'idx_upcoming_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upcoming_releases');
    }
};
