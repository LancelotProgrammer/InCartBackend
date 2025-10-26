<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderStatus: int implements HasLabel
{
    case PENDING = 1;
    case PROCESSING = 2;
    case DELIVERING = 3;
    case FINISHED = 4;
    case CLOSED = 5;
    case CANCELLED = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::DELIVERING => 'Delivering',
            self::FINISHED => 'Finished',
            self::CLOSED => 'Closed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
