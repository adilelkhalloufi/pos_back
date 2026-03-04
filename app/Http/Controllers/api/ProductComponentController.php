<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductComponentRequest;
use App\Services\ProductComponent\ProductComponentService;
use Illuminate\Http\Response;

class ProductComponentController extends BaseController
{
    public function __construct(private readonly ProductComponentService $componentService)
    {
        parent::__construct();
    }

    /** GET /products/{product}/components */
    public function index(int $productId)
    {
        return response()->json(
            $this->componentService->forProduct($productId),
            Response::HTTP_OK
        );
    }

    /** POST /products/{product}/components */
    public function store(ProductComponentRequest $request, int $productId)
    {
        $component = $this->componentService->create($productId, $request->validated());
        return response()->json($component->load(['component', 'unit']), Response::HTTP_CREATED);
    }

    /** PUT /products/{product}/components/{component} */
    public function update(ProductComponentRequest $request, int $productId, int $id)
    {
        $component = $this->componentService->update($id, $request->validated());
        return response()->json($component, Response::HTTP_OK);
    }

    /** DELETE /products/{product}/components/{component} */
    public function destroy(int $productId, int $id)
    {
        $this->componentService->delete($id);
        return response()->json(['message' => 'Component removed'], Response::HTTP_OK);
    }
}
