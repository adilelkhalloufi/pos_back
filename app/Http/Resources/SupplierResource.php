<?php

namespace App\Http\Resources;

use App\Enums\AUTOCOMPLETE;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'supplier_id' => $this->id,
            'company_name' => $this->company_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'city' => CityResource::make($this->cites),
            'store_id' => $this->store_id,
            'created_at' => $this->created_at,
            AUTOCOMPLETE::VALUE->value => $this->id,
            AUTOCOMPLETE::NAME->value => $this->company_name,
         ];
    }
}
