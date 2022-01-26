<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

class CSVSettings
{
    /**
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @param bool $haveHeader
     */
    public function __construct(
        private string $delimiter = ',',
        private string $enclosure = ' ',
        private string $escape = ' ',
        private bool $haveHeader = false,
    ) {
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @return string
     */
    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    /**
     * @return string
     */
    public function getEscape(): string
    {
        return $this->escape;
    }

    /**
     * @return bool
     */
    public function isHavingHeader(): bool
    {
        return $this->haveHeader;
    }
}
