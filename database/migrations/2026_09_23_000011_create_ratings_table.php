<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rateable_type', 255);
            $table->unsignedBigInteger('rateable_id');
            $table->enum('rating_type', ['star', 'thumbs'])->default('star');
            $table->unsignedTinyInteger('stars')->nullable();
            $table->boolean('is_thumbs_up')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'rateable_type', 'rateable_id'], 'uq_rating_unique');
            $table->index(['rateable_type', 'rateable_id'], 'idx_rating_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
