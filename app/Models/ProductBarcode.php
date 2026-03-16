<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBarcode extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'barcode',
        'is_primary',
    ];

    public const TABLE_NAME = 'product_barcodes';

    public const COL_ID = 'id';

    public const COL_PRODUCT_ID = 'product_id';

    public const COL_BARCODE = 'barcode';

    public const COL_IS_PRIMARY = 'is_primary';

    protected $casts = [
        self::COL_IS_PRIMARY => 'boolean',
    ];

    /**
     * Get the product that owns the barcode.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
