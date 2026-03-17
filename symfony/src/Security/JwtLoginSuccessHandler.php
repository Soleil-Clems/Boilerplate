<?php

namespace App\Security;

use App\Entity\User;
use App\Service\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
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

        $response = new JsonResponse([
            'success' => true,
            'access_token' => $jwt,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
        ], 200);


        $response->headers->setCookie(new Cookie(
            name: 'refresh_token',
            value: $refreshToken->getToken(),
            expire: $refreshToken->getExpiresAt()->getTimestamp(),
            path: '/api',
            domain: null,
            secure: false,   // true en prod
            httpOnly: true,
            sameSite: 'Lax'
        ));

        $response->headers->setCookie(new Cookie(
            name: 'access_token',
            value: $jwt,
            expire: time() + 3600,
            path: '/api',
            domain: null,
            secure: false,   // true en prod
            httpOnly: true,
            sameSite: 'Lax'
        ));


        return $response;
    }
}
