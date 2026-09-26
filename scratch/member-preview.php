<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.sqlite.database' => __DIR__.'/member-preview.sqlite']);
Illuminate\Support\Facades\DB::purge('sqlite');
$user = App\Models\User::firstOrCreate(['email' => 'member-preview@example.test'], ['name' => 'Hassan', 'password' => Illuminate\Support\Facades\Hash::make('Preview-Only-2026!')]);
$user->assignRole('registered-user');
$user->profile()->updateOrCreate([], ['display_name' => 'Hassan', 'theme_preference' => 'dark']);
$user->favoriteCategories()->sync(App\Models\Category::limit(6)->pluck('id'));
foreach(App\Models\Content::visibleToPublic()->with('category')->limit(4)->get() as $content) {
$user->bookmarks()->firstOrCreate(['bookmarkable_type' => App\Models\Content::class, 'bookmarkable_id' => $content->id]);
App\Models\ActivityLog::firstOrCreate(['user_id' => $user->id, 'event' => 'member.viewed', 'auditable_type' => App\Models\Content::class, 'auditable_id' => $content->id], ['new_values' => ['label' => $content->title]]);
}
echo 'Isolated member preview ready. Content: '.App\Models\Content::visibleToPublic()->count().PHP_EOL;
