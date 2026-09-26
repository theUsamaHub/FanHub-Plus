<?php
$pdo = new PDO('sqlite:F:/Devfihter/FanHub-Plus/database/database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "All categories:\n";
foreach ($pdo->query("SELECT id, name, slug FROM categories ORDER BY id") as $r) {
    echo "  id={$r['id']} name={$r['name']}\n";
}
echo "\nAll contents (id, category, title):\n";
foreach ($pdo->query("SELECT id, category_id, title FROM contents ORDER BY category_id, id") as $r) {
    echo "  id={$r['id']} cat={$r['category_id']} title={$r['title']}\n";
}
echo "\nMerchandise by category:\n";
foreach ($pdo->query("SELECT category_id, COUNT(*) as cnt FROM merchandise_items GROUP BY category_id") as $r) {
    echo "  cat={$r['category_id']} count={$r['cnt']}\n";
}
echo "\nEvents by category:\n";
foreach ($pdo->query("SELECT category_id, COUNT(*) as cnt FROM events GROUP BY category_id") as $r) {
    echo "  cat={$r['category_id']} count={$r['cnt']}\n";
}