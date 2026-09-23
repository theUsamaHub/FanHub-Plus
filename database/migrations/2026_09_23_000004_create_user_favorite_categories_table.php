<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favorite_categories', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->primary(['user_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorite_categories');
    }
};
