<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Services\Menu\MenuService;
use Illuminate\Http\Request;
use Exception;

class MenuController extends BaseController
{
    private MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        parent::__construct();
        $this->menuService = $menuService;
    }

    /**
     * Display a listing of menus
     */
    public function index(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $activeOnly = $request->query('active_only', false);
        $menus = $this->menuService->getMenusForStore($storeId, $activeOnly);

        return response()->json(MenuResource::collection($menus), 200);
    }

    /**
     * Get currently available menus (based on time windows)
     */
    public function currentlyAvailable()
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $menus = $this->menuService->getCurrentlyAvailableMenus($storeId);

        return response()->json(MenuResource::collection($menus), 200);
    }

    /**
     * Store a newly created menu
     */
    public function store(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:breakfast,lunch,dinner,drinks,all_day',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
            'available_from_time' => 'nullable|date_format:H:i',
            'available_to_time' => 'nullable|date_format:H:i',
        ]);

        try {
            $validated['store_id'] = $storeId;
            $menu = $this->menuService->createMenu($validated);

            return response()->json(new MenuResource($menu), 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create menu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified menu with all categories and items
     */
    public function show($id, Request $request)
    {
        try {
            $activeOnly = $request->query('active_only', false);
            $menu = $this->menuService->getMenuWithItems($id, $activeOnly);

            return response()->json(new MenuResource($menu), 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Menu not found'], 404);
        }
    }

    /**
     * Update the specified menu
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:breakfast,lunch,dinner,drinks,all_day',
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'available_from_time' => 'nullable|date_format:H:i:s',
            'available_to_time' => 'nullable|date_format:H:i:s',
        ]);

        try {
            $menu = $this->menuService->updateMenu($id, $validated);

            return response()->json(new MenuResource($menu), 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update menu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified menu
     */
    public function destroy($id)
    {
        try {
            $this->menuService->deleteMenu($id);

            return response()->json(['message' => 'Menu deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete menu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get menu statistics for the current store
     */
    public function statistics()
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $statistics = $this->menuService->getMenuStatistics($storeId);

        return response()->json($statistics, 200);
    }
}
