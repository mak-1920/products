<?php

declare(strict_types=1);

namespace App\Tests\Import;

use App\Tests\Import\Helpers\Import;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    public function testWithValidData(): void
    {
        $data = [
            ['P0001','TV','32” Tv','10','399.99',''],
            ['P0002','Cd Player','Nice CD player','11','50.12','yes'],
            ['P0003','VCR','Top notch VCR','12','39.33','yes'],
            ['P0004','Bluray Player','Watch it in HD','1','24.55',''],
            ['P0005','XBOX360','Best.console.ever','5','30.44',''],
            ['P0005','XBOX360','Best.console.ever','5','30',''],
        ];

        $import = new Import($data, true);

        $this->assertEquals(count($import->getComplete()), count($data));
        $this->assertEquals(count($import->getFailed()), 0);
    }

    public function testWithBigCost(): void
    {
        $data = [
            ['P0001','TV','32” Tv','10','1001.00',''],
        ];

        $import = new Import($data, true);

        $this->assertEquals(count($import->getFailed()), count($data));
        $this->assertEquals(count($import->getComplete()), 0);
    }

    public function testWithSmallCostAndCount(): void
    {
        $data = [
            ['P0001','TV','32” Tv','9','4.99',''],
        ];

        $import = new Import($data, true);

        $this->assertEquals(count($import->getFailed()), count($data));
        $this->assertEquals(count($import->getComplete()), 0);
    }

    public function testWith2FailedRow(): void
    {
        $data = [
            ['P0001','TV','32” Tv','10','399.99',''],
            ['P0002','Cd Player','Nice CD player','11','50.12','yes'],
            ['P0001','TV','32” Tv','10','1001.00',''],
            ['P0003','VCR','Top notch VCR','12','39.33','yes'],
            ['P0004','Bluray Player','Watch it in HD','1','24.55',''],
            ['P0005','XBOX360','Best.console.ever','5','30.44',''],
            ['P0001','TV','32” Tv','9','4.99',''],
            ['P0005','XBOX360','Best.console.ever','5','30',''],
        ];

        $import = new Import($data, true);

        $this->assertEquals(count($import->getComplete()), 6);
        $this->assertEquals(count($import->getFailed()), 2);
    }

    public function testStringInRequest(): void
    {
        $data = [
            ['P0001','TV','32” Tv','10','399.99','yes'],
            ['P0001','TV','32” Tv','9','4.99','yes'],
        ];

        $import = new Import($data, true);
        $complete = $import->getComplete();
        $failed = $import->getFailed();

        $this->assertEquals(count($complete), 1);
        $this->assertEquals(count($failed), 1);
        $this->assertEquals((string)$complete[0], 'P0001, TV, 32” Tv, 10, 399.99, yes (Valid)');
        $this->assertEquals((string)$failed[0], 'P0001, TV, 32” Tv, 9, 4.99, yes (Invalid)');
    }
}
