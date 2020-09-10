<?php

namespace App\Classes;

class Cache
{
    public static function remember(string $key, int $ttl, $callback)
    {
        $hashedKey = md5($key);
        $data = file_get_contents("cache/{$hashedKey}.ch");
        if ($data) {
            $data = json_decode($data, true);
        } else {
            $data = $callback();
            file_put_contents("cache/{$hashedKey}.ch", json_encode($data));
        }
        return $data;
    }
}
