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
     *
     * @return void
     */
    public function add(string $key, mixed $value): void;

    /**
     * @param string $key
     * @param mixed $newValue
     *
     * @return bool
     */
    public function set(string $key, mixed $newValue): bool;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isKeyExists(string $key): bool;
}
