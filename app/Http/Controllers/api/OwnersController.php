<?php

namespace App\Http\Controllers\api;

use App\Enums\ROLES;
use App\Services\User\UserService;

class OwnersController
{
    public function __construct(private readonly UserService $userService) {}

    public function index()
    {
        $users = $this->userService->findUsersByRole(ROLES::OWNER->value);

        // Use ownedStores relationship for proper eager loading
        $users->load(['ownedStores', 'plan']);

        return response()->json($users);
    }

    public function show($id)
    {
        return response()->json(['message' => "This is the owners show route for owner with ID: $id."]);
    }

    public function update($id)
    {
        return response()->json(['message' => "This is the owners update route for owner with ID: $id."]);
    }

    public function store()
    {
        return response()->json(['message' => 'This is the owners store route.']);
    }

    public function changePlan($id)
    {
        $request = request();
        $planId = $request->input('plan_id');

        if (! $planId) {
            return response()->json(['message' => 'Plan ID is required'], 400);
        }

        try {
            $user = $this->userService->changePlan($id, $planId);

            return response()->json(['message' => 'Plan changed successfully', 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function suspend($id)
    {
        try {
            $user = $this->userService->suspend($id);

            return response()->json(['message' => 'User suspended successfully', 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function activate($id)
    {
        try {
            $user = $this->userService->activate($id);

            return response()->json(['message' => 'User activated successfully', 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
