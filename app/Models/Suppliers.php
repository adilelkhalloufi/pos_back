<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Suppliers extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'suppliers';


    public const COL_FIRST_NAME = 'first_name';

    public const COL_LAST_NAME = 'last_name';

    public const COL_COMPANY_NAME = 'company_name';

    public const COL_EMAIL = 'email';

    public const COL_PHONE = 'phone';

    public const COL_ADDRESS = 'address';

    public const COL_CITY = 'city';

    public const COL_COUNTRY = 'country';

    public const COL_ZIP_CODE = 'zip_code';

    public const COL_USER_ID = 'user_id';

    public const COL_STORE_ID = 'store_id';



    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
