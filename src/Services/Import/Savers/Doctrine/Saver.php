<?php

declare(strict_types=1);

namespace App\Services\Import\Savers\Doctrine;

use App\Entity\ProductData;
use App\Repository\ProductDataRepository;
use App\Services\Import\Exceptions\SaverException;
use App\Services\Import\Savers\SaverInterface;
use Doctrine\ORM\EntityManager;
use Port\Doctrine\DoctrineWriter;
use Port\Exception;
use Port\Reader\ArrayReader;
use Port\Steps\StepAggregator;
use Port\Writer\ArrayWriter;

class Saver implements SaverInterface
{
    private ProductDataRepository $productRepository;

    public function __construct(
        private EntityManager $em,
    ) {
        $this->productRepository = $em->getRepository(ProductData::class);
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
            $writer = new ArrayWriter($rows);

            $transporter = new StepAggregator(new ArrayReader($rows));
            $transporter->addWriter($writer);

            if ('test' != $_ENV['APP_ENV']) {
                $doctrineWriter = new DoctrineWriter($this->em, ProductData::class);
                $doctrineWriter->disableTruncate();
                $transporter->addWriter($doctrineWriter);
            }
            $transporter->process();
        } catch (Exception $e) {
            throw new SaverException('Rows can\'t been saved!', previous: $e);
        }

        return $rows;
    }
}
