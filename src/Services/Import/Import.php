<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Savers\Saver;
use DateTime;
use Port\Reader;
use Port\Steps\Step\ConverterStep;
use Port\Steps\Step\FilterStep;
use Port\Steps\StepAggregator;

abstract class Import
{
    protected static array $headerTitles = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    /** @var StepAggregator $transporter */
    private StepAggregator $transporter;

    /** @var string[][] $success */
    private array $success;

    /** @var string[][] $failed */
    private array $failed;

    /** @var string[][] $requests */
    private array $requests;

    /**
     * @param Reader $reader
     * @param Saver|null $saver
     */
    public function __construct(
        private Reader $reader,
        private ?Saver $saver = null,
    ) {
        $this->requests = [];
        $this->success = [];
        $this->failed = [];

        $this->setTransporter();
    }

    /**
     * @return void
     */
    public function saveRequests(): void
    {
        $this->setRequests();

        $this->success = $this->saver->save($this->transporter);
        $this->failed = array_udiff($this->requests, $this->success, [$this, 'productsCompare']);
    }

    /**
     * @param string[] $a
     * @param string[] $b
     *
     * @return int
     */
    private function productsCompare(array $a, array $b): int
    {
        foreach (self::$headerTitles as $title) {
            if ('Discontinued' == $title) {
                continue;
            }
            if ($a[$title] != $b[$title]) {
                return $a[$title] <=> $b[$title];
            }
        }

        return 0;
    }

    /**
     * @return StepAggregator
     */
    public function getTransporter(): StepAggregator
    {
        return $this->transporter;
    }

    /**
     * @return void
     */
    private function setTransporter(): void
    {
        $this->transporter = new StepAggregator($this->reader);
        $this->transporter->addStep($this->getFilters());
        $this->transporter->addStep($this->getConverters());
    }

    /**
     * @return FilterStep
     */
    private function getFilters(): FilterStep
    {
        $filter = new FilterStep();

        $filter->add(fn ($el) => $this->isValidData($el));

        return $filter;
    }

    /**
     * @return ConverterStep
     */
    private function getConverters(): ConverterStep
    {
        $converter = new ConverterStep();

        $converter->add(function ($el) {
            $el['Stock'] = (int) $el['Stock'];
            $el['Cost in GBP'] = (float) $el['Cost in GBP'];
            $discontinued = !$this->stringIsNullOrEmpty($el['Discontinued']) && $el['Discontinued'];
            if ($discontinued) {
                $el['Discontinued'] = new DateTime();
            }

            return $el;
        });

        return $converter;
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
     * @param string[] $data
     *
     * @return bool
     */
    private function isValidArgsCount(array $data): bool
    {
        return count(self::$headerTitles) == count($data);
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

    /**
     * @return string[][]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @return void
     */
    private function setRequests(): void
    {
        foreach ($this->reader as $row) {
            $this->requests[] = $row;
        }
    }

    /**
     * @return string[][]
     */
    public function getFailed(): array
    {
        return $this->failed;
    }

    /**
     * @return string[]
     */
    public function getComplete(): array
    {
        return $this->success;
    }
}
