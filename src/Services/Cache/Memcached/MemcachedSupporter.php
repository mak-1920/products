<?php

declare(strict_types=1);

namespace App\Services\Cache\Memcached;

use App\Services\Cache\CacheSupporterInterface;
use ErrorException;
use Memcached;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

class MemcachedSupporter implements CacheSupporterInterface
{
    private Memcached $client;
    private int $timeout = 0;

    /**
     * @param string[] $servers
     * @param array<string, string> $settings
     *
     * @throws ErrorException
     */
    public function __construct(
        array $servers = [],
        array $settings = [],
    ) {
        $this->client = MemcachedAdapter::createConnection(
            $servers,
            $settings,
        );
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed
    {
        $value = $this->client->get($key);

        return $value ?: null;
    }

    /**
     * @param string $key
     * @param mixed $newValue
     * @param int $timeout
     *
     * @return bool
     */
    public function set(string $key, mixed $newValue, int $timeout = -1): bool
    {
        return $this->client->replace($key, $newValue, $this->getCurrentTimeout($timeout));
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $timeout
     *
     * @return void
     */
    public function add(string $key, mixed $value, int $timeout = -1): void
    {
        if (null === $value) {
            return;
        }

        $this->client->add($key, $value, $this->getCurrentTimeout($timeout));
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isKeyExists(string $key): bool
    {
        return null !== $this->get($key);
    }

    /**
     * @return Memcached
     */
    public function getClient(): Memcached
    {
        return $this->client;
    }

    /**
     * @param int $seconds
     *
     * @return void
     */
    public function setTimeout(int $seconds): void
    {
        $this->timeout = $seconds;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
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
