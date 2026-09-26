<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Users: " . App\Models\User::count() . PHP_EOL;
echo "Registered users: " . App\Models\User::whereHas('roles', function ($q) { $q->where('slug', 'registered-user'); })->count() . PHP_EOL;
echo "Admins: " . App\Models\User::whereHas('roles', function ($q) { $q->where('slug', 'admin'); })->count() . PHP_EOL;
echo "Contents: " . App\Models\Content::count() . PHP_EOL;
echo "Categories: " . App\Models\Category::count() . PHP_EOL;
echo "Bookmarks: " . App\Models\Bookmark::count() . PHP_EOL;
echo "Reviews: " . App\Models\Review::count() . PHP_EOL;
echo "Feedback: " . App\Models\Feedback::count() . PHP_EOL;
echo "Ratings: " . App\Models\Rating::count() . PHP_EOL;
echo "Favorites: " . App\Models\UserFavoriteCategory::count() . PHP_EOL;
echo "ActivityLogs: " . App\Models\ActivityLog::count() . PHP_EOL;
echo "Submissions: " . App\Models\Content::where('is_user_submitted', true)->count() . PHP_EOL;
echo "Media: " . App\Models\Media::count() . PHP_EOL;
