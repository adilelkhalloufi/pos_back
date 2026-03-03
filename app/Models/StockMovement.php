<?php

namespace App\Models;

use App\Enums\EnumStockMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends BaseModel
{
    use HasFactory;

    protected $guarded = [];



    public const TABLE_NAME = 'stock_movements';

    public const COL_PRODUCT_ID = 'product_id';
    public const COL_SOURCE_STORE_ID = 'source_store_id';
    public const COL_TARGET_STORE_ID = 'target_store_id';
    public const COL_STORE_ID = 'store_id';
    public const COL_TYPE = 'type';
    public const COL_DIRECTION = 'direction';
    public const COL_QUANTITY = 'quantity';
    public const COL_UNIT_COST = 'unit_cost';
    public const COL_TOTAL_COST = 'total_cost';
    public const COL_PREVIOUS_STOCK = 'previous_stock';
    public const COL_NEW_STOCK = 'new_stock';
    public const COL_REFERENCEABLE_TYPE = 'referenceable_type';
    public const COL_REFERENCEABLE_ID = 'referenceable_id';
    public const COL_USER_ID = 'user_id';
    public const COL_NOTE = 'note';
    public const COL_META = 'meta';

    // Movement Types
    public const TYPE_SALE = 'sale';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_INVENTORY = 'inventory';

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'source_store_id');
    }
    public function targetStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'target_store_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function referenceable()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeBySourceStore($query, $storeId)
    {
        return $query->where(self::COL_SOURCE_STORE_ID, $storeId);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where(self::COL_PRODUCT_ID, $productId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where(self::COL_TYPE, $type);
    }

    public function scopeValidated($query)
    {
        // Assuming there's a validated_at column or similar
        // For now, just return all - you may need to adjust based on your validation logic
        return $query;
    }

    public function scopePending($query)
    {
        // Assuming there's a status column or similar
        // For now, just return all - you may need to adjust based on your pending logic
        return $query;
    }
}
