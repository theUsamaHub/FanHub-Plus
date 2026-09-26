<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Find a registered user
$user = App\Models\User::whereHas('roles', function ($q) { $q->where('slug', 'registered-user'); })->first();
if (!$user) { echo "No registered user found\n"; exit(1); }

auth()->login($user);

$routes = [
    ['home', null],
    ['public.explore', null],
    ['public.section', 'characters'],
    ['public.section', 'multimedia'],
    ['public.section', 'events'],
    ['public.section', 'upcoming'],
    ['public.section', 'merchandise'],
    ['public.section', 'feedback'],
    ['public.account', 'dashboard'],
    ['public.account', 'bookmarks'],
    ['public.account', 'submit-content'],
    ['events.index', null],
    ['events.nearby', null],
    ['public.sitemap', null],
];

// Find a content, character, merchandise, event for member interactions
$content = App\Models\Content::visibleToPublic()->first();
$char = App\Models\CharacterProfile::first();
$merch = App\Models\MerchandiseItem::first();
$event = App\Models\Event::published()->first();

if ($content) $routes[] = ['public.content', $content->slug];
if ($char) $routes[] = ['public.character', $char->slug];
if ($merch) $routes[] = ['public.merchandise', $merch->slug];
if ($event) $routes[] = ['events.show', $event->slug];

foreach ($routes as [$name, $param]) {
    try {
        $url = $param ? route($name, $param) : route($name);
        $request = Illuminate\Http\Request::create($url, 'GET');
        $response = $httpKernel->handle($request);
        $code = $response->getStatusCode();
        $body = $response->getContent();

        // Check for errors
        $hasError = str_contains($body, 'Whoops') || str_contains($body, 'Exception');
        $bodyLen = strlen($body);

        $status = $code >= 200 && $code < 300 ? 'OK' : ($code >= 300 && $code < 400 ? 'REDIRECT' : 'ERROR');
        $errNote = $hasError ? ' [HAS ERROR]' : '';
        echo sprintf("[%s] %s/%s -> %d (size=%d)%s\n", $status, str_pad($name, 20), str_pad($param ?? '-', 30), $code, $bodyLen, $errNote);
        if ($hasError) {
            if (preg_match('/(Whoops|Exception)[\s\S]{0,300}/', $body, $matches)) {
                echo "  -> " . substr(strip_tags($matches[0]), 0, 200) . "\n";
            }
        }
        $httpKernel->terminate($request, $response);
    } catch (\Throwable $e) {
        echo sprintf("[EXCEPTION] %s/%s -> %s\n", $name, $param, $e->getMessage());
    }
}