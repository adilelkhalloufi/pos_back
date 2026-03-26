<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SettingsRequest;
use App\Services\Setting\SettingService;
use Illuminate\Http\Response;

class SettingController extends BaseController
{
    public function __construct(private readonly SettingService $settingService)
    {
        parent::__construct();
    }

    /** GET /settings */
    public function show()
    {
        $settings = $this->settingService->getAllSettings();
        return response()->json($settings, Response::HTTP_OK);
    }

    /** PUT /settings */
    public function update(SettingsRequest $request)
    {
        $this->settingService->updateSettings($request->validated());
        $settings = $this->settingService->getAllSettings();

        return response()->json([
            'message'  => 'Settings updated successfully.',
            'settings' => $settings,
        ], Response::HTTP_OK);
    }
}
