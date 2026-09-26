<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Find a registered user with data
$user = App\Models\User::whereHas('roles', function ($q) { $q->where('slug', 'registered-user'); })->first();
if (!$user) { echo "No registered user found\n"; exit(1); }

auth()->login($user);

$routes = [
    'user.dashboard',
    'user.bookmarks',
    'user.favorites',
    'user.activity',
    'user.reviews',
    'user.submissions',
    'user.submissions.create',
    'user.feedback',
    'profile.edit',
];

foreach ($routes as $name) {
    try {
        $url = route($name);
        $request = Illuminate\Http\Request::create($url, 'GET');
        $response = $httpKernel->handle($request);
        $code = $response->getStatusCode();
        $body = $response->getContent();

        // Check for errors in the body
        $hasError = str_contains($body, 'Whoops') || str_contains($body, 'Exception') || str_contains($body, 'Fatal error');
        $hasLaravelError = str_contains($body, '<title>') && str_contains($body, 'Error');

        // Look for first line with content
        $bodyLen = strlen($body);
        $preview = substr(strip_tags($body), 0, 80);

        $status = $code >= 200 && $code < 300 ? 'OK' : ($code >= 300 && $code < 400 ? 'REDIRECT' : 'ERROR');
        $errNote = $hasError || $hasLaravelError ? ' [HAS ERROR]' : '';
        echo sprintf("[%s] %s -> %d (size=%d)%s\n", $status, str_pad($name, 25), $code, $bodyLen, $errNote);
        if ($hasError || $hasLaravelError) {
            // Find error message
            if (preg_match('/(Whoops|Exception|Error)[\s\S]{0,500}/', $body, $matches)) {
                echo "  -> " . substr(strip_tags($matches[0]), 0, 200) . "\n";
            }
        }
        $httpKernel->terminate($request, $response);
    } catch (\Throwable $e) {
        echo sprintf("[EXCEPTION] %s -> %s\n", $name, $e->getMessage());
    }
}
