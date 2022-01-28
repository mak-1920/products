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
        private bool $haveHeader = true,
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
    public function getHaveHeader(): bool
    {
        return $this->haveHeader;
    }

    /**
     * @param ?bool $haveHeader
     */
    public function setHaveHeader(?bool $haveHeader): void
    {
        $this->haveHeader = $haveHeader ?? false;
    }

    /**
     * @param ?string $delimiter
     */
    public function setDelimiter(?string $delimiter): void
    {
        $this->delimiter = $delimiter ?? ' ';
    }

    /**
     * @param ?string $escape
     */
    public function setEscape(?string $escape): void
    {
        $this->escape = $escape ?? ' ';
    }

    /**
     * @param ?string $enclosure
     */
    public function setEnclosure(?string $enclosure): void
    {
        $this->enclosure = $enclosure ?? ' ';
    }
}
