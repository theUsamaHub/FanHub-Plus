<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

            if ($changes === []) {
                return;
            }

            $old = array_intersect_key($model->getRawOriginal(), $changes);

            ActivityLog::log('updated', $model, $old, $changes);
        });

        static::deleted(function (Model $model) use ($logger) {
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
