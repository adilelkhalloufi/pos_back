<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Settings extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'settings';
    public const COL_STORE_ID = 'store_id';

    /**
     * Get a setting value for a specific store
     */
    public static function get(string $key, int $storeId, mixed $default = null): mixed
    {
        $cacheKey = "settings.{$storeId}.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $storeId, $default) {
            $setting = self::where('key', $key)
                ->where('store_id', $storeId)
                ->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value for a specific store
     */
    public static function set(string $key, mixed $value, int $storeId, ?string $type = null, ?string $description = null): self
    {
        $type = $type ?? self::inferType($value);
        $cacheKey = "settings.{$storeId}.{$key}";
        Cache::forget($cacheKey);

        return self::updateOrCreate(
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
     * Get all settings for a store as key-value array
     */
    public static function getAllForStore(int $storeId)
    {
        $settings = self::where('store_id', $storeId)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = self::castValue($setting->value, $setting->type);
        }

        return $settings;
    }

    /**
     * Cast value based on type
     */
    private static function castValue(mixed $value, string $type): mixed
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
     */
    private static function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    /**
     * Clear cache for store
     */
    public static function clearCache(int $storeId): void
    {
        $keys = self::where('store_id', $storeId)->pluck('key');
        foreach ($keys as $key) {
            Cache::forget("settings.{$storeId}.{$key}");
        }
    }
}
