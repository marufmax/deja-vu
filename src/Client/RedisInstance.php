<?php

namespace MarufMax\DejaVu\Client;

use MarufMax\DejaVu\Contracts\RedisInterface;
use MarufMax\DejaVu\Exceptions\RedisException;

abstract class RedisInstance implements RedisInterface
{
    protected const PERSISTENT_ID  = 'eagle';
    protected const TIMEOUT = 2.5;
    protected const IDLE_TIMEOUT = 10;

    private $ip;
    private $port;
    private $name;
    protected $redisArr;

    /**
     * Array of redis info, it can be either Master or Slave
     *
     * @param array $redis either master|slave
     * @throws EagleRedisException
     * @throws RedisException
     */
    public function __construct(array $redis)
    {
        if (!\is_array($redis) || empty($redis)) {
            throw new RedisException(__CLASS__ . 'is not reachable');
        }
        $this->redisArr($redis);
    }

    /**
     * Connecting with master redis instance
     *
     * @return \Redis
     */
    public function connect() : \Redis
    {
        $redis = new \Redis();
        $persistentId = self::PERSISTENT_ID . '_' . $this->getIp();

        $redis->pconnect(
            $this->getIp(),
            $this->getPort(),
            self::TIMEOUT,
            $persistentId
        );

        $redis->auth(getenv('REDIS_PASS'));
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, 60);
        $redis->config('SET', 'timeout', '10');

        return $redis;
    }

    private function redisArr(array $redis)
    {
        $randomRedis = $this->random($redis);

        $ip = $randomRedis['ip'] ?? null;
        $port = $randomRedis['port'] ?? null;

        if ($ip === null || $port === null) {
            throw new RedisException(__CLASS__ . ' ip or port is missing');
        }

        $this->ip = $randomRedis['ip'];
        $this->port = $randomRedis['port'];
        $this->name  = $randomRedis['name'];
        $this->redisArr = $randomRedis;

        return $randomRedis;
    }

    /**
     * Get random redis array
     *
     * @param  array $redis
     * @return array
     */
    public function random(array $redis): array
    {
        return $redis[array_rand($redis)];
    }

    /**
     * Get IP of instance
     *
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * Get Port no of instance
     *
     * @return string
     */
    public function getPort() : string
    {
        return $this->port;
    }

    /**
     * Get name of instance
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}