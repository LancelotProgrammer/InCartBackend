<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'notes',
        'order_status',
        'payment_status',
        'delivery_status',
        'subtotal_price',
        'coupon_discount',
        'delivery_fee',
        'service_fee',
        'tax_amount',
        'total_price',
        'delivery_type',
        'delivery_date',
        'payment_token',
        'user_id',
        'branch_id',
        'coupon_id',
        'payment_method_id',
        'user_address_id',
    ];

    protected $casts = [
        'order_status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'delivery_status' => DeliveryStatus::class,
        'subtotal_price' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'delivery_type' => DeliveryType::class,
        'delivery_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function userAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class);
    }

    public function cartProducts(): HasManyThrough
    {
        return $this->hasManyThrough(CartProduct::class, Cart::class);
    }
}
