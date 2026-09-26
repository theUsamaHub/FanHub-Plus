<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->json('preferences')->nullable()->after('ip_address');
            $table->enum('status', ['active', 'unsubscribed', 'bounced', 'complained'])->default('active')->after('unsubscribed_at');
            $table->string('unsubscribe_token', 64)->unique()->nullable()->after('status');
            $table->timestamp('last_email_sent_at')->nullable()->after('unsubscribe_token');
            $table->integer('email_count')->default(0)->after('last_email_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['preferences', 'status', 'unsubscribe_token', 'last_email_sent_at', 'email_count']);
        });
    }
};