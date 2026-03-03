<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EnsureTrialIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if trial has expired
            if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isPast()) {
                // Invalidate the token
                if (method_exists($user->currentAccessToken(), 'delete')) {
                    $user->currentAccessToken()->delete();
                } else {
                    // For JWT or other token systems
                    $user->tokens()->delete();
                }
                
                return response()->json([
                    'message' => 'Your trial has expired. Please upgrade your subscription.',
                    'status' => 'trial_expired'
                ], 401); // Using 401 Unauthorized for expired token/authentication
            }
        }

        return $next($request);
    }
}
