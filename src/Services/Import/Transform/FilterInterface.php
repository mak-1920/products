<?php

declare(strict_types=1);

namespace App\Services\Import\Transform;

use App\Services\Import\Exceptions\FilterException;

interface FilterInterface
{
    /**
     * @param string[][] $rows
     *
     * @return string[][] filtered rows
     *
     * @throws FilterException
     */
    public function filter(array $rows): array;
}
