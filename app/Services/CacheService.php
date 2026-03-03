<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // Your service methods go here
    public function __construct() {}

    /**
     * Store data in cache
     *
     * @param  mixed  $value
     * @param  int  $ttl  (time to live, in minutes)
     * @return bool
     */
    public function storeCache(string $key, $value, int $ttl = 60)
    {
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Retrieve data from cache
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function getCache(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    /**
     * Cache data with callback (like Cache::remember)
     *
     * @param  string  $key
     * @param  \Closure  $callback
     * @param  int  $ttl  (time to live, in seconds)
     * @return mixed
     */
    public function rememberCache(string $key, \Closure $callback, int $ttl = 300)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Forget data in cache
     *
     * @return bool
     */
    public function forgetCache(string $key)
    {
        return Cache::forget($key);
    }

    /**
     * Clear all caches
     *
     * @return void
     */
    public function clearCache()
    {
        Cache::flush();
    }

    /**
     * Check if cache key exists
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCache(string $key)
    {
        return Cache::has($key);
    }

    /**
     * Get multiple cache keys
     *
     * @param  array  $keys
     * @return array
     */
    public function getMultipleCache(array $keys)
    {
        return Cache::getMultiple($keys);
    }

    /**
     * Forget multiple cache keys
     *
     * @param  array  $keys
     * @return void
     */
    public function forgetMultipleCache(array $keys)
    {
        foreach ($keys as $key) {
            $this->forgetCache($key);
        }
    }
}
