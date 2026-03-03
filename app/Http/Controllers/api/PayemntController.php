<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PayementResource;
use App\Services\Payement\PayementService;
use App\Traits\AppliesDateFilters;
use Illuminate\Http\Request;

class PayemntController extends BaseController
{
    use AppliesDateFilters;

    public function __construct(
        private readonly PayementService $payementService
    ) {}
    public function caisse(Request $request)
    {
        $query = $this->the_store()
            ->payments()
            ->getQuery()
            ->with(['order_sale', 'customer', 'mode_payemnt']);

        // Apply date filter if provided, otherwise get latest 25 records
        $query = $this->applyDateFilter($query, $request, 25);

        $payements = $query->get();


        return response()->json(PayementResource::collection($payements), 200);
    }
}
