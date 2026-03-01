<?php

namespace App\Enum;

use App\Trait\EnumFromNameTrait;

enum Roles: string
{
    use EnumFromNameTrait;
    case USER = "ROLE_USER";
    case ADMIN = "ROLE_ADMIN";
    case SUPERADMIN = "ROLE_SUPER_ADMIN";

}
