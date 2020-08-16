<?php

namespace MarufMax\DejaVu\Client;

use MarufMax\DejaVu\Exceptions\RedisException;

class RedisClient
{
    /**
     * Master Instance for witting
     *
     * @var \Redis $redisMaster
     */
    private static $redisMaster;

    /**
     * Slave Instance for reading
     *
     * @var \Redis $redisSlave
     */
    private static $redisSlave;

    private function __construct()
    {
    }

    /**
     * Can't clone singleton
     */
    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize a singleton.');
    }

    /**
     * Getting singleton of redis Master Instance
     *
     * @return \Redis
     * @throws EagleRedisException
     */
    public static function getMasterInstance() : \Redis
    {
        if (self::$redisMaster !== null) {
            return self::$redisMaster;
        }

        $redis = self::getMasterInfo();

        try {
            $connectedMaster = $redis->connect();
            self::$redisMaster = $connectedMaster;

            $masterInfo = self::$redisMaster->info();

            RedisLogger::getInstance()
                ->setRedisStats(
                    self::logRedisStats(
                        $redis->getIp(),
                        $redis->getPort(),
                        $masterInfo
                    )
                );

            return self::$redisMaster;
        } catch (\RedisException $exception) {
            $logMessage['ip'] = $redis->getIp();
            $logMessage['port'] = $redis->getPort();
            $logMessage['message'] = $exception->getMessage();

            RedisLogger::getInstance()
                ->redisErrorConnection($logMessage);
        }

        throw new RedisException('Redis Master node is not alive');
    }

    /**
     * Redis master instance
     *
     * @return RedisMaster
     * @throws EagleRedisException
     */
    private static function getMasterInfo() : RedisMaster
    {
        $sentinel = RedisSentinelClient::getInstance();

        $master = $sentinel->masters();

        return new RedisMaster($master);
    }

    public static function getSlaveInstance() : \Redis
    {
        if (self::$redisMaster !== null) {
            return self::$redisSlave = self::$redisMaster;
        }

        if (self::$redisSlave !== null) {
            return self::$redisSlave;
        }
        $slave = [];
        try {
            $redisMaster = self::getMasterInfo();
            $sentinel = RedisSentinelClient::getInstance();
            $slaves = $sentinel->slaves($redisMaster->getName());


            if (\count($slaves) < 1) {
                return self::$redisSlave = self::getMasterInstance();
            }

            $upSlaves = self::eliminateDownRedis($slaves);

            if (\count($upSlaves) < 1) {
                return self::$redisSlave = self::getMasterInstance();
            }

            $slave = new RedisSlave($slaves);

            $connectedSlave = $slave->connect();

            self::$redisSlave = $connectedSlave;
            \Flight::set('connectedWithRedis', true);
            \Flight::set('redisConnectionTime', time());

            $slaveInfo = self::$redisSlave->info();
            RedisLogger::getInstance()
                ->setRedisStats(
                    self::logRedisStats(
                        $slave->getIp(),
                        $slave->getPort(),
                        $slaveInfo
                    )
                );

            return self::$redisSlave;
        } catch (\RedisException $exception) {
            $logMessage['ip'] = $slave->getIp();
            $logMessage['port'] = $slave->getPort();
            $logMessage['message'] = $exception->getMessage();

            RedisLogger::getInstance()
                ->redisErrorConnection($logMessage);
        }

        throw new EagleRedisException('None of redis slave nodes are alive');
    }

    /**
     * Remove disconnected, subjectively and objectively redis instances
     *
     * @param array $redis list of redis instances
     *
     * @return array list of redis which is not dow
     */
    private static function eliminateDownRedis(array $redis): array
    {
        $upRedis = [];
        foreach ($redis as $server) {
            if (preg_match('/(s_down|o_down|disconnected)/i', $server['flags'])) {
                $logMessage['ip'] = $server['ip'];
                $logMessage['role'] = $server['role-reported'];
                $logMessage['message'] = "Server down: {$server['flags']}";

                RedisLogger::getInstance()->redisErrorConnection($logMessage);
            } else {
                $upRedis[] = $server;
            }
        }
        return $upRedis;
    }

    public static function logRedisStats(string $ip, string $port, array $data): array
    {
        return [
            'role' => $data['role'],
            'ip' => $ip,
            'port' => $port,
            'total_connections_received' => $data['total_connections_received'] ?? 0,
            'connected_clients' => $data['connected_clients'] ?? 0
        ];
    }

    public static function close()
    {
        if (self::$redisMaster !== null) {
            self::$redisMaster->close();
        }

        if (self::$redisSlave !== null) {
            self::$redisSlave->close();
        }

        return true;
    }
}
