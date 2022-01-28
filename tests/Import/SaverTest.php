<?php

namespace App\Tests\Import;

use App\Repository\ProductDataRepository;
use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Savers\DoctrineSaver;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\MockObject\MockObject;
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
                ['name' => 'TV', 'timeOfDiscontinued' => new DateTime('2022-01-20 15:12:38')],
                ['name' => 'Cd Player', 'timeOfDiscontinued' => new DateTime('2022-01-20 16:10:00')],
            ]);

        $em = $this->createMock(EntityManager::class);

        $em->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);

        $this->saver = new DoctrineSaver($em);
    }

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

    /**
     * @param string[] $paths
     * @param CSVSettings[] $settings
     *
     * @return ImportCSV
     */
    private function getImport(array $paths, array $settings = []): ImportCSV
    {
        $import = new ImportCSV(
            $this->getFiles($paths),
            $settings,
            false,
            $this->saver
        );

        return $import;
    }

    public function testAllRowsValidWithoutErrorsAndNotDiscontinueds() : void
    {
        $paths = [
            __DIR__.'/csv/saver_valid_without_errors_and_not_disctontinueds.csv',
        ];

        $import = $this->getImport($paths);
        
        $import->SaveRequests();

        $this->assertCount(6, $import->getRequests());
        $this->assertCount(6, $import->getComplete());
        $this->assertCount(0, $import->getFailed());
    }

    public function testWithExistsAndRepeatersProductCodesAndNotDiscontinued() : void
    {
        $paths = [
            __DIR__.'/csv/saver_with_exists_and_repeaters_product_codes_and_not_disctontinueds.csv',
        ];

        $import = $this->getImport($paths);
        
        $import->SaveRequests();

        $this->assertCount(6, $import->getRequests());
        $this->assertCount(1, $import->getComplete());
        $this->assertCount(5, $import->getFailed());
    }

    public function testDiscontinued() : void
    {
        $paths = [
            __DIR__.'/csv/saver_discontinueds.csv',
        ];

        $import = $this->getImport($paths);
        
        $import->SaveRequests();
        $requests = $import->getComplete();

        $this->assertCount(6, $import->getRequests());
        $this->assertCount(6, $import->getComplete());
        $this->assertCount(0, $import->getFailed());

        $this->assertEquals(new DateTime('2022-01-20 15:12:38'), $requests[1]['timeOfDiscontinued']);

        $this->assertEquals($requests[5]['timeOfDiscontinued'], $requests[0]['timeOfDiscontinued']);
    }

    public function testTransformDiscontinuedArr() : void
    {
        $data = [
            ['name' => 'TV', 'timeOfDiscontinued' => new DateTime('2022-01-20 15:12:38')],
            ['name' => 'Cd Player', 'timeOfDiscontinued' => new DateTime('2022-01-20 16:10:00')],
        ];

        $result = $this->invokeMethod($this->saver, 'transformDiscontinuedArr', [$data]);

        $this->assertSameSize($result, $data);
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
