<?php

namespace App\Http\Resources;

use App\Enums\AUTOCOMPLETE;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModePayementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            AUTOCOMPLETE::VALUE->value => $this->id,
            AUTOCOMPLETE::NAME->value => $this->name,
            'paid_method_id' => $this->id,

        ];
    }
}
