<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TheoreticalConsumption extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'theoretical_consumption';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_STORE_ID = 'store_id';
    public const COL_DATE = 'date';
    public const COL_THEORETICAL_QUANTITY = 'theoretical_quantity';
    public const COL_ACTUAL_QUANTITY = 'actual_quantity';
    public const COL_VARIANCE = 'variance';
    public const COL_VARIANCE_PERCENTAGE = 'variance_percentage';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        self::COL_PRODUCT_ID,
        self::COL_STORE_ID,
        self::COL_DATE,
        self::COL_THEORETICAL_QUANTITY,
        self::COL_ACTUAL_QUANTITY,
        self::COL_VARIANCE,
        self::COL_VARIANCE_PERCENTAGE,
    ];

    protected $casts = [
        self::COL_DATE => 'date',
        self::COL_THEORETICAL_QUANTITY => 'decimal:4',
        self::COL_ACTUAL_QUANTITY => 'decimal:4',
        self::COL_VARIANCE => 'decimal:4',
        self::COL_VARIANCE_PERCENTAGE => 'decimal:2',
    ];

    /**
     * Get the product this consumption record belongs to
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the store this consumption record belongs to
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Calculate and update variance
     */
    public function calculateVariance(): void
    {
        $this->{self::COL_VARIANCE} = $this->{self::COL_ACTUAL_QUANTITY} - $this->{self::COL_THEORETICAL_QUANTITY};
        
        if ($this->{self::COL_THEORETICAL_QUANTITY} > 0) {
            $this->{self::COL_VARIANCE_PERCENTAGE} = ($this->{self::COL_VARIANCE} / $this->{self::COL_THEORETICAL_QUANTITY}) * 100;
        } else {
            $this->{self::COL_VARIANCE_PERCENTAGE} = 0;
        }
    }
}
