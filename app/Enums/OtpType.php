<?php

namespace App\Enums;

enum OtpType: int
{
    case REGISTER = 1;
    case LOGIN = 2;
    case UPDATE = 3;
}
