<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Translatable\HasTranslations;

class CartProduct extends Pivot
{
    use HasFactory, HasTranslations;

    public $incrementing = true;

    protected $table = 'cart_product';

    protected $fillable = ['cart_id', 'product_id', 'title', 'quantity', 'price'];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public array $translatable = ['title'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
