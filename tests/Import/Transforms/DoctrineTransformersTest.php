<?php

declare(strict_types=1);

namespace App\Tests\Import\Transforms;

use App\Repository\ProductDataRepository;
use App\Services\Import\Transform\Doctrine\Converter;
use App\Services\Import\Transform\Doctrine\Filter;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\TestCase;

class DoctrineTransformersTest extends TestCase
{
    private static int $counter = 0;

    public function testValidation(): void
    {
        $filter = $this->getFilter();

        $data = [
            $this->getRow(),
            $this->getRow(''),
            $this->getRow(name: ''),
            $this->getRow(stock: ''),
            $this->getRow(cost: ''),
            $this->getRow(cost: '0'),
            $this->getRow(cost: '1'),
            $this->getRow(cost: '1.'),
            $this->getRow(cost: '1.1'),
            $this->getRow(cost: '1.11'),
            $this->getRow(stock: '9', cost: '4.99'),
            $this->getRow(cost: '1000.01'),
        ];

        $result = $filter->filter($data);

        $this->assertCount(4, $result);
        $this->assertEquals([$data[0], $data[5], $data[6], $data[9]], $result);
    }

    public function testExistsCodes(): void
    {
        $filter = $this->getFilter();

        $data = [
            $this->getRow(code: 'P0001'),
            $this->getRow(code: 'P0002'),
            $this->getRow(code: 'P0003'),
            $this->getRow(code: 'P0005'),
        ];

        $result = $filter->filter($data);

        $this->assertCount(2, $result);
        $this->assertEquals([$data[0], $data[2]], $result);
    }

    public function testClonedCodes(): void
    {
        $filter = $this->getFilter();

        $data = [
            $this->getRow(code: 'P0001'),
            $this->getRow(code: 'P0003', name: 'P1'),
            $this->getRow(code: 'P0003'),
            $this->getRow(code: 'P0004'),
            $this->getRow(code: 'P0001'),
        ];

        $result = $filter->filter($data);

        $this->assertCount(3, $result);
        $this->assertEquals([$data[0], $data[1], $data[3]], $result);
    }

    public function testConvertTypes(): void
    {
        $converter = $this->getConverter();

        $data = [$this->getRow()];

        $result = $converter->convert($data);

        $this->assertSame(10, $result[0]['Stock']);
        $this->assertSame(100., $result[0]['Cost in GBP']);
    }

    public function testConvertDiscontinueds(): void
    {
        $converter = $this->getConverter();

        $data = [
            $this->getRow(name: 'p'),
            $this->getRow(name: 'TV'),
            $this->getRow(name: 'p'),
            $this->getRow(name: 'Cd Player'),
        ];

        $result = $converter->convert($data);

        $this->assertEquals(new DateTime('2022-01-20 15:12:38'), $result[1]['Discontinued']);
        $this->assertEquals(new DateTime('2022-01-20 16:10:00'), $result[3]['Discontinued']);
        $this->assertEquals($result[0]['Discontinued'], $result[2]['Discontinued']);
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $stock
     * @param string $cost
     *
     * @return string[]
     */
    #[ArrayShape([
        'Product Code' => 'string',
        'Product Name' => 'string',
        'Product Description' => 'string',
        'Stock' => 'string',
        'Cost in GBP' => 'string',
        'Discontinued' => 'string', ])]
    private function getRow(string $code = 'some code', string $name = 'Product', string $stock = '10', string $cost = '100.00'): array
    {
        if ('some code' == $code) {
            $code = (string) self::$counter++;
        }

        return [
            'Product Code' => $code,
            'Product Name' => $name,
            'Product Description' => 'Product Description',
            'Stock' => $stock,
            'Cost in GBP' => $cost,
            'Discontinued' => '',
        ];
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
            ->willReturn(['P0002', 'P0005']);

        $repository->expects($this->any())
            ->method('getDiscontinuedProductsByNames')
            ->willReturn([
                ['name' => 'TV', 'discontinuedAt' => new DateTime('2022-01-20 15:12:38')],
                ['name' => 'Cd Player', 'discontinuedAt' => new DateTime('2022-01-20 16:10:00')],
            ]);

        return $repository;
    }
}
