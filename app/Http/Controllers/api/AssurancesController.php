<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\AssuranceResource;
use App\Models\Assurances;
use App\Services\Assurance\AssuranceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AssurancesController extends BaseController
{
    public function __construct(
        private readonly AssuranceService $assuranceService
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assurances = $this->assuranceService->getAll();

        return response()->json(AssuranceResource::collection($assurances), Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            Assurances::COL_NAME => 'required|string|max:255',
            Assurances::COL_DESCRIPTION => 'nullable|string',
        ]);

        $assurance = $this->assuranceService->create($validated);

        return response()->json(new AssuranceResource($assurance), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $assurance = $this->assuranceService->findById($id);

        if (!$assurance) {
            return response()->json(['message' => 'Assurance not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(new AssuranceResource($assurance), Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            Assurances::COL_NAME => 'required|string|max:255',
            Assurances::COL_DESCRIPTION => 'nullable|string',
        ]);

        $updated = $this->assuranceService->update($id, $validated);

        if (!$updated) {
            return response()->json(['message' => 'Assurance not found'], Response::HTTP_NOT_FOUND);
        }

        $assurance = $this->assuranceService->findById($id);

        return response()->json(new AssuranceResource($assurance), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deleted = $this->assuranceService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Assurance not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
