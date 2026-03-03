<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransfertItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'transfert_id',
        'product_id',
        'quantity',
        'note',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public const TABLE_NAME = 'transfert_items';
    public const COL_ID = 'id';
    public const COL_TRANSFERT_ID = 'transfert_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_QUANTITY = 'quantity';
    public const COL_NOTE = 'note';

    // Relationships
    public function transfert()
    {
        return $this->belongsTo(Transfert::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
