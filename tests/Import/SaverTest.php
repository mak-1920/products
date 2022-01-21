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

    public function testDisctontinueds() : void
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
}
