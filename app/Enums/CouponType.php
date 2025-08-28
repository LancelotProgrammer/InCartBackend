<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponType: int implements HasLabel
{
    case TIMED = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::TIMED => 'Timed',
        };
    }
}
