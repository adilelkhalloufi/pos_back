<?php

namespace App\Repositories\Purchase;

use App\Models\PurchaseDeliveryItem;
use App\Repositories\BaseRepository;

class PurchaseDeliveryItemRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return PurchaseDeliveryItem::class;
    }
}
