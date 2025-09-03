<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DeliveryType: int implements HasLabel
{
    case SCHEDULED = 1;
    case IMMEDIATE = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::IMMEDIATE => 'Immediate',
        };
    }
}
