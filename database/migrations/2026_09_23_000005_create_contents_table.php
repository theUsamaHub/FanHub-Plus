<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug', 280)->unique();
            $table->enum('type', ['article', 'video', 'audio', 'image'])->default('article');
            $table->string('kind', 40)->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();
            $table->date('release_date')->nullable();
            $table->integer('popularity_score')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->enum('status', ['draft', 'pending_review', 'published', 'rejected'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_user_submitted')->default(false);
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['category_id', 'status'], 'idx_content_category_status');
            $table->index('type', 'idx_content_type');
            $table->index(['kind', 'release_date'], 'idx_content_kind_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};