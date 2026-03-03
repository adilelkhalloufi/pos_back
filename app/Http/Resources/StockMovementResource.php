<?php

namespace App\Http\Resources;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            StockMovement::COL_PRODUCT_ID => $this->{StockMovement::COL_PRODUCT_ID},
            StockMovement::COL_SOURCE_STORE_ID => $this->{StockMovement::COL_SOURCE_STORE_ID},
            StockMovement::COL_TARGET_STORE_ID => $this->{StockMovement::COL_TARGET_STORE_ID},
            StockMovement::COL_STORE_ID => $this->{StockMovement::COL_STORE_ID},
            StockMovement::COL_TYPE => $this->{StockMovement::COL_TYPE},
            StockMovement::COL_DIRECTION => $this->{StockMovement::COL_DIRECTION},
            StockMovement::COL_QUANTITY => (float) $this->{StockMovement::COL_QUANTITY},
            StockMovement::COL_UNIT_COST => (float) $this->{StockMovement::COL_UNIT_COST},
            StockMovement::COL_TOTAL_COST => (float) $this->{StockMovement::COL_TOTAL_COST},
            StockMovement::COL_PREVIOUS_STOCK => $this->{StockMovement::COL_PREVIOUS_STOCK} ? (float) $this->{StockMovement::COL_PREVIOUS_STOCK} : null,
            StockMovement::COL_NEW_STOCK => $this->{StockMovement::COL_NEW_STOCK} ? (float) $this->{StockMovement::COL_NEW_STOCK} : null,
            StockMovement::COL_REFERENCEABLE_TYPE => $this->{StockMovement::COL_REFERENCEABLE_TYPE},
            StockMovement::COL_REFERENCEABLE_ID => $this->{StockMovement::COL_REFERENCEABLE_ID},
            StockMovement::COL_USER_ID => $this->{StockMovement::COL_USER_ID},
            StockMovement::COL_NOTE => $this->{StockMovement::COL_NOTE},
            StockMovement::COL_META => $this->{StockMovement::COL_META},


            // Relationships
            'product' => ProductResource::make($this->whenLoaded('product')),
            'source_store' => StoreResource::make($this->whenLoaded('sourceStore')),
            'target_store' => StoreResource::make($this->whenLoaded('targetStore')),
            'store' => StoreResource::make($this->whenLoaded('store')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'referenceable' => $this->whenLoaded('referenceable'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
