<?php

namespace App\DTO\UserDTO;

use App\Enum\Roles;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateRolesDTO
{
    #[Assert\Choice(
        choices: [
            Roles::SUPERADMIN->name,
            Roles::ADMIN->name,
            Roles::ADVISOR->name,
            Roles::USER->name,
        ],
        message: 'Choisissez un rôle qui existe'
    )]
    public string $roles;
}
