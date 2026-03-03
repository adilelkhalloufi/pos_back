<?php

namespace App\Http\Resources;

use App\Models\Transfert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransfertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            Transfert::COL_ID => $this->id,
            Transfert::COL_REFERENCE => $this->reference,
            Transfert::COL_SOURCE_STORE_ID => $this->source_store_id,
            Transfert::COL_TARGET_STORE_ID => $this->target_store_id,
            Transfert::COL_STATUS => $this->status,
            Transfert::COL_CREATED_BY => $this->created_by,
            Transfert::COL_RECEIVED_BY => $this->received_by,
            Transfert::COL_SENT_AT => $this->sent_at,
            Transfert::COL_RECEIVED_AT => $this->received_at,
            Transfert::COL_NOTE => $this->note,
            Transfert::COL_META => $this->meta,
            Transfert::COL_CREATED_AT => $this->created_at,
            Transfert::COL_UPDATED_AT => $this->updated_at,
            
            'source_store' => StoreResource::make($this->whenLoaded('sourceStore')),
            'target_store' => StoreResource::make($this->whenLoaded('targetStore')),
            'items' => TransfertItemResource::collection($this->whenLoaded('items')),
            'created_by_user' => UserResource::make($this->whenLoaded('createdBy')),
            'received_by_user' => UserResource::make($this->whenLoaded('receivedBy')),
        ];
    }
}
