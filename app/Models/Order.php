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
use InvalidArgumentException;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

class Order extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'order_number',
        'notes',
        'cancel_reason',
        'order_status',
        'payment_status',
        'delivery_status',
        'subtotal_price',
        'discount_price',
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
        'discount_price' => 'decimal:2',
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
        'discount_price',
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
        return ! ($this->order_status === OrderStatus::FINISHED || $this->order_status === OrderStatus::CANCELLED || $this->order_status === OrderStatus::DELIVERING);
    }

    public function isApprovable(): bool
    {
        return $this->order_status === OrderStatus::PENDING;
    }

    public function isDeliverable(): bool
    {
        return $this->order_status === OrderStatus::PROCESSING;
    }

    public function isFinishable(): bool
    {
        return $this->order_status === OrderStatus::DELIVERING;
    }

    public function archive(): void
    {
        OrderArchive::create([
            'archived_at' => now(),
            'order_number' => $this->order_number,
            'notes' => $this->notes,
            'order_status' => $this->order_status->value,
            'payment_status' => $this->payment_status->value,
            'delivery_status' => $this->delivery_status->value,
            'subtotal_price' => $this->subtotal_price,
            'discount_price' => $this->discount_price,
            'delivery_fee' => $this->delivery_fee,
            'service_fee' => $this->service_fee,
            'tax_amount' => $this->tax_amount,
            'total_price' => $this->total_price,
            'delivery_scheduled_type' => $this->delivery_scheduled_type->value,
            'delivery_date' => $this->delivery_date,
            'payment_token' => $this->payment_token,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer' => $this->customer->toJson(),
            'delivery' => $this->delivery?->toJson(),
            'manager' => $this->manager?->toJson(),
            'branch' => $this->branch->toJson(),
            'coupon' => $this->coupon?->toJson(),
            'payment_method' => $this->paymentMethod->toJson(),
            'user_address' => $this->userAddress->toJson(),
            'cart' => $this->carts()->with('cartProducts.product')->get()->toJson(),
        ]);
    }

    public static function getOrderNotificationMessage(Order $order): array
    {
        return match ($order->order_status->value) {
            2 => ['Order Processing', 'Your order is being processed.'],
            3 => ['Order Delivering', 'Your order is on the way!'],
            4 => ['Order Finished', 'Your order has been delivered successfully.'],
            5 => ['Order Cancelled', 'Your order has been cancelled.'],
            default => throw new InvalidArgumentException('Can not create message for this order'),
        };
    }
}
