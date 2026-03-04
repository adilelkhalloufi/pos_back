<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportRow extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'import_rows';
    public const COL_ID = 'id';
    public const COL_IMPORT_ID = 'import_id';
    public const COL_ROW_NUMBER = 'row_number';
    public const COL_RAW_DATA = 'raw_data';
    public const COL_ERRORS = 'errors';
    public const COL_STATUS = 'status';

    protected $fillable = [
        self::COL_IMPORT_ID,
        self::COL_ROW_NUMBER,
        self::COL_RAW_DATA,
        self::COL_ERRORS,
        self::COL_STATUS,
    ];

    protected $casts = [
        self::COL_RAW_DATA => 'array',
        self::COL_ERRORS => 'array',
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }
}
