<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CategoryType: int implements HasLabel
{
    case MAIN = 1;
    case INNOVATIVE = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::MAIN => 'Main',
            self::INNOVATIVE => 'Innovative',
        };
    }
}
