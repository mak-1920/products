<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductData;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductData[] findAll()
 * @method ProductData[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
        return $this->createQueryBuilder('p')
            ->select('p.code')
            ->where('p.code IN (:codes)')
            ->setParameter('codes', $codes, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getSingleColumnResult()
            ;
    }

    /**
     * @param string[] $names
     *
     * @return array{name: string, discontinuedAt: DateTime}
     */
    public function getDiscontinuedProductsByNames(array $names): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.name, p.discontinuedAt')
            ->distinct()
            ->where('p.name IN (:names)')
            ->andWhere('p.discontinuedAt IS NOT NULL')
            ->groupBy('p.name')
            ->setParameter('names', $names, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getArrayResult()
            ;
    }

    /**
     * @param ProductData[] $products
     *
     * @return void
     */
    public function saveProducts(array $products): void
    {
        $em = $this->getEntityManager();

        foreach ($products as $product) {
            $em->persist($product);
        }

        $em->flush();
    }
}
