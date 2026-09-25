<?php

return [
    'default' => env('BROADCAST_CONNECTION', 'reverb'),

    'connections' => [
        'log' => [
            'driver' => 'log',
        ],
        'reverb' => [
            'driver' => 'reverb',
            'app_id' => env('REVERB_APP_ID'),
            'app_key' => env('REVERB_APP_KEY'),
            'app_secret' => env('REVERB_APP_SECRET'),
            'host' => env('REVERB_HOST', '127.0.0.1'),
            'port' => env('REVERB_PORT', 8080),
            'scheme' => env('REVERB_SCHEME', 'http'),
        ],
    ],
];
