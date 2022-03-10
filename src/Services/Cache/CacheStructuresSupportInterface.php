<?php

declare(strict_types=1);

namespace App\Services\Cache;

interface CacheStructuresSupportInterface extends CacheLifeTimeInterface
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
     * @param string $value
     *
     * @return void
     */
    public function sAdd(string $key, string $value): void;

    /**
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function sRem(string $key, string $value): void;

    /**
     * @param string $key
     *
     * @return string[]
     */
    public function sGetAll(string $key): array;

    /**
     * @param string $key
     * @param int $timeout
     *
     * @return void
     */
    public function initKey(string $key, int $timeout = -1): void;
}
