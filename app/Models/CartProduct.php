<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use InvalidArgumentException;

class CartProduct extends Pivot
{
    public $incrementing = true;

    protected $table = 'cart_product';

    protected $fillable = ['cart_id', 'product_id', 'count'];

    protected $casts = [
        'count' => 'decimal:2',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (CartProduct $cartProduct) {
            $branchId = $cartProduct->cart->order->branch_id;

            $branchProduct = BranchProduct::where('product_id', $cartProduct->product_id)
                ->where('branch_id', $branchId)
                ->first();

            if (! $branchProduct) {
                throw new InvalidArgumentException('Product is not available in this branch.');
            }

            $count = $cartProduct->count;

            if ($count < $branchProduct->minimum_order_quantity) {
                throw new InvalidArgumentException(
                    "Count cannot be less than minimum order quantity ({$branchProduct->minimum_order_quantity})."
                );
            }

            if ($count > $branchProduct->maximum_order_quantity) {
                throw new InvalidArgumentException(
                    "Count cannot exceed maximum order quantity ({$branchProduct->maximum_order_quantity})."
                );
            }

            if ($count > $branchProduct->quantity) {
                throw new InvalidArgumentException(
                    "Count cannot exceed available stock ({$branchProduct->quantity})."
                );
            }
        });
    }
}
