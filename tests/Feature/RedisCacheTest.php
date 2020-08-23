<?php

namespace MarufMax\DejaVu\Test\Feature;

use MarufMax\DejaVu\RedisCache;
use MarufMax\DejaVu\Test\TestCase;

class RedisCacheTest extends TestCase
{
    /** @test */
    public function it_can_set_an_item_to_redis()
    {
        $cache = new RedisCache();
        $this->assertTrue($cache->put('test', 'hrllo', 36));

    }
}
