<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderStatus: int implements HasLabel
{
    case PENDING = 1;
    case CONFIRMED = 2;
    case PROCESSING = 3;
    case FINISHED = 4;
    case CANCELLED = 5;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::FINISHED => 'Finished',
            self::CANCELLED => 'Cancelled',
        };
    }
}
