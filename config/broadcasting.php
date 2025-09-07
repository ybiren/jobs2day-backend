<?php

return [

    'default' => env('BROADCAST_DRIVER', 'redis'),

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',  // Make sure this matches your Redis connection settings
        ],

        'log' => [
            'driver' => 'log',
            'channel' => env('BROADCAST_CHANNEL', 'default'),
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
