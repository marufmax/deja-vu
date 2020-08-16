<?php

namespace MarufMax\DejaVu;

use Exception;
use MarufMax\DejaVu\Client\RedisClient;
use MarufMax\DejaVu\Contracts\CacheInterface;

class RedisCache implements CacheInterface
{
    /**
     * A string that should be prepended to keys
     *
     * @var string
     */
    protected $prefix;

    public function __construct(string $prefix = '')
    {
        $this->setPrefix($prefix);
    }

    /**
     * @return \Redis
     * @throws Exception
     */
    public function master(): \Redis
    {
        return RedisClient::getMasterInstance();
    }

    /**
     * @return \Redis
     * @throws Exception
     */
    public function slave(): \Redis
    {
        return RedisClient::getSlaveInstance();
    }

    /**
     * Retrieve an item from the redis
     *
     * @param $key
     * @return mixed|null
     * @throws Exception
     */
    public function get($key)
    {
        $value = $this->slave()->get($this->prefix.$key);

        return ! is_null($value) ? $this->unserialize($value) : null;
    }

    /**
     * Retrieve multiple items from the redis
     *
     * @param array $keys
     * @return array
     * @throws Exception
     */
    public function many(array $keys): array
    {
        $results = [];

        $values = $this->slave()->mget(array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys));

        foreach ($values as $index => $value) {
            $results[$keys[$index]] =! is_null($value) ? $this->unserialize($value) : null;
        }

        return $results;
    }

    /**
     * Store an item in the redis for given number of seconds
     *
     * @param $key
     * @param $value
     * @param null $seconds
     * @return bool
     * @throws Exception
     */
    public function put($key, $value, $seconds = null): bool
    {
        return (bool) $this->master()->setex(
            $this->prefix.$key,
            (int) max(1, $seconds),
            $this->serialize($value)
        );
    }

    /**
     * Store multiple items in the redis for a given number of seconds.
     *
     * @param array $values
     * @param int $seconds
     * @return bool
     * @throws Exception
     */
    public function putMany(array $values, $seconds): bool
    {
        $this->master()->multi();

        $manyResult = null;

        foreach ($values as $key => $value) {
            $result = $this->put($key, $value, $seconds);

            $manyResult = is_null($manyResult) ? $result : $result && $manyResult;
        }

        $this->master()->exec();

        return $manyResult ?: false;
    }

    /**
     * Setting a value in redis if there is no value found store the result
     *
     * @param $key
     * @param null $seconds
     * @param callable $callback
     * @return mixed|null
     * @throws Exception
     */
    public function remember($key, $seconds = null, callable $callback)
    {
        if (($value = $this->get($key)) !== null && ($value = $this->get($key)) !== false) {
            return $value;
        }

        $this->put($key, $value = $callback(), $seconds);

        return $value;
    }

    /**
     * Increment the value of an item in the redis.
     *
     * @param string $key
     * @param mixed $value
     * @return int
     * @throws Exception
     */
    public function increment($key, $value = 1): int
    {
        return $this->master()->incrby($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in redis.
     *
     * @param string $key
     * @param mixed $value
     * @return int
     * @throws Exception
     */
    public function decrement($key, $value = 1): int
    {
        return $this->master()->decrby($this->prefix.$key, $value);
    }

    /**
     * Store an item in the redis indefinitely.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws Exception
     */
    public function forever($key, $value)
    {
        return (bool) $this->master()->set($this->prefix.$key, $this->serialize($value));
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public function forget($key)
    {
        return (bool) $this->master()->del($this->prefix.$key);
    }

    /**
     * Remove all items from redis.
     *
     * @return bool
     */
//    public function flush()
//    {
//        $this->master()->flushDB();
//
//        return true;
//    }

    /**
     * Serialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function serialize($value)
    {
        return is_numeric($value) && ! in_array($value, [INF, -INF]) && ! is_nan($value) ? $value : serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Determine if a key exists
     *
     * @param string $key
     * @return bool|int
     * @throws Exception
     */
    public function exists(string $key)
    {
        return $this->slave()->exists($key);
    }

    /**
     * Set the cache key prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
    }
}
