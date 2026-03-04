<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Import extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'imports';
    public const COL_ID = 'id';
    public const COL_TYPE = 'type';
    public const COL_FILE_PATH = 'file_path';
    public const COL_STATUS = 'status';
    public const COL_TOTAL_ROWS = 'total_rows';
    public const COL_VALID_ROWS = 'valid_rows';
    public const COL_ERROR_ROWS = 'error_rows';
    public const COL_COMMITTED_ROWS = 'committed_rows';
    public const COL_SUPPLIER_ID = 'supplier_id';
    public const COL_USER_ID = 'user_id';
    public const COL_STORE_ID = 'store_id';

    public const STATUS_PENDING = 'pending';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_COMMITTED = 'committed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        self::COL_TYPE,
        self::COL_FILE_PATH,
        self::COL_STATUS,
        self::COL_TOTAL_ROWS,
        self::COL_VALID_ROWS,
        self::COL_ERROR_ROWS,
        self::COL_COMMITTED_ROWS,
        self::COL_SUPPLIER_ID,
        self::COL_USER_ID,
        self::COL_STORE_ID,
    ];

    public function rows()
    {
        return $this->hasMany(ImportRow::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, self::COL_SUPPLIER_ID);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
