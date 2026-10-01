<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\Event;
use App\Models\Media;
use App\Models\MerchandiseItem;
use App\Observers\HomepageCacheObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        EventFacade::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Discord\Provider::class);
        });

        foreach ([Content::class, Event::class, MerchandiseItem::class, Category::class, CharacterProfile::class, Media::class] as $model) {
            $model::observe(HomepageCacheObserver::class);
        }

        EventFacade::listen(Login::class, function (Login $event) {
            ActivityLog::log('login', $event->user, null, [
                'email' => $event->user->email,
                'guard' => $event->guard,
            ], $event->user);
        });
    }
}
