<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ImportStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<ImportStatus>
 */
class ImportStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
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
}
