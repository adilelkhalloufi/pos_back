<?php

namespace App\Services\Store;

use App\Models\Store;
use App\Repositories\Store\StoreRepository;

class StoreService
{
     public function __construct(
        private readonly StoreRepository $storeRepository
     )
    {}


    public function findById(int $id)
    {
        return $this->storeRepository->find($id);
    }

    public function ownerStores(int $ownerId)
    {
        return $this->storeRepository->find($ownerId,Store::COL_OWNER_ID);
    }

    public function create(array $attributes) :? Store
    {
        $store = $this->storeRepository->create([
            Store::COL_NAME => $attributes[Store::COL_NAME],
            Store::COL_ADDRESS => $attributes[Store::COL_ADDRESS],
            Store::COL_PHONE => $attributes[Store::COL_PHONE],
            Store::COL_EMAIL => $attributes[Store::COL_EMAIL] ?? null,
            Store::COL_OWNER_ID =>  auth()->id(),
        ]);

        return $store;
    }
}