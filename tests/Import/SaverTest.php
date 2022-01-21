<?php

namespace App\Tests\Import;

use App\Repository\TblproductdataRepository;
use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Savers\MySQLSaver;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Services\Import\ImportRequest;
use DateTime;
use ReflectionClass;

class SaverTest extends KernelTestCase
{
    private MySQLSaver $saver;

    protected function setUp() : void
    {
        /** @var MockObject $saver */
        $repository = $this->createMock(TblproductdataRepository::class);

        $repository->expects($this->any())
            ->method('getExistsProductCodes')
            ->willReturn(['P0002', 'P0005']);

        $repository->expects($this->any())
            ->method('getDiscontinuedProductsByNames')
            ->willReturn([
                ['strproductname' => 'TV', 'dtmdiscontinued' => new DateTime('2022-01-20 15:12:38')],
                ['strproductname' => 'Cd Player', 'dtmdiscontinued' => new DateTime('2022-01-20 16:10:00')],
            ]);

        $this->saver = new MySQLSaver($repository);
    }

    public function testAllRowsValidWithoutErrorsAndNotDiscontinueds() : void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/saver_valid_without_errors_and_not_disctontinueds.csv',
            new CSVSettings(haveHeader: true),
            false,
            $this->saver
        );
        
        $import->SaveRequests();

        $this->assertEquals(count($import->getRequests()), 6);
        $this->assertEquals(count($import->getComplete()), 6);
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testWithExistsAndRepeatersProductCodesAndNotDiscontinueds() : void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/saver_with_exists_and_repeaters_product_codes_and_not_disctontinueds.csv',
            new CSVSettings(haveHeader: true),
            false,
            $this->saver
        );
        
        $import->SaveRequests();

        $this->assertEquals(count($import->getRequests()), 6);
        $this->assertEquals(count($import->getComplete()), 1);
        $this->assertEquals(count($import->getFailed()), 5);
    }

    public function testDiscontinueds() : void
    {
        $import = new ImportCSV(
            __DIR__.'/csv/saver_discontinueds.csv',
            new CSVSettings(haveHeader: true),
            false,
            $this->saver
        );
        
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
