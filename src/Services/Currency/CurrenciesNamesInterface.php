<?php

declare(strict_types=1);

namespace App\Services\Currency;

interface CurrenciesNamesInterface
{
    /**
     * @return string[]
     */
    public function getCurrenciesNames(): array;
}
