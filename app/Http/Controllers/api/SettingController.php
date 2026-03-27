<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Repositories\SettingRepository;
use Illuminate\Http\Request;
 
class SettingController extends BaseController
{
    protected $service;

    public function __construct(SettingRepository $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'nullable|string',
            'type' => 'string|in:string,integer,boolean,json',
            'description' => 'nullable|string',
        ]);

        $setting = $this->service->create($data);
        return response()->json($setting, 201);
    }

    public function show($id)
    {
        $setting = $this->service->find($id);
        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }
        return response()->json($setting);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'key' => 'sometimes|string|unique:settings,key,' . $id,
            'value' => 'nullable|string',
            'type' => 'string|in:string,integer,boolean,json',
            'description' => 'nullable|string',
        ]);

        $setting = $this->service->update($id, $data);
        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }
        return response()->json($setting);
    }

    public function destroy($id)
    {
        $deleted = $this->service->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Setting not found'], 404);
        }
        return response()->json(['message' => 'Setting deleted']);
    }

    public function getByKey($key)
    {
        $setting = $this->service->findByKey($key);
        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }
        return response()->json($setting);
    }

    public function setByKey(Request $request, $key)
    {
        $data = $request->validate([
            'value' => 'required',
            'type' => 'string|in:string,integer,boolean,json',
            'description' => 'nullable|string',
        ]);

        $setting = $this->service->setValue($key, $data['value'], $data['type'] ?? 'string', $data['description'] ?? null);
        return response()->json($setting);
    }
}