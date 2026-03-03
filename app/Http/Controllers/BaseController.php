<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use App\Services\User\UserService;
use Illuminate\Log\Logger;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Store;

abstract class BaseController extends Controller
{
     public Logger $logger;
     public Response $response;
     protected CacheService $cacheService;


     public function __construct()
    {
        $this->logger = resolve(Logger::class);
        $this->response = resolve(Response::class);
        $this->cacheService = resolve(CacheService::class);
    }

    public function user()
    {
         $userService = make(UserService::class);

        return $userService->findById(auth()->id());
    }

    /**
     * Get the current store from the request.
     * Store is resolved by StoreMiddleware.
     */
    public function the_store(): ?Store
    {
        return request()->attributes->get('current_store');
    }

    /**
     * Get the current store ID.
     */
    public function storeId(): ?int
    {
        return $this->the_store()?->id;
    }

    /**
     * Cache data with store-scoped key
     * Automatically prefixes cache key with store ID for multi-tenant isolation
     *
     * @param string $key Cache key (e.g., 'dashboard', 'products')
     * @param \Closure $callback Function that returns data to cache
     * @param int $ttl Time to live in seconds (default: 5 minutes)
     * @return mixed
     */
    protected function cacheForStore(string $key, \Closure $callback, int $ttl = 300)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            // If no store ID, execute callback directly without caching
            return $callback();
        }

        $cacheKey = "store_{$storeId}_{$key}";

        return $this->cacheService->rememberCache($cacheKey, $callback, $ttl);
    }

    /**
     * Forget (clear) cached data for current store
     *
     * @param string $key Cache key to forget
     * @return bool
     */
    protected function forgetStoreCache(string $key): bool
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return false;
        }

        $cacheKey = "store_{$storeId}_{$key}";

        return $this->cacheService->forgetCache($cacheKey);
    }

    /**
     * Clear multiple cache keys for current store
     *
     * @param array $keys Array of cache keys to forget
     * @return void
     */
    protected function forgetMultipleStoreCache(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forgetStoreCache($key);
        }
    }

    /**
     * Clear all cache for current store (be careful with this!)
     * Uses pattern matching to delete all keys for the store
     *
     * @return void
     */
    protected function clearAllStoreCache(): void
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return;
        }

        // This works with Redis, for file cache it will clear entire cache
        $this->cacheService->clearCache();
    }
}
