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
                ['name' => 'TV', 'discontinuedAt' => new DateTime('2022-01-20 15:12:38')],
                ['name' => 'Cd Player', 'discontinuedAt' => new DateTime('2022-01-20 16:10:00')],
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
     * @return ImportCSV[]
     */
    private function getImports(array $paths, array $settings = []): array
    {
        $files = $this->getFiles($paths);
        $settings = array_pad($settings, count($files), CSVSettings::getDefault());
        $imports = [];

        for($i = 0; $i < count($files); $i++) {
            $imports[] = new ImportCSV(
                $files[$i],
                $settings[$i],
                $this->saver
            );
        }

        return $imports;
    }

    public function testAllRowsValidWithoutErrorsAndNotDiscontinueds() : void
    {
        $paths = [
            __DIR__.'/csv/saver_valid_without_errors_and_not_disctontinueds.csv',
        ];

        $import = $this->getImports($paths)[0];
        
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

        $import = $this->getImports($paths)[0];
        
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

        $import = $this->getImports($paths)[0];
        
        $import->SaveRequests();
        $requests = $import->getComplete();

        $this->assertCount(6, $import->getRequests());
        $this->assertCount(6, $import->getComplete());
        $this->assertCount(0, $import->getFailed());

        $this->assertEquals(new DateTime('2022-01-20 15:12:38'), $requests[1]['discontinuedAt']);

        $this->assertEquals($requests[5]['discontinuedAt'], $requests[0]['discontinuedAt']);
    }

    public function testTransformDiscontinuedArr() : void
    {
        $data = [
            ['name' => 'TV', 'discontinuedAt' => new DateTime('2022-01-20 15:12:38')],
            ['name' => 'Cd Player', 'discontinuedAt' => new DateTime('2022-01-20 16:10:00')],
        ];

        $result = $this->invokeMethod($this->saver, 'transformDiscontinuedArr', [$data]);

        $this->assertSameSize($result, $data);
        $this->assertArrayHasKey('TV', $result);
        $this->assertArrayHasKey('Cd Player', $result);
        $this->assertEquals($result['TV'], new DateTime('2022-01-20 15:12:38'));
        $this->assertEquals($result['Cd Player'], new DateTime('2022-01-20 16:10:00'));
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
    ): mixed
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
