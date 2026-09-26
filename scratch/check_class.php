<?php
require __DIR__ . '/../vendor/autoload.php';

$class = 'App\\Http\\Controllers\\Admin\\RelationLookupController';
echo "class_exists? " . (class_exists($class) ? 'YES' : 'NO') . "\n";
echo "file_exists? " . (file_exists('F:/Devfihter/FanHub-Plus/app/Http/Controllers/Admin/RelationLookupController.php') ? 'YES' : 'NO') . "\n";
echo "file_exists worktree? " . (file_exists('F:/Devfihter/FanHub-Plus/.worktrees/feat-auto-20260926-474f4a56/app/Http/Controllers/Admin/RelationLookupController.php') ? 'YES' : 'NO') . "\n";