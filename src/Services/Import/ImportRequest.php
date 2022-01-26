<?php

declare(strict_types=1);

namespace App\Services\Import;

use DateTime;

class ImportRequest
{
    private bool $isValidFormat;
    private string $productCode;
    private string $productName;
    private string $productDesc;
    private int $stock;
    private float $cost;
    private bool $discontinued;
    private ?DateTime $discontinuedDate;
    private string $inString;

    private const COLUMNS_COUNT = 6;

    /**
     * @param string[] $data
     */
    public function __construct(
        array $data,
    ) {
        $isValidFormat = $this->isValidData($data);

        $this->discontinuedDate = null;
        $this->setIsValid($isValidFormat);
        $this->setInfo($data);
    }

    /**
     * @return string
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @return string
     */
    public function getProductDesc(): string
    {
        return $this->productDesc;
    }

    /**
     * @return int
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @return bool
     */
    public function getDiscontinued(): bool
    {
        return $this->discontinued;
    }

    /**
     * @return DateTime|null
     */
    public function getDiscontinuedDate(): ?DateTime
    {
        return $this->discontinuedDate;
    }

    /**
     * @param DateTime|null $date
     *
     * @return void
     */
    public function setDiscontinuedDate(?DateTime $date): void
    {
        $this->discontinued = true;
        $this->discontinuedDate = $date;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->inString.' '.($this->isValidFormat ? '(Valid)' : '(Invalid)');
    }

    /**
     * @return bool
     */
    public function getIsValid(): bool
    {
        return $this->isValidFormat;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function setIsValid(bool $value): void
    {
        $this->isValidFormat = $value;
    }

    /**
     * @param string[] $data
     *
     * @return void
     */
    private function setInfo(array $data): void
    {
        $this->setString($data);

        if ($this->isValidFormat) {
            $this->productCode = $data[0];
            $this->productName = $data[1];
            $this->productDesc = $data[2];
            $this->stock = (int) $data[3];
            $this->cost = (float) $data[4];
            $this->discontinued = !$this->stringIsNullOrEmpty($data[5]) && $data[5];
        }
    }

    /**
     * @param string[] $data
     *
     * @return void
     */
    private function setString(array $data): void
    {
        $this->inString = implode(', ', $data);
    }

    /**
     * @param string[] $data
     *
     * @return bool
     */
    private function isValidData(array $data): bool
    {
        return $this->isValidArgsCount($data)
            && !$this->stringIsNullOrEmpty($data[0])
            && !$this->stringIsNullOrEmpty($data[1])
            && !$this->stringIsNullOrEmpty($data[3])
            && $this->isValidCost($data[4])
            && $this->isSatisfiesRules($data);
    }

    /**
     * @param string[] $data
     *
     * @return bool
     */
    private function isValidArgsCount(array $data): bool
    {
        return self::COLUMNS_COUNT == count($data);
    }

    /**
     * @param string|null $str
     *
     * @return bool
     */
    private function stringIsNullOrEmpty(?string $str): bool
    {
        return null == $str || '' == trim($str);
    }

    /**
     * @param string $cost
     *
     * @return bool
     */
    private function isValidCost(string $cost): bool
    {
        return (bool) preg_match('/^\d+(\.\d{2})?$/i', $cost);
    }

    /**
     * @param string[] $data
     *
     * @return bool
     */
    private function isSatisfiesRules(array $data): bool
    {
        return !(round((float) $data[4], 2) < 5 && (int) $data[3] < 10)
            && !(round((float) $data[4], 2) > 1000);
    }
}
