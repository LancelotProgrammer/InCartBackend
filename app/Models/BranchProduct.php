<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Services\Cache;
use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use stdClass;

#[ScopedBy([BranchScope::class])]
class BranchProduct extends Pivot
{
    use HasPublishAttribute;

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

    protected static function booted(): void
    {
        static::created(fn(Product $productBranch) => Cache::deleteHomeCache());
        static::updated(fn(Product $productBranch) => Cache::deleteHomeCache());
        static::deleted(fn(Product $productBranch) => Cache::deleteHomeCache());
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
}
