<?php

namespace App\Enums;

enum AdvertisementType: int
{
    case STATUS = 1;
    case VIDEO = 2;
    case OFFER = 3;
    case CARD = 4;
}
