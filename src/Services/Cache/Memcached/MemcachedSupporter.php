<?php

declare(strict_types=1);

namespace App\Services\Cache\Memcached;

use App\Services\Cache\CacheChangingInterface;
use App\Services\Cache\CacheSupporterInterface;
use ErrorException;
use Memcached;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

class MemcachedSupporter implements CacheSupporterInterface, CacheChangingInterface
{
    private Memcached $client;

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

        if (false === $value) {
            return null;
        }

        return $value;
    }

    /**
     * @param string $key
     * @param mixed $newValue
     *
     * @return bool
     */
    public function set(string $key, mixed $newValue): bool
    {
        return $this->client->replace($key, $newValue);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function add(string $key, mixed $value): void
    {
        if (null === $value) {
            return;
        }

        $this->client->add($key, $value);
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
}
