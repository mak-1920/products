<?php

namespace App\Tests\Import;

use App\Repository\ProductDataRepository;
use App\Services\Import\Import;
use App\Services\Import\Readers\CSV\Reader;
use App\Services\Import\Readers\CSV\Settings;
use App\Services\Import\Savers\Doctrine\Saver;
use App\Services\Import\Transform\Doctrine\Converter;
use App\Services\Import\Transform\Doctrine\Filter;
use PHPUnit\Framework\TestCase;
use Port\Reader\ArrayReader;
use Port\Steps\StepAggregator;
use Port\Writer\ArrayWriter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportTest extends TestCase
{
    public function testValid(): void
    {
        $import = $this->getImport(__DIR__.'/csv/normal_data_with_header.csv');

        $this->assertCount(5, $import->getComplete());
        $this->assertCount(0, $import->getFailed());
    }

    public function testInvalidBySyntax(): void
    {
        $import = $this->getImport(__DIR__.'/csv/invalid_rows_by_syntax.csv');

        $this->assertCount(0, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function testInvalidByRules(): void
    {
        $import = $this->getImport(__DIR__.'/csv/invalid_rows_by_rules.csv');

        $this->assertCount(0, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function test2Valid3Invalid(): void
    {
        $import = $this->getImport(__DIR__.'/csv/2_valid_3_invalid.csv');

        $this->assertCount(2, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    public function testBigFile1(): void
    {
        $import = $this->getImport(__DIR__.'/csv/bf/1_2451_2549.csv');

        $this->assertCount(2451, $import->getComplete());
        $this->assertCount(2549, $import->getFailed());

        $this->checkFailedInBigFile($import, '1_f_2549.csv');
    }

    public function testBigFile2(): void
    {
        $import = $this->getImport(__DIR__.'/csv/bf/2_2472_2528.csv');

        $this->assertCount(2472, $import->getComplete());
        $this->assertCount(2528, $import->getFailed());

        $this->checkFailedInBigFile($import, '2_f_2528.csv');
    }

    public function testBigFile3(): void
    {
        $import = $this->getImport(__DIR__.'/csv/bf/3_2520_2480.csv');

        $this->assertCount(2520, $import->getComplete());
        $this->assertCount(2480, $import->getFailed());

        $this->checkFailedInBigFile($import, '3_f_2480.csv');
    }

    public function testWithExistsAndRepeatersCodes(): void
    {
        $import = $this->getImport(__DIR__.'/csv/data_with_exists_codes.csv');

        $this->assertCount(2, $import->getComplete());
        $this->assertCount(3, $import->getFailed());
    }

    /**
     * @param string $file
     * @param string $settings
     *
     * @return Import
     */
    private function getImport(string $file, string $settings = ',  1'): Import
    {
        $import = new Import(
            $this->getReader($file, $settings),
            $this->getSaver(),
            $this->getConverter(),
            $this->getFilter(),
        );

        $import->import();

        return $import;
    }

    /**
     * @param string $file
     * @param string $settings
     *
     * @return Reader
     */
    private function getReader(string $file, string $settings): Reader
    {
        $reader = new Reader(
            $this->getFile($file),
            Settings::fromString($settings),
        );

        return $reader;
    }

    /**
     * @return Filter
     */
    private function getFilter(): Filter
    {
        $filter = new Filter($this->getRepository());

        return $filter;
    }

    private function getConverter(): Converter
    {
        $converter = new Converter($this->getRepository());

        return $converter;
    }

    private function getRepository(): ProductDataRepository
    {
        $repository = $this->createMock(ProductDataRepository::class);

        $repository->expects($this->any())
            ->method('getExistsProductCodes')
            ->willReturn([
                'E0001',
                'E0002',
            ]);

        $repository->expects($this->any())
            ->method('getDiscontinuedProductsByNames')
            ->willReturn([
            ]);

        return $repository;
    }

    private function getSaver(): Saver
    {
        $saver = $this->getMockBuilder(Saver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $saver->expects($this->any())
            ->method('save')
            ->will(
                $this->returnCallback(function ($rows) {
                    $transporter = new StepAggregator(new ArrayReader($rows));
                    $valid = [];

                    $transporter->addWriter(new ArrayWriter($valid));
                    $transporter->process();

                    return $valid;
                })
            );

        return $saver;
    }

    /**
     * @param string $path
     *
     * @return UploadedFile
     */
    private function getFile(string $path): UploadedFile
    {
        $file = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRealPath', 'getClientOriginalName'])
            ->getMock();

        $file->expects($this->any())
            ->method('getRealPath')
            ->will($this->returnValue($path));

        return $file;
    }

    /**
     * @param Import $import
     * @param string $fileWithErrors
     *
     * @return void
     */
    private function checkFailedInBigFile(Import $import, string $fileWithErrors): void
    {
        $failed = file_get_contents(__DIR__.'/csv/bf/failed/'.$fileWithErrors);
        $result = $import->getFailed();

        foreach ($result as $row) {
            $this->assertStringContainsString(implode(',', $row), $failed);
        }
    }
}
