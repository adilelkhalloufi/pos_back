<?php

namespace App\Repositories\Customer;

use App\Models\Customer;
use App\Repositories\BaseRepository;

class CustomerRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Customer::class;
    }

    public function getCustomersForStore(int $storeId)
    {
        return $this->getQueryBuilder()
            ->where(Customer::COL_STORE_ID, $storeId)
            ->get();
    }
}
