<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Find a registered user with some data
$user = App\Models\User::whereHas('roles', function ($q) { $q->where('slug', 'registered-user'); })->first();
if (!$user) { echo "No registered user found\n"; exit(1); }

auth()->login($user);
echo "Logged in as: " . $user->name . " (id: " . $user->id . ")\n";
echo "Profile exists: " . ($user->profile ? 'yes' : 'no') . "\n";
if ($user->profile) {
    echo "  - display_name: " . ($user->profile->display_name ?? 'null') . "\n";
    echo "  - theme_preference: " . ($user->profile->theme_preference ?? 'null') . "\n";
    echo "  - font_size_preference: " . ($user->profile->font_size_preference ?? 'null') . "\n";
    echo "  - avatar_media_id: " . ($user->profile->avatar_media_id ?? 'null') . "\n";
}

echo "\nBookmarks: " . $user->bookmarks()->count() . "\n";
echo "Reviews: " . $user->reviews()->count() . "\n";
echo "Feedback: " . $user->feedbacks()->count() . "\n";
echo "Ratings: " . $user->ratings()->count() . "\n";
echo "Favorites: " . $user->favoriteCategories()->count() . "\n";
echo "Submissions: " . $user->submittedContents()->count() . "\n";

echo "\n--- Testing public pages with member interactions ---\n";

$routes = [
    'public.dashboard' => null,
    'public.content' => null,
    'public.character' => null,
    'public.merchandise' => null,
    'events.show' => null,
    'public.upcoming-release' => null,
];

// Find first content
$content = App\Models\Content::visibleToPublic()->first();
if ($content) $routes['public.content'] = $content->slug;
$char = App\Models\CharacterProfile::first();
if ($char) $routes['public.character'] = $char->slug;
$merch = App\Models\MerchandiseItem::first();
if ($merch) $routes['public.merchandise'] = $merch->slug;
$event = App\Models\Event::published()->first();
if ($event) $routes['events.show'] = $event->slug;
$release = App\Models\UpcomingRelease::published()->first();
if ($release) $routes['public.upcoming-release'] = $release->slug;

foreach ($routes as $name => $param) {
    try {
        $url = $param ? route($name, $param) : route($name);
        $request = Illuminate\Http\Request::create($url, 'GET');
        $response = $httpKernel->handle($request);
        $code = $response->getStatusCode();
        echo sprintf("[%s] %s %s -> %d\n", $code === 200 ? 'OK' : 'ERR', str_pad($name, 25), $url, $code);
        $httpKernel->terminate($request, $response);
    } catch (\Throwable $e) {
        echo sprintf("[EXCEPTION] %s -> %s\n", $name, $e->getMessage());
    }
}
