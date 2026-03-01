<?php

namespace App\Controller;

use App\Entity\User;
use App\Helper\CustomValidator;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

final class AuthController extends AbstractController
{
    private UserRepository $userRepository;
    private CustomValidator $customValidator;
    private SerializerInterface $serializer;
    public function __construct(UserRepository $userRepository, CustomValidator $customValidator, SerializerInterface $serializer)
    {
        $this->userRepository = $userRepository;
        $this->customValidator = $customValidator;
        $this->serializer = $serializer;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];

        return $this->json([
            'success' => true,
            'message' => "Authentication successful",
            'user' => $data
        ]);
    }


    #[Route('/api/me', name: 'api_authUser', methods: ['GET'])]
    public function authUser(#[CurrentUser] ?User $user): JsonResponse
    {
        if($user){
            $data = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'phone' => $user->getPhone(),
            ];

            return $this->json(["success"=>true,"user" => $data]);
        }
        return $this->json(["message"=>"Vous n'êtes pas authentifié"], 401);
    }
}