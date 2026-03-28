<?php

namespace App\Services\Setting;

use App\Repositories\SettingRepository;

class SettingService
{
    protected $repository;

    public function __construct(SettingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByKey(string $key)
    {
        return $this->repository->findbyfield('key', $key);
    }

    public function all()
    {
        return $this->repository->all();
    }

    public function update($id, array $data)
    {
        $setting = $this->repository->find($id);
        if (!$setting) {
            return null;
        }
        $this->repository->update($setting, $data);
        return $setting->fresh();
    }

    public function delete($id)
    {
        $setting = $this->repository->find($id);
        if (!$setting) {
            return false;
        }
        return $this->repository->delete($setting);
    }

    public function getValue(string $key, $default = null)
    {
        return $this->repository->getValue($key, currentStoreId(), $default);
    }

    public function setValue(string $key, $value, string $type = 'string', string $description = null)
    {
        return $this->repository->setValue($key, $value, currentStoreId(), $type, $description);
    }

    /**
     * Get the next purchase order number with prefix (e.g., PUR-0001)
     *
     * @return string
     */
    public function getNextPurchaseOrderNumber(): string
    {
        $currentNumber = (int) $this->getValue('order_purchase_number', 0);
        $nextNumber = $currentNumber + 1;
        $prefix = $this->getValue('prefix_purchase', 'PUR-');

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Increment the purchase order number in settings
     *
     * @return void
     */
    public function incrementPurchaseOrderNumber(): void
    {
        $currentNumber = (int) $this->getValue('order_purchase_number', 0);
        $nextNumber = $currentNumber + 1;

        $this->setValue('order_purchase_number', $nextNumber, 'integer', 'Current purchase order sequence number');
    }
}
