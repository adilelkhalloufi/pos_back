<?php

namespace App\Services\PrintProfile;

use App\Models\PrintProfile;

class PrintProfileService
{
    public function all(int $storeId)
    {
        return PrintProfile::where(PrintProfile::COL_STORE_ID, $storeId)
            ->orderBy(PrintProfile::COL_NAME)
            ->get();
    }

    public function create(array $attributes): PrintProfile
    {
        if (!empty($attributes[PrintProfile::COL_IS_DEFAULT])) {
            PrintProfile::where(PrintProfile::COL_STORE_ID, currentStoreId())
                ->update([PrintProfile::COL_IS_DEFAULT => false]);
        }

        return PrintProfile::create([
            PrintProfile::COL_NAME            => $attributes[PrintProfile::COL_NAME],
            PrintProfile::COL_PRINTER_NAME    => $attributes[PrintProfile::COL_PRINTER_NAME] ?? null,
            PrintProfile::COL_CONNECTION_TYPE => $attributes[PrintProfile::COL_CONNECTION_TYPE] ?? 'usb',
            PrintProfile::COL_COM_PORT        => $attributes[PrintProfile::COL_COM_PORT] ?? null,
            PrintProfile::COL_MAX_COPIES      => $attributes[PrintProfile::COL_MAX_COPIES] ?? 1,
            PrintProfile::COL_IS_DEFAULT      => $attributes[PrintProfile::COL_IS_DEFAULT] ?? false,
            PrintProfile::COL_IS_ACTIVE       => $attributes[PrintProfile::COL_IS_ACTIVE] ?? true,
            PrintProfile::COL_STORE_ID        => currentStoreId(),
        ]);
    }

    public function update(int $id, array $attributes): PrintProfile
    {
        $profile = PrintProfile::findOrFail($id);

        if (!empty($attributes[PrintProfile::COL_IS_DEFAULT])) {
            PrintProfile::where(PrintProfile::COL_STORE_ID, $profile->store_id)
                ->where(PrintProfile::COL_ID, '!=', $id)
                ->update([PrintProfile::COL_IS_DEFAULT => false]);
        }

        $profile->update([
            PrintProfile::COL_NAME            => $attributes[PrintProfile::COL_NAME] ?? $profile->name,
            PrintProfile::COL_PRINTER_NAME    => $attributes[PrintProfile::COL_PRINTER_NAME] ?? $profile->printer_name,
            PrintProfile::COL_CONNECTION_TYPE => $attributes[PrintProfile::COL_CONNECTION_TYPE] ?? $profile->connection_type,
            PrintProfile::COL_COM_PORT        => $attributes[PrintProfile::COL_COM_PORT] ?? $profile->com_port,
            PrintProfile::COL_MAX_COPIES      => $attributes[PrintProfile::COL_MAX_COPIES] ?? $profile->max_copies,
            PrintProfile::COL_IS_DEFAULT      => $attributes[PrintProfile::COL_IS_DEFAULT] ?? $profile->is_default,
            PrintProfile::COL_IS_ACTIVE       => $attributes[PrintProfile::COL_IS_ACTIVE] ?? $profile->is_active,
        ]);

        return $profile->fresh();
    }

    public function delete(int $id): void
    {
        PrintProfile::findOrFail($id)->delete();
    }
}
