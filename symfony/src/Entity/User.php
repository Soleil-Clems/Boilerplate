<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JsonSerializable;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'This email already exists.')]
class User extends TimestampableEntity implements UserInterface, PasswordAuthenticatedUserInterface, JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 180)]
    #[Assert\Length(min: 5, max: 180)]
    #[Assert\Email(message: 'The email must be a valid email address')]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = ["ROLE_USER"];

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The password cannot be blank')]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RefreshToken::class, orphanRemoval: true)]
    private Collection $refreshTokens;

    public function __construct()
    {
        $this->uuid = Uuid::v7();
        $this->refreshTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): static
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->add($refreshToken);
            $refreshToken->setUser($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): static
    {
        if ($this->refreshTokens->removeElement($refreshToken)) {
            if ($refreshToken->getUser() === $this) {
                $refreshToken->setUser(null);
            }
        }

        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    public function jsonSerialize(): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "email" => $this->email,
            "roles" => $this->roles,
        ];
    }
}
