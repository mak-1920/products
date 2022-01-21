<?php

namespace App\Tests\Import;

use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ImportCSVTest extends TestCase
{
    public function testValid() : void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/normal_data_with_header.csv', 
            new CSVSettings(haveHeader: true),
            true
        );

        $this->assertEquals(count($import->getComplete()), 5);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testInvalidBySyntax() : void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/invalid_rows_by_syntax.csv', 
            new CSVSettings(haveHeader: true),
            true
        );

        $this->assertEquals(count($import->getComplete()), 0);
        $this->assertEquals(count($import->getFailed()), 3);
    }

    public function testInvalidByRules() : void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/invalid_rows_by_rules.csv', 
            new CSVSettings(haveHeader: true),
            true
        );

        $this->assertEquals(count($import->getComplete()), 0);
        $this->assertEquals(count($import->getFailed()), 3);
    }

    public function test2Valid3Invalid() : void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/2_valid_3_invalid.csv', 
            new CSVSettings(haveHeader: true),
            true
        );

        $this->assertEquals(count($import->getComplete()), 2);
        $this->assertEquals(count($import->getFailed()), 3);
    }

    public function testWithRepeatedNamesByColumns() : void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/header_with_more_columns.csv', 
            new CSVSettings(haveHeader: true),
            true
        );

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
            $import = new ImportCSV(
                __DIR__ . '/csv/' . $title, 
                new CSVSettings(haveHeader: true),
                true
            );

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
            $import = new ImportCSV(
                __DIR__ . '/csv/' . $title, 
                new CSVSettings(haveHeader: true),
                true
            );

            $this->assertEquals($this->invokeMethod($import, 'checkHeader'), $testResult);
        }
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
