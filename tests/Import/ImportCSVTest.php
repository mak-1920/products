<?php

namespace App\Tests\Import;

use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportCSVTest extends TestCase
{
    private function getFiles(array $pathes): array
    {
        $files = [];
        foreach($pathes as $path) {
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

    private function getImport(array $pathes): ImportCSV
    {
        $import = new ImportCSV(
            $this->getFiles($pathes),
            new CSVSettings(haveHeader: true),
            true
        );

        return $import;
    }

    public function testValid() : void
    {
        $pathes = [
            __DIR__.'/csv/normal_data_with_header.csv', 
        ];

        $import = $this->getImport($pathes);

        $this->assertEquals(count($import->getComplete()), 5);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testInvalidBySyntax() : void
    {
        $pathes = [
            __DIR__.'/csv/invalid_rows_by_syntax.csv', 
        ];

        $import = $this->getImport($pathes);

        $this->assertEquals(count($import->getComplete()), 0);
        $this->assertEquals(count($import->getFailed()), 3);
    }

    public function testInvalidByRules() : void
    {
        $pathes = [
            __DIR__.'/csv/invalid_rows_by_rules.csv', 
        ];

        $import = $this->getImport($pathes);

        $this->assertEquals(count($import->getComplete()), 0);
        $this->assertEquals(count($import->getFailed()), 3);
    }

    public function test2Valid3Invalid() : void
    {
        $pathes = [
            __DIR__.'/csv/2_valid_3_invalid.csv', 
        ];

        $import = $this->getImport($pathes);

        $this->assertEquals(count($import->getComplete()), 2);
        $this->assertEquals(count($import->getFailed()), 3);
    }

    public function testWithRepeatedNamesByColumns() : void
    {
        $pathes = [
            __DIR__.'/csv/header_with_more_columns.csv', 
        ];

        $import = $this->getImport($pathes);

        $this->assertEquals(count($import->getComplete()), 0);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testCheckHeader() : void
    {
        $fileTitles = [
            'header_valid_shuffle.csv' => true,
            'header_valid.csv' => true,
            'header_with_less_colums.csv' => false,
            'header_with_more_columns.csv' => false,
        ];

        foreach($fileTitles as $title => $testResult) {
            $import = $this->getImport([__DIR__ . '/csv/' . $title]);

            $this->assertEquals($this->invokeMethod($import, 'checkHeader'), $testResult);
        }
    }

    public function testCheckMustSynchronization() : void
    {
        $fileTitles = [
            'header_valid_shuffle.csv' => true,
            'header_valid.csv' => true,
            'header_with_less_colums.csv' => false,
            'header_with_more_columns.csv' => false,
        ];

        foreach($fileTitles as $title => $testResult) {
            $import = $this->getImport([__DIR__ . '/csv/' . $title]);

            $this->assertEquals($this->invokeMethod($import, 'checkHeader'), $testResult);
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

        $this->assertEquals(count($requests), 2);
        $this->assertEquals(count($import->getComplete()), 2);
        $this->assertEquals(count($import->getFailed()), 0);
        $this->assertEquals((string)$requests[0], 'P0001, TV, 32” Tv, 10, 399.99,  (Valid)');
        $this->assertEquals((string)$requests[1], 'P0009, Harddisk, Great for storing data, 0, 99.99,  (Valid)');
    }

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
