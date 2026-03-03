<?php

namespace App\Http\Controllers\api;

use App\Enums\EnumAccountStatue;
use App\Enums\ROLES;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Settings;
use App\Models\Store;
use App\Models\User;
use App\Models\UserStore;
use App\Models\Plan;
use App\Services\User\Exceptions\InactiveAccountException;
use App\Services\User\Exceptions\InvalidCredentialsException;
use App\Services\User\Exceptions\TrialExpiredException;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserController extends BaseController
{
    public function __construct(private readonly UserService $userService) {}

    /**
     * Authenticate user and return token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $result = $this->userService->login($request->only('email', 'password'));

            return response()->json([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]);
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        } catch (InactiveAccountException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        } catch (TrialExpiredException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'trial_expired',
            ], 403);
        }
    }

    /**
     * Revoke user's access token
     */
    public function logout(Request $request)
    {
        $this->userService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function register(Request $request)
    {
        // when create account you should create store and setting for store 
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'company' => 'required|string',
            'password' => 'required|string',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'Email already exists',
            ], 400);
        }

        // Get the default trial plan
        $defaultPlan = Plan::where('name', 'Free Trial')->first();
        if (!$defaultPlan) {
            return response()->json([
                'message' => 'Default plan not found. Please contact administrator.',
            ], 500);
        }

        $trialEndDate = Carbon::now()->addDays($defaultPlan->trial_days);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'statue' => EnumAccountStatue::ACTIVE->value,
            'role' => ROLES::OWNER->value,
            'phone' => $request->phone,
            'trial_ends_at' => $trialEndDate,
            'plan_id' => $defaultPlan->id,
        ]);

        $store = Store::create([
            Store::COL_OWNER_ID => $user->id,
            Store::COL_NAME => $request->company,
            Store::COL_PHONE => $request->phone,
            Store::COL_EMAIL => $request->email,
        ]);

        $userStore = UserStore::create([
            'user_id' => $user->id,
            'store_id' => $store->id,
        ]);

        $settings1 = Settings::create([
            Settings::COL_ORDER_SALE_NUMBER => 0,
            Settings::COL_ORDER_PURCHASE_NUMBER => 0,
            Settings::COL_INVOICE_NUMBER => 0,
            Settings::COL_STORE_ID => $store->id,
        ]);




        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'plan' => [
                'name' => $defaultPlan->name,
                'max_users' => $defaultPlan->max_users,
                'max_stores' => $defaultPlan->max_stores,
                'trial_days' => $defaultPlan->trial_days,
            ],
            'message' => 'Registration successful. Your free trial ends on ' . $trialEndDate->format('Y-m-d')
        ], 201);
    }

    public function index()
    {
        // bring user for the store
        $users = $this->userService->getUsersForStore(currentStoreId());

        return response()->json(UserResource::collection($users));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'password' => 'required|string',
            'role' => 'required|string|in:owner,manager,vendor,viewer,inventory_manager,accountant',
            'status' => 'nullable|string|in:active,inactive',
            'store_id' => 'nullable|integer|exists:stores,id',
            'can_access_all_stores' => 'nullable|boolean',
        ]);

        $user = $this->userService->createUserForStore($request->all());

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Update user information
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:255',
        ]);

        // Update user fields
        $user->fill($request->only([
            'name',
            'email', 
            'phone',
            'role',
            'status',
            'can_access_all_stores'
        ]));

        $user->save();

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'User updated successfully',
        ]);
    }

    /**
     * Get sales data for a specific user
     */
    public function sales(Request $request, $id)
    {
        $storeId = currentStoreId();

        $salesData = $this->userService->getUserSales($id, $storeId);

        return response()->json($salesData);
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request, $id)
    {
        $user = User::findOrFail($id);

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
            ], 400);
        }

        // Check if new password is different from current password
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'New password must be different from current password',
            ], 400);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }
}
