<?php

namespace App\Services\Sale;

use App\Models\OrderItems;
use App\Models\OrderSale;
use App\Models\Product;
use App\Models\TypeGlasses;

class OrderSaleDetailService
{
    // Your service methods go here
    public function __construct() {}

    public function create(array $item, OrderSale $order)
    {
        // Determine product type and ID for polymorphic relationship
        $productId = null;
        $productType = null;

        if (!empty($item[OrderItems::COL_PRODUCT_ID])) {
            $productId = $item[OrderItems::COL_PRODUCT_ID];
            $productType = Product::class;
        } else {
            $productId = $item[TypeGlasses::COL_ID] ?? null;
            $productType = TypeGlasses::class;
        }

        return OrderItems::create([
            OrderItems::COL_NAME       => $item[OrderItems::COL_NAME] ?? '',
            OrderItems::COL_ORDER_ID   => $order->getAttribute(OrderSale::COL_ID),
            OrderItems::COL_STORE_ID   => $order->getAttribute(OrderSale::COL_STORE_ID),
            OrderItems::COL_PRODUCT_ID => $productId,
            OrderItems::COL_PRODUCT_TYPE => $productType,
            OrderItems::COL_PRICE      => $item[OrderItems::COL_PRICE],
            OrderItems::COL_QTE        => $item[OrderItems::COL_QTE] ?? 1,
            OrderItems::COL_TOTAL      => ($item[OrderItems::COL_QTE] ?? 1) * $item[OrderItems::COL_PRICE],
            OrderItems::COL_INVOICE_PRICE => $item[OrderItems::COL_PRICE] ?? 0,
        ]);
    }

    public function addRange(array $items, OrderSale $order)
    {


        foreach ($items as $item) {
            $createdItems[] = $this->create($item, $order);
        }




        return $createdItems;
    }
}
