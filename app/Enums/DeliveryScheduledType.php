<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DeliveryScheduledType: int implements HasLabel
{
    case IMMEDIATE = 1;
    case SCHEDULED = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::IMMEDIATE => 'Immediate',
            self::SCHEDULED => 'Scheduled',
        };
    }
}
