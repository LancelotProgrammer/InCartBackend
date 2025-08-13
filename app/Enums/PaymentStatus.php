<?php

namespace App\Enums;

enum PaymentStatus: int
{
    case UNPAID = 1;
    case PAID = 2;
    case REFUNDED = 3;
}
