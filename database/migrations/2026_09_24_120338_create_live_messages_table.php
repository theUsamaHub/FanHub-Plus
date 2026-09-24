<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->index('created_at', 'idx_live_msg_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_messages');
    }
};