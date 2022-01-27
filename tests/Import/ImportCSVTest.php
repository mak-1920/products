<?php

namespace App\Tests\Import;

use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportCSVTest extends TestCase
{
    private function getFiles(array $paths): array
    {
        $files = [];
        foreach($paths as $path) {
            $file = $this->getMockBuilder(UploadedFile::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getRealPath'])
                ->getMock();

            $file->expects($this->once())
                ->method('getRealPath')
                ->will($this->returnValue($path));

            $files[] = $file;
        }

        return $files;
    }

    private function getImport(array $paths): ImportCSV
    {
        $import = new ImportCSV(
            $this->getFiles($paths),
            new CSVSettings(haveHeader: true),
            true
        );

        $import->saveRequests();

        return $import;
    }

    public function testValid() : void
    {
        $paths = [
            __DIR__.'/csv/normal_data_with_header.csv', 
        ];

        $import = $this->getImport($paths);

        $this->assertCount(5, $import->getComplete());
        $this->assertCount(0, $import->getFailed());
    }

    public function testInvalidBySyntax() : void
    {
        $paths = [
            __DIR__.'/csv/invalid_rows_by_syntax.csv', 
        ];

        $import = $this->getImport($paths);

        $this->assertCount(0, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function testInvalidByRules() : void
    {
        $paths = [
            __DIR__.'/csv/invalid_rows_by_rules.csv', 
        ];

        $import = $this->getImport($paths);

        $this->assertCount(0, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function test2Valid3Invalid() : void
    {
        $paths = [
            __DIR__.'/csv/2_valid_3_invalid.csv', 
        ];

        $import = $this->getImport($paths);

        $this->assertCount(2, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function testWithRepeatedNamesByColumns() : void
    {
        $paths = [
            __DIR__.'/csv/header_with_more_columns.csv', 
        ];

        $import = $this->getImport($paths);

        $this->assertCount(0, $import->getComplete());
        $this->assertCount(0, $import->getFailed());
    }

    public function testCheckHeader() : void
    {
        $headers = [
            'Discontinued,Stock,Product Code,Product Description,Cost in GBP,Product Name' => true,
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued' => true,
            'Product Code,Product Name,Product Description,Stock,Cost in GBP' => false,
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued,Product Name' => false,
        ];

        foreach($headers as $header => $testResult) {
            $titles = explode(',', $header);

            $import = $this->getImport([__DIR__ . '/csv/header_valid.csv']);

            $this->assertEquals($testResult, $this->invokeMethod($import, 'isValidHeader', [$titles]));
        }
    }

    public function testMultiple() : void
    {
        $fileTitles = [
            __DIR__ . '/csv/multiple_1.csv',
            __DIR__ . '/csv/multiple_2.csv',
        ];

        $import = $this->getImport($fileTitles);
        $requests = $import->getRequests();

        $this->assertCount(2, $requests);
        $this->assertCount(2, $import->getComplete());
        $this->assertCount(0, $import->getFailed());
        $this->assertEquals('TV, P0001, , 32” Tv, 10, 399.99', implode(', ', $requests[0]));
        $this->assertEquals('P0009, Harddisk, Great for storing data, 0, 99.99, ', implode(', ', $requests[1]));
    }

    /**
     * @throws \ReflectionException
     */
    private function invokeMethod(
        object &$object, 
        string $methodName, 
        array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
