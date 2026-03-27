<?php

namespace App\Repositories;

use App\Models\Setting;
use App\Models\Settings;

class SettingRepository
{
    public function create(array $data): Settings
    {
        return Settings::create($data);
    }

    public function find($id): ?Settings
    {
        return Settings::find($id);
    }

    public function findByKey(string $key): ?Settings
    {
        return Settings::where('key', $key)->first();
    }

    public function all()
    {
        return Settings::all();
    }

    public function update(Settings $setting, array $data): bool
    {
        return $setting->update($data);
    }

    public function delete(Settings $setting): bool
    {
        return $setting->delete();
    }

    public function getValue(string $key, $default = null)
    {
        $setting = $this->findByKey($key);
        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'integer':
                return (int) $setting->value;
            case 'boolean':
                return $setting->value === 'true';
            case 'json':
                return json_decode($setting->value, true);
            case 'string':
            default:
                return $setting->value;
        }
    }

    public function setValue(string $key, $value, string $type = 'string', string $description = null): Settings
    {
        // Handle value encoding based on type
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? 'true' : 'false';
        }

        $setting = $this->findByKey($key);
        if ($setting) {
            $this->update($setting, [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]);
            return $setting->fresh();
        } else {
            return $this->create([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]);
        }
    }
}