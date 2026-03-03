<?php

namespace App\Repositories\Inventary;

use App\Models\Inventary;
use App\Repositories\BaseRepository;

class InventaryRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Inventary::class;
    }

    /**
     * Get inventories for a specific store
     * 
     * @param int $storeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStore(int $storeId)
    {
        return $this->getQueryBuilder()
            ->where(Inventary::COL_STORE_ID, currentStoreId())
            
            ->with(['store', 'createdBy', 'completedBy', 'items', 'targetStore'])
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get inventories by status
     * 
     * @param string $status
     * @param int|null $storeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStatus(string $status, ?int $storeId = null)
    {
        $query = $this->getQueryBuilder()
            ->where(Inventary::COL_STATUS, $status)
                        ->where(Inventary::COL_STORE_ID, currentStoreId())

            ->with(['store', 'createdBy', 'completedBy',' targetStore', 'items']);

        if ($storeId) {
            $query->where(Inventary::COL_TARGET_STORE_ID, $storeId);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    /**
     * Get inventory with items
     * 
     * @param int $id
     * @return Inventary|null
     */
    public function findWithItems(int $id)
    {
        return $this->getQueryBuilder()
            ->where(Inventary::COL_ID, $id)
            ->with(['items.product', 'store', 'createdBy', 'completedBy', 'targetStore'])
            ->first();
    }
}
