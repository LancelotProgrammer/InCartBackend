<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PaymentStatus: int implements HasLabel, HasColor, HasIcon
{
    case UNPAID = 1;
    case PAID = 2;
    case REFUNDED = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::UNPAID => 'warning',
            self::PAID => 'success',
            self::REFUNDED => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::UNPAID => Heroicon::XCircle->value,
            self::PAID => Heroicon::CheckCircle->value,
            self::REFUNDED => Heroicon::ArrowUturnDown->value,
        };
    }
}
