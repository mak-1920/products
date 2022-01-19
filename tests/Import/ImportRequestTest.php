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
            new ImportRequest(['apple', '1$', '10']),
            new ImportRequest(['apple', '0$', '10']),
            new ImportRequest(['apple', '1$', '0']),
            new ImportRequest(['apple', '10000$', '10000']),
        ];
        
        for($i = 0; $i < count($requests); $i++){
            $this->assertTrue($requests[$i]->getIsValid());
        }
    }

    public function testNotValidValue(): void
    {
        $requests = [
            new ImportRequest(['apple', '1a$', '10']),
            new ImportRequest(['apple', '0$', '10a']),
            new ImportRequest(['apple', '1', '0']),
            new ImportRequest(['apple', '10', '10']),
            new ImportRequest(['apple', '10000', '10000']),
            new ImportRequest(['', '1$', '10']),
            new ImportRequest(['apple', '1$', '10', '123']),
            new ImportRequest(['apple', '1$']),
        ];
        
        for($i = 0; $i < count($requests); $i++){
            $this->assertFalse($requests[$i]->getIsValid());
        }
    }
}
