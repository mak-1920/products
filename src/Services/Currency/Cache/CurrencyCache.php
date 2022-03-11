<?php

declare(strict_types=1);

namespace App\Services\Currency\Cache;

use App\Services\Cache\CacheHashTableSupportInterface;

class CurrencyCache implements CurrencyCacheInterface
{
    private const CURRENCY_KEY = 'currency';

    private const CURRENCY_INIT_KEY = 'init';

    public function __construct(
        private CacheHashTableSupportInterface $supporter,
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
    public function getCurrencyValues(): array
    {
        $names = $this->supporter->htGetAll(self::CURRENCY_KEY);
        unset($names['init']);
        ksort($names);

        return $names;
    }

    /**
     * {@inheritDoc}
     */
    public function initCache($rates): void
    {
        $this->supporter->htSet(self::CURRENCY_KEY, self::CURRENCY_INIT_KEY, 'true');
        $this->supporter->setTTL(self::CURRENCY_KEY, $this->getTimeout());

        foreach ($rates as $rate) {
            $this->supporter->htSet(self::CURRENCY_KEY, $rate->getCode(), (string) $rate->getExchangeRate());
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
