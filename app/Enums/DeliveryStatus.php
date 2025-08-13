<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DeliveryStatus: int implements HasLabel
{
    case NOT_SHIPPED = 1;
    case OUT_FOR_DELIVERY = 2;
    case DELIVERED = 3;
    case RETURNED = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NOT_SHIPPED => 'Not Shipped',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::RETURNED => 'Returned',
        };
    }
}
