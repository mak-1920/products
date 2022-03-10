<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Services\Currency\Converters\CurrencyConverterInterface;

class CBRProvider implements CurrencyProviderInterface
{
    public function __construct(
        private CurrencyConverterInterface $converter,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function convert(string $fromCurrency, string $toCurrency, float $value): float
    {
        return round($this->converter->convert($fromCurrency, $toCurrency, $value), 2);
    }

    public function getCurrenciesNames(): array
    {
        return $this->converter->getCurrenciesNames();
    }
}
