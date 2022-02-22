<?php

declare(strict_types=1);

namespace App\Tests\Import\Readers;

use App\Entity\ImportStatus;
use App\Services\Import\Exceptions\ReaderException;
use App\Services\Import\Readers\StatusOfCSV\Reader;
use PHPUnit\Framework\TestCase;

class ReaderByStatusTest extends TestCase
{
    public function testGetRows(): void
    {
        $reader = $this->getReader(__DIR__.'/../csv/normal_data_with_header.csv');
        $result = $reader->read();

        $this->assertCount(5, $result);
        $this->assertCount(6, $result[0]);
    }

    public function testGetReaderWithValidHeader(): void
    {
        $reader = $this->getReader(__DIR__.'/../csv/header_valid.csv');

        $result = $reader->read();

        $this->assertCount(1, $result);
        $this->assertEquals([
            'Product Code' => 'Product Code',
            'Product Name' => 'Product Name',
            'Product Description' => 'Product Description',
            'Stock' => 'Stock',
            'Cost in GBP' => 'Cost in GBP',
            'Discontinued' => 'Discontinued',
        ], $result[0]);
    }

    public function testGetReaderWithShuffleHeader(): void
    {
        $reader = $this->getReader(__DIR__.'/../csv/header_valid_shuffle.csv');

        $result = $reader->read();

        $this->assertCount(0, $result);
    }

    public function testGetReaderWithLessColumns(): void
    {
        $reader = $this->getReader(__DIR__.'/../csv/header_with_less_columns.csv');
        $isFailed = false;

        try {
            $reader->read();
        } catch (ReaderException) {
            $isFailed = true;
        }

        $this->assertTrue($isFailed);
    }

    public function testGetReaderWithMoreColumns(): void
    {
        $reader = $this->getReader(__DIR__.'/../csv/header_with_more_columns.csv');
        $isFailed = false;

        try {
            $reader->read();
        } catch (ReaderException) {
            $isFailed = true;
        }

        $this->assertTrue($isFailed);
    }

    /**
     * @param string $fileName
     * @param string $settings
     *
     * @return Reader
     */
    private function getReader(string $fileName, string $settings = ',  1'): Reader
    {
        $reader = new Reader($this->getImportStatus($fileName, $settings));

        return $reader;
    }

    /**
     * @param string $fileName
     * @param string $settings
     *
     * @return ImportStatus
     */
    private function getImportStatus(string $fileName, string $settings = ',  1'): ImportStatus
    {
        $status = new ImportStatus();

        $status->setCsvSettings($settings);
        $status->setFileTmpName($fileName);
        $status->setFileOriginalName($fileName);

        return $status;
    }
}
