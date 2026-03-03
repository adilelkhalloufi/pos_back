<?php

namespace App\Http\Resources;

use App\Enums\AUTOCOMPLETE;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            Customer::COL_ID => $this->id,
            Customer::COL_CIN => $this->cin,
            Customer::COL_NAME => $this->name,
            Customer::COL_GENDER => $this->gender,
            Customer::COL_BIRTHDAY => $this->birthday,
            Customer::COL_EMAIL => $this->email,
            Customer::COL_PHONE => $this->phone,
            Customer::COL_ADRESS => $this->adress,
            Customer::COL_STATUS => $this->status,
            Customer::COL_USER_ID => $this->user_id,
            Customer::COL_STORE_ID => $this->store_id,
            AUTOCOMPLETE::VALUE->value => $this->id,
            Customer::COL_TOTAL_ORDERS => $this->total_orders,
            Customer::COL_TOTAL_PAYMENTS => $this->total_payments,
            Customer::COL_TOTAL_PRESCRIPTIONS => $this->total_prescriptions,
            Customer::COL_TOTAL_AMOUNT_ORDERS => $this->total_amount_orders,
            Customer::COL_LAST_ORDER_DATE => $this->last_order_date,
            Customer::COL_CREATED_AT => $this->created_at,
            'prescriptions' => $this->whenLoaded('prescriptions'),
            'orders' => $this->whenLoaded('orders'),
            'payments' => $this->whenLoaded('payments'),




        ];
    }
}
