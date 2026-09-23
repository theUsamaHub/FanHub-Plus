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
            $table->string('bookmarkable_type', 255);
            $table->unsignedBigInteger('bookmarkable_id');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id'], 'uq_bookmark_unique');
            $table->index(['bookmarkable_type', 'bookmarkable_id'], 'idx_bookmark_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
