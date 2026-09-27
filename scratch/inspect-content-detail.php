<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\Content::visibleToPublic()->withCount(['characters', 'merchandiseItems', 'media'])->get() as $content) {
    echo json_encode(['slug'=>$content->slug,'characters'=>$content->characters_count,'merchandise'=>$content->merchandise_items_count,'media'=>$content->media_count]).PHP_EOL;
}
