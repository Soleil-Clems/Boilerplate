<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter
{
    public const EDIT = 'USER_EDIT';
    public const EDITROLES = 'USER_EDIT_ROLES';
    public const VIEW = 'USER_VIEW';
    public const DELETE = 'USER_DELETE';
    public const CREATE = 'USER_CREATE';

    /**
     * Hiérarchie des rôles : plus le nombre est élevé, plus le rôle a de pouvoir
     */
    private const ROLE_HIERARCHY = [
        'ROLE_USER' => 1,
        'ROLE_ADVISOR' => 2,
        'ROLE_ADMIN' => 3,
        'ROLE_SUPER_ADMIN' => 4,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        // CREATE ne nécessite pas de subject (création d'un nouvel user)
        if ($attribute === self::CREATE) {
            return true;
        }

        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::EDITROLES])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $authUser = $token->getUser();

        if (!$authUser instanceof User) {
            return false;
        }

        // SUPER_ADMIN peut tout faire
        if ($this->hasRole($authUser, 'ROLE_SUPER_ADMIN')) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $authUser),
            self::EDIT => $this->canEdit($subject, $authUser),
            self::EDITROLES => $this->canEditRoles($subject, $authUser),
            self::DELETE => $this->canDelete($subject, $authUser),
            self::CREATE => $this->canCreate($authUser),
            default => false,
        };
    }

    /**
     * Règle : Tout le monde peut voir tout le monde
     */
    private function canView(?User $targetUser, User $authUser): bool
    {
        return true;
    }

    /**
     * Règle : On peut éditer un utilisateur de niveau INFÉRIEUR ou ÉGAL
     */
    private function canEdit(User $targetUser, User $authUser): bool
    {
        return $targetUser->getId() === $authUser->getId() || $this->getHighestRoleLevel($authUser) > $this->getHighestRoleLevel($targetUser);
    }


    /**
     * Règle : On peut éditer le role d'un utilisateur de niveau INFÉRIEUR
     */
    private function canEditRoles(User $targetUser, User $authUser): bool
    {

        if ($targetUser->getId() === $authUser->getId()) {
            return false;
        }

        if ($this->hasRole($authUser, 'ROLE_SUPER_ADMIN') || $this->hasRole($authUser, 'ROLE_ADMIN')) {
            return $this->getHighestRoleLevel($authUser) > $this->getHighestRoleLevel($targetUser);
        }

        return false;
    }

    /**
     * Règle : On peut supprimer un utilisateur de niveau STRICTEMENT INFÉRIEUR
     */
    private function canDelete(User $targetUser, User $authUser): bool
    {

        if ($targetUser->getId() === $authUser->getId()) {
            return false;
        }

        return $this->getHighestRoleLevel($authUser) > $this->getHighestRoleLevel($targetUser);
    }

    /**
     * Règle : Seuls ADMIN et SUPER_ADMIN peuvent créer des utilisateurs (advisor)
     */
    private function canCreate(User $authUser): bool
    {
        return $this->hasRole($authUser, 'ROLE_ADMIN')
            || $this->hasRole($authUser, 'ROLE_SUPER_ADMIN');
    }

    /**
     * Retourne le niveau du rôle le plus élevé d'un utilisateur
     */
    private function getHighestRoleLevel(User $user): int
    {
        $maxLevel = 0;

        foreach ($user->getRoles() as $role) {
            $level = self::ROLE_HIERARCHY[$role] ?? 0;
            if ($level > $maxLevel) {
                $maxLevel = $level;
            }
        }

        return $maxLevel;
    }

    /**
     * Vérifie si l'utilisateur possède un rôle spécifique
     */
    private function hasRole(User $user, string $role): bool
    {
        return in_array($role, $user->getRoles(), true);
    }
}
