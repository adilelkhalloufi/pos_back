<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'categories';

 
    public const COL_NAME = 'name';

    public const COL_SLUG = 'slug';

    public const COL_DESCRIPTION = 'description';

    public const COL_USER_ID = 'user_id';

    public const COL_STORE_ID = 'store_id';

 

    // has many products
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
