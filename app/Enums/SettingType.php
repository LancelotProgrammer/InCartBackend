<?php

namespace App\Enums;

enum SettingType: string
{
    case INT = 'int';
    case FLOAT = 'float';
    case BOOL = 'bool';
    case STR = 'str';
    case JSON = 'json';
}
