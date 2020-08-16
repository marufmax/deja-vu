<?php

namespace MarufMax\DejaVu\Exceptions;

use Eagle\src\Logger\RedisLogger;

class RedisException extends \Exception
{
    public function __construct($message = "Redis not found", $code = 424, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
       // $logger = RedisLogger::getInstance();

        //   $this->log($logger);
    }
}
