<?php

declare(strict_types=1);

namespace App\Services\Import\Readers;

use App\Services\Import\Exceptions\ReaderException;

interface ReaderInterface
{
    /**
     * @return string[][]
     *
     * @throws ReaderException
     */
    public function read(): array;
}
