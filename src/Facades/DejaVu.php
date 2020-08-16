<?php

namespace MarufMax\DejaVu\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Redis master()
 * @method static \Redis slave()
 * @method static bool add(string $key, $value, \DateTimeInterface|\DateInterval|int $ttl = null)
 * @method static bool forever(string $key, $value)
 * @method static bool forget(string $key)
 * @method static bool exists(string $key)
 * @method static bool has(string $key)
 * @method static bool put(string $key, $value, \DateTimeInterface|\DateInterval|int $ttl = null)
 * @method static bool putMany(array $values, \DateTimeInterface|\DateInterval|int $ttl = null)
 * @method static array many(array $keys),
 * @method static int|bool decrement(string $key, $value = 1)
 * @method static int|bool increment(string $key, $value = 1)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static mixed remember(string $key, \DateTimeInterface|\DateInterval|int $ttl, \Closure $callback)
 * @method static string getPrefix()
 * @method static void setPrefix(string $prefix)
 *
 * @see \Illuminate\Cache\CacheManager
 * @see \Illuminate\Cache\Repository
 */
class DejaVu extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DejaVu';
    }
}