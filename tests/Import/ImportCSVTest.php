<?php

declare(strict_types=1);

namespace App\Tests\Import;

use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use App\Tests\Import\Helpers\Saver;
use PHPUnit\Framework\TestCase;

class ImportCSVTest extends TestCase
{
    public function testValidateWithoutHeaders(): void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/normal_without_headers.csv', 
            new CSVSettings(),
            true
        );

        $this->assertEquals(count($import->getComplete()), 5);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testValidateWithHeadersInDefaultOrder(): void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/normal_with_headers_in_default_order.csv', 
            new CSVSettings(haveHeader: true),
            true
        );

        $this->assertEquals(count($import->getComplete()), 5);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testValidateWithHeadersInNotDefaultOrder1(): void
    {
        for($i = 1; $i <= 5; $i++) {
            $import = new ImportCSV(
                __DIR__.'/csv/normal_with_headers_in_not_default_order_' . $i . '.csv', 
                new CSVSettings(haveHeader: true),
                true
            );

            $this->assertEquals(count($import->getComplete()), 5);
            $this->assertEquals(count($import->getFailed()), 0);
        }
    }

    public function testWithLessColums(): void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/invalid_with_less_columns.csv', 
            new CSVSettings(),
            true
        );

        $this->assertEquals(count($import->getComplete()), 0);
        $this->assertEquals(count($import->getFailed()), 5);
    }

    public function testWithMoreColums(): void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/invalid_with_more_columns.csv', 
            new CSVSettings(),
            true
        );

        $this->assertEquals(count($import->getComplete()), 0);
        $this->assertEquals(count($import->getFailed()), 5);
    }

    public function testWithInvalidDataInCSV(): void
    {
        $columnName = ['product' => 1, 'cost' => 4, 'count' => 2];

        foreach($columnName as $name => $rows) {
            $import = new ImportCSV(
                __DIR__.'/csv/invalid_with_error_in_' . $name . '.csv', 
                new CSVSettings(),
                true
            );

            $this->assertEquals(count($import->getComplete()), 0);
            $this->assertEquals(count($import->getFailed()), $rows);
        }
    }
}
