<?php

namespace App\Http\Resources;

use App\Enums\EnumPayementStatue;
use App\Models\OrderSale;
use App\Models\Payemnt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed> 
     */
    public function toArray(Request $request): array
    {
        return [
            Payemnt::COL_ID => $this->id,
            Payemnt::COL_AMOUNT => (float)$this->amount,
            OrderSale::COL_ORDER_NUMBER => padNumber($this->order_sale->order_number),
            Payemnt::COL_CREATED_AT => $this->created_at->format('Y-m-d H:i'),
            OrderSale::COL_STATUS => EnumPayementStatue::from($this->status)->getLabel(),
            'customer' => $this->customer,




        ];
    }
}
