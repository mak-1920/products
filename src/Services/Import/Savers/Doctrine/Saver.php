<?php

declare(strict_types=1);

namespace App\Services\Import\Savers\Doctrine;

use App\Entity\ProductData;
use App\Services\Import\Exceptions\SaverException;
use App\Services\Import\Savers\SaverInterface;
use Doctrine\ORM\EntityManager;
use Port\Doctrine\DoctrineWriter;
use Port\Exception;
use Port\Reader\ArrayReader;
use Port\Steps\StepAggregator;

class Saver implements SaverInterface
{
    public function __construct(
        private EntityManager $em,
    ) {
    }

    /**
     * @param string[][] $rows
     *
     * @return string[][] success
     *
     * @throws SaverException
     */
    public function save(array $rows): array
    {
        try {
            $transporter = new StepAggregator(new ArrayReader($rows));

            $doctrineWriter = new DoctrineWriter($this->em, ProductData::class);
            $doctrineWriter->disableTruncate();

            $transporter->addWriter($doctrineWriter);
            $transporter->process();
        } catch (Exception $e) {
            throw new SaverException('Rows can\'t been saved!', previous: $e);
        }

        return $rows;
    }
}
