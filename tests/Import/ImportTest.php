<?php

declare(strict_types=1);

namespace App\Tests\Import;

use App\Tests\Import\Helpers\Import;
use PHPUnit\Framework\TestCase;
use App\Tests\Import\Helpers\Saver;

class ImportTest extends TestCase
{
    public function testWithValidData(): void
    {
        $data = [
            ['apple', '10$', '100'],
            ['apple', '5$', '100'],
            ['apple', '1$', '100'],
            ['apple', '5$', '1'],
            ['apple', '1000$', '100'],
        ];

        $import = new Import($data, true, new Saver());

        $this->assertEquals(count($import->getComplete()), count($data));
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testWithBigCost(): void
    {
        $data = [
            ['apple', '1001$', '100'],
        ];

        $import = new Import($data, true, new Saver());

        $this->assertEquals(count($import->getFailed()), count($data));
        $this->assertEquals(count($import->getComplete()), 0);
    }

    public function testWithSmallCostAndCount(): void
    {
        $data = [
            ['apple', '4$', '9'],
        ];

        $import = new Import($data, true, new Saver());

        $this->assertEquals(count($import->getFailed()), count($data));
        $this->assertEquals(count($import->getComplete()), 0);
    }

    public function testWith2FailedRow(): void
    {
        $data = [
            ['apple', '10$', '100'],
            ['apple', '5$', '100'],
            ['apple', '1$', '100'],
            ['apple', '5$', '1'],
            ['apple', '1000$', '100'],
            ['apple', '1001$', '100'],
            ['apple', '4$', '9'],
        ];

        $import = new Import($data, true, new Saver());

        $this->assertEquals(count($import->getComplete()), 5);
        $this->assertEquals(count($import->getFailed()), 2);
    }

    public function testStringInRequest(): void
    {
        $data = [
            ['apple', '10$', '100'],
        ];

        $import = new Import($data, true, new Saver());

        $this->assertEquals(count($import->getComplete()), 1);
        $this->assertEquals((string)$import->getComplete()[0], 'apple, 10$, 100 (Valid)');
    }
}
