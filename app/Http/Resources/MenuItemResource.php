<?php

namespace App\Http\Resources;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            MenuItem::COL_ID => $this->id,
            MenuItem::COL_MENU_CATEGORY_ID => $this->menu_category_id,
            MenuItem::COL_NAME => $this->name,
            MenuItem::COL_DESCRIPTION => $this->description,
            MenuItem::COL_IMAGE => $this->image ? asset('storage/' . $this->image) : null,
            MenuItem::COL_PRICE => (float) $this->price,
            MenuItem::COL_COST => (float) $this->cost,
            MenuItem::COL_IS_ACTIVE => $this->is_active,
            MenuItem::COL_IS_AVAILABLE => $this->is_available,
            MenuItem::COL_PREPARATION_TIME_MINUTES => $this->preparation_time_minutes,
            MenuItem::COL_ITEM_TYPE => $this->item_type,
            MenuItem::COL_RECIPE_ID => $this->recipe_id,
            MenuItem::COL_STORE_ID => $this->store_id,
            MenuItem::COL_DISPLAY_ORDER => $this->display_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Computed attributes
            'food_cost_percentage' => $this->when(
                isset($this->resource->food_cost_percentage),
                fn() => $this->food_cost_percentage
            ),
            'profit_margin' => $this->when(
                isset($this->resource->profit_margin),
                fn() => $this->profit_margin
            ),

            // Conditionally loaded relationships
            'category' => new MenuCategoryResource($this->whenLoaded('category')),
            'recipe' => $this->whenLoaded('recipe'),
            'store' => new StoreResource($this->whenLoaded('store')),
        ];
    }
}
