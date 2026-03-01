<?php

namespace App\Service;

use App\DTO\UserDTO\RegisterDTO;
use App\DTO\UserDTO\UpdateRolesDTO;
use App\DTO\UserDTO\UpdateUserDTO;
use App\Entity\User;
use App\Enum\Roles;
use App\Exception\ValidationException;
use App\Helper\CustomValidator;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


readonly class UserService
{

    public function __construct(
        private UserRepository              $userRepository,
        private UserMapper                  $userMapper,
        private CustomValidator             $customValidator,
        private UserPasswordHasherInterface $passwordHasher

    ){}

    public function fetchAll(array $filters=[], $page=1, $limit=20):array {
        $users =$this->userRepository->search(
            filters: $filters,
            page: $page,
            limit: $limit,
        );

        $users["success"]=true;

        return $users;
    }

    public function fetchUser(int $id):array {
        $user = $this->userRepository->find($id);

        if(!$user){
            throw new NotFoundHttpException("User not found");
        }

        return ["success"=>true, "user"=>$user];
    }

    public function createUser(RegisterDTO $dto):array {
        $user = $this->userMapper->fromRegisterDTOToUser($dto);

        $error = $this->customValidator->validate($user);

        if (count($error) > 0) {
            throw new ValidationException($error);
        }
        $this->userRepository->save($user);
        return ["success"=>true,"message" => "User has been created successfully"];
    }

    /**
     * @throws ExceptionInterface
     */
    public function updateUser(UpdateUserDTO $dto, User $user ):array {

        if($dto->password && !$this->passwordHasher->isPasswordValid($user, $dto->password)){
            throw new \Exception("Password incorrect.");
        }
        $userEntity = $this->userMapper->fromUpdateDTOToUser($dto, $user);

        $error = $this->customValidator->validate($userEntity);

        if (count($error) > 0) {
            throw new ValidationException($error);
        }

        $this->userRepository->save($userEntity);
        return ["success"=>true,"message" => "User has been updated successfully"];
    }

    /**
     * @throws ExceptionInterface
     */
    public function updateRoleUser(UpdateRolesDTO $dto, User $user ):array {
        $roleValue = match ($dto->roles) {
            Roles::USER->name => Roles::USER->value,
            Roles::ADMIN->name => Roles::ADMIN->value,
            Roles::SUPERADMIN->name => Roles::SUPERADMIN->value,
            default => throw new \InvalidArgumentException('Rôle inconnu'),
        };


        $userEntity = $this->userMapper->fromUpdateRolesDTOToUser($roleValue, $user);

        $error = $this->customValidator->validate($userEntity);

        if (count($error) > 0) {
            throw new ValidationException($error);
        }

        $this->userRepository->save($userEntity);
        return ["success"=>true,"message" => "User role has been updated successfully"];
    }

    public function removeUser(int $id):array {
        $user = $this->userRepository->find($id);
        $this->userRepository->remove($user);
        return ["success"=>true,"message"=>"user has been removed"];
    }
}
