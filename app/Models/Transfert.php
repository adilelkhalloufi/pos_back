<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transfert extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'source_store_id',
        'target_store_id',
        'status',
        'created_by',
        'received_by',
        'sent_at',
        'received_at',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public const TABLE_NAME = 'transferts';
    public const COL_ID = 'id';
    public const COL_REFERENCE = 'reference';
    public const COL_STORE_ID = 'store_id';
    public const COL_SOURCE_STORE_ID = 'source_store_id';
    public const COL_TARGET_STORE_ID = 'target_store_id';
    public const COL_STATUS = 'status';
    public const COL_CREATED_BY = 'created_by';
    public const COL_RECEIVED_BY = 'received_by';
    public const COL_SENT_AT = 'sent_at';
    public const COL_RECEIVED_AT = 'received_at';
    public const COL_NOTE = 'note';
    public const COL_META = 'meta';

    

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function sourceStore()
    {
        return $this->belongsTo(Store::class, 'source_store_id');
    }

    public function targetStore()
    {
        return $this->belongsTo(Store::class, 'target_store_id');
    }

    public function items()
    {
        return $this->hasMany(TransfertItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
