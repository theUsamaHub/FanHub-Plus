<?php
$pdo = new PDO('sqlite:F:/Devfihter/FanHub-Plus/database/database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = ['categories','contents','character_profiles','character_contents','merchandise_items','events'];
foreach ($tables as $t) {
    try {
        $cols = $pdo->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_COLUMN, 1);
        $cnt = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo str_pad($t, 24) . " rows=$cnt\n";
        foreach ($cols as $c) echo "    $c\n";
    } catch (Exception $e) {
        echo "$t MISSING (" . $e->getMessage() . ")\n";
    }
}

echo "\n--- Sample data ---\n";
echo "\ncategories:\n";
foreach ($pdo->query("SELECT id, name, slug FROM categories LIMIT 5") as $r) {
    echo "  id={$r['id']} name={$r['name']} slug={$r['slug']}\n";
}
echo "\ncontents:\n";
foreach ($pdo->query("SELECT id, category_id, title, status FROM contents LIMIT 5") as $r) {
    echo "  id={$r['id']} cat={$r['category_id']} title={$r['title']} status={$r['status']}\n";
}
echo "\ncharacter_profiles:\n";
foreach ($pdo->query("SELECT id, category_id, name FROM character_profiles LIMIT 10") as $r) {
    echo "  id={$r['id']} cat={$r['category_id']} name={$r['name']}\n";
}
echo "\ncharacter_contents:\n";
foreach ($pdo->query("SELECT character_id, content_id FROM character_contents LIMIT 20") as $r) {
    echo "  char={$r['character_id']} content={$r['content_id']}\n";
}
echo "\nmerchandise_items:\n";
foreach ($pdo->query("SELECT id, category_id, name FROM merchandise_items LIMIT 10") as $r) {
    echo "  id={$r['id']} cat={$r['category_id']} name={$r['name']}\n";
}
echo "\nevents:\n";
foreach ($pdo->query("SELECT id, category_id, title FROM events LIMIT 10") as $r) {
    echo "  id={$r['id']} cat={$r['category_id']} title={$r['title']}\n";
}