<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\Media;
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
        foreach ([Content::class, MerchandiseItem::class, Category::class, CharacterProfile::class, Media::class] as $model) {
            $model::observe(HomepageCacheObserver::class);
        }
    }
}
