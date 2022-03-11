<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Services\Currency\Converters\CurrencyConverterInterface;
use App\Services\Currency\Exceptions\CurrencyNotFoundInCacheException;
use App\Services\Import\Exceptions\Transform\ConverterException;

class CBRProvider implements CurrencyProviderInterface
{
    public function __construct(
        private CurrencyConverterInterface $converter,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws ConverterException
     */
    public function convert(string $fromCurrency, string $toCurrency, float $value): float
    {
        try {
            return round($this->converter->convert($fromCurrency, $toCurrency, $value), 2);
        } catch (CurrencyNotFoundInCacheException $e) {
            throw new ConverterException($e->getMessage(), previous: $e);
        }
    }

    public function getCurrencyValues(): array
    {
        return $this->converter->getCurrencyValues();
    }
}
