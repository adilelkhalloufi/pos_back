<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitConversion extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'unit_conversions';
    public const COL_FROM_UNIT_ID = 'from_unit_id';
    public const COL_TO_UNIT_ID = 'to_unit_id';
    public const COL_CONVERSION_FACTOR = 'conversion_factor';
    public const COL_STORE_ID = 'store_id';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        self::COL_FROM_UNIT_ID,
        self::COL_TO_UNIT_ID,
        self::COL_CONVERSION_FACTOR,
        self::COL_STORE_ID,
    ];

    protected $casts = [
        self::COL_CONVERSION_FACTOR => 'decimal:6',
    ];

    /**
     * Get the source unit for this conversion
     */
    public function fromUnit()
    {
        return $this->belongsTo(Unit::class, self::COL_FROM_UNIT_ID);
    }

    /**
     * Get the target unit for this conversion
     */
    public function toUnit()
    {
        return $this->belongsTo(Unit::class, self::COL_TO_UNIT_ID);
    }

    /**
     * Get the store this conversion belongs to (nullable for global conversions)
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
