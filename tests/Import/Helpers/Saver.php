<?php

declare(strict_types=1);

namespace App\Tests\Import\Helpers;

use App\Services\Import\Savers\Saver as SaversSaver;

class Saver implements SaversSaver
{
    /** @var ImportRequest $request */
    public function Save(array $requests) : void
    {

    }
}