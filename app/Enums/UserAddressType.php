<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserAddressType: int implements HasLabel
{
    case HOME = 1;
    case WORK = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::HOME => 'Home',
            self::WORK => 'Work',
        };
    }
}
