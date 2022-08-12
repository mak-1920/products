<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use App\Services\Import\Exceptions\Saver\SaverException;

interface SaverInterface
{
    /**
     * @param string[][] $rows
     *
     * @return string[][] results
     *
     * @throws SaverException
     */
    public function save(array $rows): array;
}
