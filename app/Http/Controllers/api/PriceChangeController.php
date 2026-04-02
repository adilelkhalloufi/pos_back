<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PriceChangeRequest;
use App\Models\PriceChangeLog;
use App\Services\PriceChange\PriceChangeService;
use Illuminate\Http\Response;

class PriceChangeController extends BaseController
{
    public function __construct(private readonly PriceChangeService $priceChangeService)
    {
        parent::__construct();
    }

    /**
     * POST /price-changes
     * Apply a batch price change to one or many products.
     */
    public function store(PriceChangeRequest $request)
    {
        // {
//     "change_type": "category",
//     "start_date": "2026-04-02",
//     "modification_type": "percentage",
//     "category_values": [
//         {
//             "id": 6,
//             "value": 2
//         }
//     ]
// }
// {
//     "change_type": "article",
//     "start_date": "2026-04-02",
//     "modification_type": "amount",
//     "modification_value": 7,
//     "product_ids": [
//         2
//     ]
 

   }

   
}
