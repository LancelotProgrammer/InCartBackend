<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AdvertisementType: int implements HasLabel
{
    case STATUS = 1;
    case VIDEO = 2;
    case OFFER = 3;
    case CARD = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::STATUS => 'Status',
            self::VIDEO => 'Video',
            self::OFFER => 'Offer',
            self::CARD => 'Card',
        };
    }
}
