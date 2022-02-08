<?php

namespace App\Tests\Import;

use App\Entity\ImportStatus;
use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Savers\DoctrineSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Port\Writer\ArrayWriter;
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
                ->onlyMethods(['getRealPath', 'getClientOriginalName'])
                ->getMock();

            $file->expects($this->once())
                ->method('getRealPath')
                ->will($this->returnValue($path));

            $files[] = $file;
        }

        return $files;
    }

    /**
     * @param string[] $paths
     * @param CSVSettings[] $settings
     *
     * @return ImportCSV[]
     */
    private function getImports(array $paths, array $settings = []): array
    {
        $imports = [];
        $files = $this->getFiles($paths);
        $settings = array_pad($settings, count($files), CSVSettings::getDefault());

        for($i = 0; $i < count($files); $i++) {
            $import = new ImportCSV(
                $files[$i],
                $settings[$i],
                $this->getSaver()
            );
            $import->saveRequests();
            $imports[] = $import;
        }

        return $imports;
    }

    private function getSaver() : DoctrineSaver
    {
        $saver = $this->getMockBuilder(DoctrineSaver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $saver->expects($this->any())
            ->method('save')
            ->will(
                $this->returnCallback(function($transporter) {
                    $valid = [];
                    $transporter->addWriter(new ArrayWriter($valid));
                    $transporter->process();
                    return $valid;
                })
            );

        return $saver;
    }

    public function testValid() : void
    {
        $paths = [
            __DIR__.'/csv/normal_data_with_header.csv', 
        ];

        $import = $this->getImports($paths)[0];

        $this->assertCount(5, $import->getComplete());
        $this->assertCount(0, $import->getFailed());
    }

    public function testInvalidBySyntax() : void
    {
        $paths = [
            __DIR__.'/csv/invalid_rows_by_syntax.csv', 
        ];

        $import = $this->getImports($paths)[0];

        $this->assertCount(0, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function testInvalidByRules() : void
    {
        $paths = [
            __DIR__.'/csv/invalid_rows_by_rules.csv', 
        ];

        $import = $this->getImports($paths)[0];

        $this->assertCount(0, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function test2Valid3Invalid() : void
    {
        $paths = [
            __DIR__.'/csv/2_valid_3_invalid.csv', 
        ];

        $import = $this->getImports($paths)[0];

        $this->assertCount(2, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function testWithRepeatedNamesByColumns() : void
    {
        $paths = [
            __DIR__.'/csv/header_with_more_columns.csv', 
        ];

        $typeError = false;
        try {
            $this->getImports($paths)[0];
        } catch(\TypeError) {
            $typeError = true;
        }

        $this->assertTrue($typeError);
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

            $import = $this->getImports([__DIR__ . '/csv/header_valid.csv'])[0];

            $this->assertEquals($testResult, $this->invokeMethod($import, 'isValidHeader', [$titles]));
        }
    }

    public function testMultiple() : void
    {
        $fileTitles = [
            __DIR__ . '/csv/multiple_1.csv',
            __DIR__ . '/csv/multiple_2.csv',
            __DIR__ . '/csv/multiple_3.csv',
            __DIR__ . '/csv/multiple_4.csv',
        ];

        $imports = $this->getImports(
            $fileTitles,
            [
                new CSVSettings(),
                new CSVSettings('|', '"'),
                new CSVSettings('|', '"', haveHeader: false),
                new CSVSettings(haveHeader: false),
            ]
        );

        $rows = [
            'TV, P0001, , 32” Tv, 10, 399.99',
            'P0009, Harddisk, Great for storing data, 0, 99.99, ',
            'P0010, Harddisk, Great for storing data, 0, 99.99, ',
            'P0002, TV, 32” Tv, 10, 399.99, ',
        ];

        for($i=0; $i < 4; $i++){
            $this->assertCount(1, $imports[$i]->getRequests());
            $this->assertCount(1, $imports[$i]->getComplete());
            $this->assertCount(0, $imports[$i]->getFailed());
            $this->assertEquals($rows[$i], implode(', ', $imports[$i]->getRequests()[0]));
        }
    }

    public function testBigFile(): void
    {
        $paths = [
            __DIR__.'/csv/bf/1_2451_2549.csv',
        ];

        $import = $this->getImports($paths)[0];

        $this->assertCount(2451, $import->getComplete());
        $this->assertCount(2549, $import->getFailed());

        $this->checkFailedInBigFile($import, '1_f_2549.csv');
    }

    public function testImportBigFileByStatus(): void
    {
        $status = new ImportStatus();
        $status->setFileTmpName(__DIR__ . '/csv/bf/2_2472_2528.csv');
        $status->setFileOriginalName('orig.file');
        $status->setCsvSettings(',  1');

        $import = ImportCSV::ImportFileByStatus($status, $this->getSaver());

        $this->assertCount(2472, $import->getComplete());
        $this->assertCount(2528, $import->getFailed());

        $this->checkFailedInBigFile($import, '2_f_2528.csv');
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     *
     * @return mixed
     */
    private function invokeMethod(
        object &$object, 
        string $methodName, 
        array $parameters = []
    ) : mixed
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param ImportCSV $import
     * @param string $fileWithErrors
     *
     * @return void
     */
    private function checkFailedInBigFile(ImportCSV $import, string $fileWithErrors): void
    {
        $failed = file_get_contents(__DIR__.'/csv/bf/failed/'.$fileWithErrors);
        $result = $import->getFailed();

        foreach($result as $row) {
            $this->assertStringContainsString(implode(',', $row), $failed);
        }
    }
}
