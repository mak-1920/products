<?php

declare(strict_types=1);

namespace App\Services\Import;

use DateTime;

class ImportRequest
{
    private bool $isValidFormat;
    private string $productCode;
    private string $productName;
    private string $productDecs;
    private int $stock;
    private float $cost;
    private bool $discontinued;
    private ?DateTime $discontinuedDate;
    private string $inString;

    private const COLUMNS_COUNT = 6;

    public function __construct(
        array $data,
    )
    {   
        $isValidFormat = $this->isValidData($data);
        
        $this->discontinuedDate = null;
        $this->setIsValid($isValidFormat);
        $this->setInfo($data);
    }

    public function getProductCode() : string
    {
        return $this->productCode;
    }
    public function getProductName() : string
    {
        return $this->productName;
    }
    public function getProductDecs() : string
    {
        return $this->productDecs;
    }
    public function getStock() : int
    {
        return $this->stock;
    }
    public function getCost() : float
    {
        return $this->cost;
    }
    public function getDiscontinued() : bool
    {
        return $this->discontinued;
    }

    public function getDiscontinuedDate() : ?DateTime
    {
        return $this->discontinuedDate;
    }

    public function setDiscontinuedDate(?DateTime $date) : void
    {
        $this->discontinuedDate = $date;
    }

    public function __toString() : string
    {
        return $this->inString . ' ' . ($this->isValidFormat ? '(Valid)' : '(Invalid)');
    }

    public function getIsValid() : bool
    {
        return $this->isValidFormat;
    }

    public function setIsValid(bool $value) : void
    {
        $this->isValidFormat = $value;
    }

    private function setInfo(array $data) : void
    {
        $this->setString($data);

        if($this->isValidFormat) {
            $this->productCode = $data[0];
            $this->productName = $data[1];
            $this->productDecs = $data[2];
            $this->stock = (int)$data[3];
            $this->cost = (float)$data[4];
            $this->discontinued = $this->stringIsNullOrEmpty($data[5]) ? false : (bool)$data[5];
        }
    }

    private function setString($data) : void 
    {
        $this->inString = implode(', ', $data);
    }

    private function isValidData(array $data) : bool 
    {
        return $this->isValidArgsCount($data)
            && !$this->stringIsNullOrEmpty($data[0])
            && !$this->stringIsNullOrEmpty($data[1])
            && !$this->stringIsNullOrEmpty($data[3])
            && $this->isValidCost($data[4]);
    }

    private function isValidArgsCount(array $data) : bool
    {
        return count($data) == self::COLUMNS_COUNT;
    }

    private function stringIsNullOrEmpty(?string $str) : bool
    {
        return $str == null || trim($str) == ''; 
    }

    private function isValidCost(string $cost) : bool
    {
        return (bool)preg_match('/^\d+(\.\d{2})?$/i', $cost);
    }
}