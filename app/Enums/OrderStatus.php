<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 1;
    case CONFIRMED = 2;
    case PROCESSING = 3;
    case FINISHED = 4;
    case CANCELLED = 5;
}
