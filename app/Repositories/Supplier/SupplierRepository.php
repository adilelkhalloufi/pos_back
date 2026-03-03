<?php

namespace App\Repositories\Supplier;

use App\Models\Suppliers;
use App\Repositories\BaseRepository;

class SupplierRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Suppliers::class;
    }
}