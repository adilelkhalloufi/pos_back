<?php

namespace App\Http\Resources;

use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            MenuCategory::COL_ID => $this->id,
            MenuCategory::COL_MENU_ID => $this->menu_id,
            MenuCategory::COL_NAME => $this->name,
            MenuCategory::COL_DESCRIPTION => $this->description,
            MenuCategory::COL_DISPLAY_ORDER => $this->display_order,
            MenuCategory::COL_IS_ACTIVE => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Conditionally loaded relationships
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
            'menu' => new MenuResource($this->whenLoaded('menu')),
        ];
    }
}
