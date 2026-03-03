<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends BaseController
{
    /**
     * Display a listing of active plans
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)
                    ->orderBy('price')
                    ->get();

        return response()->json($plans);
    }

    /**
     * Display the specified plan
     */
    public function show(Plan $plan)
    {
        return response()->json([
            'plan' => $plan
        ]);
    }

    /**
     * Get user's current plan with usage statistics
     */
    public function getUserPlan(Request $request)
    {
        $user = $request->user();
        
        if (!$user->plan) {
            return response()->json([
                'message' => 'No plan assigned to user'
            ], 404);
        }

        $userCount = $user->stores()->withCount('users')->get()->sum('users_count');
        $storeCount = $user->stores()->count();

        return response()->json([
            'plan' => $user->plan,
            'usage' => [
                'users' => [
                    'current' => $userCount,
                    'limit' => $user->plan->max_users,
                    'remaining' => $user->plan->getRemainingUsers($userCount)
                ],
                'stores' => [
                    'current' => $storeCount,
                    'limit' => $user->plan->max_stores,
                    'remaining' => $user->plan->getRemainingStores($storeCount)
                ]
            ],
            'trial_ends_at' => $user->trial_ends_at
        ]);
    }
}
