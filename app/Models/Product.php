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
        'supplier_code',
        'codebar',
        'slug',
        'description',
        'image',
        'price',
        'price_buy',
        'price_sell_1',
        'price_sell_2',
        'stock_min',
        'stock_max',
        'is_active',
        'is_stockable',
        'archive',
        'quantity',
         'category_id',
        'unit_id',
        'print_profile_id',
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

 
    public const COL_CATEGORY_ID = 'category_id';

    public const COL_USER_ID = 'user_id';

    public const COL_STORE_ID = 'store_id';

    public const COL_ARCHIVE = 'archive';

    public const COL_SUPPLIER_CODE = 'supplier_code';

    public const COL_PRICE_BUY = 'price_buy';

    public const COL_PRICE_SELL_1 = 'price_sell_1';

    public const COL_PRICE_SELL_2 = 'price_sell_2';

    public const COL_IS_STOCKABLE = 'is_stockable';

    public const COL_UNIT_ID = 'unit_id';

    public const COL_PRINT_PROFILE_ID = 'print_profile_id';

    // casts
    protected $casts = [
        self::COL_PRICE => 'float',
        self::COL_PRICE_BUY => 'float',
        self::COL_PRICE_SELL_1 => 'float',
        self::COL_PRICE_SELL_2 => 'float',
        self::COL_IS_ACTIVE => 'boolean',
        self::COL_IS_STOCKABLE => 'boolean',
        self::COL_ARCHIVE => 'boolean',
        self::COL_STOCK_MAX => 'integer',
        self::COL_STOCK_MIN => 'integer',
    ];

   

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

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function printProfile()
    {
        return $this->belongsTo(PrintProfile::class, 'print_profile_id');
    }

    public function components()
    {
        return $this->hasMany(ProductComponent::class, 'product_id');
    }

    public function usedIn()
    {
        return $this->hasMany(ProductComponent::class, 'component_id');
    }

    public function priceChangeLogs()
    {
        return $this->hasMany(PriceChangeLog::class);
    }
}
