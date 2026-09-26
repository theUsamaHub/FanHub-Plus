<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::whereHas('roles', function ($q) { $q->where('slug', 'registered-user'); })->first();
auth()->login($user);
echo "Logged in as: " . $user->name . "\n\n";

$session = $app->make('session.store');
$session->start();
$token = csrf_token();

$test = function ($method, $name, $params = [], $body = []) use ($httpKernel, $app, $session, $token) {
    $url = is_array($params) && !empty($params) ? route($name, $params) : route($name);
    $request = Illuminate\Http\Request::create($url, $method, $body);
    $request->setLaravelSession($session);
    $request->headers->set('X-CSRF-TOKEN', $token);
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    // Also set the token in body for Laravel to pick up
    $request->merge(['_token' => $token]);
    $response = $httpKernel->handle($request);
    $code = $response->getStatusCode();
    echo sprintf("[%d] %s %s -> %d\n", $code, $method, $name, $code);
    $httpKernel->terminate($request, $response);
    return $code;
};

echo "=== Test 1: Send feedback ===\n";
$test('POST', 'user.feedback.store', [], [
    'type' => 'suggestion', 'message' => 'Test feedback message that is long enough for validation.',
]);

echo "\n=== Test 2: Toggle favorite category ===\n";
$category = App\Models\Category::first();
$test('POST', 'user.favorites.store', ['category' => $category->id], ['saved' => 1]);

echo "\n=== Test 3: Update preferences ===\n";
$test('PATCH', 'user.preferences', [], ['theme_preference' => 'dark']);

echo "\n=== Test 4: Bookmark content ===\n";
$content = App\Models\Content::visibleToPublic()->first();
if ($content) $test('POST', 'user.bookmark', ['type' => 'content', 'id' => $content->id], ['saved' => 1]);

echo "\n=== Test 5: Rate content ===\n";
if ($content) $test('POST', 'user.rating', ['type' => 'content', 'id' => $content->id], ['stars' => 5]);

echo "\n=== Test 6: Mark as watched ===\n";
$video = App\Models\Content::visibleToPublic()->whereIn('type', ['video', 'audio'])->first();
if ($video) $test('POST', 'user.watched', ['content' => $video->id], ['watched' => 1]);

echo "\n=== Summary ===\n";
$user->refresh();
echo "Bookmarks: " . $user->bookmarks()->count() . "\n";
echo "Ratings: " . $user->ratings()->count() . "\n";
echo "Favorites: " . $user->favoriteCategories()->count() . "\n";
echo "Feedback: " . $user->feedbacks()->count() . "\n";
echo "Activity: " . App\Models\ActivityLog::where('user_id', $user->id)->count() . "\n";
