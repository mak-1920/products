<?php

declare(strict_types=1);

namespace App\Services\Cache\Memcached;

use App\Services\Cache\CacheInterface;
use ErrorException;
use Memcached as OriginMemcached;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

class Memcached implements CacheInterface
{
    private OriginMemcached $client;

    /**
     * @param string[] $servers
     * @param string[] $settings
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
        $status = $this->client->get($key);

        if (false === $status) {
            return null;
        }

        return $status;
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
}
