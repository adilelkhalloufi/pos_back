<?php

namespace App\Services\Menu;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;

/**
 * MenuService - Handles menu management, cost calculation, and availability
 */
class MenuService
{
    /**
     * Create a new menu
     * 
     * @param array $menuData
     * @return Menu
     * @throws Exception
     */
    public function createMenu(array $menuData): Menu
    {
        try {
            $menu = Menu::create([
                'name' => $menuData['name'],
                'description' => $menuData['description'] ?? null,
                'type' => $menuData['type'] ?? Menu::TYPE_ALL_DAY,
                'is_active' => $menuData['is_active'] ?? true,
                'display_order' => $menuData['display_order'] ?? 0,
                'available_from_time' => $menuData['available_from_time'] ?? null,
                'available_to_time' => $menuData['available_to_time'] ?? null,
                'store_id' => $menuData['store_id'],
            ]);

            return $menu->fresh();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update an existing menu
     * 
     * @param int $menuId
     * @param array $menuData
     * @return Menu
     * @throws Exception
     */
    public function updateMenu(int $menuId, array $menuData): Menu
    {
        try {
            $menu = Menu::findOrFail($menuId);

            $menu->update(array_filter([
                'name' => $menuData['name'] ?? $menu->name,
                'description' => $menuData['description'] ?? $menu->description,
                'type' => $menuData['type'] ?? $menu->type,
                'is_active' => $menuData['is_active'] ?? $menu->is_active,
                'display_order' => $menuData['display_order'] ?? $menu->display_order,
                'available_from_time' => $menuData['available_from_time'] ?? $menu->available_from_time,
                'available_to_time' => $menuData['available_to_time'] ?? $menu->available_to_time,
            ], function ($value) {
                return $value !== null;
            }));

            return $menu->fresh();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a menu (cascade deletes categories and items)
     * 
     * @param int $menuId
     * @return bool
     * @throws Exception
     */
    public function deleteMenu(int $menuId): bool
    {
        try {
            $menu = Menu::findOrFail($menuId);
            return $menu->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a menu category with optional menu items
     * 
     * @param array $categoryData
     * @param array $items - Optional array of menu items to create
     * @return MenuCategory
     * @throws Exception
     */
    public function createCategory(array $categoryData, array $items = []): MenuCategory
    {
        DB::beginTransaction();

        try {
            $category = MenuCategory::create([
                'menu_id' => $categoryData['menu_id'],
                'name' => $categoryData['name'],
                'description' => $categoryData['description'] ?? null,
                'display_order' => $categoryData['display_order'] ?? 0,
                'is_active' => $categoryData['is_active'] ?? true,
            ]);

            // Create menu items if provided
            if (!empty($items)) {
                foreach ($items as $itemData) {
                    $itemData['menu_category_id'] = $category->id;
                    $this->createMenuItem($itemData);
                }
            }

            DB::commit();

            return $category->fresh(['items.recipe']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a menu category
     * 
     * @param int $categoryId
     * @param array $categoryData
     * @return MenuCategory
     * @throws Exception
     */
    public function updateCategory(int $categoryId, array $categoryData): MenuCategory
    {
        try {
            $category = MenuCategory::findOrFail($categoryId);

            $category->update(array_filter([
                'name' => $categoryData['name'] ?? $category->name,
                'description' => $categoryData['description'] ?? $category->description,
                'display_order' => $categoryData['display_order'] ?? $category->display_order,
                'is_active' => $categoryData['is_active'] ?? $category->is_active,
            ], function ($value) {
                return $value !== null;
            }));

            return $category->fresh();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a menu category (cascade deletes items)
     * 
     * @param int $categoryId
     * @return bool
     * @throws Exception
     */
    public function deleteCategory(int $categoryId): bool
    {
        try {
            $category = MenuCategory::findOrFail($categoryId);
            return $category->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a menu item
     * 
     * @param array $itemData
     * @return MenuItem
     * @throws Exception
     */
    public function createMenuItem(array $itemData): MenuItem
    {
        DB::beginTransaction();

        try {
            $item = MenuItem::create([
                'menu_category_id' => $itemData['menu_category_id'],
                'name' => $itemData['name'],
                'description' => $itemData['description'] ?? null,
                'image' => $itemData['image'] ?? null,
                'price' => $itemData['price'],
                'cost' => 0, // Will be calculated if recipe linked
                'is_active' => $itemData['is_active'] ?? true,
                'is_available' => $itemData['is_available'] ?? true,
                'preparation_time_minutes' => $itemData['preparation_time_minutes'] ?? null,
                'item_type' => $itemData['item_type'] ?? MenuItem::ITEM_TYPE_RECIPE,
                'recipe_id' => $itemData['recipe_id'] ?? null,
                'store_id' => $itemData['store_id'],
                'display_order' => $itemData['display_order'] ?? 0,
            ]);

            // If recipe is linked, update cost from recipe
            if ($item->item_type === MenuItem::ITEM_TYPE_RECIPE && $item->recipe_id) {
                $item->updateCostFromRecipe();
            } elseif (isset($itemData['cost'])) {
                // Manual cost for simple items
                $item->update(['cost' => $itemData['cost']]);
            }

            DB::commit();

            return $item->fresh(['recipe', 'category']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a menu item
     * 
     * @param int $itemId
     * @param array $itemData
     * @return MenuItem
     * @throws Exception
     */
    public function updateMenuItem(int $itemId, array $itemData): MenuItem
    {
        DB::beginTransaction();

        try {
            $item = MenuItem::findOrFail($itemId);

            $item->update(array_filter([
                'name' => $itemData['name'] ?? $item->name,
                'description' => $itemData['description'] ?? $item->description,
                'image' => $itemData['image'] ?? $item->image,
                'price' => $itemData['price'] ?? $item->price,
                'is_active' => $itemData['is_active'] ?? $item->is_active,
                'is_available' => $itemData['is_available'] ?? $item->is_available,
                'preparation_time_minutes' => $itemData['preparation_time_minutes'] ?? $item->preparation_time_minutes,
                'item_type' => $itemData['item_type'] ?? $item->item_type,
                'recipe_id' => $itemData['recipe_id'] ?? $item->recipe_id,
                'display_order' => $itemData['display_order'] ?? $item->display_order,
            ], function ($value) {
                return $value !== null;
            }));

            // Update cost if recipe changed or manual cost provided
            if (isset($itemData['recipe_id']) && $item->item_type === MenuItem::ITEM_TYPE_RECIPE) {
                $item->updateCostFromRecipe();
            } elseif (isset($itemData['cost'])) {
                $item->update(['cost' => $itemData['cost']]);
            }

            DB::commit();

            return $item->fresh(['recipe', 'category']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a menu item
     * 
     * @param int $itemId
     * @return bool
     * @throws Exception
     */
    public function deleteMenuItem(int $itemId): bool
    {
        try {
            $item = MenuItem::findOrFail($itemId);
            return $item->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get menu with all categories and items
     * 
     * @param int $menuId
     * @param bool $activeOnly - Only return active items
     * @return Menu
     */
    public function getMenuWithItems(int $menuId, bool $activeOnly = false): Menu
    {
        $menu = Menu::with([
            'categories' => function ($query) use ($activeOnly) {
                if ($activeOnly) {
                    $query->where('is_active', true);
                }
                $query->orderBy('display_order');
            },
            'categories.items' => function ($query) use ($activeOnly) {
                if ($activeOnly) {
                    $query->where('is_active', true);
                }
                $query->orderBy('display_order');
            },
            'categories.items.recipe'
        ])->findOrFail($menuId);

        return $menu;
    }

    /**
     * Get all menus for a store
     * 
     * @param int $storeId
     * @param bool $activeOnly
     * @return Collection
     */
    public function getMenusForStore(int $storeId, bool $activeOnly = false): Collection
    {
        $query = Menu::where('store_id', $storeId)->orderBy('display_order');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * Get currently available menus for a store (based on time windows)
     * 
     * @param int $storeId
     * @return Collection
     */
    public function getCurrentlyAvailableMenus(int $storeId): Collection
    {
        return Menu::currentlyAvailable()
            ->where('store_id', $storeId)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Calculate menu item profitability
     * 
     * @param int $itemId
     * @return array
     */
    public function calculateItemProfitability(int $itemId): array
    {
        $item = MenuItem::with('recipe')->findOrFail($itemId);

        return [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'price' => $item->price,
            'cost' => $item->cost,
            'profit_margin' => $item->profit_margin,
            'profit_margin_percentage' => $item->profit_margin_percentage,
            'food_cost_percentage' => $item->food_cost_percentage,
            'recipe_linked' => $item->recipe_id !== null,
            'recipe_name' => $item->recipe?->name,
        ];
    }

    /**
     * Update costs for all menu items linked to a recipe
     * This should be called when recipe costs change
     * 
     * @param int $recipeId
     * @return int Number of items updated
     */
    public function updateMenuItemCostsForRecipe(int $recipeId): int
    {
        $items = MenuItem::where('recipe_id', $recipeId)
            ->where('item_type', MenuItem::ITEM_TYPE_RECIPE)
            ->get();

        $updated = 0;
        foreach ($items as $item) {
            if ($item->updateCostFromRecipe()) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Get menu items by profitability (for menu engineering)
     * 
     * @param int $storeId
     * @param string $orderBy - 'profit' or 'margin_percentage'
     * @return Collection
     */
    public function getItemsByProfitability(int $storeId, string $orderBy = 'profit'): Collection
    {
        $items = MenuItem::with(['recipe', 'category'])
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->get();

        if ($orderBy === 'margin_percentage') {
            return $items->sortByDesc('profit_margin_percentage');
        }

        return $items->sortByDesc('profit_margin');
    }

    /**
     * Toggle menu item availability (for temporary out of stock)
     * 
     * @param int $itemId
     * @param bool $isAvailable
     * @return MenuItem
     */
    public function toggleItemAvailability(int $itemId, bool $isAvailable): MenuItem
    {
        $item = MenuItem::findOrFail($itemId);
        $item->update(['is_available' => $isAvailable]);

        return $item->fresh();
    }

    /**
     * Get menu statistics
     * 
     * @param int $storeId
     * @return array
     */
    public function getMenuStatistics(int $storeId): array
    {
        $totalMenus = Menu::where('store_id', $storeId)->count();
        $activeMenus = Menu::where('store_id', $storeId)->where('is_active', true)->count();
        
        $totalCategories = MenuCategory::whereHas('menu', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })->count();

        $totalItems = MenuItem::where('store_id', $storeId)->count();
        $activeItems = MenuItem::where('store_id', $storeId)->where('is_active', true)->count();
        $availableItems = MenuItem::where('store_id', $storeId)
            ->where('is_active', true)
            ->where('is_available', true)
            ->count();

        $recipeBasedItems = MenuItem::where('store_id', $storeId)
            ->where('item_type', MenuItem::ITEM_TYPE_RECIPE)
            ->count();

        $averageFoodCost = MenuItem::where('store_id', $storeId)
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->get()
            ->avg('food_cost_percentage');

        return [
            'total_menus' => $totalMenus,
            'active_menus' => $activeMenus,
            'total_categories' => $totalCategories,
            'total_items' => $totalItems,
            'active_items' => $activeItems,
            'available_items' => $availableItems,
            'recipe_based_items' => $recipeBasedItems,
            'average_food_cost_percentage' => round($averageFoodCost ?? 0, 2),
        ];
    }
}
