<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

use App\Services\Import\Import;
use App\Services\Import\ImportRequest;
use App\Services\Import\Savers\Saver;
use League\Csv\Reader;
use League\Csv\SyntaxError;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportCSV extends Import
{
    private static array $headerTitles = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    private bool $headerMustSynchronize;
    private Reader $csv;

    public function __construct(
        array $files,
        CSVSettings $csvSettings,
        bool $isTest,
        Saver $saver = null,
    ) {
        $data = $this->setDataFromFiles($files, $csvSettings);

        parent::__construct($data, $isTest, $saver);
    }

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

    /** @param UploadedFile[] $files */
    private function setDataFromFiles(array $files, CSVSettings $csvSettings): array
    {
        $records = [];

        foreach ($files as $file) {
            $records = array_merge($records, $this->setDataFromFile($file->getRealPath(), $csvSettings));
        }

        return $records;
    }

    private function setDataFromFile(string $filePath, CSVSettings $settings): array
    {
        $this->csv = Reader::createFromPath($filePath);

        $this->setCSVSettings($settings);
        $this->headerMustSynchronize = $this->isNeedSynchronizeColumnNamesWithArrayElements();

        try {
            $records = $this->csv->getRecords();
        } catch (SyntaxError) {
            return [];
        }
        $rows = [];

        foreach ($records as $record) {
            $rows[] = $record;
        }

        return $rows;
    }

    private function setCSVSettings(CSVSettings $settings): void
    {
        $this->csv->setDelimiter($settings->getDelimiter());
        $this->csv->setEscape($settings->getEscape());
        $this->csv->setEnclosure($settings->getEnclosure());
        if ($settings->isHavingHeader()) {
            $this->csv->setHeaderOffset(0);
        }
    }

    private function isNeedSynchronizeColumnNamesWithArrayElements(): bool
    {
        if (null === $this->csv->getHeaderOffset()) {
            return false;
        }

        return $this->isValidHeader();
    }

    private function isValidHeader(): bool
    {
        try {
            $header = $this->csv->getHeader();
        } catch (SyntaxError) {
            return false;
        }

        return $this->isValidCountTitles($header)
            && $this->isValidTitles($header);
    }

    private function isValidCountTitles(array $header): bool
    {
        return count($header) == count(self::$headerTitles);
    }

    private function isValidTitles(array $header): bool
    {
        for ($i = 0; $i < count(self::$headerTitles); ++$i) {
            if (false === array_search(self::$headerTitles[$i], $header)) {
                return false;
            }
        }

        return true;
    }
}
