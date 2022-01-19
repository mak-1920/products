<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getExistsProducts(array $products) : array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('i')
            ->where('p.name IN (:names)')
            ->setParameter('names', $products, Connection::PARAM_STR_ARRAY)
            ->leftJoin('p.imports', 'i')
            ->getQuery()
            ->getResult();
    }

    public function saveProducts(array $products) : void
    {
        $em = $this->getEntityManager();

        foreach($products as $product){
            $em->persist($product);
        }

        $em->flush();
    }
}
