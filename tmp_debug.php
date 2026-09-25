<?php

use App\Models\ActivityLog;
use App\Services\ActivityLogger;

$log = ActivityLog::where('event', 'created')->latest('id')->first();

if (! $log) {
    echo "NO ROW\n";
    exit;
}

echo '1. changes count: '.count($log->changes)."\n";
echo '2. changes count again: '.count($log->changes)."\n";
echo '3. cache: '.json_encode($log->changeCache)."\n";

$manual = array_filter($log->changes, fn ($c) => $c['type'] !== 'unchanged');
echo '4. manual filter count: '.count($manual)."\n";
echo '5. change_count accessor: '.$log->change_count."\n";
echo '6. types: '.json_encode(array_column($log->changes, 'type'))."\n";
echo '7. summary: ['.$log->summary."]\n";
echo '8. changeCache prop exists: '.var_export(property_exists($log, 'changeCache'), true)."\n";
echo '9. subject: '.var_export($log->subject, true)."\n";
echo '10. event: '.var_export($log->event, true)."\n";
