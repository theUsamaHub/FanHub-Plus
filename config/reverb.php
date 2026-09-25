<?php

return [
    'default' => env('REVERB_APP_KEY', 'fanhubplus'),

    'apps' => [
        [
            'id' => env('REVERB_APP_ID', 1),
            'name' => env('REVERB_APP_NAME', 'FanHub Plus'),
            'key' => env('REVERB_APP_KEY', 'fanhubplus-key'),
            'secret' => env('REVERB_APP_SECRET', 'fanhubplus-secret'),
            'path' => env('REVERB_APP_PATH', ''),
            'allowed_origins' => [env('REVERB_ALLOWED_ORIGINS', '*')],
            'ping_interval' => env('REVERB_PING_INTERVAL', 60),
            'max_message_size' => env('REVERB_MAX_MESSAGE_SIZE', 10000),
        ],
    ],

    'batching' => [
        'enabled' => env('REVERB_BATCHING', false),
        'delay' => env('REVERB_BATCHING_DELAY', 15),
    ],

    'logging' => [
        'enabled' => env('REVERB_LOGGING', true),
    ],
];
