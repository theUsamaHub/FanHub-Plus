<?php
require __DIR__ . '/../vendor/autoload.php';

$class = 'App\\Http\\Controllers\\Admin\\RelationLookupController';
echo "class_exists? " . (class_exists($class) ? 'YES' : 'NO') . "\n";

if (! class_exists($class)) {
    $found = false;
    foreach (spl_autoload_functions() as $f) {
        if (is_array($f) && $f[0] instanceof \Composer\Autoload\ClassLoader) {
            $loader = $f[0];
            $found = $loader->findFile($class);
            if ($found) {
                echo "Found in: $found\n";
                break;
            }
        }
    }
    if (! $found) echo "Not found by any autoloader\n";
}