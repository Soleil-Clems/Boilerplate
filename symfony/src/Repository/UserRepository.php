<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends BaseRepository implements PasswordUpgraderInterface
{
    protected string $alias = 'u';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function search(array $filters, int $page = 1, int $limit = 20, ?array $orderBy = null): array
    {
        $qb = $this->createQueryBuilder($this->alias);

        $allowedFilters = [
            'firstname' => ['type' => 'like', 'column' => 'firstname'],
            'lastname'  => ['type' => 'like', 'column' => 'lastname'],
            'email'     => ['type' => 'eq', 'column' => 'email'],
            'roles'     => ['type' => 'json_contains', 'column' => 'roles'],
            'isActive'  => ['type' => 'bool', 'column' => 'isActive'],
            'createdFrom' => ['type' => 'date_from', 'column' => 'createdAt'],
            'createdTo'   => ['type' => 'date_to', 'column' => 'createdAt'],
            'ageMin'      => ['type' => 'gte', 'column' => 'age'],
        ];

        $qb = $this->applyFilters($qb, $filters, $allowedFilters);

//        return $this->paginate($qb, $page, $limit, $orderBy ?? ['createdAt' => 'DESC']);
        return $this->paginate($qb, $page, $limit);
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
