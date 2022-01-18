<?php

declare(strict_types=1);

namespace App\Tests\Import\Helpers;

use App\Services\Import\Import as ImportImport;
use App\Services\Import\ImportRequest;

class Import extends ImportImport
{
    public function setRequestsFromData(array $data) : void
    {
        foreach($data as $row) {
            $this->requests[] = new ImportRequest($row);
        }
    }
}