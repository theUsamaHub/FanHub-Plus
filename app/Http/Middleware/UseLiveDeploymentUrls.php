<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class UseLiveDeploymentUrls
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getHost() !== 'fanhubplus.infinityfree.io') {
            return $next($request);
        }

        // InfinityFree serves this Laravel application below the domain root.
        $baseUrl = 'https://fanhubplus.infinityfree.io/FanHub-Plus/public';
        $redirects = [];
        URL::forceRootUrl($baseUrl);
        URL::forceScheme('https');
        foreach (['google', 'discord'] as $provider) {
            $redirects[$provider] = config("services.$provider.redirect");
            config(["services.$provider.redirect" => "$baseUrl/auth/$provider/callback"]);
        }

        try {
            return $next($request);
        } finally {
            // Keep request-specific overrides out of later requests in persistent workers.
            URL::forceRootUrl(null);
            URL::forceScheme(null);
            foreach ($redirects as $provider => $redirect) {
                config(["services.$provider.redirect" => $redirect]);
            }
        }
    }
}
