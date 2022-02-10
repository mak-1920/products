<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Exceptions\ImportException;
use App\Services\Import\Readers\ReaderInterface;
use App\Services\Import\Savers\SaverInterface;
use App\Services\Import\Transform\ConverterInterface;
use App\Services\Import\Transform\FilterInterface;

class Import
{
    public static array $headerTitles = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    /** @var string[][] $success */
    private array $success;

    /** @var string[][] $failed */
    private array $failed;

    /** @var string[][] $requests */
    private array $requests;

    private bool $isFailed;

    /**
     * @param ReaderInterface $reader
     * @param SaverInterface $saver
     * @param ConverterInterface|null $converter
     * @param FilterInterface|null $filter
     */
    public function __construct(
        private ReaderInterface $reader,
        private SaverInterface $saver,
        private ?ConverterInterface $converter = null,
        private ?FilterInterface $filter = null,
    ) {
        $this->requests = [];
        $this->success = [];
        $this->failed = [];
        $this->isFailed = false;
    }

    /**
     * @return string[][] imported rows
     */
    public function import(): array
    {
        $rows = [];

        try {
            $rows = $this->reader->read();
            $this->setRequests($rows);

            $rows = $this->filter?->filter($rows);
            $rows = $this->converter?->convert($rows);
            $rows = $this->saver->save($rows);
            $this->setResult($rows);
        } catch (ImportException) {
            $this->isFailed = true;
        }

        return $rows;
    }

    /**
     * @param string[][] $validRows
     *
     * @return void
     */
    private function setResult(array $validRows): void
    {
        $this->success = $validRows;
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
     * @return string[][]
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @param string[][] $rows
     *
     * @return void
     */
    private function setRequests(array $rows): void
    {
        foreach ($rows as $row) {
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

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->isFailed;
    }
}
