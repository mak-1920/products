<?php

declare(strict_types=1);

namespace App\Tests\Import;

use App\Services\Import\ImportRequest;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ImportRequestTest extends TestCase
{
    public function testNormalvalidate() : void
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

    public function testNotValidValue() : void
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

    public function testNullOrEmptyString() : void
    {
        $data = ['', null, ' ', '     ', '123', 123];
        $result = [true, true, true, true, false, false];
        $ir = new ImportRequest([]);

        for ($i=0; $i < count($data); $i++) { 
            $this->assertEquals($this->invokeMethod($ir, 'stringIsNullOrEmpty', [$data[$i]]), $result[$i]);
        }
    }

    public function testValidCost() : void
    {
        $invalidData = ['', '1.', '1.1', '.1', '.11', 'a1.11', '1,11', '-1', '-1.11'];
        $validData = ['1', '1.11'];
        $ir = new ImportRequest([]);

        foreach($invalidData as $value) {
            $this->assertEquals($this->invokeMethod($ir, 'isValidCost', [$value]), false);
        }

        foreach($validData as $value) {
            $this->assertEquals($this->invokeMethod($ir, 'isValidCost', [$value]), true);
        }
    }

    public function isValidData() : void
    {
        $invalidData = [
            ['P0001','TV','32” Tv','10','399.99'],
            ['P0002','Cd Player','Nice CD player','11','50.12','yes', ''],
            ['','VCR','Top notch VCR','12','39.33','yes'],
            ['P0004','','Watch it in HD','1','24.55',''],
            ['P0005','XBOX360','Best.console.ever','','30.44',''],
            ['P0005','XBOX360','Best.console.ever','5','',''],
        ];
        $validData = [
            ['P0001','TV','32” Tv','10','399.99',''],
            ['P0002','Cd Player','Nice CD player','11','50.12','yes'],
            ['P0003','VCR','Top notch VCR','12','39.33','yes'],
            ['P0004','Bluray Player','Watch it in HD','1','24.55',''],
            ['P0005','XBOX360','Best.console.ever','5','30.44',''],
            ['P0005','XBOX360','Best.console.ever','5','30',''],    
        ];
        $ir = new ImportRequest([]);

        foreach($invalidData as $value) {
            $this->assertEquals($this->invokeMethod($ir, 'isValidData', [$value]), false);
        }

        foreach($validData as $value) {
            $this->assertEquals($this->invokeMethod($ir, 'isValidData', [$value]), true);
        }
    }

    public function isSatisfiesRules() : void
    {
        $invalidData = [
            ['P0001','TV','32” Tv','9','4.99'],
            ['P0002','Cd Player','Nice CD player','11','1000.01','yes', ''],
        ];
        $validData = [
            ['P0001','TV','32” Tv','9','5'],
            ['P0001','TV','32” Tv','10','4.99'],
            ['P0001','TV','32” Tv','10','5'],
            ['P0002','Cd Player','Nice CD player','11','1000','yes', ''],  
        ];
        $ir = new ImportRequest([]);

        foreach($invalidData as $value) {
            $this->assertEquals($this->invokeMethod($ir, 'isValidData', [$value]), false);
        }

        foreach($validData as $value) {
            $this->assertEquals($this->invokeMethod($ir, 'isValidData', [$value]), true);
        }
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
