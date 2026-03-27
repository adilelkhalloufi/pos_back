<?php

namespace App\Services;

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
        return $this->repository->findByKey($key);
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
        return $this->repository->getValue($key, $default);
    }

    public function setValue(string $key, $value, string $type = 'string', string $description = null)
    {
        return $this->repository->setValue($key, $value, $type, $description);
    }
}