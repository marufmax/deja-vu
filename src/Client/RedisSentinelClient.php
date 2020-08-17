<?php

namespace MarufMax\DejaVu\Client;

use Illuminate\Support\Facades\Log;
use MarufMax\DejaVu\Exceptions\RedisException;

class RedisSentinelClient
{
    private static $sentinel = null;

    private function __construct()
    {
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize a singleton.');
    }

    public static function getInstance(): ?\RedisSentinel
    {
        if (self::$sentinel !== null) {
            return self::$sentinel;
        }

        // Resolve dependency
        $isRedisExists = class_exists('\Redis') && class_exists('\RedisSentinel');
        if (!$isRedisExists) {
            throw new RedisException('phpredis not found. Please install phpredis');
        }

        $sentinels = self::getSentinels();

        if ($sentinels === null) {
            throw new RedisException('No sentinels found. Did you publish the config file?');
        }

        foreach ($sentinels as $sentinel) {
            if (self::$sentinel !== null) {
                return self::$sentinel;
            }

            $redisSentinel = new \RedisSentinel(
                $sentinel['host'],
                $sentinel['port'],
                $sentinel['timeout'],
                'sentinel',
                3
            );

            try {
                $redisSentinel->ping();
                return self::$sentinel = $redisSentinel;
            } catch (\Throwable $exception) {
                $logMessage['ip'] = $sentinel['host'] ?? '';
                $logMessage['port'] = $sentinel['port'] ?? '';
                $logMessage['message'] = $exception->getMessage();

                throw new RedisException($logMessage['ip'] . ":".$logMessage['port'] . ' Redis Sentinel error. ' . $logMessage['message']);

                continue;
            }
        }

        throw new RedisException('None of redis sentinels are alive');
    }

    protected static function getSentinels() : array
    {
        $sentinelsIp = self::getRedisServers('dejavu.sentinels_ips');
        $sentinelsPort = self::getRedisServers('dejavu.sentinels_ports');
        $sentinelsTimeout = self::getRedisServers('dejavu.sentinels_timeouts');

        $sentinels = [];

        foreach ($sentinelsIp as $key => $ip) {
            $sentinel['host'] = $ip;
            $sentinel['port'] = isset($sentinelsPort[$key]) ? (int)$sentinelsPort[$key] : 26379;
            $sentinel['timeout'] = isset($sentinelsTimeout[$key]) ? (float) $sentinelsTimeout[$key] : 1.0;

            $sentinels[] = $sentinel;
        }

        return $sentinels;
    }

    /**
     * @param string $key
     * @return array
     */
    protected static function getRedisServers(string $key)
    {
        return array_map('trim', explode(',', config($key)));
    }
}