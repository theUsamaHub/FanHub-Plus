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
echo "Logged in as: " . $user->name . " (id: " . $user->id . ")\n";

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
        $status = $code >= 200 && $code < 300 ? 'OK' : ($code >= 300 && $code < 400 ? 'REDIRECT' : 'ERROR');
        echo sprintf("[%s] %s %s -> %d\n", $status, str_pad($name, 25), $url, $code);
        $httpKernel->terminate($request, $response);
    } catch (\Throwable $e) {
        echo sprintf("[EXCEPTION] %s -> %s\n", $name, $e->getMessage());
    }
}
