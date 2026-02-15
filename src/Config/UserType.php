<?php

namespace App\Config;

enum UserType: string
{
    case ADMIN = 'ADMIN';
    case CUSTOMER = 'CUSTOMER';
}
