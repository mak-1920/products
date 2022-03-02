<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ImportStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\UnexpectedResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<ImportStatus>
 */
class ImportStatusRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ImportStatus::class);
    }

    /**
     * @param ImportStatus $status
     *
     * @return int row's id
     */
    public function addStatus(ImportStatus $status): int
    {
        $this->_em->persist($status);
        $this->_em->flush();

        return $status->getId();
    }

    /**
     * @param ImportStatus $status
     * @param bool $isComplete
     * @param array{success: array<array<string>>, failed: array<array<string>>}|null $result
     *
     * @return void
     */
    public function changeStatus(ImportStatus $status, bool $isComplete, ?array $result = null): void
    {
        if ($isComplete) {
            /** @var array{success: array<array<string>>, failed: array<array<string>>} $result */
            $status->setValidRows($result['success']);
            $status->setInvalidRows($result['failed']);
            $status->setStatus('STATUS_IMPORTED');
        } else {
            $status->setStatus('STATUS_FAILED');
        }

        $this->_em->persist($status);
        $this->_em->flush();
    }

    /**
     * @param int $lastId
     *
     * @return Query
     */
    public function getQueryForTakeAllStatuses(int $lastId): Query
    {
        $qb = $this->createQueryBuilder('s');

        if (-1 != $lastId) {
            $qb
                ->where('s.id <= :lastId')
                ->setParameter('lastId', $lastId);
        }

        return $qb->orderBy('s.id', 'desc')
            ->getQuery()
            ;
    }

    /**
     * @return int
     */
    public function getLastStatusId(): int
    {
        try {
            $result = intval(
                $this->createQueryBuilder('s')
                    ->select('s.id')
                    ->orderBy('s.id', 'desc')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleScalarResult()
            );

            return $result;
        } catch (UnexpectedResultException) {
            return -1;
        }
    }

    /**
     * @param int $id
     *
     * @return ImportStatus|null
     *
     * @throws NonUniqueResultException
     */
    public function findByID(int $id): ?ImportStatus
    {
        /** @var ImportStatus $result */
        $result = $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
