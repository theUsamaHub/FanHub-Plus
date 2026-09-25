<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Records created / updated / deleted / restored activity for a model.
 *
 * Payloads are redacted and stripped of bookkeeping attributes before they are
 * written, so the log stays readable and never stores secrets.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        $logger = app(ActivityLogger::class);

        static::created(function (Model $model) use ($logger) {
            ActivityLog::log('created', $model, null, $logger->redact($model->getAttributes()));
        });

        static::updated(function (Model $model) use ($logger) {
            $changes = $logger->changed($model);

            // Pure counter or timestamp churn carries no audit value.
            if ($changes === []) {
                return;
            }

            $old = array_intersect_key($model->getRawOriginal(), $changes);

            ActivityLog::log('updated', $model, $old, $changes);
        });

        static::deleted(function (Model $model) use ($logger) {
            // Soft-deleted models emit `deleted` here; the full pre-delete state
            // is captured so the detail view can still show what was removed.
            ActivityLog::log('deleted', $model, $logger->redact($model->getAttributes()), null);
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function (Model $model) use ($logger) {
                ActivityLog::log('restored', $model, null, $logger->redact($model->getAttributes()));
            });

            static::forceDeleted(function (Model $model) use ($logger) {
                ActivityLog::log('force_deleted', $model, $logger->redact($model->getAttributes()), null);
            });
        }
    }
}
