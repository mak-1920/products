<?php

namespace App\Tests\Import\Readers;

use App\Services\Import\Readers\CSV\Reader;
use App\Services\Import\Readers\CSV\Settings;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CSVReaderTest extends TestCase
{
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
     * @param string $path
     * @param Settings|null $settings
     *
     * @return Reader
     */
    private function getReader(string $path, Settings $settings = null): Reader
    {
        $file = $this->getFile($path);
        $settings ??= Settings::getDefault();

        $reader = new Reader(
            $file,
            $settings,
        );

        return $reader;
    }

    private function getMockReader(): Reader
    {
        $reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $reader;
    }

    public function testCheckHeader(): void
    {
        $headers = [
            'Discontinued,Stock,Product Code,Product Description,Cost in GBP,Product Name' => true,
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued' => true,
            'Product Code,Product Name,Product Description,Stock,Cost in GBP' => false,
            'Product Code,Product Name,Product Description,Stock,Cost in GBP,Discontinued,Product Name' => false,
        ];

        foreach ($headers as $header => $testResult) {
            $titles = explode(',', $header);

            $reader = $this->getReader(__DIR__.'/csv/header_valid.csv');

            $this->assertEquals($testResult, $this->invokeMethod($reader, 'isValidHeader', [$titles]));
        }
    }

    public function testGetRows(): void
    {
        $reader = $this->getReader(__DIR__.'/../csv/normal_data_with_header.csv');
        $result = $reader->read();

        $this->assertCount(5, $result);
        $this->assertCount(6, $result[0]);
    }

    public function testGetReaderWithValidHeader(): void
    {
        $settings = new Settings();
        $file = $this->getFile(__DIR__.'/../csv/header_valid.csv');

        $reader = $this->getMockReader();

        $result = $this->invokeMethod($reader, 'getReader', [$file, $settings]);

        $this->assertNotNull($result);
    }

    public function testGetReaderWithShuffleHeader(): void
    {
        $settings = new Settings();
        $file = $this->getFile(__DIR__.'/../csv/header_valid_shuffle.csv');

        $reader = $this->getMockReader();

        $result = $this->invokeMethod($reader, 'getReader', [$file, $settings]);

        $this->assertNotNull($result);
    }

    public function testGetReaderWithLessColumns(): void
    {
        $settings = new Settings();
        $file = $this->getFile(__DIR__.'/../csv/header_with_less_columns.csv');

        $reader = $this->getMockReader();

        $result = $this->invokeMethod($reader, 'getReader', [$file, $settings]);

        $this->assertNull($result);
    }

    public function testGetReaderWithMoreColumns(): void
    {
        $settings = new Settings();
        $file = $this->getFile(__DIR__.'/../csv/header_with_more_columns.csv');

        $reader = $this->getMockReader();

        $result = $this->invokeMethod($reader, 'getReader', [$file, $settings]);

        $this->assertNull($result);
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
    ): mixed {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
