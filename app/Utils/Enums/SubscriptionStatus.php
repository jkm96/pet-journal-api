<?php

namespace App\Utils\Enums;

enum SubscriptionStatus
{
    case ACTIVE;
    case INACTIVE ;
    case CANCELED ;
    case EXPIRED;
}
