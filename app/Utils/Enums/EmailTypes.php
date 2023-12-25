<?php

namespace App\Utils\Enums;

enum EmailTypes
{
    case USER_VERIFICATION;
    case PAYMENT_CHECKOUT_RECEIPT;
    case PAYMENT_CHECKOUT_CONFIRMATION;
}
