<?php

namespace App\Services\Supplier;

use App\Models\Suppliers;
use App\Repositories\Supplier\SupplierRepository;

class SupplierService
{
     public function __construct(
        private readonly SupplierRepository $supplierRepository
     )
    {}


    public function findById(int $id)
    {
        return $this->supplierRepository->find($id);
    }

   
    public function create(array $attributes) :? Suppliers
    {
        $store = $this->supplierRepository->create([
            Suppliers::COL_LAST_NAME => $attributes[Suppliers::COL_LAST_NAME] ?? null,
            Suppliers::COL_FIRST_NAME => $attributes[Suppliers::COL_FIRST_NAME] ?? null,
            Suppliers::COL_COMPANY_NAME => $attributes[Suppliers::COL_COMPANY_NAME] ,
            Suppliers::COL_PHONE => $attributes[Suppliers::COL_PHONE] ?? null,
            Suppliers::COL_EMAIL => $attributes[Suppliers::COL_EMAIL] ?? null,
            Suppliers::COL_CITY => $attributes[Suppliers::COL_CITY] ?? null,
            Suppliers::COL_ADDRESS => $attributes[Suppliers::COL_ADDRESS] ?? null,
            Suppliers::COL_ZIP_CODE => $attributes[Suppliers::COL_ZIP_CODE] ?? null,
            Suppliers::COL_STORE_ID => currentStoreId(),
            Suppliers::COL_USER_ID => auth()->id(),
        ]);

        return $store;
    }


    public function update(int $id, array $attributes) :? bool
    {
        $supplier = $this->supplierRepository->update($id, [
            Suppliers::COL_LAST_NAME => $attributes[Suppliers::COL_LAST_NAME] ?? null,
            Suppliers::COL_FIRST_NAME => $attributes[Suppliers::COL_FIRST_NAME] ?? null,
            Suppliers::COL_COMPANY_NAME => $attributes[Suppliers::COL_COMPANY_NAME] ,
            Suppliers::COL_PHONE => $attributes[Suppliers::COL_PHONE] ?? null,
            Suppliers::COL_EMAIL => $attributes[Suppliers::COL_EMAIL] ?? null,
            Suppliers::COL_CITY => $attributes[Suppliers::COL_CITY] ?? null,
            Suppliers::COL_ADDRESS => $attributes[Suppliers::COL_ADDRESS] ?? null,
            Suppliers::COL_ZIP_CODE => $attributes[Suppliers::COL_ZIP_CODE] ?? null,
        ]);

        return $supplier;
    }
}