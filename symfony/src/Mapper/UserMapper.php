<?php

namespace App\Mapper;


use App\DTO\UserDTO\RegisterDTO;
use App\DTO\UserDTO\UpdateUserDTO;
use App\DTO\UserDTO\UpdateRolesDTO;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserMapper
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher){
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function fromRegisterDTOToUser(RegisterDTO $dto):User{
        $user = new User();
        $user->setEmail($dto->email);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $dto->password));
//        $user->setFirstname($dto->firstname);
//        $user->setLastname($dto->lastname);
//        $user->setPhone($dto->phone);
        return $user;
    }


    public function fromUpdateDTOToUser(UpdateDTO $dto, User $user):User{

        if(isset($dto->email)) $user->setEmail($dto->email);
//        if(isset($dto->firstname)) $user->setFirstname($dto->firstname);
//        if(isset($dto->lastname)) $user->setLastname($dto->lastname);
//        if(isset($dto->phone)) $user->setPhone($dto->phone);

        return $user;
    }

    public function fromUpdateRolesDTOToUser(string $roles, User $user):User{

        $user->setRoles([$roles]);

        return $user;
    }
}
