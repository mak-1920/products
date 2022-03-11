<?php

declare(strict_types=1);

namespace App\Services\Cache;

interface CacheHashTableSupportInterface extends CacheLifeTimeInterface
{
    /**
     * @param string $key
     * @param string $subKey
     * @param string $value
     *
     * @return void
     */
    public function htSet(string $key, string $subKey, string $value): void;

    /**
     * @param string $key
     * @param string $subKey
     *
     * @return string|null
     */
    public function htGet(string $key, string $subKey): ?string;

    /**
     * @param string $key
     *
     * @return array<string, string>
     */
    public function htGetAll(string $key): array;

    /**
     * @param string $key
     * @param int $timeout
     *
     * @return void
     */
    public function setTTL(string $key, int $timeout = -1): void;
}
