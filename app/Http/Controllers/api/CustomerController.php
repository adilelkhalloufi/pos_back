<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends BaseController
{
    public function __construct(private readonly CustomerService $customerService)
    {

        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $customers = $this->customerService->getCustomersByStoreId(currentStoreId());
        return response()->json(CustomerResource::collection($customers), Response::HTTP_OK);
    }


    public function store(CustomerRequest $request)
    {
        $customer = $this->customerService->create($request->validated());

        return response()->json(new CustomerResource($customer), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $customer = $this->customerService->getCustomersWithRelations($id, ['orders', 'payments', 'prescriptions']);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(new CustomerResource($customer), Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, $id)
    {
        $updated = $this->customerService->update($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $customer = $this->customerService->findById($id);

        return response()->json(new CustomerResource($customer), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deleted = $this->customerService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
