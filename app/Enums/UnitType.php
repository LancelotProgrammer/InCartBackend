<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UnitType: int implements HasLabel
{
    case PIECE = 1;
    case GRAM = 2;
    case MILLILITERS = 3;
    case MILLIMETERS = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::PIECE => 'Piece',
            self::GRAM => 'Gram',
            self::MILLILITERS => 'Milliliter',
            self::MILLIMETERS => 'Millimeter',
        };
    }
}
