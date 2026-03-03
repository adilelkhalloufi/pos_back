<?php

use App\Models\Store;

if (!function_exists('currentStore')) {
    /**
     * Get the current store from the request.
     *
     * @return Store|null
     */
    function currentStore(): ?Store
    {
        return request()->attributes->get('current_store');
    }
}

if (!function_exists('currentStoreId')) {
    /**
     * Get the current store ID.
     *
     * @return int|null
     */
    function currentStoreId(): ?int
    {
        return currentStore()?->id;
    }
}
