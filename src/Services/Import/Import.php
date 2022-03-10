<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Exceptions\ImportException;
use App\Services\Import\Readers\ReaderInterface;
use App\Services\Import\Savers\SaverInterface;
use App\Services\Import\Transform\TransformInterface;

class Import
{
    /** @var string[] $headerTitles */
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
     * @param TransformInterface|null $costConverter
     * @param TransformInterface|null $converter
     * @param TransformInterface|null $filter
     */
    public function __construct(
        private ReaderInterface $reader,
        private SaverInterface $saver,
        private ?TransformInterface $costConverter = null,
        private ?TransformInterface $converter = null,
        private ?TransformInterface $filter = null,
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

            $rows = $this->tryTransform('costConverter', $rows);
            $rows = $this->tryTransform('filter', $rows);
            $rows = $this->tryTransform('converter', $rows);
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
        $this->failed = array_values(array_udiff($this->requests, $this->success, [$this, 'productsCompare']));
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
     * @param string $transformType
     * @param string[][] $rows
     *
     * @return string[][]
     */
    private function tryTransform(string $transformType, array $rows): array
    {
        if (!is_null($this->$transformType)) {
            $rows = $this->$transformType->transform($rows);
        }

        return $rows;
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
     * @return string[][]
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
