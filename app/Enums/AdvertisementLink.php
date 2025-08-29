<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AdvertisementLink: int implements HasLabel
{
    case PRODUCT = 1;
    case CATEGORY = 2;
    case EXTERNAL = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::PRODUCT => 'Product',
            self::CATEGORY => 'Category',
            self::EXTERNAL => 'External',
        };
    }
}
