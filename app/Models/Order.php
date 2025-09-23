<?php

namespace App\Models;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

class Order extends Model  implements AuditableContract
{
    use HasFactory, Auditable;

    protected $fillable = [
        'order_number',
        'notes',
        'cancel_reason',
        'order_status',
        'payment_status',
        'delivery_status',
        'subtotal_price',
        'coupon_discount',
        'delivery_fee',
        'service_fee',
        'tax_amount',
        'total_price',
        'delivery_scheduled_type',
        'delivery_date',
        'payment_token',
        'customer_id',
        'manager_id',
        'delivery_id',
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
        'delivery_scheduled_type' => DeliveryScheduledType::class,
        'delivery_date' => 'datetime',
    ];

    protected $auditInclude = [
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
        'delivery_scheduled_type',
        'delivery_date',
        'payment_token',
        'customer_id',
        'manager_id',
        'delivery_id',
        'branch_id',
        'coupon_id',
        'payment_method_id',
        'user_address_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_id');
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

    public function auditsLogs(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function isCancelable(): bool
    {
        return !($this->order_status === OrderStatus::FINISHED || $this->order_status === OrderStatus::CANCELLED || $this->order_status === OrderStatus::DELIVERING);
    }
}
