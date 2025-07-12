<?php

namespace App\Enums;

enum AddressTypeEnum: string
{
    case Billing = 'billing';
    case Shipping = 'shipping';
}
