<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Setting::class;
    }

    /**
     * Get a setting value for a specific store
     *
     * @param string $key The setting key
     * @param int $storeId The store ID
     * @param mixed $default Default value if setting not found
     * @return mixed
     */
    public function getValue(string $key, int $storeId, mixed $default = null): mixed
    {
        $setting = $this->getQueryBuilder()
            ->where('key', $key)
            ->where('store_id', $storeId)
            ->first();

        if (!$setting) {
            return $default;
        }

        return $this->castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value for a specific store
     *
     * @param string $key The setting key
     * @param mixed $value The value to store
     * @param int $storeId The store ID
     * @param string|null $type The type of the value (auto-detected if null)
     * @param string|null $description Optional description
     * @return Setting
     */
    public function setValue(string $key, mixed $value, int $storeId, ?string $type = null, ?string $description = null): Setting
    {
        $type = $type ?? $this->inferType($value);

        return $this->updateOrCreate(
            [
                'key' => $key,
                'store_id' => $storeId,
            ],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings for a store
     *
     * @param int $storeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllForStore(int $storeId)
    {
        return $this->getQueryBuilder()
            ->where('store_id', $storeId)
            ->get();
    }

    /**
     * Cast value based on type
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'float', 'double' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Infer type from value
     *
     * @param mixed $value
     * @return string
     */
    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };
    }
}