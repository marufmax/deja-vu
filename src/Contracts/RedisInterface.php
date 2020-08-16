<?php


namespace MarufMax\DejaVu\Contracts;


interface RedisInterface
{
    /**
     * Connecting to a redis instance
     *
     * @return \Redis
     */
    public function connect() : \Redis;

    /**
     * Get ip of instance
     *
     * @return string
     */
    public function getIp() : string;

    /**
     * Get name of instance
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get which port is running
     *
     * @return string
     */
    public function getPort(): string;
}
