<?php

namespace App\Enums;

enum TenantUserType: int
{
    case ADMIN = 1;
    case USER = 2;
}
