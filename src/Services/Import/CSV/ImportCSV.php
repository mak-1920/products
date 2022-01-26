<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

use App\Services\Import\Import;
use App\Services\Import\ImportRequest;
use App\Services\Import\Savers\Saver;
use League\CSV\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\SyntaxError;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportCSV extends Import
{
    private static array $headerTitles = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    private bool $headerMustSynchronize;
    private Reader $csv;

    /**
     * @param UploadedFile[] $files
     * @param CSVSettings $csvSettings
     * @param bool $isTest
     * @param Saver|null $saver
     *
     * @throws InvalidArgument|Exception
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
     * @param UploadedFile[] $files
     * @param CSVSettings $csvSettings
     *
     * @return string[][]
     *
     * @throws InvalidArgument|Exception
     */
    private function setDataFromFiles(array $files, CSVSettings $csvSettings): array
    {
        $records = [];

        foreach ($files as $file) {
            $records = array_merge($records, $this->setDataFromFile($file->getRealPath(), $csvSettings));
        }

        return $records;
    }

    /**
     * @param string $filePath
     * @param CSVSettings $settings
     *
     * @return string[][]
     *
     * @throws InvalidArgument|Exception
     */
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

    /**
     * @param CSVSettings $settings
     *
     * @return void
     *
     * @throws Exception
     * @throws InvalidArgument
     */
    private function setCSVSettings(CSVSettings $settings): void
    {
        $this->csv->setDelimiter($settings->getDelimiter());
        $this->csv->setEscape($settings->getEscape());
        $this->csv->setEnclosure($settings->getEnclosure());
        if ($settings->isHavingHeader()) {
            $this->csv->setHeaderOffset(0);
        }
    }

    /**
     * @return bool
     */
    private function isNeedSynchronizeColumnNamesWithArrayElements(): bool
    {
        if (null === $this->csv->getHeaderOffset()) {
            return false;
        }

        return $this->isValidHeader();
    }

    /**
     * @return bool
     */
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
}
