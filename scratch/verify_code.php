<?php
// Standalone verification: parse every modified PHP file and assert
// the new fields/relations/routes are syntactically valid + present.

$base = 'F:/Devfihter/FanHub-Plus/.worktrees/feat-auto-20260926-474f4a56';

$checks = [
    // Files
    'app/Models/MerchandiseItem.php' => ["content_id", "character_id", "function content()", "function character()"],
    'app/Models/Event.php' => ["content_id", "function content()"],
    'app/Models/Content.php' => ["function merchandiseItems()", "function events()", "function characters()"],
    'app/Models/CharacterProfile.php' => ["function merchandiseItems()"],
    'app/Http/Requests/CharacterRequest.php' => ["'required'", "'min:1'", "Cross-category validation"],
    'app/Http/Requests/MerchandiseRequest.php' => ["'content_id' => ['required'", "'character_id'", "characterContents"],
    'app/Http/Requests/EventRequest.php' => ["'content_id' => ['nullable'", "category"],
    'app/Http/Controllers/Admin/RelationLookupController.php' => ["contentsByCategory", "charactersByContent", "JsonResponse"],
    'app/Http/Controllers/Admin/MerchandiseController.php' => ["'content_id'", "'character_id'", "relationPayload"],
    'app/Http/Controllers/Admin/EventController.php' => ["'content_id'", "relationPayload"],
    'app/Http/Controllers/Admin/CharacterController.php' => ["DB::transaction", "'content_ids'"],
    'app/Http/Controllers/PublicSiteController.php' => ["characters", "merchandiseItems", "events"],
    'app/Http/Controllers/EventController.php' => ["content"],
    'database/migrations/2026_09_26_205000_add_content_id_to_events_table.php' => ["content_id", "constrained('contents')", "nullOnDelete"],
    'database/migrations/2026_09_26_205001_add_content_and_character_to_merchandise_items.php' => ["content_id", "character_id", "constrained('contents')", "constrained('character_profiles')"],
    'database/migrations/2026_09_26_205002_enforce_merchandise_items_content_required.php' => ["NOT NULL", "drop_constrained_foreign"],
    'routes/admin.php' => ["lookups.contents-by-category", "lookups.characters-by-content"],
    'resources/views/admin/characters/partials/form.blade.php' => ["data-related-content-list", "data-lookup-url-template"],
    'resources/views/admin/merchandise/partials/form.blade.php' => ["data-merch-content-select", "data-merch-character-select", "data-lookup-url-template"],
    'resources/views/admin/events/partials/form.blade.php' => ["data-event-content-select", "data-lookup-url-template", "'General category event / No specific content'"],
];

$failed = 0;
foreach ($checks as $relPath => $needles) {
    $path = $base . '/' . $relPath;
    if (!file_exists($path)) {
        echo "MISSING FILE: $relPath\n";
        $failed++;
        continue;
    }
    $content = file_get_contents($path);
    foreach ($needles as $needle) {
        if (strpos($content, $needle) === false) {
            echo "MISSING '$needle' in $relPath\n";
            $failed++;
        }
    }
}

echo $failed === 0 ? "\nAll checks passed.\n" : "\n$failed checks failed.\n";

// PHP syntax check on every modified PHP file
$phpFiles = array_map(fn($r) => $baseDir = $base . '/' . $r, array_keys(array_filter($checks, fn($k) => str_ends_with($k, '.php') && !str_contains($k, 'migrations/'), ARRAY_FILTER_USE_KEY)));
$phpFiles = array_merge($phpFiles, [
    $base . '/database/migrations/2026_09_26_205000_add_content_id_to_events_table.php',
    $base . '/database/migrations/2026_09_26_205001_add_content_and_character_to_merchandise_items.php',
    $base . '/database/migrations/2026_09_26_205002_enforce_merchandise_items_content_required.php',
]);

echo "\n--- PHP syntax lint ---\n";
foreach ($phpFiles as $f) {
    if (!file_exists($f)) continue;
    $output = [];
    exec("php -l " . escapeshellarg($f) . " 2>&1", $output);
    $line = implode("\n", $output);
    if (strpos($line, 'No syntax errors') === false) {
        echo "FAIL " . basename($f) . ":\n  " . $line . "\n";
        $failed++;
    } else {
        echo "OK   " . basename($f) . "\n";
    }
}

exit($failed === 0 ? 0 : 1);