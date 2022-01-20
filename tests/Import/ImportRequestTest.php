<?php

declare(strict_types=1);

namespace App\Tests\Import;

use App\Services\Import\ImportRequest;
use PHPUnit\Framework\TestCase;

class ImportRequestTest extends TestCase
{
    public function testNormalvalidate(): void
    {
        $requests = [
            new ImportRequest(['P0001','TV','32” Tv','10','399.99','']),
            new ImportRequest(['P0002','Cd Player','Nice CD player','11','50.12','yes']),
            new ImportRequest(['P0003','VCR','Top notch VCR','12','39.33','yes']),
            new ImportRequest(['P0004','Bluray Player','Watch it in HD','1','24.55','']),
            new ImportRequest(['P0005','XBOX360','Best.console.ever','5','30.44','']),
            new ImportRequest(['P0005','XBOX360','Best.console.ever','5','30','']),
        ];
        
        for($i = 0; $i < count($requests); $i++){
            $this->assertTrue($requests[$i]->getIsValid());
        }
    }

    public function testNotValidValue(): void
    {
        $requests = [
            new ImportRequest(['','TV','32” Tv','10','399.99','']),
            new ImportRequest(['P0001','','32” Tv','10','399.99','']),
            new ImportRequest(['P0001','TV','32” Tv','','399.99','']),
            new ImportRequest(['P0001','TV','32” Tv','10','','']),
            new ImportRequest(['P0001','TV','32” Tv','1a0','3a99.99','']),
            new ImportRequest(['P0005','XBOX360','Best.console.ever','5','30.4','']),
            new ImportRequest(['P0005','XBOX360','Best.console.ever','5','30.','']),
            new ImportRequest(['P0005','XBOX360','Best.console.ever']),
            new ImportRequest(['P0005','XBOX360','Best.console.ever','5','30','','more','args']),
        ];
        
        for($i = 0; $i < count($requests); $i++){
            $this->assertFalse($requests[$i]->getIsValid());
        }
    }
}
