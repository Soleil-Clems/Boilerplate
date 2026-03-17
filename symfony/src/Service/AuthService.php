<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;

final readonly class AuthService
{
    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws RandomException
     */
    public function createRefreshToken(User $user, \DateInterval $ttl = new \DateInterval('P30D')): RefreshToken
    {
        $token = bin2hex(random_bytes(64));
        $expiresAt = new \DateTimeImmutable()->add($ttl);

        $refreshToken = new RefreshToken($user, $token, $expiresAt);

        $this->refreshTokenRepository->save($refreshToken);

        return $refreshToken;
    }

    public function validateRefreshToken(string $token): ?RefreshToken
    {
        $refreshToken = $this->refreshTokenRepository->findOneBy(['token' => $token]);

        if (!$refreshToken || $refreshToken->isRevoked() || $refreshToken->isExpired()) {
            return null;
        }

        return $refreshToken;
    }

    public function revokeRefreshToken(RefreshToken $refreshToken): void
    {
        $refreshToken->setRevoked(true);
        $this->entityManager->flush();
    }
}
