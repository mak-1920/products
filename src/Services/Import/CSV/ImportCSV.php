<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

use App\Services\Import\Import;
use App\Services\Import\Savers\Saver;
use Port\Csv\CsvReader;
use Port\Exception\DuplicateHeadersException;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportCSV extends Import
{
    /** @var string[] $notParsedFiles */
    private array $notParsedFiles;

    /**
     * @param UploadedFile[] $files
     * @param CSVSettings[] $csvSettings
     * @param bool $isTest
     * @param Saver|null $saver
     */
    public function __construct(
        array $files,
        array $csvSettings,
        bool $isTest,
        Saver $saver = null,
    ) {
        $csvSettings = array_pad($csvSettings, count($files), new CSVSettings());

        $readers = $this->getReaders($files, $csvSettings);

        parent::__construct($readers, $isTest, $saver);
    }

    /**
     * @param UploadedFile[] $files
     * @param CSVSettings[] $csvSettings
     *
     * @return CsvReader[]
     */
    private function getReaders(array $files, array $csvSettings): array
    {
        $readers = [];

        for ($i = 0; $i < count($files); ++$i) {
            $reader = $this->getReader($files[$i], $csvSettings[$i]);
            if (!is_null($reader)) {
                $readers[] = $reader;
            } else {
                $this->notParsedFiles[] = $files[$i]->getClientOriginalName();
            }
        }

        return $readers;
    }

    /**
     * @param UploadedFile $file
     * @param CSVSettings $settings
     *
     * @return ?CsvReader
     */
    private function getReader(UploadedFile $file, CSVSettings $settings): ?CsvReader
    {
        $reader = new CsvReader(
            new SplFileObject($file->getRealPath()),
            $settings->getDelimiter(),
            $settings->getEnclosure(),
            $settings->getEscape()
        );
        $reader->setStrict(false);

        if (!$this->setHeaderSettings($reader, $settings->getHaveHeader())) {
            return null;
        }

        return $reader;
    }

    /**
     * @param CsvReader $reader
     * @param bool $haveHeader
     *
     * @return bool
     */
    private function setHeaderSettings(CsvReader $reader, bool $haveHeader): bool
    {
        $success = true;

        if ($haveHeader) {
            $success = $this->setExistsHeaderSettings($reader);
        } else {
            $reader->setColumnHeaders(self::$headerTitles);
        }

        return $success;
    }

    /**
     * @param CsvReader $reader
     *
     * @return bool false if invalid
     */
    private function setExistsHeaderSettings(CsvReader $reader): bool
    {
        try {
            $reader->setHeaderRowNumber(0);
            $header = $reader->getColumnHeaders();
            if (!$this->isValidHeader($header)) {
                return false;
            }
        } catch (DuplicateHeadersException) {
            return false;
        }

        return true;
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
     * @return string[]
     */
    public function getNotParsedFiles() : array
    {
        return $this->notParsedFiles;
    }
}
