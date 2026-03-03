<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Get store ID from request (if applicable)
        $storeId = $request->input('store_id') ?? $request->route('store_id') ?? null;

        // Check if user has any of the required roles
        $hasRole = $user->roles()
            ->when($storeId, function ($query) use ($storeId) {
                $query->where('user_role.store_id', $storeId);
            })
            ->whereIn('name', $roles)
            ->exists();

        if (!$hasRole) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have the required role to perform this action'
            ], 403);
        }

        return $next($request);
    }
}
