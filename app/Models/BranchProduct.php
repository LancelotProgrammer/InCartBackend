<?php

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BranchProduct extends Pivot
{
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

    public function getDiscountPriceAttribute(): mixed
    {
        if (empty($this->discount) || $this->discount <= 0) {
            return $this->price;
        }
        $discountedPrice = $this->price - $this->price * ($this->discount / 100);

        return round($discountedPrice, 2);
    }
}
