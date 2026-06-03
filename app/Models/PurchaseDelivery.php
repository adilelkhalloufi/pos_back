<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseDelivery extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'purchase_deliveries';
    public const COL_ID = 'id';
    public const COL_DELIVERY_NUMBER = 'delivery_number';
    public const COL_ORDER_PURCHASE_ID = 'order_purchase_id';
    public const COL_STORE_ID = 'store_id';
    public const COL_SUPPLIER_ID = 'supplier_id';
    public const COL_RECEIVED_BY = 'received_by';
    public const COL_DELIVERY_DATE = 'delivery_date';
    public const COL_SUPPLIER_DELIVERY_NOTE = 'supplier_delivery_note';
    public const COL_TRANSPORT_COMPANY = 'transport_company';
    public const COL_DRIVER_NAME = 'driver_name';
    public const COL_VEHICLE_PLATE = 'vehicle_plate';
    public const COL_DELIVERY_NOTE = 'delivery_note';
    public const COL_QUALITY_CHECK_NOTE = 'quality_check_note';
    public const COL_HAS_ISSUES = 'has_issues';
    public const COL_STATUS = 'status';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the purchase order that owns this delivery
     */
    public function orderPurchase()
    {
        return $this->belongsTo(OrderPurchase::class, self::COL_ORDER_PURCHASE_ID, OrderPurchase::COL_ID);
    }

    /**
     * Get the delivery items for this delivery
     */
    public function deliveryItems()
    {
        return $this->hasMany(PurchaseDeliveryItem::class, 'purchase_delivery_id');
    }

    /**
     * Get the user who received this delivery
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, self::COL_RECEIVED_BY, 'id');
    }

    /**
     * Get the store for this delivery
     */
    public function store()
    {
        return $this->belongsTo(Store::class, self::COL_STORE_ID, Store::COL_ID);
    }

    /**
     * Get the supplier for this delivery
     */
    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, self::COL_SUPPLIER_ID, 'id');
    }
}
