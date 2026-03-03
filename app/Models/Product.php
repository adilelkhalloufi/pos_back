<?php

namespace App\Models;

use App\Services\Product\ProductService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reference',
        'codebar',
        'slug',
        'description',
        'image',
        'price',
        'stock_min',
        'stock_max',
        'is_active',
        'archive',
        'quantity',
        'brand_id',
        'category_id',
        'user_id',
        'store_id',
    ];

    public const TABLE_NAME = 'products';

    public const COL_ID = 'id';

    public const COL_NAME = 'name';

    public const COL_REFERENCE = 'reference';

    public const COL_CODEBAR = 'codebar';

    public const COL_SLUG = 'slug';

    public const COL_DESCRIPTION = 'description';

    public const COL_IMAGE = 'image';

    public const COL_PRICE = 'price';

    public const COL_STOCK_MIN = 'stock_min';

    public const COL_STOCK_MAX = 'stock_max';

    public const COL_IS_ACTIVE = 'is_active';

    public const COL_BRAND_ID = 'brand_id';

    public const COL_CATEGORY_ID = 'category_id';

    public const COL_USER_ID = 'user_id';

    public const COL_STORE_ID = 'store_id';

    public const COL_ARCHIVE = 'archive';

    // casts
    protected $casts = [
        self::COL_PRICE => 'float',
        self::COL_IS_ACTIVE => 'boolean',
        self::COL_ARCHIVE => 'boolean',
        self::COL_STOCK_MAX => 'integer',
        self::COL_STOCK_MIN => 'integer',
    ];

    // belong to brand
    public function brand()
    {
        return $this->belongsTo(Brands::class);
    }

    // belong to category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // belong to order details
    // has many order sale
    public function sales()
    {
        return $this->hasMany(OrderItems::class);
    }

    // has many order purshar
    public function purchase()
    {
        return $this->hasMany(OrderPurchaseItems::class);
    }

    public function invoice()
    {
        return $this->hasMany(Invoice::class);
    }

    public function store()
    {
        return $this->hasMany(StoreProducts::class, self::COL_ID);
    }

    public function orderItems()
    {
        return $this->morphMany(OrderItems::class, 'product');
    }
}
