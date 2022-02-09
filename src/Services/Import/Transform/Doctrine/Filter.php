<?php

declare(strict_types=1);

namespace App\Services\Import\Transform\Doctrine;

use App\Repository\ProductDataRepository;
use App\Services\Import\Exceptions\FilterException;
use App\Services\Import\Import;
use App\Services\Import\Transform\FilterInterface;
use Port\Exception;
use Port\Reader\ArrayReader;
use Port\Steps\Step\FilterStep;
use Port\Steps\StepAggregator;
use Port\Writer\ArrayWriter;

class Filter implements FilterInterface
{
    /** @var string[][] $rows */
    private array $rows;

    public function __construct(
        private ProductDataRepository $repository,
    ) {
    }

    /**
     * @param array $rows
     *
     * @return string[][] filtered rows
     *
     * @throws FilterException
     */
    public function filter(array $rows): array
    {
        $this->initFields($rows);

        try {
            $this->setRowsByFiltered();
        } catch (Exception $e) {
            throw new FilterException('Rows can\'t been filtered!', previous: $e);
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
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    private function setRowsByFiltered(): void
    {
        $this->setRowsByFilter([$this, 'getFilterByValidation']);
        $this->setRowsByFilter([$this, 'getFilterByExistsCodes']);
        $this->setRowsByFilter([$this, 'getFilterByClonedCodes']);
    }

    /**
     * @param callable $getFilter
     *
     * @return void
     *
     * @throws Exception
     */
    private function setRowsByFilter(callable $getFilter): void
    {
        $transporter = $this->getTransporter();

        $transporter->addStep($getFilter());
        $transporter->process();
    }

    /**
     * @return FilterStep
     */
    private function getFilterByValidation(): FilterStep
    {
        $filter = new FilterStep();

        $filter->add(fn ($el) => $this->isValidData($el));

        return $filter;
    }

    /**
     * @return FilterStep
     */
    private function getFilterByExistsCodes(): FilterStep
    {
        $existsCodes = $this->getExistsProductCodes($this->rows);

        $filter = new FilterStep();
        $filter->add(fn ($el) => !in_array($el['Product Code'], $existsCodes));

        return $filter;
    }

    /**
     * @return FilterStep
     */
    private function getFilterByClonedCodes(): FilterStep
    {
        $codes = [];

        $filter = new FilterStep();
        $filter->add(function ($el) use (&$codes) {
            $exists = in_array($el['Product Code'], $codes);
            if (!$exists) {
                $codes[] = $el['Product Code'];
            }

            return !$exists;
        });

        return $filter;
    }

    /**
     * @param string[] $data
     *
     * @return bool
     */
    private function isValidData(array $data): bool
    {
        return $this->isValidArgsCount($data)
            && !$this->stringIsNullOrEmpty($data['Product Code'])
            && !$this->stringIsNullOrEmpty($data['Product Name'])
            && !$this->stringIsNullOrEmpty($data['Stock'])
            && $this->isValidCost($data['Cost in GBP'])
            && $this->isSatisfiesRules($data);
    }

    /**
     * @param string[][] $rows
     *
     * @return string[]
     */
    private function getExistsProductCodes(array $rows): array
    {
        $codes = $this->getCodes($rows);
        $existsCodes = $this->repository->getExistsProductCodes($codes);

        return $existsCodes;
    }

    /**
     * @param string[][] $rows
     *
     * @return string[]
     */
    private function getCodes(array $rows): array
    {
        $result = [];

        foreach ($rows as $row) {
            $result[] = $row['Product Code'];
        }

        return $result;
    }

    /**
     * @return StepAggregator
     */
    private function getTransporter(): StepAggregator
    {
        $transporter = new StepAggregator(new ArrayReader($this->rows));
        $transporter->addWriter(new ArrayWriter($this->rows));

        return $transporter;
    }

    /**
     * @param string[] $data
     *
     * @return bool
     */
    private function isValidArgsCount(array $data): bool
    {
        return count(Import::$headerTitles) == count($data);
    }

    /**
     * @param string|null $str
     *
     * @return bool
     */
    private function stringIsNullOrEmpty(?string $str): bool
    {
        return null == $str || '' == trim($str);
    }

    /**
     * @param string $cost
     *
     * @return bool
     */
    private function isValidCost(string $cost): bool
    {
        return (bool) preg_match('/^\d+(\.\d{2})?$/i', $cost);
    }

    /**
     * @param string[] $data
     *
     * @return bool
     */
    private function isSatisfiesRules(array $data): bool
    {
        return !(round((float) $data['Cost in GBP'], 2) < 5 && (int) $data['Stock'] < 10)
            && !(round((float) $data['Cost in GBP'], 2) > 1000);
    }
}
