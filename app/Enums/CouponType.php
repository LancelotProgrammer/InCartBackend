<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponType: int implements HasLabel
{
    case FIXED = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::FIXED => 'Fixed',
        };
    }
}
