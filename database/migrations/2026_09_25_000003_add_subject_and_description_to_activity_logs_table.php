<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('subject', 200)->nullable()->after('event');
            $table->string('description', 500)->nullable()->after('subject');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('created_at', 'idx_activity_logs_created_at');
            $table->index(['event', 'created_at'], 'idx_activity_logs_event_created');
        });

        $this->backfillLegacyRows();
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_activity_logs_created_at');
            $table->dropIndex('idx_activity_logs_event_created');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['subject', 'description']);
        });
    }

    /**
     * Give pre-existing rows a best-effort subject so the log stays readable.
     * The subject is derived from whichever value looks like a human label.
     */
    private function backfillLegacyRows(): void
    {
        DB::table('activity_logs')
            ->whereNull('subject')
            ->orderBy('id')
            ->chunkById(200, function ($logs) {
                foreach ($logs as $log) {
                    $values = json_decode($log->new_values ?: $log->old_values ?: '[]', true);

                    if (! is_array($values)) {
                        continue;
                    }

                    $subject = collect(['title', 'name', 'key', 'original_filename'])
                        ->map(fn (string $key) => $values[$key] ?? null)
                        ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                        ->first();

                    if ($subject === null) {
                        continue;
                    }

                    DB::table('activity_logs')
                        ->where('id', $log->id)
                        ->update(['subject' => \Illuminate\Support\Str::limit($subject, 200, '')]);
                }
            });
    }
};
