<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

use App\Services\Import\Import;
use App\Services\Import\ImportRequest;
use App\Services\Import\Savers\Saver;
use Port\Csv\CsvReader;
use Port\Exception\DuplicateHeadersException;
use SplFileObject;

class ImportCSV extends Import
{
    private static array $headerTitles = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    private bool $headerMustSynchronize;

    /**
     * @param SplFileObject[] $files
     * @param CSVSettings $csvSettings
     * @param bool $isTest
     * @param Saver|null $saver
     */
    public function __construct(
        array $files,
        CSVSettings $csvSettings,
        bool $isTest,
        Saver $saver = null,
    ) {
        $data = $this->setDataFromFiles($files, $csvSettings);

        parent::__construct($data, $isTest, $saver);
    }

    /**
     * @param string[][] $data
     *
     * @return void
     */
    protected function setRequestsFromData(array $data): void
    {
        foreach ($data as $row) {
            if ($this->headerMustSynchronize) {
                $request = [];
                for ($i = 0; $i < count(self::$headerTitles); ++$i) {
                    $request[] = $row[self::$headerTitles[$i]];
                }
                $this->requests[] = new ImportRequest($request);
            } else {
                $row = array_values($row);
                $this->requests[] = new ImportRequest($row);
            }
        }
    }

    /**
     * @param SplFileObject[] $files
     * @param CSVSettings $csvSettings
     *
     * @return string[][]
     */
    private function setDataFromFiles(array $files, CSVSettings $csvSettings): array
    {
        $records = [];

        foreach ($files as $file) {
            $records = array_merge($records, $this->setDataFromFile($file, $csvSettings));
        }

        return $records;
    }

    /**
     * @param SplFileObject $file
     * @param CSVSettings $settings
     *
     * @return string[][]
     */
    private function setDataFromFile(SplFileObject $file, CSVSettings $settings): array
    {
        $reader = $this->getReader($file, $settings);

        if(is_null($reader)) {
            return [];
        }

        $this->headerMustSynchronize = $this->isNeedSynchronizeColumnNamesWithArrayElements($reader->getColumnHeaders());

        $rows = [];
        foreach ($reader as $record) {
            $rows[] = $record;
        }

        return $rows;
    }

    /**
     * @param SplFileObject $file
     * @param CSVSettings $settings
     * @return ?CsvReader
     */
    private function getReader(SplFileObject $file, CSVSettings $settings) : ?CsvReader
    {
        $reader = new CsvReader($file, $settings->getDelimiter(), $settings->getEnclosure(), $settings->getEscape());
        $reader->setStrict(false);
        if ($settings->isHavingHeader()) {
            try {
                $reader->setHeaderRowNumber(0);
            } catch (DuplicateHeadersException) {
                return null;
            }
        }

        return $reader;
    }

    /**
     * @param string[] $header
     *
     * @return bool
     */
    private function isValidHeader(array $header): bool
    {
        return $this->isValidCountTitles($header)
            && $this->isValidTitles($header);
    }

    /**
     * @param string[] $header
     *
     * @return bool
     */
    private function isValidCountTitles(array $header): bool
    {
        return count($header) == count(self::$headerTitles);
    }

    /**
     * @param string[] $header
     *
     * @return bool
     */
    private function isValidTitles(array $header): bool
    {
        for ($i = 0; $i < count(self::$headerTitles); ++$i) {
            if (!in_array(self::$headerTitles[$i], $header)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[] $header
     *
     * @return bool
     */
    private function isNeedSynchronizeColumnNamesWithArrayElements(array $header): bool
    {
        if (0 === count($header)) {
            return false;
        }

        return $this->isValidHeader($header);
    }
}
