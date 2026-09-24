<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HomepageCacheObserver
{
    public function saved(object $model): void
    {
        Cache::forever('homepage:version', (string) Str::uuid());
    }

    public function deleted(object $model): void
    {
        $this->saved($model);
    }
}
