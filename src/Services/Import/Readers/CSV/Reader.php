<?php

declare(strict_types=1);

namespace App\Services\Import\Readers\CSV;

use App\Services\Import\Exceptions\Reader\ReaderException;
use App\Services\Import\Import;
use App\Services\Import\Readers\ReaderInterface;
use Port\Csv\CsvReader;
use Port\Exception;
use Port\Steps\StepAggregator;
use Port\Writer\ArrayWriter;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\File;

class Reader implements ReaderInterface
{
    /**
     * @param File $file
     * @param Settings $settings
     */
    public function __construct(
        private File $file,
        private Settings $settings,
    ) {
    }

    /**
     * @return string[][]
     *
     * @throws ReaderException
     */
    public function read(): array
    {
        $reader = $this->getReader($this->file, $this->settings);

        if (is_null($reader)) {
            throw new ReaderException('File can\'t been read');
        }

        return $this->getRows($reader);
    }

    /**
     * @param File $file
     * @param Settings $settings
     *
     * @return ?CsvReader
     */
    private function getReader(File $file, Settings $settings): ?CsvReader
    {
        $path = $file->getRealPath();

        if (false === $path) {
            return null;
        }

        $reader = new CsvReader(
            new SplFileObject($path),
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
     *
     * @return string[][]
     *
     * @throws ReaderException
     */
    private function getRows(CsvReader $reader): array
    {
        $transporter = new StepAggregator($reader);

        $rows = [];
        $transporter->addWriter(new ArrayWriter($rows));

        try {
            $transporter->process();
        } catch (Exception $e) {
            throw new ReaderException('File can\'t been read', previous: $e);
        }

        return $rows;
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
            $reader->setColumnHeaders(Import::$headerTitles);
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
        } catch (Exception\DuplicateHeadersException) {
            return false;
        }

        if (!$this->isValidHeader($header)) {
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
        for ($i = 0; $i < count(Import::$headerTitles); ++$i) {
            if (!in_array(Import::$headerTitles[$i], $header)) {
                return false;
            }
        }

        return count(Import::$headerTitles) === count($header);
    }
}
