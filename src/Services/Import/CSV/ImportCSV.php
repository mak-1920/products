<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

use App\Entity\ImportStatus;
use App\Services\Import\Import;
use App\Services\Import\Savers\Saver;
use Port\Csv\CsvReader;
use Port\Exception\ReaderException;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportCSV extends Import
{
    private bool $isParsed;

    /**
     * @param ImportStatus $status
     * @param Saver|null $saver
     *
     * @return ImportCSV
     */
    public static function ImportFileByStatus(
        ImportStatus $status,
        Saver|null $saver,
    ): ImportCSV {
        $file = new UploadedFile($status->getFileTmpName(), $status->getFileOriginalName());
        $settings = CSVSettings::fromString($status->getCsvSettings());

        $import = new ImportCSV(
            $file,
            $settings,
            $saver
        );
        $import->saveRequests();

        return $import;
    }

    /**
     * @param File $file
     * @param CSVSettings $csvSetting
     * @param Saver|null $saver
     */
    public function __construct(
        File $file,
        CSVSettings $csvSetting,
        Saver $saver = null,
    ) {
        $this->isParsed = true;

        $reader = $this->getReader($file, $csvSetting);

        parent::__construct($reader, $saver);
    }

    /**
     * @param File $file
     * @param CSVSettings $settings
     *
     * @return ?CsvReader
     */
    private function getReader(File $file, CSVSettings $settings): ?CsvReader
    {
        $reader = new CsvReader(
            new SplFileObject($file->getRealPath()),
            $settings->getDelimiter(),
            $settings->getEnclosure(),
            $settings->getEscape()
        );
        $reader->setStrict(false);

        if (!$this->setHeaderSettings($reader, $settings->getHaveHeader())) {
            $this->isParsed = false;

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
        } catch (ReaderException) {
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
        for ($i = 0; $i < count(self::$headerTitles); ++$i) {
            if (!in_array(self::$headerTitles[$i], $header)) {
                return false;
            }
        }

        return count(self::$headerTitles) === count($header);
    }

    /**
     * @return bool
     */
    public function isParsed(): bool
    {
        return $this->isParsed;
    }
}
