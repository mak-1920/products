<?php

declare(strict_types=1);

namespace App\Services\Import\Transform\Doctrine;

use App\Repository\ProductDataRepository;
use App\Services\Import\Exceptions\ConverterException;
use App\Services\Import\Transform\ConverterInterface;
use DateTime;
use Port\Exception;
use Port\Reader\ArrayReader;
use Port\Steps\Step\ConverterStep;
use Port\Steps\StepAggregator;
use Port\Writer\ArrayWriter;

class Converter implements ConverterInterface
{
    /** @var string[][] $rows */
    private array $rows;

    private StepAggregator $transporter;

    public function __construct(
        private ProductDataRepository $repository,
    ) {
    }

    /**
     * @param string[][] $rows
     *
     * @return string[][] converted rows
     *
     * @throws ConverterException
     */
    public function convert(array $rows): array
    {
        $this->initFields($rows);

        try {
            $this->convertRows();
        } catch (Exception $e) {
            throw new ConverterException('Rows can\'t been converted!', previous: $e);
        }

        return $this->rows;
    }

    /**
     * @param string[][] $rows
     *
     * @return void
     */
    private function initFields(array $rows): void
    {
        $this->rows = $rows;

        $this->transporter = new StepAggregator(new ArrayReader($this->rows));
        $this->transporter->addWriter(new ArrayWriter($this->rows));
    }

    /**
     * @throws Exception
     */
    private function convertRows(): void
    {
        $this->transporter->addStep($this->getConverterTypes());
        $this->transporter->addStep($this->getConverterByDiscontinued());
        $this->transporter->addStep($this->getConverterKeys());

        $this->transporter->process();
    }

    private function getConverterTypes(): ConverterStep
    {
        $converter = new ConverterStep();

        $converter->add(function ($el) {
            $el['Stock'] = (int) $el['Stock'];
            $el['Cost in GBP'] = (float) $el['Cost in GBP'];
            $discontinued = '' !== $el['Discontinued'] && $el['Discontinued'];
            if ($discontinued) {
                $el['Discontinued'] = new DateTime();
            }

            return $el;
        });

        return $converter;
    }

    /**
     * @return ConverterStep
     */
    private function getConverterByDiscontinued(): ConverterStep
    {
        $discontinued = $this->getDiscontinued($this->rows);

        $converter = new ConverterStep();
        $converter->add(function ($el) use (&$discontinued) {
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
                $el['discontinuedAt'] = $el['Discontinued'];
            }

            return $el;
        });

        return $converter;
    }

    /**
     * @param string[][] $rows
     *
     * @return array<string, DateTime>
     */
    private function getDiscontinued(array $rows): array
    {
        $names = $this->getProductsFieldByColumnTitle($rows, 'Product Name');

        $discontinuedProducts = $this->repository->getDiscontinuedProductsByNames($names);
        $discontinuedProducts = $this->transformDiscontinuedArr($discontinuedProducts);

        return $discontinuedProducts;
    }

    /**
     * @param array<array{name: string, discontinuedAt: DateTime}> $array
     *
     * @return array<string, DateTime>
     */
    private function transformDiscontinuedArr(array $array): array
    {
        $result = [];

        for ($i = 0; $i < count($array); ++$i) {
            $result[$array[$i]['name']] = $array[$i]['discontinuedAt'];
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
