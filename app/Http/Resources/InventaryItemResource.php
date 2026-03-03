<?php

namespace App\Http\Resources;

use App\Models\InventaryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventaryItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            InventaryItem::COL_ID => $this->id,
            InventaryItem::COL_INVENTARY_ID => $this->inventary_id,
            InventaryItem::COL_PRODUCT_ID => $this->product_id,
            InventaryItem::COL_EXPECTED_QUANTITY => (float) $this->expected_quantity,
            InventaryItem::COL_ACTUAL_QUANTITY => $this->actual_quantity ? (float) $this->actual_quantity : null,
            InventaryItem::COL_DIFFERENCE => (float) $this->difference,
            InventaryItem::COL_STATUS => $this->status,
            InventaryItem::COL_NOTE => $this->note,
            InventaryItem::COL_CREATED_AT => $this->created_at,
            InventaryItem::COL_UPDATED_AT => $this->updated_at,
            'product' => ProductResource::make($this->whenLoaded('product')),
        ];
    }
}
