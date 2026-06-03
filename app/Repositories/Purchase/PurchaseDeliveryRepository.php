<?php

namespace App\Repositories\Purchase;

use App\Models\PurchaseDelivery;
use App\Repositories\BaseRepository;

class PurchaseDeliveryRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return PurchaseDelivery::class;
    }
}
