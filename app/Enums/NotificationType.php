<?php

namespace App\Enums;

enum NotificationType: int
{
    case GENERAL = 1;
    case SECURITY = 2;
    case ORDER = 3;
}
