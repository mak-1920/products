<?php

declare(strict_types=1);

namespace App\Services\Cache\Redis;

use App\Services\Cache\CacheSupporterInterface;
use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class RedisSupporter implements CacheSupporterInterface
{
    private Redis $client;

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
}
