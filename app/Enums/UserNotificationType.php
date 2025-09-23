<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserNotificationType: int implements HasLabel
{
    case GENERAL = 1;
    case SECURITY = 2;
    case ORDER = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::GENERAL => 'General',
            self::SECURITY => 'Security',
            self::ORDER => 'Order',
        };
    }
}
