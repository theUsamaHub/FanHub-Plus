<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->text('message');
            $table->text('response')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->index('session_id', 'idx_chatbot_session');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_queries');
    }
};
