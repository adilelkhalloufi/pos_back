<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Store;
use Illuminate\Support\Facades\Session;

class StoreMiddleware
{
    /**
     * Handle an incoming request and resolve the current store.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = auth()->user();
        $store = null;

        // Try to get store ID from multiple sources
        $storeId = Session::get('current_store_id') 
                   ?? $request->header('X-Store-Id') 
                   ?? $request->input('store_id')
                   ?? $request->route('store_id');

        // If store ID is provided, validate user has access
        if ($storeId) {
            $store = Store::find($storeId);
            
            if ($store && $authUser) {
                $isOwner = $store->owner_id === $authUser->id;
                $isWorker = $authUser->workingStores()->where(Store::TABLE_NAME . '.id', $storeId)->exists();
                
                // User must own or work at the store
                if (!$isOwner && !$isWorker) {
                    $store = null;
                }
            }
        }

        // Fallback: get first available store for authenticated user
        if (!$store && $authUser) {
            $store = $authUser->workingStores()->first() ?? $authUser->stores()->first();
        }

        // Attach store to request for easy access throughout the app
        $request->attributes->set('current_store', $store);
        
        // Bind to app container for dependency injection
        app()->instance('currentStore', $store);
        app()->instance(Store::class, $store);

        return $next($request);
    }
}
