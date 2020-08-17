<?php

/**
 * Configuration for Laravel DejaVu package
 * currently it is only PHP Redis by default.
 */
return [
    'redis_auth' => env('REDIS_PASS', null),
    'persistant_id' => env('PERSISTENT_ID', 'dejavu'),
    'sentinels_ips' => env('REDIS_SENTINELS_IPS', '172.16.0.11'),
    'sentinels_ports' => env('REDIS_SENTINELS_PORTS', '26379'),
    'sentinels_timeouts' => env('REDIS_SENTINELS_TIMEOUTS', '26379'),
];