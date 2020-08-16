<?php


namespace MarufMax\DejaVu\Client;


class RedisMaster extends RedisInstance
{
    /**
     * Total number of slaves connected with this master
     *
     * @return mixed
     */
    public function getTotalSlaves()
    {
        return $this->redisArr['num-slaves'];
    }
}