<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedInteger('chunk_size')->nullable()->after('alt_text');
            $table->unsignedInteger('total_chunks')->nullable()->after('chunk_size');
            $table->unsignedInteger('uploaded_chunks')->default(0)->after('total_chunks');
            $table->string('status')->default('ready')->after('uploaded_chunks');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['chunk_size', 'total_chunks', 'uploaded_chunks', 'status']);
        });
    }
};