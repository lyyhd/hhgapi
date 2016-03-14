<?php

namespace App\Storage;

use Illuminate\Support\Facades\Cache;
use Toplan\Sms\Storage;

class RedisStorage implements Storage
{
    public function set($key, $value)
    {
        Cache::add($key,$value,5);
    }

    public function get($key, $default)
    {
        return Cache::get($key, $default);
    }

    public function forget($key)
    {
        Cache::forget($key);
    }
}
