<?php

declare(strict_types=1);

namespace App\Services\Currency\Cache;

use App\Services\Cache\CacheStructuresSupportInterface;

class CurrencyCache implements CurrencyCacheInterface
{
    private const CURRENCY_KEY = 'currency';

    private const CURRENCY_INIT_KEY = 'init';

    private const CURRENCIES_NAMES_KEY = 'currencies';

    public function __construct(
        private CacheStructuresSupportInterface $supporter,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getRate($currency): ?float
    {
        $rate = $this->supporter->htGet(self::CURRENCY_KEY, $currency);

        if (!is_null($rate)) {
            $rate = floatval($rate);
        }

        return $rate;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrenciesNames(): array
    {
        $names = $this->supporter->sGetAll(self::CURRENCIES_NAMES_KEY);
        sort($names);

        return $names;
    }

    /**
     * {@inheritDoc}
     */
    public function initCache($rates): void
    {
        $this->supporter->htSet(self::CURRENCY_KEY, self::CURRENCY_INIT_KEY, 'true');
        $this->supporter->initKey(self::CURRENCY_KEY, $this->getTimeout());

        foreach ($rates as $rate) {
            $this->supporter->htSet(self::CURRENCY_KEY, $rate->getCode(), (string) $rate->getExchangeRate());
            $this->supporter->sAdd(self::CURRENCIES_NAMES_KEY, $rate->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isInit(): bool
    {
        $result = $this->supporter->htGet(self::CURRENCY_KEY, self::CURRENCY_INIT_KEY) ?? false;

        return boolval($result);
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeout(): int
    {
        return $this->supporter->getTimeout();
    }

    /**
     * {@inheritDoc}
     */
    public function setTimeout(int $seconds): void
    {
        $this->supporter->setTimeout($seconds);
    }
}
