<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

abstract class BaseRepository extends ServiceEntityRepository
{
    protected string $alias = 'e';

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * Applique les filtres avec validation et typage
     */
    public function applyFilters(QueryBuilder $qb, array $filters, array $allowedFields): QueryBuilder
    {
        foreach ($filters as $field => $value) {
            if (!isset($allowedFields[$field]) || $value === null || $value === '') {
                continue;
            }

            $config = $allowedFields[$field];
            $column = sprintf('%s.%s', $this->alias, $config['column']);
            $paramName = str_replace('.', '_', $field); // Évite les conflits

            switch ($config['type']) {
                case 'like':
                    $qb->andWhere("$column LIKE :$paramName")
                        ->setParameter($paramName, "%$value%");
                    break;

                case 'eq':
                    $qb->andWhere("$column = :$paramName")
                        ->setParameter($paramName, $value);
                    break;

                case 'neq':
                    $qb->andWhere("$column != :$paramName")
                        ->setParameter($paramName, $value);
                    break;

                case 'in':
                    if (!is_array($value)) {
                        $value = explode(',', $value);
                    }
                    $qb->andWhere("$column IN (:$paramName)")
                        ->setParameter($paramName, $value);
                    break;

                case 'gt':
                    $qb->andWhere("$column > :$paramName")
                        ->setParameter($paramName, $value);
                    break;

                case 'gte':
                    $qb->andWhere("$column >= :$paramName")
                        ->setParameter($paramName, $value);
                    break;

                case 'lt':
                    $qb->andWhere("$column < :$paramName")
                        ->setParameter($paramName, $value);
                    break;

                case 'lte':
                    $qb->andWhere("$column <= :$paramName")
                        ->setParameter($paramName, $value);
                    break;

                case 'date_from':
                    try {
                        $date = new \DateTimeImmutable($value);
                        $qb->andWhere("$column >= :$paramName")
                            ->setParameter($paramName, $date);
                    } catch (\Exception $e) {

                        continue 2;
                    }
                    break;

                case 'date_to':
                    try {
                        $date = (new \DateTimeImmutable($value))->setTime(23, 59, 59);
                        $qb->andWhere("$column <= :$paramName")
                            ->setParameter($paramName, $date);
                    } catch (\Exception $e) {
                        continue 2;
                    }
                    break;

                case 'bool':
                    $qb->andWhere("$column = :$paramName")
                        ->setParameter($paramName, filter_var($value, FILTER_VALIDATE_BOOLEAN));
                    break;

                case 'json_contains':
                    $qb->andWhere("$column LIKE :$paramName")
                        ->setParameter($paramName, '%"' . $value . '"%');
                    break;

                default:
                    throw new \InvalidArgumentException("Type de filtre non supporté : {$config['type']}");
            }
        }

        return $qb;
    }

    /**
     * Pagination avec tri
     */
    public function paginate(mixed $data, int $page = 1, int $limit = 20, ?array $orderBy = null): array
    {
        // Si c'est un QueryBuilder, on fait la pagination SQL
        if ($data instanceof QueryBuilder) {
            $qb = $data;

            if ($orderBy) {
                foreach ($orderBy as $field => $direction) {
                    $qb->addOrderBy(sprintf('%s.%s', $this->alias, $field), $direction);
                }
            }

            $offset = max(0, ($page - 1) * $limit);

            $qbCount = clone $qb;
            $total = (int) $qbCount
                ->select(sprintf('COUNT(DISTINCT %s.id)', $this->alias))
                ->setFirstResult(0)
                ->setMaxResults(null)
                ->getQuery()
                ->getSingleScalarResult();

            $results = $qb
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        }

        // Sinon, on suppose que c'est un array simple et on pagine en mémoire
        elseif (is_iterable($data)) {
            $resultsArray = is_array($data) ? $data : iterator_to_array($data);
            $total = count($resultsArray);
            $offset = max(0, ($page - 1) * $limit);
            $results = array_slice($resultsArray, $offset, $limit);
        } else {
            throw new \InvalidArgumentException('paginate() expects a QueryBuilder or an array/iterable.');
        }

        return [
            'data' => $results,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int) ceil($total / $limit),
                'hasNext' => $page < ceil($total / $limit),
                'hasPrev' => $page > 1,
            ]
        ];
    }


    public function save(object|array $entities, bool $flush = true): void
    {
        $em = $this->getEntityManager();

        if (is_iterable($entities)) {
            foreach ($entities as $entity) {
                $em->persist($entity);
            }
        } else {
            $em->persist($entities);
        }

        if ($flush) {
            $em->flush();
        }
    }

    public function remove(object|array $entities, bool $flush = true): void
    {
        $em = $this->getEntityManager();

        if (is_iterable($entities)) {
            foreach ($entities as $entity) {
                $em->remove($entity);
            }
        } else {
            $em->remove($entities);
        }

        if ($flush) {
            $em->flush();
        }
    }


}
