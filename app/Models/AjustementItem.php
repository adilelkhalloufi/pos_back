<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AjustementItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'ajustement_id',
        'product_id',
        'type',
        'quantity',
        'previous_stock',
        'new_stock',
        'note',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'previous_stock' => 'decimal:4',
        'new_stock' => 'decimal:4',
    ];

    public const TABLE_NAME = 'ajustement_items';
    public const COL_ID = 'id';
    public const COL_AJUSTEMENT_ID = 'ajustement_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_TYPE = 'type';
    public const COL_QUANTITY = 'quantity';
    public const COL_PREVIOUS_STOCK = 'previous_stock';
    public const COL_NEW_STOCK = 'new_stock';
    public const COL_NOTE = 'note';

    // Relationships
    public function ajustement()
    {
        return $this->belongsTo(Ajustement::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
