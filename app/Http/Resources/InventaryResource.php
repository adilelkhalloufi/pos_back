<?php

namespace App\Http\Resources;

use App\Models\Inventary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            Inventary::COL_ID => $this->id,
            Inventary::COL_REFERENCE => $this->reference,
            Inventary::COL_STORE_ID => $this->store_id,
            Inventary::COL_STATUS => $this->status,
            Inventary::COL_STARTED_AT => $this->started_at,
            Inventary::COL_COMPLETED_AT => $this->completed_at,
            Inventary::COL_CREATED_BY => $this->created_by,
            Inventary::COL_COMPLETED_BY => $this->completed_by,
            Inventary::COL_TOTAL_ITEMS => $this->total_items,
            Inventary::COL_CHECKED_ITEMS => $this->checked_items,
            Inventary::COL_TOTAL_DIFFERENCE => (float) $this->total_difference,
            Inventary::COL_NOTE => $this->note,
            Inventary::COL_META => $this->meta,
            Inventary::COL_CREATED_AT => $this->created_at,
            Inventary::COL_UPDATED_AT => $this->updated_at,
            'store' => StoreResource::make($this->whenLoaded('store')),
            'created_by_user' => UserResource::make($this->whenLoaded('createdBy')),
            'completed_by_user' => UserResource::make($this->whenLoaded('completedBy')),
            'items' => InventaryItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
