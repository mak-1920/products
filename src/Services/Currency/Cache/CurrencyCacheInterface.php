<?php

declare(strict_types=1);

namespace App\Services\Currency\Cache;

use App\Services\Cache\CacheLifeTimeInterface;
use App\Services\Currency\CurrenciesNamesInterface;
use Fullpipe\CbrCurrency\CurrencyInterface;

interface CurrencyCacheInterface extends CacheLifeTimeInterface, CurrenciesNamesInterface
{
    /**
     * @param CurrencyInterface[] $rates
     *
     * @return void
     */
    public function initCache(array $rates): void;

    /**
     * @param string $currency
     *
     * @return float|null
     */
    public function getRate(string $currency): ?float;

    /**
     * @return bool
     */
    public function isInit(): bool;
}
