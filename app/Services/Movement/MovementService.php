<?php

namespace App\Services\Movement;

use App\Models\StockMovement;
use App\Repositories\Movement\MovementRepository;

class MovementService
{
     public function __construct(
        private readonly MovementRepository $movementRepository
     )
    {}


    public function findById(int $id)
    {
        return $this->movementRepository->find($id);
    }

   
    public function create(array $attributes) :? StockMovement
    {
        $store = $this->movementRepository->create([
            StockMovement::COL_PRODUCT_ID => $attributes[StockMovement::COL_PRODUCT_ID],
            StockMovement::COL_STORE_ID => $attributes[StockMovement::COL_STORE_ID],
            StockMovement::COL_TYPE => $attributes[StockMovement::COL_TYPE],
            StockMovement::COL_DIRECTION => $attributes[StockMovement::COL_DIRECTION],
            StockMovement::COL_QUANTITY => $attributes[StockMovement::COL_QUANTITY],
            StockMovement::COL_TARGET_STORE_ID => $attributes[StockMovement::COL_TARGET_STORE_ID] ?? null,
            StockMovement::COL_UNIT_COST => $attributes[StockMovement::COL_UNIT_COST] ?? null,
            StockMovement::COL_TOTAL_COST => $attributes[StockMovement::COL_TOTAL_COST] ?? null,
            StockMovement::COL_PREVIOUS_STOCK => $attributes[StockMovement::COL_PREVIOUS_STOCK] ?? null,
            StockMovement::COL_NEW_STOCK => $attributes[StockMovement::COL_NEW_STOCK] ?? null,
            StockMovement::COL_REFERENCEABLE_TYPE => $attributes[StockMovement::COL_REFERENCEABLE_TYPE] ?? null,
            StockMovement::COL_REFERENCEABLE_ID => $attributes[StockMovement::COL_REFERENCEABLE_ID] ?? null,
            StockMovement::COL_USER_ID => $attributes[StockMovement::COL_USER_ID] ?? auth()->id(),
            StockMovement::COL_NOTE => $attributes[StockMovement::COL_NOTE] ?? null,
            StockMovement::COL_META => $attributes[StockMovement::COL_META] ?? null,
         
        ]);

        return $store;
    }
}