<?php

declare(strict_types=1);

namespace App\Services\Import\Readers\StatusOfCSV;

use App\Entity\ImportStatus;
use App\Services\Import\Exceptions\ReaderException;
use App\Services\Import\Readers\CSV\Reader as MyCSVReader;
use App\Services\Import\Readers\CSV\Settings;
use App\Services\Import\Readers\ReaderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Reader implements ReaderInterface
{
    /**
     * @param ImportStatus $status
     */
    public function __construct(
        private ImportStatus $status,
    ) {
    }

    /**
     * @return string[][]
     *
     * @throws ReaderException
     */
    public function read(): array
    {
        $file = new UploadedFile($this->status->getFileTmpName(), $this->status->getFileOriginalName());
        $settings = Settings::fromString($this->status->getCsvSettings());

        $reader = new MyCSVReader(
            $file,
            $settings,
        );

        return $reader->read();
    }
}
