<?php

declare(strict_types=1);

namespace App\Services\Import\Transform;

use App\Services\Import\Exceptions\Transform\ConverterException;

interface TransformInterface
{
    /**
     * @param string[][] $rows
     *
     * @return string[][] converted rows
     *
     * @throws ConverterException
     */
    public function transform(array $rows): array;
}
