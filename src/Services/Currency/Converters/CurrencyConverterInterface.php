<?php

declare(strict_types=1);

namespace App\Services\Currency\Converters;

use App\Services\Currency\CurrencyValuesInterface;

interface CurrencyConverterInterface extends CurrencyValuesInterface
{
    /**
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $value
     *
     * @return float
     */
    public function convert(string $fromCurrency, string $toCurrency, float $value): float;
}
