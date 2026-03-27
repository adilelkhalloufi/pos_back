<?php

namespace App\Models;

 
class Setting extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'string', // or handle json if needed
    ];

    public const TABLE_NAME = 'settings';

    public const COL_ID = 'id';
 

    public const COL_CREATED_AT = 'created_at';

    public const COL_UPDATED_AT = 'updated_at';
}