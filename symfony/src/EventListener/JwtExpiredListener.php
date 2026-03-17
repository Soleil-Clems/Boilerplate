<?php

namespace App\EventListener;

use App\Service\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

readonly class JwtExpiredListener
{
    public function __construct(
        private AuthService $authService,
        private JWTTokenManagerInterface $jwtManager,
        private HttpKernelInterface $kernel,
    ) {}

    public function onJwtExpired(JWTExpiredEvent $event): void
    {
        $this->handleRefresh($event, $event->getRequest());
    }


    public function onJwtInvalid(JWTInvalidEvent $event): void
    {
        $request = $event->getRequest();
        $accessToken = $request->cookies->get('access_token');

        if (!$accessToken) {
            return;
        }

        $parts = explode('.', $accessToken);
        if (count($parts) !== 3) {
            return;
        }

        $payload = json_decode(base64_decode(str_pad(
            strtr($parts[1], '-_', '+/'),
            strlen($parts[1]) % 4,
            '='
        )), true);

        if (!isset($payload['exp']) || $payload['exp'] > time()) {
            return;
        }


        $this->handleRefresh($event, $request);
    }

    private function handleRefresh(mixed $event, Request $request): void
    {
        $refreshTokenValue = $request->cookies->get('refresh_token');

        if (!$refreshTokenValue) {
            return;
        }

        $refreshToken = $this->authService->validateRefreshToken($refreshTokenValue);

        if (!$refreshToken) {
            return;
        }

        $user = $refreshToken->getUser();
        $newJwt = $this->jwtManager->create($user);

        $newRequest = $request->duplicate();
        $newRequest->headers->set('Authorization', 'Bearer ' . $newJwt);

        $response = $this->kernel->handle($newRequest, HttpKernelInterface::SUB_REQUEST);

        $response->headers->setCookie(new Cookie(
            name: 'access_token',
            value: $newJwt,
            expire: time() + 900,
            path: '/api',
            domain: null,
            secure: false,
            httpOnly: true,
            sameSite: 'Lax'
        ));

        $event->setResponse($response);
    }
}
