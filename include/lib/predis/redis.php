<?php
// Uset redis Hashes functions

require_once(__DIR__ . '/autoload.php');

$redis = new \Predis\Client();
try {
    $redis->connect();
} catch (Predis\Connection\ConnectionException $e) {
    $redis_enabled = false;
}


function redisGet($key, $diff_time = 120)
{
    global $redis, $redis_enabled;

    if (!$redis_enabled) {
        return false;
    }

    $current_time = time();

    $is_exits = $redis->exists($key);

    if (empty($is_exits)) {
        return false;
    }

    $last_update_str = $redis->hget($key, 'time');

    if (empty($last_update_str)) {
        return false;
    }

    $last_update = strtotime($last_update_str);

    if (($current_time - $last_update) < $diff_time) {
        return false;
    }

    $value = $redis->hget($key, 'value');

    if (empty($value)) {
        return false;
    }

    $value = json_decode(gzuncompress($value), true);

    return $value;
}

function redisSet($key, $value)
{
    global $redis, $redis_enabled;
    if (!$redis_enabled) {
        return false;
    }
    $redis->hset($key, 'time', time());
    $res = $redis->hset($key, 'value', gzcompress(json_encode($value, JSON_UNESCAPED_UNICODE)));
    return $res;
}

function redisDel($key)
{
    global $redis, $redis_enabled;

    if (!$redis_enabled) {
        return false;
    }

    if ($redis->exists($key)) {
        $redis->del($key);
    }
    return true;
}


function redisDelKeys($keys)
{
    global $redis, $redis_enabled;

    if (!$redis_enabled) {
        return false;
    }

    if (!empty($keys)) {
        foreach ($keys as $key) {
            if ($redis->exists($key)) {
                $redis->del($key);
            }
        }
    }

    return true;
}


function redisDelAll()
{
    global $redis, $redis_enabled;

    if (!$redis_enabled) {
        return false;
    }

    return $redis->flushdb();
}


function redisDelKey($key)
{
    global $redis, $redis_enabled;

    if (!$redis_enabled) {
        return false;
    }

    return $redis->del($key);
}

function redisDelKeyPattern($pattern)
{
    global $redis, $redis_enabled;

    if (!$redis_enabled) {
        return false;
    }

    $keys = $redis->keys($pattern);
    return redisDelKeys($keys);
}


?>