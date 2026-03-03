<?php

namespace App\Http\Resources;

use App\Models\TransfertItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransfertItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            TransfertItem::COL_ID => $this->id,
            TransfertItem::COL_TRANSFERT_ID => $this->transfert_id,
            TransfertItem::COL_PRODUCT_ID => $this->product_id,
            TransfertItem::COL_QUANTITY => (float) $this->quantity,
            TransfertItem::COL_NOTE => $this->note,
            
            'product' => ProductResource::make($this->whenLoaded('product')),
        ];
    }
}
