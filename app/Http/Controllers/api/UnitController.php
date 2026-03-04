<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\Services\Unit\UnitService;
use Illuminate\Http\Response;

class UnitController extends BaseController
{
    public function __construct(private readonly UnitService $unitService)
    {
        parent::__construct();
    }

    public function index()
    {
        $units = $this->unitService->all($this->storeId());
        return response()->json($units, Response::HTTP_OK);
    }

    public function store(UnitRequest $request)
    {
        $unit = $this->unitService->create($request->validated());
        return response()->json($unit, Response::HTTP_CREATED);
    }

    public function show(int $id)
    {
        $unit = Unit::findOrFail($id);
        return response()->json($unit, Response::HTTP_OK);
    }

    public function update(UnitRequest $request, int $id)
    {
        $unit = $this->unitService->update($id, $request->validated());
        return response()->json($unit, Response::HTTP_OK);
    }

    public function destroy(int $id)
    {
        $this->unitService->delete($id);
        return response()->json(['message' => 'Unit deleted'], Response::HTTP_OK);
    }
}
