<?php

/**
 * Configuration for Laravel DejaVu package
 * currently it is only PHP Redis by default.
 */
return [
    'password' => env('REDIS_PASSWORD', null),
    'sentinels' => [
        'ips'       => env('REDIS_SENTINELS', '172.16.0.11'),
        'ports'     => env('REDIS_SENTINELS_PORTS', '26379'),
        'timeouts'   => env('REDIS_SENTINELS_TIMEOUTS', '1.0'), // in seconds
    ],
];
