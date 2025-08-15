<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SettingType: string implements HasLabel
{
    case INT = 'int';
    case FLOAT = 'float';
    case BOOL = 'bool';
    case STR = 'str';
    case JSON = 'json';

    public function getLabel(): string
    {
        return match ($this) {
            self::INT => 'Integer',
            self::FLOAT => 'Float',
            self::BOOL => 'Boolean',
            self::STR => 'String',
            self::JSON => 'JSON',
        };
    }
}
