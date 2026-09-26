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
echo "Logged in as: " . $user->name . " (id: " . $user->id . ")\n\n";

$routes = [
    'home' => null,
    'public.explore' => null,
    'public.section' => 'characters',
    'public.section' => 'multimedia',
    'public.section' => 'events',
    'public.section' => 'upcoming',
    'public.section' => 'merchandise',
    'public.section' => 'feedback',
    'public.section' => 'privacy',
    'public.section' => 'terms',
    'public.account' => 'dashboard',
    'public.account' => 'bookmarks',
    'public.account' => 'submit-content',
    'events.index' => null,
    'events.nearby' => null,
    'public.about' => null,
    'public.services' => null,
    'public.pricing' => null,
    'public.contact' => null,
    'public.sitemap' => null,
];

foreach ($routes as $name => $param) {
    try {
        $url = $param ? route($name, $param) : route($name);
        $request = Illuminate\Http\Request::create($url, 'GET');
        $response = $httpKernel->handle($request);
        $code = $response->getStatusCode();
        echo sprintf("[%s] %s %s -> %d\n", $code === 200 ? 'OK' : 'ERR', str_pad($name . ($param ? "/$param" : ''), 35), $url, $code);
        $httpKernel->terminate($request, $response);
    } catch (\Throwable $e) {
        echo sprintf("[EXCEPTION] %s/%s -> %s\n", $name, $param, $e->getMessage());
    }
}
