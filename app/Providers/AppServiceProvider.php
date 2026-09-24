<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Content;
use App\Models\MerchandiseItem;
use App\Observers\HomepageCacheObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Content::class, MerchandiseItem::class, Category::class] as $model) {
            $model::observe(HomepageCacheObserver::class);
        }
    }
}
