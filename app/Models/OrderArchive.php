<?php

namespace App\Models;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class OrderArchive extends Model
{
    protected $casts = [
        'archived_at' => 'datetime',
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
        'customer' => 'array',
        'delivery' => 'array',
        'manager' => 'array',
        'branch' => 'array',
        'coupon' => 'array',
        'payment_method' => 'array',
        'user_address' => 'array',
        'cart' => 'array',
    ];
}
