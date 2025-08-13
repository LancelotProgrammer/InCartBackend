<?php

namespace App\Enums;

enum DeliveryStatus: int
{
    case NOT_SHIPPED = 1;
    case OUT_FOR_DELIVERY = 2;
    case DELIVERED = 3;
    case RETURNED = 4;
}
