<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use Illuminate\Support\Str;

$events = Event::all();
$count = 0;

foreach ($events as $event) {
    if (empty($event->slug)) {
        $base = Str::slug($event->title) ?: 'event';
        $slug = $base;
        for ($s = 2; Event::where('slug', $slug)->where('id', '!=', $event->id)->exists(); $s++) {
            $slug = $base . '-' . $s;
        }
        $event->slug = $slug;
        $event->save();
        $count++;
    }
}

echo "Successfully updated {$count} event slugs in the database.\n";
