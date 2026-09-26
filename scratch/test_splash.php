<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$routes = ['/', '/explore', '/user/dashboard', '/events', '/stories/' . App\Models\Content::visibleToPublic()->first()?->slug];

foreach ($routes as $url) {
    $request = Illuminate\Http\Request::create($url, 'GET');
    $response = $httpKernel->handle($request);
    $body = $response->getContent();
    echo "URL: $url -> Status: " . $response->getStatusCode() . PHP_EOL;
    echo "  Has splash div: " . (str_contains($body, 'id="splash-screen"') ? 'YES' : 'NO') . PHP_EOL;
    echo "  Has sessionStorage check: " . (str_contains($body, 'fanhub-splash-shown') ? 'YES' : 'NO') . PHP_EOL;
    echo "  Default display style: " . (preg_match('/id="splash-screen"[^>]*style="[^"]*display:none/i', $body) ? 'display:none (HIDDEN initially)' : 'OTHER') . PHP_EOL;
    echo PHP_EOL;
    $httpKernel->terminate($request, $response);
}
