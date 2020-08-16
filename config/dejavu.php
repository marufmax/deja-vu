<?php
/**
 * Configuration for Laravel DejaVu package
 * currently it is only PHP Redis by default.
 */

/**
 * @param string $key
 * @return array
 */
function getRedisServers(string $key): array {
    return array_map('trim', explode(',', env($key)));
}

$sentinelsIp = getRedisServers('REDIS_SENTINELS_IPS');
$sentinelsPort = getRedisServers('REDIS_SENTINELS_PORTS');
$sentinelsTimeout = getRedisServers('REDIS_SENTINELS_TIMEOUTS');

$sentinels = [];

foreach ($sentinelsIp as $key => $ip) {
    $sentinel['host'] = $ip;
    $sentinel['port'] = isset($sentinelsPort[$key]) ? (int)$sentinelsPort[$key] : 26379;
    $sentinel['timeout'] = isset($sentinelsTimeout[$key]) ? (float) $sentinelsTimeout[$key] : 1.0;

    $sentinels[] = $sentinel;
}


return [
    'redis_auth' => env('REDIS_PASS', ''),
    'sentinels' => $sentinels,
];