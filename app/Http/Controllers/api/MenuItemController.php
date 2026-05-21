<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\MenuItemResource;
use App\Services\Menu\MenuService;
use Illuminate\Http\Request;
use Exception;

class MenuItemController extends BaseController
{
    private MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        parent::__construct();
        $this->menuService = $menuService;
    }

    /**
     * Store a newly created menu item
     */
    public function store(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $validated = $request->validate([
            'menu_category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'preparation_time_minutes' => 'nullable|integer|min:0',
            'item_type' => 'required|in:recipe,combo,simple',
            'recipe_id' => 'nullable|exists:recipes,id',
            'display_order' => 'integer',
        ]);

        try {
            $validated['store_id'] = $storeId;
            $item = $this->menuService->createMenuItem($validated);

            return response()->json(new MenuItemResource($item), 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create menu item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified menu item
     */
    public function show($id)
    {
        try {
            $item = \App\Models\MenuItem::with(['recipe', 'category', 'store'])->findOrFail($id);

            return response()->json(new MenuItemResource($item), 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Menu item not found'], 404);
        }
    }

    /**
     * Update the specified menu item
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'preparation_time_minutes' => 'nullable|integer|min:0',
            'item_type' => 'sometimes|required|in:recipe,combo,simple',
            'recipe_id' => 'nullable|exists:recipes,id',
            'display_order' => 'integer',
        ]);

        try {
            $item = $this->menuService->updateMenuItem($id, $validated);

            return response()->json(new MenuItemResource($item), 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update menu item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified menu item
     */
    public function destroy($id)
    {
        try {
            $this->menuService->deleteMenuItem($id);

            return response()->json(['message' => 'Menu item deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete menu item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get profitability analysis for a menu item
     */
    public function profitability($id)
    {
        try {
            $profitability = $this->menuService->calculateItemProfitability($id);

            return response()->json($profitability, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to calculate profitability',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle menu item availability (temporary out of stock)
     */
    public function toggleAvailability(Request $request, $id)
    {
        $validated = $request->validate([
            'is_available' => 'required|boolean',
        ]);

        try {
            $item = $this->menuService->toggleItemAvailability($id, $validated['is_available']);

            return response()->json(new MenuItemResource($item), 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to toggle availability',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get menu items by profitability
     */
    public function byProfitability(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $orderBy = $request->query('order_by', 'profit'); // 'profit' or 'margin_percentage'
        $items = $this->menuService->getItemsByProfitability($storeId, $orderBy);

        return response()->json(MenuItemResource::collection($items), 200);
    }
}
