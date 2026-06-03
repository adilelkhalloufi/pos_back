<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseDeliveryItem extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'purchase_delivery_items';
    public const COL_ID = 'id';
    public const COL_PURCHASE_DELIVERY_ID = 'purchase_delivery_id';
    public const COL_ORDER_PURCHASE_ITEM_ID = 'order_purchase_item_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_ORDERED_QUANTITY = 'ordered_quantity';
    public const COL_DELIVERED_QUANTITY = 'delivered_quantity';
    public const COL_ACCEPTED_QUANTITY = 'accepted_quantity';
    public const COL_REJECTED_QUANTITY = 'rejected_quantity';
    public const COL_UNIT_PRICE = 'unit_price';
    public const COL_TOTAL_PRICE = 'total_price';
    public const COL_REJECTION_REASON = 'rejection_reason';
    public const COL_BATCH_NUMBER = 'batch_number';
    public const COL_EXPIRY_DATE = 'expiry_date';

    /**
     * Get the delivery that owns this item
     */
    public function purchaseDelivery()
    {
        return $this->belongsTo(PurchaseDelivery::class, self::COL_PURCHASE_DELIVERY_ID, PurchaseDelivery::COL_ID);
    }

    /**
     * Get the purchase order item
     */
    public function orderPurchaseItem()
    {
        return $this->belongsTo(OrderPurchaseItems::class, self::COL_ORDER_PURCHASE_ITEM_ID, OrderPurchaseItems::COL_ID);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, self::COL_PRODUCT_ID, Product::COL_ID);
    }
}
