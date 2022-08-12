<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductData;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<ProductData>
 */
class ProductDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductData::class);
    }

    /**
     * @param string[] $codes
     *
     * @return string[]
     */
    public function getExistsProductCodes(array $codes): array
    {
        /** @var string[] $existsCodes */
        $existsCodes = $this->createQueryBuilder('p')
            ->select('p.code')
            ->where('p.code IN (:codes)')
            ->setParameter('codes', $codes, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getSingleColumnResult()
            ;

        return $existsCodes;
    }

    /**
     * @param string[] $names
     *
     * @return array<array{name: string, discontinuedAt: DateTime}>
     */
    public function getDiscontinuedProductsByNames(array $names): array
    {
        /** @var array<array{name: string, discontinuedAt: DateTime}> $result */
        $result = $this->createQueryBuilder('p')
            ->select('p.name, p.discontinuedAt')
            ->distinct()
            ->where('p.name IN (:names)')
            ->andWhere('p.discontinuedAt IS NOT NULL')
            ->groupBy('p.name')
            ->setParameter('names', $names, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getArrayResult()
            ;

        return $result;
    }

    /**
     * @param int $lastId
     *
     * @return Query
     */
    public function getQueryForTakeAllProducts(int $lastId): Query
    {
        $qb = $this->createQueryBuilder('p');

        if (-1 != $lastId) {
            $qb->where('p.id <= :lastId')
                ->setParameter('lastId', $lastId);
        }

        return $qb->orderBy('p.id', 'desc')
            ->getQuery()
            ;
    }

    /**
     * @return int
     */
    public function getLastProductId(): int
    {
        try {
            $result = intval(
                $this->createQueryBuilder('p')
                ->select('p.id')
                ->orderBy('p.id', 'desc')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult()
            );

            return $result;
        } catch (UnexpectedResultException) {
            return -1;
        }
    }
}
