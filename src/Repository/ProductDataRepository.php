<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProductData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Import|null find($id, $lockMode = null, $lockVersion = null)
 * @method Import|null findOneBy(array $criteria, array $orderBy = null)
 * @method Import[]    findAll()
 * @method Import[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductData::class);
    }

    public function getExistsProductCodes(array $codes): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.strproductcode')
            ->where('p.strproductcode IN (:codes)')
            ->setParameter('codes', $codes, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getSingleColumnResult()
            ;
    }

    public function getDiscontinuedProductsByNames(array $names): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.strproductname, p.dtmdiscontinued')
            ->distinct()
            ->where('p.strproductname IN (:names)')
            ->andWhere('p.dtmdiscontinued IS NOT NULL')
            ->groupBy('p.strproductname')
            ->setParameter('names', $names, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getArrayResult()
            ;
    }

    public function saveProducts(array $products): void
    {
        $em = $this->getEntityManager();

        foreach ($products as $product) {
            $em->persist($product);
        }

        $em->flush();
    }
}
