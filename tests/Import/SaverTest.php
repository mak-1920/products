<?php

namespace App\Tests\Import;

use App\Repository\ProductDataRepository;
use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Savers\DoctrineSaver;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Services\Import\ImportRequest;
use DateTime;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SaverTest extends KernelTestCase
{
    private DoctrineSaver $saver;

    protected function setUp() : void
    {
        /** @var MockObject $saver */
        $repository = $this->createMock(ProductDataRepository::class);

        $repository->expects($this->any())
            ->method('getExistsProductCodes')
            ->willReturn(['P0002', 'P0005']);

        $repository->expects($this->any())
            ->method('getDiscontinuedProductsByNames')
            ->willReturn([
                ['strproductname' => 'TV', 'dtmdiscontinued' => new DateTime('2022-01-20 15:12:38')],
                ['strproductname' => 'Cd Player', 'dtmdiscontinued' => new DateTime('2022-01-20 16:10:00')],
            ]);

        $this->saver = new DoctrineSaver($repository);
    }

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
            false,
            $this->saver
        );

        return $import;
    }

    public function testAllRowsValidWithoutErrorsAndNotDiscontinueds() : void
    {
        $pathes = [
            __DIR__.'/csv/saver_valid_without_errors_and_not_disctontinueds.csv',
        ];

        $import = $this->getImport($pathes);
        
        $import->SaveRequests();

        $this->assertEquals(count($import->getRequests()), 6);
        $this->assertEquals(count($import->getComplete()), 6);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testWithExistsAndRepeatersProductCodesAndNotDiscontinueds() : void
    {
        $pathes = [
            __DIR__.'/csv/saver_with_exists_and_repeaters_product_codes_and_not_disctontinueds.csv',
        ];

        $import = $this->getImport($pathes);
        
        $import->SaveRequests();

        $this->assertEquals(count($import->getRequests()), 6);
        $this->assertEquals(count($import->getComplete()), 1);
        $this->assertEquals(count($import->getFailed()), 5);
    }

    public function testDiscontinueds() : void
    {
        $pathes = [
            __DIR__.'/csv/saver_discontinueds.csv',
        ];

        $import = $this->getImport($pathes);
        
        $import->SaveRequests();
        /** @var ImportRequest[] $requests */
        $requests = $import->getRequests();

        $this->assertEquals(count($import->getRequests()), 6);
        $this->assertEquals(count($import->getComplete()), 6);
        $this->assertEquals(count($import->getFailed()), 0);

        $this->assertEquals($requests[1]->getDiscontinued(), true);
        $this->assertEquals($requests[1]->getDiscontinuedDate(), new DateTime('2022-01-20 15:12:38'));

        $this->assertEquals($requests[5]->getDiscontinued(), true);
        $this->assertEquals($requests[5]->getDiscontinuedDate(), $requests[0]->getDiscontinuedDate());
    }

    public function testGetProductsFieldsByObjMethod() : void
    {
        $requests = [
            new ImportRequest(['P0001','TV','32” Tv','10','399.98','']),
            new ImportRequest(['','TV2','32” Tv','10','399.99','']),
        ];

        $this->assertEquals($this->invokeMethod($this->saver, 'getProductsFieldsByObjMethod', [$requests, 'getProductname']), ['TV']);
        $this->assertEquals($this->invokeMethod($this->saver, 'getProductsFieldsByObjMethod', [$requests, 'getCost']), ['399.98']);
    }

    public function testTransformDiscontinuedArr() : void
    {
        $data = [
            ['strproductname' => 'TV', 'dtmdiscontinued' => new DateTime('2022-01-20 15:12:38')],
            ['strproductname' => 'Cd Player', 'dtmdiscontinued' => new DateTime('2022-01-20 16:10:00')],
        ];

        $result = $this->invokeMethod($this->saver, 'transformDiscontinuedArr', [$data]);

        $this->assertEquals(count($result), count($data));
        $this->assertArrayHasKey('TV', $result);
        $this->assertArrayHasKey('Cd Player', $result);
        $this->assertEquals($result['TV'], new DateTime('2022-01-20 15:12:38'));
        $this->assertEquals($result['Cd Player'], new DateTime('2022-01-20 16:10:00'));
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
