<?php

namespace App\Services\User;

use App\Enums\EnumAccountStatue;
use App\Enums\ROLES;
use App\Models\OrderSale;
use App\Models\Role;
use App\Models\User;
use App\Models\UserStore;
use App\Repositories\User\UserRepository;
use App\Services\User\Exceptions\InactiveAccountException;
use App\Services\User\Exceptions\InvalidCredentialsException;
use App\Services\User\Exceptions\TrialExpiredException;
use App\Services\User\Exceptions\UserEmailAlreadyInUseException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private readonly UserRepository $userRepository) {}

    /**
     * Authenticate user and generate access token
     *
     * @throws InvalidCredentialsException
     * @throws InactiveAccountException (except for super admin)
     * @throws TrialExpiredException (except for super admin)
     */
    public function login(array $credentials): array
    {
        // Attempt authentication
        if (! Auth::attempt($credentials)) {
            throw new InvalidCredentialsException();
        }

        /** @var User $user */
        $user = Auth::user();

        // Skip validations for super admin
        if (! $user->isSuperAdmin()) {
            // Verify account is active
            $this->validateAccountStatus($user);

            // Verify trial period
            $this->validateTrialPeriod($user);
        }

        // Load user relationships
        if ($user->isOwner()) {
            $user->load(['plan']);
            // Load stores relationship for owners
            $user->setRelation('stores', $user->stores);
        } else {
            $user->load(['stores', 'plan']);
        }

        // Generate access token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Validate if user account is active
     *
     * @throws InactiveAccountException
     */
    protected function validateAccountStatus(User $user): void
    {
        if ($user->statue !== EnumAccountStatue::ACTIVE->value) {
            throw new InactiveAccountException();
        }
    }

    /**
     * Validate if user trial period is still valid
     *
     * @throws TrialExpiredException
     */
    protected function validateTrialPeriod(User $user): void
    {
        if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isPast()) {
            throw new TrialExpiredException();
        }
    }

    /**
     * Revoke user's current access token
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Register a new user
     *
     * @return User
     *
     * @throws UserEmailAlreadyInUseException
     */

    /**
     * Register a new user
     *
     * @return User
     *
     * @throws UserEmailAlreadyInUseException
     */
    public function register(array $attributes)
    {
        $email = $attributes[User::COL_EMAIL] ?? null;

        $user = $this->findByEmail($email);

        if ($user instanceof User) {
            throw new UserEmailAlreadyInUseException();
        }

        $trialEndDate = Carbon::now()->addDays(30);
        $data[User::COL_TRIAL_ENDS_AT] = $trialEndDate;

        if (isset($data[User::COL_PASSWORD])) {
            $data[User::COL_PASSWORD] = Hash::make($data[User::COL_PASSWORD]);
        }

        return $this->userRepository->create([
            User::COL_NAME => $data[User::COL_NAME],
            User::COL_EMAIL => $data[User::COL_EMAIL],
            User::COL_PASSWORD => $data[User::COL_PASSWORD],
            User::COL_PHONE => $data[User::COL_PHONE] ?? null,
            User::COL_STATUE => EnumAccountStatue::ACTIVE->value,
            User::COL_ROLE => ROLES::OWNER->value,
            User::COL_TRIAL_ENDS_AT => $data[User::COL_TRIAL_ENDS_AT],
            User::COL_PLAN_ID => 1, // Default plan ID
        ]);
    }

    /**
     * Find user by ID
     *
     * @return User|null
     */
    public function findById(int $id)
    {
        return $this->userRepository->find($id, User::COL_ID);
    }

    /**
     * Find user by email
     *
     * @return User|null
     */
    public function findByEmail(string $email)
    {
        return $this->userRepository->find($email, User::COL_EMAIL);
    }

    public function createUserForStore(array $data)
    {
        $data[User::COL_PASSWORD] = Hash::make($data[User::COL_PASSWORD] ?? 'password');

        $user = $this->userRepository->create([
            User::COL_NAME => $data[User::COL_NAME],
            User::COL_EMAIL => $data[User::COL_EMAIL],
            User::COL_PASSWORD => $data[User::COL_PASSWORD],
            User::COL_PHONE => $data[User::COL_PHONE] ?? null,
            User::COL_STATUE => $data['status'] ?? $data[User::COL_STATUE] ?? EnumAccountStatue::ACTIVE->value,
            User::COL_ROLE => $data['role'] ?? $data[User::COL_ROLE] ?? ROLES::VENDOR->value,
        ]);

        $roleName = $data['role'] ?? ROLES::VENDOR->value;
        $userRole = Role::where('name', $roleName)->first();
        if ($userRole) {
            $storeId = $data['store_id'] ?? currentStoreId();
            $user->assignRole($userRole, $storeId);
        }

        // Handle store assignment based on can_access_all_stores flag
        if (isset($data['can_access_all_stores']) && $data['can_access_all_stores']) {
            // If user can access all stores, get all stores and assign them
            $stores = currentStore()->owner->stores;
            foreach ($stores as $store) {
                UserStore::firstOrCreate([
                    'user_id' => $user->id,
                    'store_id' => $store->id,
                ]);
            }
        } else {
            // Assign to specific store
            $storeId = $data['store_id'] ?? currentStoreId();
            UserStore::firstOrCreate([
                'user_id' => $user->id,
                'store_id' => $storeId,
            ]);
        }

        return $user;
    }

    public function getUsersForStore(int $storeId)
    {
        return $this->userRepository->getUsersByStoreId($storeId);
    }

    /**
     * Get sales data for a specific user in a store
     */
    public function getUserSales(int $userId, int $storeId): array
    {
        // Get all sales for the user in the store
        $sales = OrderSale::where('user_id', $userId)
            ->where('store_id', $storeId)
            ->with(['customer:id,name', 'store:id,name'])
            ->select([
                'id',
                'order_number',
                'total_command as total',
                'created_at as date',
                'customer_id',
                'status',
                'store_id',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'order_number' => $sale->order_number,
                    'total' => (float) $sale->total,
                    'date' => $sale->date,
                    'customer' => $sale->customer ? [
                        'name' => $sale->customer->name,
                    ] : null,
                    'status' => $sale->status,
                ];
            });

        // Get monthly stats
        $monthlyStats = OrderSale::where('user_id', $userId)
            ->where('store_id', $storeId)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_command) as total_sales'),
                DB::raw('SUM(total_command) as total_revenue')
            )
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($stat) {
                return [
                    'month' => Carbon::create()->month($stat->month)->format('F'),
                    'year' => (int) $stat->year,
                    'total_sales' => (float) $stat->total_sales,
                    'total_revenue' => (float) $stat->total_revenue,
                    'order_count' => (int) $stat->order_count,
                ];
            });

        // Get yearly stats
        $yearlyStats = OrderSale::where('user_id', $userId)
            ->where('store_id', $storeId)
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_command) as total_sales'),
                DB::raw('SUM(total_command) as total_revenue')
            )
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('year', 'desc')
            ->get()
            ->map(function ($stat) {
                return [
                    'year' => (int) $stat->year,
                    'total_sales' => (float) $stat->total_sales,
                    'total_revenue' => (float) $stat->total_revenue,
                    'order_count' => (int) $stat->order_count,
                ];
            });

        return [
            'sales' => $sales,
            'monthly_stats' => $monthlyStats,
            'yearly_stats' => $yearlyStats,
        ];
    }

    public function findUsersByRole(string $roleName)
    {
        return $this->userRepository->findbyfield($roleName, User::COL_ROLE);
    }

    /**
     * Change the plan for a user
     */
    public function changePlan(int $userId, int $planId): User
    {
        $user = $this->findById($userId);
        if (! $user) {
            throw new \Exception('User not found');
        }

        $user->update([User::COL_PLAN_ID => $planId]);

        return $user->fresh();
    }

    /**
     * Suspend a user account
     */
    public function suspend(int $userId): User
    {
        $user = $this->findById($userId);
        if (! $user) {
            throw new \Exception('User not found');
        }

        $user->update([User::COL_STATUE => EnumAccountStatue::INACTIVE->value]);

        return $user->fresh();
    }

    /**
     * Activate a user account
     */
    public function activate(int $userId): User
    {
        $user = $this->findById($userId);
        if (! $user) {
            throw new \Exception('User not found');
        }

        $user->update([User::COL_STATUE => EnumAccountStatue::ACTIVE->value]);

        return $user->fresh();
    }
}
