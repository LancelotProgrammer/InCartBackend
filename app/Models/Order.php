<?php

namespace App\Models;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderDeleting;
use App\Models\Scopes\BranchScope;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use InvalidArgumentException;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

#[ScopedBy([BranchScope::class])]
class Order extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'order_number',
        'notes',
        'cancel_reason',
        'metadata',
        'order_status',
        'payment_status',
        'delivery_status',
        'subtotal_price',
        'discount_price',
        'delivery_fee',
        'service_fee',
        'tax_amount',
        'total_price',
        'payed_price',
        'delivery_scheduled_type',
        'delivery_date',
        'user_address_title',
        'payment_token',
        'cancelled_by_id',
        'customer_id',
        'manager_id',
        'delivery_id',
        'branch_id',
        'coupon_id',
        'payment_method_id',
        'user_address_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'order_status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'delivery_status' => DeliveryStatus::class,
        'subtotal_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'payed_price' => 'decimal:2',
        'delivery_scheduled_type' => DeliveryScheduledType::class,
        'delivery_date' => 'datetime',
    ];

    protected $auditInclude = [
        'order_number',
        'notes',
        'cancel_reason',
        'metadata',
        'order_status',
        'payment_status',
        'delivery_status',
        'subtotal_price',
        'discount_price',
        'delivery_fee',
        'service_fee',
        'tax_amount',
        'total_price',
        'payed_price',
        'delivery_scheduled_type',
        'delivery_date',
        'user_address_title',
        'payment_token',
        'cancelled_by_id',
        'customer_id',
        'manager_id',
        'delivery_id',
        'branch_id',
        'coupon_id',
        'payment_method_id',
        'user_address_id',
    ];

    protected static function booted(): void
    {
        static::deleting(fn (Order $order) => event(new OrderDeleting($order)));
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_id');
    }

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

    public function forceApproveOrder(): HasOne
    {
        return $this->hasOne(ForceApproveOrder::class);
    }

    public function isPayOnDelivery(): bool
    {
        return $this->paymentMethod->code === PaymentMethod::PAY_ON_DELIVERY_CODE;
    }

    public function isCancelable(): bool
    {
        return $this->order_status === OrderStatus::PENDING || $this->order_status === OrderStatus::PROCESSING;
    }

    public function isApprovable(): bool
    {
        return $this->order_status === OrderStatus::PENDING;
    }

    public function isForceApprovable(): bool
    {
        return $this->order_status === OrderStatus::PENDING &&
            (
                ! $this->delivery_date->inApplicationTimezone()->isSameDay(now()->inApplicationTimezone()) ||
                (! $this->isPayOnDelivery() && $this->payment_status === PaymentStatus::UNPAID) ||
                ! SettingsService::isSystemOnline()
            );
    }

    public function isDeliverable(): bool
    {
        return $this->order_status === OrderStatus::PROCESSING;
    }

    public function isFinishable(): bool
    {
        return $this->order_status === OrderStatus::DELIVERING;
    }

    public function isClosable(): bool
    {
        return $this->order_status === OrderStatus::FINISHED;
    }

    public function isArchivable(): bool
    {
        return $this->order_status === OrderStatus::CANCELLED || $this->order_status === OrderStatus::CLOSED;
    }

    public function archive(): void
    {
        OrderArchive::create([
            'archived_at' => now(),
            'order_number' => $this->order_number,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'order_status' => $this->order_status->value,
            'payment_status' => $this->payment_status->value,
            'delivery_status' => $this->delivery_status->value,
            'subtotal_price' => $this->subtotal_price,
            'discount_price' => $this->discount_price,
            'delivery_fee' => $this->delivery_fee,
            'service_fee' => $this->service_fee,
            'tax_amount' => $this->tax_amount,
            'total_price' => $this->total_price,
            'payed_price' => $this->payed_price,
            'delivery_scheduled_type' => $this->delivery_scheduled_type->value,
            'delivery_date' => $this->delivery_date,
            'user_address_title' => $this->user_address_title,
            'payment_token' => $this->payment_token,
            'cancelled_by' => $this->cancelledBy?->toArray(),
            'customer' => $this->customer->toArray(),
            'delivery' => $this->delivery?->toArray(),
            'manager' => $this->manager?->toArray(),
            'branch' => $this->branch->toArray(),
            'coupon' => $this->coupon?->toArray(),
            'payment_method' => $this->paymentMethod->toArray(),
            'user_address' => $this->userAddress?->toArray(),
            'cart' => $this->carts()->with('cartProducts.product')->get()->toArray(),
        ]);
    }

    public function createForceApproveOrder(string $reason, int $userId, array $types): void
    {
        ForceApproveOrder::create([
            'types' => $types,
            'reason' => $reason,
            'order_id' => $this->id,
            'user_id' => $userId,
        ]);
    }

    public static function getUserOrderNotificationMessage(Order $order): array
    {
        return match ($order->order_status->value) {
            OrderStatus::PROCESSING->value => ['Order Processing', 'Your order is being processed.'],
            OrderStatus::DELIVERING->value => ['Order Delivering', 'Your order is on the way!'],
            OrderStatus::FINISHED->value => ['Order Finished', 'Your order has been delivered successfully.'],
            OrderStatus::CANCELLED->value => ['Order Cancelled', 'Your order has been cancelled.'],
            default => throw new InvalidArgumentException('Can not create message for this order'),
        };
    }

    public function auditsLogs(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}
