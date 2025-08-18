<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
}
