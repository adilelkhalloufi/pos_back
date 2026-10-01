<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class StockController extends BaseController
{
 
    public function __construct(
        private StockService $stockService
        )
    {
         
    }

    /**
     * Get stock movements list
     */
    public function index(StockMovementRequest $request): JsonResponse
    {
        $filters = $request->getFilters();
        $pagination = $request->getPagination();
        $sorting = $request->getSorting();

        // Merge all parameters
        $params = array_merge($filters, $pagination, $sorting);

        $movements = $this->stockService->getStockMovements($params);

        return response()->json($movements, Response::HTTP_OK);
    }

    /**
     * Get stock movements for a specific product.
     * Endpoint: /products/{id}/stock-movements?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function productStockMovements(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'type' => ['nullable'],
            'type.*' => ['nullable', 'string', 'max:100'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $storeId = (int) ($validated['store_id'] ?? currentStoreId());
        $fromDateTime = !empty($validated['from']) ? ($validated['from'] . ' 00:00:00') : null;
        $toDateTime = !empty($validated['to']) ? ($validated['to'] . ' 23:59:59') : null;

        $typesInput = $request->input('type');
        if (is_array($typesInput)) {
            $types = array_values(array_filter(array_map('strval', $typesInput)));
        } elseif (is_string($typesInput) && trim($typesInput) !== '') {
            $types = array_values(array_filter(array_map('trim', explode(',', $typesInput))));
        } else {
            $types = [];
        }

        $saleQuery = DB::table('order_items as oi')
            ->join('order_sales as os', 'os.id', '=', 'oi.order_id')
            ->leftJoin('stores as st', 'st.id', '=', 'os.store_id')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
            ->where('oi.product_id', $id)
            ->where('oi.product_type', Product::class)
            ->whereNull('os.cancelled_at')
            ->selectRaw("\n                CONCAT('sale-', oi.id) as movement_id,\n                oi.product_id as product_id,\n                p.name as product_name,\n                os.store_id as store_id,\n                st.name as store_name,\n                os.store_id as source_store_id,\n                st.name as source_store_name,\n                NULL as target_store_id,\n                NULL as target_store_name,\n                'sale' as type,\n                NULL as custom_type,\n                'out' as direction,\n                oi.qte as quantity,\n                oi.price as unit_cost,\n                oi.total as total_cost,\n                'App\\\\Models\\\\OrderSale' as referenceable_type,\n                os.id as referenceable_id,\n                os.order_number as reference_number,\n                oi.name as note,\n                oi.created_at as created_at\n            ");

        $purchaseQuery = DB::table('purchase_delivery_items as pdi')
            ->join('purchase_deliveries as pd', 'pd.id', '=', 'pdi.purchase_delivery_id')
            ->leftJoin('stores as st', 'st.id', '=', 'pd.store_id')
            ->leftJoin('products as p', 'p.id', '=', 'pdi.product_id')
            ->where('pdi.product_id', $id)
            ->where('pd.status', 'validated')
            ->selectRaw("\n                CONCAT('purchase-', pdi.id) as movement_id,\n                pdi.product_id as product_id,\n                p.name as product_name,\n                pd.store_id as store_id,\n                st.name as store_name,\n                NULL as source_store_id,\n                NULL as source_store_name,\n                pd.store_id as target_store_id,\n                st.name as target_store_name,\n                'purchase' as type,\n                NULL as custom_type,\n                'in' as direction,\n                pdi.accepted_quantity as quantity,\n                pdi.unit_price as unit_cost,\n                pdi.total_price as total_cost,\n                'App\\\\Models\\\\PurchaseDelivery' as referenceable_type,\n                pd.id as referenceable_id,\n                pd.delivery_number as reference_number,\n                pd.delivery_note as note,\n                pdi.created_at as created_at\n            ");

        $transferOutQuery = DB::table('transfert_items as ti')
            ->join('transferts as t', 't.id', '=', 'ti.transfert_id')
            ->leftJoin('stores as ssrc', 'ssrc.id', '=', 't.source_store_id')
            ->leftJoin('stores as stgt', 'stgt.id', '=', 't.target_store_id')
            ->leftJoin('products as p', 'p.id', '=', 'ti.product_id')
            ->where('ti.product_id', $id)
            ->whereNotNull('t.sent_at')
            ->selectRaw("\n                CONCAT('transfer-out-', ti.id) as movement_id,\n                ti.product_id as product_id,\n                p.name as product_name,\n                t.source_store_id as store_id,\n                ssrc.name as store_name,\n                t.source_store_id as source_store_id,\n                ssrc.name as source_store_name,\n                t.target_store_id as target_store_id,\n                stgt.name as target_store_name,\n                'transfer' as type,\n                NULL as custom_type,\n                'out' as direction,\n                ti.quantity as quantity,\n                NULL as unit_cost,\n                NULL as total_cost,\n                'App\\\\Models\\\\Transfert' as referenceable_type,\n                t.id as referenceable_id,\n                t.reference as reference_number,\n                ti.note as note,\n                COALESCE(t.sent_at, ti.created_at) as created_at\n            ");

        $transferInQuery = DB::table('transfert_items as ti')
            ->join('transferts as t', 't.id', '=', 'ti.transfert_id')
            ->leftJoin('stores as ssrc', 'ssrc.id', '=', 't.source_store_id')
            ->leftJoin('stores as stgt', 'stgt.id', '=', 't.target_store_id')
            ->leftJoin('products as p', 'p.id', '=', 'ti.product_id')
            ->where('ti.product_id', $id)
            ->whereNotNull('t.received_at')
            ->selectRaw("\n                CONCAT('transfer-in-', ti.id) as movement_id,\n                ti.product_id as product_id,\n                p.name as product_name,\n                t.target_store_id as store_id,\n                stgt.name as store_name,\n                t.source_store_id as source_store_id,\n                ssrc.name as source_store_name,\n                t.target_store_id as target_store_id,\n                stgt.name as target_store_name,\n                'transfer' as type,\n                NULL as custom_type,\n                'in' as direction,\n                ti.quantity as quantity,\n                NULL as unit_cost,\n                NULL as total_cost,\n                'App\\\\Models\\\\Transfert' as referenceable_type,\n                t.id as referenceable_id,\n                t.reference as reference_number,\n                ti.note as note,\n                COALESCE(t.received_at, ti.created_at) as created_at\n            ");

        $adjustmentQuery = DB::table('ajustement_items as ai')
            ->join('ajustements as a', 'a.id', '=', 'ai.ajustement_id')
            ->leftJoin('stores as st', 'st.id', '=', 'a.target_store_id')
            ->leftJoin('products as p', 'p.id', '=', 'ai.product_id')
            ->where('ai.product_id', $id)
            ->where('a.status', 'completed')
            ->selectRaw("\n                CONCAT('adjustment-', ai.id) as movement_id,\n                ai.product_id as product_id,\n                p.name as product_name,\n                a.target_store_id as store_id,\n                st.name as store_name,\n                a.target_store_id as source_store_id,\n                st.name as source_store_name,\n                NULL as target_store_id,\n                NULL as target_store_name,\n                'adjustment' as type,\n                ai.type as custom_type,\n                CASE WHEN ai.type = 'increase' THEN 'in' ELSE 'out' END as direction,\n                ai.quantity as quantity,\n                NULL as unit_cost,\n                NULL as total_cost,\n                'App\\\\Models\\\\Ajustement' as referenceable_type,\n                a.id as referenceable_id,\n                a.reference as reference_number,\n                COALESCE(ai.note, a.note) as note,\n                ai.created_at as created_at\n            ");

        $inventoryQuery = DB::table('inventary_items as ii')
            ->join('inventaries as i', 'i.id', '=', 'ii.inventary_id')
            ->leftJoin('stores as st', 'st.id', '=', 'i.store_id')
            ->leftJoin('products as p', 'p.id', '=', 'ii.product_id')
            ->where('ii.product_id', $id)
            ->where('i.status', 'completed')
            ->whereNotNull('ii.actual_quantity')
            ->where('ii.difference', '!=', 0)
            ->selectRaw("\n                CONCAT('inventory-', ii.id) as movement_id,\n                ii.product_id as product_id,\n                p.name as product_name,\n                i.store_id as store_id,\n                st.name as store_name,\n                i.store_id as source_store_id,\n                st.name as source_store_name,\n                NULL as target_store_id,\n                NULL as target_store_name,\n                'inventory' as type,\n                ii.status as custom_type,\n                CASE WHEN ii.difference >= 0 THEN 'in' ELSE 'out' END as direction,\n                ABS(ii.difference) as quantity,\n                NULL as unit_cost,\n                NULL as total_cost,\n                'App\\\\Models\\\\Inventary' as referenceable_type,\n                i.id as referenceable_id,\n                i.reference as reference_number,\n                ii.note as note,\n                COALESCE(i.completed_at, ii.created_at) as created_at\n            ");

        $allMovements = $saleQuery
            ->unionAll($purchaseQuery)
            ->unionAll($transferOutQuery)
            ->unionAll($transferInQuery)
            ->unionAll($adjustmentQuery)
            ->unionAll($inventoryQuery);

        $query = DB::query()->fromSub($allMovements, 'm');

        $query->where('m.product_id', $id);

        if (!empty($storeId)) {
            $query->where('m.store_id', $storeId);
        }

        if (!empty($fromDateTime)) {
            $query->where('m.created_at', '>=', $fromDateTime);
        }

        if (!empty($toDateTime)) {
            $query->where('m.created_at', '<=', $toDateTime);
        }

        if (!empty($types)) {
            $query->where(function ($q) use ($types) {
                $q->whereIn('m.type', $types)
                    ->orWhereIn('m.custom_type', $types);
            });
        }

        $query->orderByDesc('m.created_at');

        $perPage = (int) ($validated['per_page'] ?? 50);
        $page = (int) ($validated['page'] ?? 1);
        $movements = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'product_id' => $id,
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
            'filters' => [
                'type' => $types,
                'store_id' => $storeId,
            ],
            'data' => $movements->items(),
            'pagination' => [
                'current_page' => $movements->currentPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
                'last_page' => $movements->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

   

  
 
 

   

  
}
