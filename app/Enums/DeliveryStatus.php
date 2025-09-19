<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DeliveryStatus: int implements HasLabel
{
    case SCHEDULED = 1;
    case NOT_SHIPPED = 2;
    case OUT_FOR_DELIVERY = 3;
    case DELIVERED = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::NOT_SHIPPED => 'Not Shipped',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
        };
    }
}
