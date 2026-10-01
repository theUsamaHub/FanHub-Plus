<?php

namespace App\Support;

class SocialLogin
{
    public const PROVIDERS = ['google' => 'Google', 'discord' => 'Discord'];

    public static function enabled(string $provider): bool
    {
        return isset(self::PROVIDERS[$provider])
            && config("services.$provider.client_id")
            && config("services.$provider.client_secret")
            && config("services.$provider.redirect");
    }

    public static function providers(): array
    {
        return array_filter(self::PROVIDERS, fn ($provider) => self::enabled($provider), ARRAY_FILTER_USE_KEY);
    }
}
