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

        $sentinels = config('dejavu.sentinels');

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
}