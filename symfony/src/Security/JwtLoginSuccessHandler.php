<?php

namespace App\Security;

use App\Entity\User;
use App\Service\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class JwtLoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private JWTTokenManagerInterface $jwtManager;
    private AuthService $authService;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        AuthService $authService
    ) {
        $this->jwtManager = $jwtManager;
        $this->authService = $authService;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        /** @var User $user */
        $user = $token->getUser();

        $jwt = $this->jwtManager->create($user);

        $refreshToken = $this->authService->getOrCreateRefreshToken($user);

        return new JsonResponse([
            'success' => true,
            'access_token' => $jwt,
            'refresh_token' => $refreshToken->getToken(),
        ], 200);
    }
}
