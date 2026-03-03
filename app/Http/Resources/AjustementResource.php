<?php

namespace App\Http\Resources;

use App\Models\Ajustement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AjustementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            Ajustement::COL_ID => $this->id,
            Ajustement::COL_REFERENCE => $this->reference,
            Ajustement::COL_STORE_ID => $this->store_id,
            Ajustement::COL_REASON => $this->reason,
            Ajustement::COL_NOTE => $this->note,
            Ajustement::COL_USER_ID => $this->user_id,
            Ajustement::COL_META => $this->meta,
            Ajustement::COL_STATUS => $this->status,
            Ajustement::COL_CREATED_AT => $this->created_at,
            Ajustement::COL_UPDATED_AT => $this->updated_at,
            
            'store' => StoreResource::make($this->whenLoaded('store')),
            'items' => AjustementItemResource::collection($this->whenLoaded('items')),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
