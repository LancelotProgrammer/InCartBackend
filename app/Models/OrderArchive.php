<?php

namespace App\Models;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class OrderArchive extends Model
{
    protected $fillable = [
        'archived_at',
        'order_number',
        'cancel_reason',
        'metadata',
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
        'payed_price',
        'delivery_scheduled_type',
        'delivery_date',
        'user_address_title',
        'payment_token',
        'cancelled_by',
        'customer',
        'delivery',
        'manager',
        'branch',
        'coupon',
        'payment_method',
        'user_address',
        'cart',
        'audit',
    ];

    protected $casts = [
        'metadata' => 'array',
        'archived_at' => 'datetime',
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
        'customer' => 'array',
        'delivery' => 'array',
        'manager' => 'array',
        'branch' => 'array',
        'coupon' => 'array',
        'payment_method' => 'array',
        'user_address' => 'array',
        'cart' => 'array',
        'audit' => 'array',
    ];
}
