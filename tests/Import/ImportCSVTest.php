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
            true,
            new Saver()
        );

        $this->assertEquals(count($import->getComplete()), 5);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testValidateWithHeadersInDefaultOrder(): void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/normal_with_headers_in_default_order.csv', 
            new CSVSettings(haveHeader: true),
            true,
            new Saver()
        );

        $this->assertEquals(count($import->getComplete()), 5);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testValidateWithHeadersInNotDefaultOrder1(): void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/normal_with_headers_in_not_default_order_1.csv', 
            new CSVSettings(haveHeader: true),
            true,
            new Saver()
        );

        $this->assertEquals(count($import->getComplete()), 5);
        $this->assertEquals(count($import->getFailed()), 0);
    }
}
