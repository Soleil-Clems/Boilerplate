<?php

namespace App\Controller;

use App\DTO\UserDTO\RegisterDTO;
use App\Entity\User;
use App\Service\AuthService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class AuthController extends AbstractController
{

    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService,
        private readonly JWTTokenManagerInterface $jwtManager,
    )
    {
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }


        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];

        return $this->json([
            'success' => true,
            'user' => $data,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function create(#[MapRequestPayload] RegisterDTO $dto): JsonResponse
    {
        if ($dto->password != $dto->confirm_password) {
            return $this->json(["message" => "Les mots de passe ne correspondent pas"], 400);
        }

        $result = $this->userService->createUser($dto);

        return $this->json($result);

    }


    #[Route('/api/me', name: 'api_authUser', methods: ['GET'])]
    public function authUser(#[CurrentUser] ?User $user): JsonResponse
    {
        if($user){
            $data = [
                'id' => $user->getId(),
                'uuid' => $user->getUuid(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ];

            return $this->json(["success"=>true,"user" => $data]);
        }
        return $this->json(["message"=>"Vous n'êtes pas authentifié"], 401);
    }

    #[Route('/api/token/refresh', name: 'api_refresh_token', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tokenValue = $data['refresh_token'] ?? null;

        if (!$tokenValue) {
            return $this->json(['error' => 'No token provided'], 400);
        }

        $refreshToken = $this->authService->validateRefreshToken($tokenValue);

        if (!$refreshToken) {
            return $this->json(['error' => 'Refresh token expired or invalid'], 401);
        }

        $user = $refreshToken->getUser();

        $jwt = $this->jwtManager->create($user);

        return $this->json([
            'token' => $jwt,
            'refresh_token' => $refreshToken->getToken(),
        ]);
    }
}
