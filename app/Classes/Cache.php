<?php

namespace App\Classes;

class Cache
{
    public static function remember(string $key, int $ttl, $callback)
    {
        $hashedKey = md5($key);
        if (file_exists("cache/{$hashedKey} .ch") && filectime("cache/{$hashedKey}.ch") + $ttl > time()) {
            $data = file_get_contents("cache/{$hashedKey}.ch");
            $data = json_decode($data, true);
        } else {
            $data = $callback();
            file_put_contents("cache/{$hashedKey}.ch", json_encode($data));
        }
        return $data;
    }
}
