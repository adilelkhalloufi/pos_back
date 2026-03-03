<?php

namespace App\Repositories\Transfert;

use App\Models\Transfert;
use App\Repositories\BaseRepository;

class TransfertRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Transfert::class;
    }

    /**
     * Get transfers for a specific store
     * 
     * @param int $storeId
     * @param string|null $type 'source' or 'target'
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStore(int $storeId, ?string $type = null)
    {
        $query = $this->getQueryBuilder()
            ->with(['sourceStore', 'targetStore', 'items.product', 'createdBy', 'receivedBy'])
            ->orderBy('id', 'desc')
            ->where(Transfert::COL_STORE_ID, currentStoreId());

        if ($type === 'source') {
            $query->where(Transfert::COL_SOURCE_STORE_ID, $storeId);
        } elseif ($type === 'target') {
            $query->where(Transfert::COL_TARGET_STORE_ID, $storeId);
        } else {
            $query->where(function($q) use ($storeId) {
                $q->where(Transfert::COL_SOURCE_STORE_ID, $storeId)
                  ->orWhere(Transfert::COL_TARGET_STORE_ID, $storeId);
            });
        }

        return $query->get();
    }

    /**
     * Get transfers by status
     * 
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStatus(string $status)
    {
        return $this->getQueryBuilder()
            ->where(Transfert::COL_STATUS, $status)
            ->with(['sourceStore', 'targetStore', 'items.product', 'createdBy','store'])
            ->where(Transfert::COL_STORE_ID, currentStoreId())
            ->orderBy('id', 'desc')
            ->get();
    }
}
