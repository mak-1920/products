<?php

declare(strict_types=1);

namespace App\Services\Currency;

interface CurrencyValuesInterface
{
    /**
     * @return string[]
     */
    public function getCurrencyValues(): array;
}
