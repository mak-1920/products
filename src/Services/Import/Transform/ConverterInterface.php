<?php

declare(strict_types=1);

namespace App\Services\Import\Transform;

use App\Services\Import\Exceptions\ConverterException;

interface ConverterInterface
{
    /**
     * @param string[][] $rows
     *
     * @return string[][] converted rows
     *
     * @throws ConverterException
     */
    public function convert(array $rows): array;
}
