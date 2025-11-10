<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ForceApproveOrderType: int implements HasLabel
{
    case PASSING_CHECKOUT = 1;
    case PASSING_DATE = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::PASSING_CHECKOUT => 'Passing checkout',
            self::PASSING_DATE => 'Passing date',
        };
    }
}
