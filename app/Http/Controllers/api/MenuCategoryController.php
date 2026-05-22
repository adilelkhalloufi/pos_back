<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\MenuCategoryResource;
use App\Models\MenuCategory;
use App\Services\Menu\MenuService;
use Illuminate\Http\Request;
use Exception;

class MenuCategoryController extends BaseController
{
    private MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        parent::__construct();
        $this->menuService = $menuService;
    }

    /**
     * Display a listing of categories (optionally filtered by menu_id)
     */
    public function index(Request $request)
    {
        $menuId = $request->query('menu_id');

        $query = MenuCategory::with(['items', 'menu']);

        if ($menuId) {
            $query->where('menu_id', $menuId);
        }

        $categories = $query->orderBy('display_order')->get();

        return response()->json(MenuCategoryResource::collection($categories), 200);
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        try {
            $category = MenuCategory::with('items', 'menu')->findOrFail($id);

            return response()->json(new MenuCategoryResource($category), 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Menu category not found'], 404);
        }
    }

    /**
     * Store a newly created category with optional menu items
     */
    public function store(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'display_order' => 'integer',
            'is_active' => 'boolean',

            // Optional menu items array
            'items' => 'nullable|array',
            'items.*.name' => 'required|string|max:100',
            'items.*.description' => 'nullable|string',
            'items.*.image' => 'nullable|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.cost' => 'nullable|numeric|min:0',
            'items.*.is_active' => 'boolean',
            'items.*.is_available' => 'boolean',
            'items.*.preparation_time_minutes' => 'nullable|integer|min:0',
            'items.*.item_type' => 'required|in:recipe,combo,simple',
            'items.*.recipe_id' => 'nullable|exists:recipes,id',
            'items.*.display_order' => 'integer',
        ]);

        try {
            $items = $validated['items'] ?? [];
            unset($validated['items']);

            // Add store_id to each item
            foreach ($items as &$item) {
                $item['store_id'] = $storeId;
            }

            $category = $this->menuService->createCategory($validated, $items);

            return response()->json(new MenuCategoryResource($category), 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        try {
            $category = $this->menuService->updateCategory($id, $validated);

            return response()->json(new MenuCategoryResource($category), 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        try {
            $this->menuService->deleteCategory($id);

            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete category',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
