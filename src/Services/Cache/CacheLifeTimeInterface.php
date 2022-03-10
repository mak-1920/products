<?php

declare(strict_types=1);

namespace App\Services\Cache;

interface CacheLifeTimeInterface
{
    /**
     * @return int
     */
    public function getTimeout(): int;

    /**
     * @param int $seconds
     *
     * @return void
     */
    public function setTimeout(int $seconds): void;
}
