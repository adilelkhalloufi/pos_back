<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'title',
        'message',
        'severity',
        'is_read',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'customer_id',
        'product_id',
        'store_id',
        'user_id',
        'metadata',
        'triggered_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_resolved' => 'boolean',
        'metadata' => 'array',
        'resolved_at' => 'datetime',
        'triggered_at' => 'datetime',
    ];

    public const TABLE_NAME = 'alerts';

    // Alert Types
    public const TYPE_CUSTOMER_INACTIVE_CHILD = 'customer_inactive_child'; // Under 10, no visit > 6 months
    public const TYPE_CUSTOMER_INACTIVE_MINOR = 'customer_inactive_minor'; // 10-16, no visit > 12 months
    public const TYPE_CUSTOMER_INACTIVE_ADULT = 'customer_inactive_adult'; // 16+, no visit > 24 months
    public const TYPE_PRODUCT_LOW_STOCK = 'product_low_stock';
    public const TYPE_PRODUCT_OUT_OF_STOCK = 'product_out_of_stock';
    public const TYPE_PRODUCT_OVERSTOCK = 'product_overstock';
    public const TYPE_STAFF_PERFORMANCE = 'staff_performance';
    public const TYPE_SYSTEM_MAINTENANCE = 'system_maintenance';

    // Categories
    public const CATEGORY_CUSTOMER = 'customer';
    public const CATEGORY_PRODUCT = 'product';
    public const CATEGORY_STAFF = 'staff';
    public const CATEGORY_SYSTEM = 'system';

    // Severity Levels
    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    // Thresholds (could be configurable)
    public const CUSTOMER_INACTIVE_CHILD_DAYS = 180; // 6 months (under 10)
    public const CUSTOMER_INACTIVE_MINOR_DAYS = 365; // 12 months (10-16)
    public const CUSTOMER_INACTIVE_ADULT_DAYS = 730; // 24 months (16+)
    public const CUSTOMER_CHILD_AGE = 10;
    public const CUSTOMER_MINOR_AGE = 16;

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    // Helper Methods
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function markAsResolved(User $user = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $user?->id,
        ]);
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_LOW => 'green',
            self::SEVERITY_MEDIUM => 'yellow',
            self::SEVERITY_HIGH => 'orange',
            self::SEVERITY_CRITICAL => 'red',
            default => 'gray',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_CUSTOMER_INACTIVE_CHILD, self::TYPE_CUSTOMER_INACTIVE_MINOR, self::TYPE_CUSTOMER_INACTIVE_ADULT => 'heroicon-o-user-minus',
            self::TYPE_PRODUCT_LOW_STOCK => 'heroicon-o-exclamation-triangle',
            self::TYPE_PRODUCT_OUT_OF_STOCK => 'heroicon-o-x-circle',
            self::TYPE_PRODUCT_OVERSTOCK => 'heroicon-o-arrow-trending-up',
            self::TYPE_STAFF_PERFORMANCE => 'heroicon-o-user-group',
            self::TYPE_SYSTEM_MAINTENANCE => 'heroicon-o-cog-6-tooth',
            default => 'heroicon-o-bell',
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_CUSTOMER => 'blue',
            self::CATEGORY_PRODUCT => 'green',
            self::CATEGORY_STAFF => 'purple',
            self::CATEGORY_SYSTEM => 'gray',
            default => 'gray',
        };
    }

    // Static Methods for Creating Alerts
    public static function createCustomerInactiveAlert(Customer $customer, string $type): self
    {
        $age = $customer->birthday ? now()->diffInYears($customer->birthday) : null;
        $lastOrderDate = $customer->last_order_date;
        $daysSinceLastOrder = $lastOrderDate ? now()->diffInDays($lastOrderDate) : null;

        $title = match ($type) {
            self::TYPE_CUSTOMER_INACTIVE_CHILD => "Child Customer Inactive (Age: {$age})",
            self::TYPE_CUSTOMER_INACTIVE_MINOR => "Minor Customer Inactive (Age: {$age})",
            self::TYPE_CUSTOMER_INACTIVE_ADULT => "Adult Customer Inactive (Age: {$age})",
            default => "Customer Inactive (Age: {$age})",
        };

        $message = "Customer {$customer->name} hasn't visited for " .
            ($daysSinceLastOrder ? "{$daysSinceLastOrder} days" : "unknown period") .
            ". Last order: " . ($lastOrderDate ? $lastOrderDate->format('Y-m-d') : 'Never');

        return self::create([
            'type' => $type,
            'category' => self::CATEGORY_CUSTOMER,
            'title' => $title,
            'message' => $message,
            'severity' => self::SEVERITY_MEDIUM,
            'customer_id' => $customer->id,
            'store_id' => $customer->store_id,
            'metadata' => [
                'age' => $age,
                'days_since_last_order' => $daysSinceLastOrder,
                'last_order_date' => $lastOrderDate?->toDateString(),
            ],
            'triggered_at' => now(),
        ]);
    }

    public static function createProductStockAlert(Product $product, Store $store, string $type, float $currentStock, float $threshold = null): self
    {
        $titles = [
            self::TYPE_PRODUCT_LOW_STOCK => 'Low Stock Alert',
            self::TYPE_PRODUCT_OUT_OF_STOCK => 'Out of Stock Alert',
            self::TYPE_PRODUCT_OVERSTOCK => 'Overstock Alert',
        ];

        $severities = [
            self::TYPE_PRODUCT_LOW_STOCK => self::SEVERITY_HIGH,
            self::TYPE_PRODUCT_OUT_OF_STOCK => self::SEVERITY_CRITICAL,
            self::TYPE_PRODUCT_OVERSTOCK => self::SEVERITY_MEDIUM,
        ];

        $messages = [
            self::TYPE_PRODUCT_LOW_STOCK => "Product '{$product->name}' is running low. Current stock: {$currentStock}",
            self::TYPE_PRODUCT_OUT_OF_STOCK => "Product '{$product->name}' is out of stock!",
            self::TYPE_PRODUCT_OVERSTOCK => "Product '{$product->name}' has excess stock. Current stock: {$currentStock}",
        ];

        return self::create([
            'type' => $type,
            'category' => self::CATEGORY_PRODUCT,
            'title' => $titles[$type] ?? 'Stock Alert',
            'message' => $messages[$type] ?? 'Stock alert for product',
            'severity' => $severities[$type] ?? self::SEVERITY_MEDIUM,
            'product_id' => $product->id,
            'store_id' => $store->id,
            'metadata' => [
                'current_stock' => $currentStock,
                'threshold' => $threshold,
                'product_name' => $product->name,
                'store_name' => $store->name,
            ],
            'triggered_at' => now(),
        ]);
    }
}
