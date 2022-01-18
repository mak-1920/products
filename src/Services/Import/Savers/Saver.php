<?php

declare(strict_types=1);

namespace App\Services\Import\Savers;

use App\Services\Import\ImportRequest;

interface Saver
{
    /** @var ImportRequest $request */
    public function Save(array $requests);
}