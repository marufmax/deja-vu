<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require ('./vendor/autoload.php');

$redis = new \MarufMax\DejaVu\RedisCache();

$redis->put('hi', 'hello', 30);

dd($redis->get('hi'));

echo "Hello";

