<?php

declare(strict_types=1);

namespace App\Services\Import\CSV;

class CSVSettings
{
    public const DEF_CHAR_DELIMITER = ',';
    public const DEF_CHAR_ENCLOSURE = ' ';
    public const DEF_CHAR_ESCAPE = ' ';
    public const DEF_CHAR_HAVEHEADER = '1';

    /**
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @param bool $haveHeader
     */
    public function __construct(
        private string $delimiter = self::DEF_CHAR_DELIMITER,
        private string $enclosure = self::DEF_CHAR_ENCLOSURE,
        private string $escape = self::DEF_CHAR_ESCAPE,
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
