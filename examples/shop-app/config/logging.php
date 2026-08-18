<?php

return [
    'default' => env('LOG_CHANNEL', 'file'),
    'channels' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('logs/app.log'),
        ],
        'stderr' => [
            'driver' => 'stderr',
        ],
    ],
];
