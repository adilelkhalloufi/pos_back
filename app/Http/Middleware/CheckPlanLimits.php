<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limitType = null): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->plan) {
            return response()->json([
                'message' => 'No plan assigned to user'
            ], 403);
        }

        $plan = $user->plan;

        switch ($limitType) {
            case 'users':
                $currentUserCount = $user->stores()->withCount('users')->get()->sum('users_count');
                if (!$plan->canCreateUser($currentUserCount)) {
                    return response()->json([
                        'message' => 'User limit reached for your current plan',
                        'current_users' => $currentUserCount,
                        'max_users' => $plan->max_users,
                        'plan_name' => $plan->name
                    ], 403);
                }
                break;

            case 'stores':
                $currentStoreCount = $user->stores()->count();
                if (!$plan->canCreateStore($currentStoreCount)) {
                    return response()->json([
                        'message' => 'Store limit reached for your current plan',
                        'current_stores' => $currentStoreCount,
                        'max_stores' => $plan->max_stores,
                        'plan_name' => $plan->name
                    ], 403);
                }
                break;
        }

        return $next($request);
    }
}
