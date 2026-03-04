<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PrintProfileRequest;
use App\Models\PrintProfile;
use App\Services\PrintProfile\PrintProfileService;
use Illuminate\Http\Response;

class PrintProfileController extends BaseController
{
    public function __construct(private readonly PrintProfileService $printProfileService)
    {
        parent::__construct();
    }

    public function index()
    {
        $profiles = $this->printProfileService->all($this->storeId());
        return response()->json($profiles, Response::HTTP_OK);
    }

    public function store(PrintProfileRequest $request)
    {
        $profile = $this->printProfileService->create($request->validated());
        return response()->json($profile, Response::HTTP_CREATED);
    }

    public function show(int $id)
    {
        $profile = PrintProfile::findOrFail($id);
        return response()->json($profile, Response::HTTP_OK);
    }

    public function update(PrintProfileRequest $request, int $id)
    {
        $profile = $this->printProfileService->update($id, $request->validated());
        return response()->json($profile, Response::HTTP_OK);
    }

    public function destroy(int $id)
    {
        $this->printProfileService->delete($id);
        return response()->json(['message' => 'Print profile deleted'], Response::HTTP_OK);
    }
}
