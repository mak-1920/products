<?php

declare(strict_types=1);

namespace App\Services\Cache;

interface CacheChangingInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * @param mixed $value
     * @param int $timeout
     *
     * @return void
     */
    public function add(string $key, mixed $value, int $timeout = -1): void;

    /**
     * @param string $key
     * @param mixed $newValue
     * @param int $timeout
     *
     * @return bool
     */
    public function set(string $key, mixed $newValue, int $timeout = -1): bool;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isKeyExists(string $key): bool;
}
