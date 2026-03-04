<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrintProfile extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'print_profiles';
    public const COL_ID = 'id';
    public const COL_NAME = 'name';
    public const COL_PRINTER_NAME = 'printer_name';
    public const COL_CONNECTION_TYPE = 'connection_type';
    public const COL_COM_PORT = 'com_port';
    public const COL_MAX_COPIES = 'max_copies';
    public const COL_IS_DEFAULT = 'is_default';
    public const COL_IS_ACTIVE = 'is_active';
    public const COL_STORE_ID = 'store_id';

    protected $fillable = [
        self::COL_NAME,
        self::COL_PRINTER_NAME,
        self::COL_CONNECTION_TYPE,
        self::COL_COM_PORT,
        self::COL_MAX_COPIES,
        self::COL_IS_DEFAULT,
        self::COL_IS_ACTIVE,
        self::COL_STORE_ID,
    ];

    protected $casts = [
        self::COL_IS_DEFAULT => 'boolean',
        self::COL_IS_ACTIVE => 'boolean',
        self::COL_MAX_COPIES => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'print_profile_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
