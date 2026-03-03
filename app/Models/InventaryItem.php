<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventaryItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'inventary_id',
        'product_id',
        'expected_quantity',
        'actual_quantity',
        'difference',
        'status',
        'note',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:4',
        'actual_quantity' => 'decimal:4',
        'difference' => 'decimal:4',
    ];

    public const TABLE_NAME = 'inventary_items';
    public const COL_ID = 'id';
    public const COL_INVENTARY_ID = 'inventary_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_EXPECTED_QUANTITY = 'expected_quantity';
    public const COL_ACTUAL_QUANTITY = 'actual_quantity';
    public const COL_DIFFERENCE = 'difference';
    public const COL_STATUS = 'status';
    public const COL_NOTE = 'note';

    // Relationships
    public function inventary()
    {
        return $this->belongsTo(Inventary::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
