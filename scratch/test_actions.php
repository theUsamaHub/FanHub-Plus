<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::whereHas('roles', function ($q) { $q->where('slug', 'registered-user'); })->first();
auth()->login($user);
echo "Logged in as: " . $user->name . "\n\n";

// Test sending feedback
echo "=== Test 1: Send feedback ===\n";
$request = Illuminate\Http\Request::create(route('user.feedback.store'), 'POST', [
    'type' => 'suggestion',
    'message' => 'This is a test feedback message that is long enough.',
]);
$request->setLaravelSession($app->make('session.store'));
$response = $httpKernel->handle($request);
echo "POST user.feedback.store: " . $response->getStatusCode() . "\n";
$httpKernel->terminate($request, $response);

// Test toggling favorite category
echo "\n=== Test 2: Toggle favorite category ===\n";
$category = App\Models\Category::first();
$request = Illuminate\Http\Request::create(route('user.favorites.store', $category), 'POST', [
    'saved' => 1,
]);
$request->setLaravelSession($app->make('session.store'));
$response = $httpKernel->handle($request);
echo "POST user.favorites.store: " . $response->getStatusCode() . "\n";
$httpKernel->terminate($request, $response);

// Test preference update
echo "\n=== Test 3: Update preferences ===\n";
$request = Illuminate\Http\Request::create(route('user.preferences'), 'PATCH', [
    'theme_preference' => 'dark',
]);
$request->setLaravelSession($app->make('session.store'));
$response = $httpKernel->handle($request);
echo "PATCH user.preferences: " . $response->getStatusCode() . "\n";
$httpKernel->terminate($request, $response);

// Test bookmarking content
echo "\n=== Test 4: Bookmark content ===\n";
$content = App\Models\Content::visibleToPublic()->first();
if ($content) {
    $request = Illuminate\Http\Request::create(route('user.bookmark', ['content', $content->id]), 'POST', [
        'saved' => 1,
    ]);
    $request->setLaravelSession($app->make('session.store'));
    $response = $httpKernel->handle($request);
    echo "POST user.bookmark content: " . $response->getStatusCode() . "\n";
    $httpKernel->terminate($request, $response);
}

// Test rating content
echo "\n=== Test 5: Rate content ===\n";
if ($content) {
    $request = Illuminate\Http\Request::create(route('user.rating', ['content', $content->id]), 'POST', [
        'stars' => 5,
    ]);
    $request->setLaravelSession($app->make('session.store'));
    $response = $httpKernel->handle($request);
    echo "POST user.rating content: " . $response->getStatusCode() . "\n";
    $httpKernel->terminate($request, $response);
}

// Test marking as watched (only for video/audio)
echo "\n=== Test 6: Mark as watched ===\n";
$videoContent = App\Models\Content::visibleToPublic()->whereIn('type', ['video', 'audio'])->first();
if ($videoContent) {
    $request = Illuminate\Http\Request::create(route('user.watched', $videoContent), 'POST', [
        'watched' => 1,
    ]);
    $request->setLaravelSession($app->make('session.store'));
    $response = $httpKernel->handle($request);
    echo "POST user.watched: " . $response->getStatusCode() . "\n";
    $httpKernel->terminate($request, $response);
} else {
    echo "No video/audio content available\n";
}

echo "\n=== Summary ===\n";
echo "Bookmarks: " . $user->bookmarks()->count() . "\n";
echo "Ratings: " . $user->ratings()->count() . "\n";
echo "Favorites: " . $user->favoriteCategories()->count() . "\n";
echo "Feedback: " . $user->feedbacks()->count() . "\n";
echo "Activity: " . $user->id ? App\Models\ActivityLog::where('user_id', $user->id)->count() : 0 . "\n";
