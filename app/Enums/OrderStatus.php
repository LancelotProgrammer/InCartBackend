<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;

enum OrderStatus: int implements HasLabel, HasColor, HasIcon
{
    case PENDING = 1;
    case PROCESSING = 2;
    case DELIVERING = 3;
    case FINISHED = 4;
    case CLOSED = 5;
    case CANCELLED = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::DELIVERING => 'Delivering',
            self::FINISHED => 'Finished',
            self::CLOSED => 'Closed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'primary',
            self::PROCESSING => 'warning',
            self::DELIVERING => 'warning',
            self::FINISHED => 'success',
            self::CLOSED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => Heroicon::Clock->getIconForSize(IconSize::Medium),
            self::PROCESSING => Heroicon::ArrowPath->getIconForSize(IconSize::Medium),
            self::DELIVERING => Heroicon::Truck->getIconForSize(IconSize::Medium),
            self::FINISHED => Heroicon::CheckCircle->getIconForSize(IconSize::Medium),
            self::CLOSED => Heroicon::CheckCircle->getIconForSize(IconSize::Medium),
            self::CANCELLED => Heroicon::XCircle->getIconForSize(IconSize::Medium),
        };
    }
}
