<?php
$pdo = new PDO('sqlite:F:/Devfihter/FanHub-Plus/database/database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== merchandise_items schema ===\n";
foreach ($pdo->query("PRAGMA table_info(merchandise_items)") as $r) {
    $nn = $r['notnull'] ? 'NOT NULL' : 'NULL';
    echo "  {$r['name']} ({$r['type']}) $nn\n";
}
echo "\nMerch FKs:\n";
foreach ($pdo->query("PRAGMA foreign_key_list(merchandise_items)") as $r) {
    echo "  {$r['from']} -> {$r['table']}({$r['to']})\n";
}
echo "\n=== events schema ===\n";
foreach ($pdo->query("PRAGMA table_info(events)") as $r) {
    $nn = $r['notnull'] ? 'NOT NULL' : 'NULL';
    echo "  {$r['name']} ({$r['type']}) $nn\n";
}
echo "\n=== Data preservation ===\n";
foreach (['categories','contents','character_profiles','merchandise_items','events','character_contents'] as $t) {
    echo "  $t: " . $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn() . " rows\n";
}
echo "\nMerchandise content/character mapping:\n";
foreach ($pdo->query("SELECT id, category_id, content_id, character_id, name FROM merchandise_items") as $r) {
    echo "  id={$r['id']} cat={$r['category_id']} content={$r['content_id']} char={$r['character_id']} name={$r['name']}\n";
}
echo "\nEvents content mapping (should all be NULL since existing events were general):\n";
foreach ($pdo->query("SELECT id, category_id, content_id, title FROM events") as $r) {
    echo "  id={$r['id']} cat={$r['category_id']} content=" . ($r['content_id'] === null ? 'NULL' : $r['content_id']) . " title={$r['title']}\n";
}