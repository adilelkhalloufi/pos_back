<?php

namespace App\Http\Resources;

use App\Models\RecipeIngredient;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeIngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            RecipeIngredient::COL_ID => $this->id,
            RecipeIngredient::COL_RECIPE_ID => $this->recipe_id,
            RecipeIngredient::COL_PRODUCT_ID => $this->product_id,
            RecipeIngredient::COL_QUANTITY => (float) $this->quantity,
            RecipeIngredient::COL_UNIT_ID => $this->unit_id,
            RecipeIngredient::COL_WASTE_PERCENTAGE => (float) $this->waste_percentage,
            RecipeIngredient::COL_PREPARATION_NOTE => $this->preparation_note,
            RecipeIngredient::COL_IS_OPTIONAL => $this->is_optional,
            RecipeIngredient::COL_COST => (float) $this->cost,
            'effective_quantity' => (float) $this->effective_quantity,

            // Relationships
            'product' => ProductResource::make($this->whenLoaded('product')),
            'unit' => $this->when($this->relationLoaded('unit'), function () {
                return [
                    'id' => $this->unit?->id,
                    'name' => $this->unit?->name,
                    'symbol' => $this->unit?->symbol,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
