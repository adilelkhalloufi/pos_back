<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventary extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'store_id',
        'status',
        'started_at',
        'completed_at',
        'created_by',
        'completed_by',
        'total_items',
        'checked_items',
        'total_difference',
        'note',
        'meta',
        'target_store_id'
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_difference' => 'decimal:4',
    ];

    public const TABLE_NAME = 'inventaries';
    public const COL_ID = 'id';
    public const COL_REFERENCE = 'reference';
    public const COL_STORE_ID = 'store_id';
    public const COL_TARGET_STORE_ID = 'target_store_id';
    public const COL_STATUS = 'status';
    public const COL_STARTED_AT = 'started_at';
    public const COL_COMPLETED_AT = 'completed_at';
    public const COL_CREATED_BY = 'created_by';
    public const COL_COMPLETED_BY = 'completed_by';
    public const COL_TOTAL_ITEMS = 'total_items';
    public const COL_CHECKED_ITEMS = 'checked_items';
    public const COL_TOTAL_DIFFERENCE = 'total_difference';
    public const COL_NOTE = 'note';
    public const COL_META = 'meta';

    // Relationships
    public function targetStore()
    {
        return $this->belongsTo(Store::class, 'target_store_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function items()
    {
        return $this->hasMany(InventaryItem::class);
    }
}
