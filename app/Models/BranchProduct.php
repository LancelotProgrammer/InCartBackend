<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Models\Scopes\BranchScope;
use App\Traits\HasPublishAttribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use InvalidArgumentException;
use stdClass;

#[ScopedBy([BranchScope::class])]
class BranchProduct extends Pivot
{
    use HasPublishAttribute;

    public $incrementing = true;

    protected $table = 'branch_product';

    protected $fillable = [
        'price',
        'unit',
        'discount',
        'maximum_order_quantity',
        'minimum_order_quantity',
        'quantity',
        'expires_at',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'unit' => UnitType::class,
        'maximum_order_quantity' => 'decimal:2',
        'minimum_order_quantity' => 'decimal:2',
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted() {}

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->price < 0) {
                throw new InvalidArgumentException('Price cannot be negative.');
            }

            if ($model->discount < 0) {
                throw new InvalidArgumentException('Discount cannot be negative.');
            }
            if ($model->discount > 100) {
                throw new InvalidArgumentException('Discount cannot exceed 100%.');
            }

            if ($model->minimum_order_quantity < 0 || $model->maximum_order_ < 0) {
                throw new InvalidArgumentException('Order quantities cannot be negative.');
            }
            if ($model->maximum_order_quantity <= $model->minimum_order_quantity) {
                throw new InvalidArgumentException('Maximum order quantity must be greater than minimum order quantity.');
            }
            if ($model->maximum_order_quantity > $model->quantity) {
                throw new InvalidArgumentException('Maximum order quantity cannot exceed available stock.');
            }

            if ($model->quantity < 0) {
                throw new InvalidArgumentException('Quantity cannot be negative.');
            }

            if (! empty($model->expires_at)) {
                $expires = Carbon::parse($model->expires_at);
                if ($expires->isPast()) {
                    throw new InvalidArgumentException('The expires_at date must be in the future.');
                }
            }
        });
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
