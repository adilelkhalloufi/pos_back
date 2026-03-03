<?php

namespace App\Http\Resources;

use App\Enums\AUTOCOMPLETE;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            Store::COL_ID => $this->id,
            Store::COL_NAME => $this->name,
            AUTOCOMPLETE::VALUE->value => $this->id,
            Store::COL_EMAIL => $this->email,
            Store::COL_PHONE => $this->phone,
            Store::COL_ADDRESS => $this->address,
            Store::COL_LOGO => $this->logo,
            Store::COL_WEBSITE => $this->website,
            Store::COL_ZIP_CODE => $this->zip_code,
            'city' => new CityResource($this->cites),
            Store::COL_IF => $this->if,
            Store::COL_ICE => $this->ice,
            Store::COL_RC => $this->rc,
            Store::COL_PATENTE => $this->patente,
            Store::COL_CNSS => $this->cnss,
            Store::COL_TAX => $this->tax,
            Store::COL_LATITUDE => $this->latitude,
            Store::COL_LONGITUDE => $this->longitude,
        ];
    }
}
