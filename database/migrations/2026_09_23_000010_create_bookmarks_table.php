<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('bookmarkable_type', 100);
            $table->unsignedBigInteger('bookmarkable_id');
            $table->string('note', 500)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id'], 'uq_bookmark_unique');
            $table->index(['bookmarkable_type', 'bookmarkable_id'], 'idx_bookmark_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
