<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ajustement extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'store_id',
        'reason',
        'note',
        'user_id',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public const TABLE_NAME = 'ajustements';
    public const COL_ID = 'id';
    public const COL_REFERENCE = 'reference';
    public const COL_STORE_ID = 'store_id';
    public const COL_TARGET_STORE_ID = 'target_store_id';
    public const COL_REASON = 'reason';
    public const COL_NOTE = 'note';
    public const COL_USER_ID = 'user_id';
    public const COL_META = 'meta';
    public const COL_STATUS = 'status';

    // Relationships
    public function targetStore()
    {
        return $this->belongsTo(Store::class, 'target_store_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(AjustementItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
