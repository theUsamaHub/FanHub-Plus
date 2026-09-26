<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::whereHas('roles', function ($q) { $q->where('slug', 'registered-user'); })->first();
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
        $bodyLen = strlen($body);
        $hasError = str_contains($body, 'Whoops') || str_contains($body, 'Undefined') || str_contains($body, 'Error:');

        // Find any error messages
        $errorMsg = '';
        if ($hasError) {
            if (preg_match('/(Undefined|Whoops|Error)[\s\S]{0,400}/', $body, $matches)) {
                $errorMsg = substr(strip_tags($matches[0]), 0, 250);
            }
        }

        $status = $code === 200 ? 'OK' : ($code === 302 ? 'REDIRECT' : 'ERROR');
        echo sprintf("[%s] %s -> %d (size=%d)\n", $status, str_pad($name, 25), $code, $bodyLen);
        if ($errorMsg) echo "  ERR: " . $errorMsg . "\n";
        $httpKernel->terminate($request, $response);
    } catch (\Throwable $e) {
        echo sprintf("[EXCEPTION] %s -> %s\n", $name, $e->getMessage());
    }
}
