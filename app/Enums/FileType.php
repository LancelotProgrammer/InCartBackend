<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FileType: int implements HasLabel
{
    case IMAGE = 1;
    case VIDEO = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::IMAGE => 'Image',
            self::VIDEO => 'Video',
        };
    }
}
