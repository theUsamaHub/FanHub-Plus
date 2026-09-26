<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->json('preferences')->nullable();
            $table->enum('status', ['active', 'unsubscribed', 'bounced', 'complained'])->default('active');
            $table->string('unsubscribe_token', 64)->unique()->nullable();
            $table->timestamp('last_email_sent_at')->nullable();
            $table->integer('email_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['preferences', 'status', 'unsubscribe_token', 'last_email_sent_at', 'email_count']);
        });
    }
};