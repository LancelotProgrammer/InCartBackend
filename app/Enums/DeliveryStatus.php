<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum DeliveryStatus: int implements HasLabel, HasColor, HasIcon
{
    case SCHEDULED = 1;
    case NOT_DELIVERED = 2;
    case OUT_FOR_DELIVERY = 3;
    case DELIVERED = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::NOT_DELIVERED => 'Not Delivered',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SCHEDULED => 'primary',
            self::NOT_DELIVERED => 'danger',
            self::OUT_FOR_DELIVERY => 'warning',
            self::DELIVERED => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SCHEDULED => Heroicon::Calendar->value,
            self::NOT_DELIVERED => Heroicon::XCircle->value,
            self::OUT_FOR_DELIVERY => Heroicon::Truck->value,
            self::DELIVERED => Heroicon::CheckCircle->value,
        };
    }
}
