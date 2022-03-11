<?php

declare(strict_types=1);

namespace App\Services\Currency\Converters;

use App\Services\Currency\Cache\CurrencyCacheInterface;
use App\Services\Currency\Exceptions\CurrenciesInitException;
use App\Services\Currency\Exceptions\CurrencyNotFoundInCacheException;
use Fullpipe\CbrCurrency\Importer;
use Throwable;

class CurrencyConverterByCBR implements CurrencyConverterInterface
{
    private const CACHE_LIFETIME_IN_SECONDS = 3600;

    /**
     * @throws CurrenciesInitException
     */
    public function __construct(
        private CurrencyCacheInterface $cache,
    ) {
        $this->cache->setTimeout(self::CACHE_LIFETIME_IN_SECONDS);

        if (!$this->cache->isInit()) {
            $rates = [];
            try {
                $rates = (new Importer())->import();
            } catch (Throwable $e) {
//                throw new CurrenciesInitException('Can\'t get data from CBR\'s site', previous: $e);
            }
            $this->cache->initCache($rates);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws CurrencyNotFoundInCacheException
     */
    public function convert(string $fromCurrency, string $toCurrency, float $value): float
    {
        $value = $this->convertToRub($fromCurrency, $value);
        $value = $this->convertFromRub($toCurrency, $value);

        return $value;
    }

    /**
     * @param string $from
     * @param float $value
     *
     * @return float
     *
     * @throws CurrencyNotFoundInCacheException
     */
    private function convertToRub(string $from, float $value): float
    {
        $rate = $this->cache->getRate($from);

        if (is_null($rate)) {
            throw new CurrencyNotFoundInCacheException($from.' not contains in cache');
        }

        return $value * $rate;
    }

    /**
     * @param string $to
     * @param float $value
     *
     * @return float
     *
     * @throws CurrencyNotFoundInCacheException
     */
    private function convertFromRub(string $to, float $value): float
    {
        $rate = $this->cache->getRate($to);

        if (is_null($rate)) {
            throw new CurrencyNotFoundInCacheException($to.' not contains in cache');
        }

        return $value / $rate;
    }

    /**
     * @return string[]
     */
    public function getCurrencyValues(): array
    {
        return $this->cache->getCurrencyValues();
    }
}
