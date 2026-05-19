<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\POSResource;
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
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        try {
            $category = $this->menuService->createCategory($validated);

            return response()->json(new POSResource($category), 201);
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

            return response()->json(new POSResource($category), 200);
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
