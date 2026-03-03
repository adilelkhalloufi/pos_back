<?php

namespace App\Http\Resources;

use App\Enums\EnumPayementStatue;
use App\Models\OrderSale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            OrderSale::COL_ID => $this->id,
            OrderSale::COL_ORDER_NUMBER => padNumber($this->order_number),
            OrderSale::COL_CREATED_AT => $this->created_at->format('Y-m-d H:i'),
            OrderSale::COL_TOTAL_COMMAND => $this->total_command,
            OrderSale::COL_DISCOUNT => $this->discount,
            OrderSale::COL_ADVANCE => $this->advance,
            OrderSale::COL_TOTAL_PAYMENT => $this->total_payment,
            OrderSale::COL_REST_A_PAY => $this->rest_a_pay,
            OrderSale::COL_IS_INVOICE => $this->is_invoice,
            OrderSale::COL_INVOICE_TOTAL => $this->invoice_total,
            OrderSale::COL_STATUS => EnumPayementStatue::from($this->status)->getLabel(),
            'customer' => $this->customer,
            'details' => $this->orderItems,
            'payments' => $this->payments,
            'prescription' => $this->prescription,
            'user' => $this->user,




        ];
    }
}
