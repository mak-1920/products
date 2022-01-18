<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

class CSVSettings
{
    public function __construct(
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape = '"',
        private bool $haveHeader = false,
    )
    {
    }

    public function getDelimiter() : string
    {
        return $this->delimiter;
    }

    public function getEnclosure() : string
    {
        return $this->enclosure;
    }

    public function getEscape() : string
    {
        return $this->escape;
    }

    public function isHavingHeader() : bool
    {
        return $this->haveHeader;
    }
}