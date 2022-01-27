<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use App\Entity\ProductData;
use App\Repository\ProductDataRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Port\Doctrine\DoctrineWriter;
use Port\Steps\Step\ConverterStep;
use Port\Steps\Step\FilterStep;
use Port\Steps\StepAggregator;
use Port\Writer\ArrayWriter;

class DoctrineSaver implements Saver
{
    private ProductDataRepository $productRepository;

    public function __construct(
        private EntityManager $em,
    ) {
        $this->productRepository = $em->getRepository(ProductData::class);
    }

    /**
     * @param StepAggregator $transporter
     *
     * @return string[][] success
     */
    public function save(StepAggregator $transporter): array
    {
        $validRows = [];
        $transporter->addWriter(new ArrayWriter($validRows));
        $transporter->process();

        $transporter->addStep($this->getFilterByExistsCodes($validRows));
        $transporter->process();
        $transporter->addStep($this->getConverterByDiscontinued($validRows));

        $transporter->addStep($this->getConverterKeys());

        $doctrineWriter = new DoctrineWriter($this->em, ProductData::class);
        $doctrineWriter->disableTruncate();
        $transporter->addWriter($doctrineWriter);
        $transporter->process();

        return $validRows;
    }

    /**
     * @param string[][] $rows
     *
     * @return FilterStep
     */
    private function getFilterByExistsCodes(array $rows): FilterStep
    {
        $existsCodes = $this->getExistsProductCodes($rows);

        $filter = new FilterStep();
        $filter->add(function ($el) use ($existsCodes) {
            $isExists = in_array($el['Product Code'], $existsCodes);
            if (!$isExists) {
                $existsCodes[] = $el['Product Code'];
            }

            return !$isExists;
        });

        return $filter;
    }

    /**
     * @param string[][] $rows
     *
     * @return ConverterStep
     */
    private function getConverterByDiscontinued(array $rows): ConverterStep
    {
        $discontinued = $this->getDiscontinued($rows);

        $converter = new ConverterStep();
        $converter->add(function ($el) use ($discontinued) {
            if (array_key_exists($el['Product Name'], $discontinued)) {
                $el['Discontinued'] = $discontinued[$el['Product Name']];
            } elseif ($el['Discontinued']) {
                $discontinued[$el['Product Name']] = $el['Discontinued'];
            }

            return $el;
        });

        return $converter;
    }

    /**
     * @return ConverterStep
     */
    private function getConverterKeys(): ConverterStep
    {
        $converter = new ConverterStep();

        $converter->add(function ($el) {
            $el['name'] = $el['Product Name'];
            $el['descriptions'] = $el['Product Description'];
            $el['code'] = $el['Product Code'];
            $el['cost'] = $el['Cost in GBP'];
            $el['stock'] = $el['Stock'];
            if ($el['Discontinued'] instanceof DateTime) {
                $el['timeOfDiscontinued'] = $el['Discontinued'];
            }

            return $el;
        });

        return $converter;
    }

    /**
     * @param string[][] $rows
     *
     * @return string[]
     */
    private function getExistsProductCodes(array $rows): array
    {
        $codes = $this->getProductsFieldByColumnTitle($rows, 'Product Code');
        $existsCodes = $this->productRepository->getExistsProductCodes($codes);

        return $existsCodes;
    }

    /**
     * @param string[][] $rows
     *
     * @return array<string, DateTime>
     */
    private function getDiscontinued(array $rows): array
    {
        $names = $this->getProductsFieldByColumnTitle($rows, 'Product Name');

        $discontinuedProducts = $this->productRepository->getDiscontinuedProductsByNames($names);
        $discontinuedProducts = $this->transformDiscontinuedArr($discontinuedProducts);

        return $discontinuedProducts;
    }

    /**
     * @param array{dtmdiscontinued: DateTime, strproductname: string} $array
     *
     * @return array<string, DateTime>
     */
    private function transformDiscontinuedArr(array $array): array
    {
        $result = [];

        for ($i = 0; $i < count($array); ++$i) {
            $result[$array[$i]['name']] = $array[$i]['timeOfDiscontinued'];
        }

        return $result;
    }

    /**
     * @param string[][] $rows
     * @param string $title
     *
     * @return string[]
     */
    private function getProductsFieldByColumnTitle(array $rows, string $title): array
    {
        $result = [];

        foreach ($rows as $row) {
            $result[] = $row[$title];
        }

        return $result;
    }
}
