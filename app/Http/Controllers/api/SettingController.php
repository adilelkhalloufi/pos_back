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
        $settings = $this->settingService->getSettings();
        return response()->json($settings, Response::HTTP_OK);
    }

    /** PUT /settings */
    public function update(SettingsRequest $request)
    {
        $settings = $this->settingService->updateSettings($request->validated());
        return response()->json([
            'message'  => 'Settings updated successfully.',
            'settings' => $settings,
        ], Response::HTTP_OK);
    }
}
