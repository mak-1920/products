<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Savers\Saver;
use Port\Reader;
use Port\Steps\Step\ConverterStep;
use Port\Steps\Step\FilterStep;
use Port\Steps\StepAggregator;
use Port\Writer\ArrayWriter;

abstract class Import
{
//    /** @var ImportRequest[] $requests * */
//    protected array $requests;
    protected static array $headerTitles = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    /** @var StepAggregator[] $transporters */
    private array $transporters;

    /** @var string[][] $success */
    private array $success;

    /** @var string[][] $failed */
    private array $failed;

    /** @var string[][] $requests */
    private array $requests;

    /**
     * @param Reader[] $readers
     * @param bool $isTest
     * @param Saver|null $saver
     */
    public function __construct(
        private array $readers,
        protected bool $isTest,
        private ?Saver $saver = null,
    ) {
        $this->requests = [];
        $this->success = [];
        $this->failed = [];
        $this->transporters = [];

        $this->setTransporters();
    }

    /**
     * @return void
     */
    public function saveRequests(): void
    {
        $this->setRequests();
        if ($this->isTest) {
            $this->success = $this->testProcess();
        } else {
            $this->success = $this->saver->save($this->transporters);
        }
        $this->failed = array_udiff($this->requests, $this->success, [$this, 'productsCompare']);
    }

    private function productsCompare(array $a, array $b): int
    {
        foreach (self::$headerTitles as $title) {
            if ($a[$title] != $b[$title]) {
                return $a[$title] <=> $b[$title];
            }
        }

        return 0;
    }

    /**
     * @return string[][] success
     */
    private function testProcess(): array
    {
        $result = [];

        foreach ($this->transporters as $transporter) {
            $success = [];
            $transporter->addWriter(new ArrayWriter($success));
            $transporter->process();
            $result = array_merge($result, $success);
        }

        return $result;
    }

    /**
     * @return StepAggregator[]
     */
    public function getTransporters(): array
    {
        return $this->transporters;
    }

    /**
     * @return void
     */
    private function setTransporters(): void
    {
        foreach ($this->readers as $reader) {
            $this->addTransporter($reader);
        }
    }

    /**
     * @param Reader $reader
     */
    private function addTransporter(Reader $reader): void
    {
        $transporter = new StepAggregator($reader);
        $transporter->addStep($this->getFilters());
        $transporter->addStep($this->getConverters());

        $this->transporters[] = $transporter;
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
            $el['Discontinued'] = !$this->stringIsNullOrEmpty($el['Discontinued']) && $el['Discontinued'];

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
        foreach ($this->readers as $reader) {
            foreach ($reader as $row) {
                $this->requests[] = $row;
            }
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
