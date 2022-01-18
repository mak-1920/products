<?php

declare(strict_types=1);

namespace App\Services\Import;

use function PHPUnit\Framework\isEmpty;

class ImportRequest
{
    private bool $isValidFormat;
    private string $product;
    private int $count;
    private int $cost;
    private string $inString;

    public function __construct(
        array $data,
    )
    {   
        $isValidFormat = $this->isValidData($data);
        $this->setIsValid($isValidFormat);
        $this->setFields($data);
    }

    public function __toString() : string
    {
        return $this->inString . ' ' . ($this->isValidFormat ? '(Valid)' : '(Not valid)');
    }

    public function getProduct() : string
    {
        return $this->product;
    }

    public function getCount() : int
    {
        return $this->count;
    }

    public function getCost() : int
    {
        return $this->cost;
    }

    public function getIsValid() : bool
    {
        return $this->isValidFormat;
    }

    private function setIsValid(bool $value) : void
    {
        $this->isValidFormat = $value;
    }

    private function setFields(array $data) : void
    {
        $this->setString($data);

        if($this->isValidFormat) {
            $this->product = $data[0];
            $this->cost = (int)substr($data[1], 0, -1);
            $this->count = (int)$data[2];
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
            && $this->isValidCost($data[1])
            && $this->isValidCount($data[2]); 
    }

    private function isValidArgsCount(array $data) : bool
    {
        return count($data) == 3;
    }

    private function stringIsNullOrEmpty(string $str) : bool
    {
        return $str == null || trim($str) == ''; 
    }

    private function isValidCost(string $cost) : bool
    {
        return (bool)preg_match('/\d+\$/i', $cost);
    }

    private function isValidCount(string $count) : bool
    {
        return is_numeric($count);
    }

}