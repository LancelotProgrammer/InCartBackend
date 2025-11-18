<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Services\CacheService;
use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;
use stdClass;

#[ScopedBy([BranchScope::class])]
class BranchProduct extends Pivot implements AuditableContract
{
    use Auditable, HasPublishAttribute;

    public $incrementing = true;

    protected $table = 'branch_product';

    protected $fillable = [
        'branch_id',
        'product_id',
        'price',
        'discount',
        'maximum_order_quantity',
        'minimum_order_quantity',
        'quantity',
        'expires_at',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'maximum_order_quantity' => 'decimal:2',
        'minimum_order_quantity' => 'decimal:2',
        'quantity' => 'decimal:2',
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $auditInclude = [
        'branch_id',
        'product_id',
        'price',
        'discount',
        'maximum_order_quantity',
        'minimum_order_quantity',
        'quantity',
        'expires_at',
        'published_at',
    ];

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    protected static function booted(): void
    {
        static::created(function (BranchProduct $productBranch) {
            Log::channel('app_log')->info('Models: created new branch product and deleted home cache.');
            return CacheService::deleteHomeCache();
        });
        static::updated(function (BranchProduct $productBranch) {
            Log::channel('app_log')->info('Models: updated branch product and deleted home cache.');
            return CacheService::deleteHomeCache();
        });
        static::deleted(function (BranchProduct $productBranch) {
            Log::channel('app_log')->info('Models: deleted branch product and deleted home cache.');
            return CacheService::deleteHomeCache();
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getDiscountPriceAttribute(): mixed
    {
        return self::getDiscountPrice($this);
    }

    public static function getDiscountPriceValue(stdClass $branchProduct): mixed
    {
        return self::getDiscountPrice($branchProduct);
    }

    public static function getDiscountPrice(stdClass|self $branchProduct): mixed
    {
        if (empty($branchProduct->discount) || $branchProduct->discount <= 0) {
            return $branchProduct->price;
        }
        $discountedPrice = $branchProduct->price - $branchProduct->price * ($branchProduct->discount / 100);

        return round($discountedPrice, 2);
    }

    protected function maximumOrderQuantity(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => self::getMaximumOrderQuantity((object) $attributes),
            set: fn ($value) => $value,
        );
    }

    public static function getMaximumOrderQuantityValue(stdClass|self $branchProduct): mixed
    {
        return self::getMaximumOrderQuantity($branchProduct);
    }

    public static function getMaximumOrderQuantity(stdClass|self $branchProduct): mixed
    {
        return min($branchProduct->maximum_order_quantity, $branchProduct->quantity);
    }
}
