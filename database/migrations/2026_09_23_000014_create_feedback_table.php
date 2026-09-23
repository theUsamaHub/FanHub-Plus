<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['bug', 'suggestion', 'query'])->default('query');
            $table->text('message');
            $table->enum('status', ['open', 'in_review', 'resolved', 'closed'])->default('open');
            $table->timestamps();
            $table->index('status', 'idx_feedback_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
