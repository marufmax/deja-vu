<?php
/**
 * Configuration for Laravel DejaVu package
 * currently it is only PHP Redis by default.
 */

/**
 * @param string $key
 * @return array
 */

$get = static function(string $key): array {
    return array_map('trim', explode(',', env($key)));
};

$sentinelsIp = $get('REDIS_SENTINELS_IPS');
$sentinelsPort = $get('REDIS_SENTINELS_PORTS');
$sentinelsTimeout = $get('REDIS_SENTINELS_TIMEOUTS');

$sentinels = [];

foreach ($sentinelsIp as $key => $ip) {
    $sentinel['host'] = $ip;
    $sentinel['port'] = isset($sentinelsPort[$key]) ? (int)$sentinelsPort[$key] : 26379;
    $sentinel['timeout'] = isset($sentinelsTimeout[$key]) ? (float) $sentinelsTimeout[$key] : 1.0;

    $sentinels[] = $sentinel;
}


return [
  'sentinels' => $sentinels
];