<?php

namespace App\Http\Resources;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            Menu::COL_ID => $this->id,
            Menu::COL_NAME => $this->name,
            Menu::COL_DESCRIPTION => $this->description,
            Menu::COL_TYPE => $this->type,
            Menu::COL_IS_ACTIVE => $this->is_active,
            Menu::COL_DISPLAY_ORDER => $this->display_order,
            Menu::COL_AVAILABLE_FROM_TIME => $this->available_from_time,
            Menu::COL_AVAILABLE_TO_TIME => $this->available_to_time,
            Menu::COL_STORE_ID => $this->store_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Conditionally loaded relationships
            'categories' => MenuCategoryResource::collection($this->whenLoaded('categories')),
            'store' => new StoreResource($this->whenLoaded('store')),

            // Computed properties
            'is_currently_available' => $this->when(
                method_exists($this->resource, 'isCurrentlyAvailable'),
                fn() => $this->isCurrentlyAvailable()
            ),
        ];
    }
}
