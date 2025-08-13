<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UnitType: int implements HasLabel
{
    case PIECE = 1;
    case GRAM = 2;
    case LITER = 3;
    case METER = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PIECE => 'Piece',
            self::GRAM => 'Gram',
            self::LITER => 'Liter',
            self::METER => 'Meter',
        };
    }
}
