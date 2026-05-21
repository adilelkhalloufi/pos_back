<?php

namespace App\Http\Resources;

use App\Models\Recipe;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            Recipe::COL_ID => $this->id,
            Recipe::COL_NAME => $this->name,
            Recipe::COL_DESCRIPTION => $this->description,
            Recipe::COL_INSTRUCTIONS => $this->instructions,
            Recipe::COL_YIELD_QUANTITY => (float) $this->yield_quantity,
            Recipe::COL_YIELD_UNIT_ID => $this->yield_unit_id,
            Recipe::COL_PREPARATION_TIME_MINUTES => $this->preparation_time_minutes,
            Recipe::COL_COOKING_TIME_MINUTES => $this->cooking_time_minutes,
            Recipe::COL_SKILL_LEVEL => $this->skill_level,
            Recipe::COL_TOTAL_COST => (float) $this->total_cost,
            Recipe::COL_COST_PER_SERVING => (float) $this->cost_per_serving,
            Recipe::COL_VERSION => $this->version,
            Recipe::COL_IS_ACTIVE => $this->is_active,
            Recipe::COL_STORE_ID => $this->store_id,
            Recipe::COL_USER_ID => $this->user_id,

            // Computed attributes
            'total_time_minutes' => $this->total_time,

            // Relationships
            'ingredients' => RecipeIngredientResource::collection($this->whenLoaded('ingredients')),
            'yield_unit' => $this->when($this->relationLoaded('yieldUnit'), function () {
                return [
                    'id' => $this->yieldUnit?->id,
                    'name' => $this->yieldUnit?->name,
                    'symbol' => $this->yieldUnit?->symbol,
                ];
            }),
            'store' => $this->when($this->relationLoaded('store'), function () {
                return [
                    'id' => $this->store?->id,
                    'name' => $this->store?->name,
                ];
            }),
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
