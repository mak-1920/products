<?php

declare(strict_types=1);

namespace App\Services\Cache\Redis;

use App\Services\Cache\CacheStructuresSupportInterface;
use App\Services\Cache\CacheSupporterInterface;
use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisSupporter implements CacheSupporterInterface, CacheStructuresSupportInterface
{
    private Redis $client;
    private int $timeout = 0;

    /**
     * @param string $server
     * @param array<string, string> $settings
     */
    public function __construct(
        string $server,
        array $settings,
    ) {
        /** @var Redis $client */
        $client = RedisAdapter::createConnection(
            $server,
            $settings
        );
        $this->client = $client;
    }

    /**
     * @return Redis
     */
    public function getClient(): Redis
    {
        return $this->client;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): mixed
    {
        $value = $this->client->get($key);

        return $value ?: null;
    }

    /**
     * {@inheritDoc}
     */
    public function add(string $key, mixed $value, int $timeout = -1): void
    {
        if (null === $value) {
            return;
        }
        $this->client->set($key, $value, $this->getCurrentTimeout($timeout));
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $newValue, int $timeout = -1): bool
    {
        /** @var bool $result */
        $result = $this->client->set($key, $newValue, $this->getCurrentTimeout($timeout));

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function isKeyExists(string $key): bool
    {
        $result = $this->client->exists($key);

        return 0 !== $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setTimeout(int $seconds): void
    {
        $this->timeout = $seconds;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * {@inheritDoc}
     */
    public function htSet(string $key, string $subKey, string $value): void
    {
        $this->client->hSet($key, $subKey, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function htGet(string $key, string $subKey): ?string
    {
        return $this->client->hGet($key, $subKey) ?: null;
    }

    /**
     * {@inheritDoc}
     */
    public function sAdd(string $key, string $value): void
    {
        $this->client->sAdd($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function sRem(string $key, string $value): void
    {
        $this->client->sRem($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function sGetAll(string $key): array
    {
        return $this->client->sMembers($key);
    }

    /**
     * {@inheritDoc}
     */
    public function initKey(string $key, int $timeout = -1): void
    {
        if (-1 !== $timeout) {
            $this->client->expire($key, $timeout);
        }
    }

    /**
     * @param int $timeout
     *
     * @return int
     */
    private function getCurrentTimeout(int $timeout): int
    {
        return -1 === $timeout ? $this->timeout : $timeout;
    }
}
