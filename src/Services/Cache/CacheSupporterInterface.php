<?php

declare(strict_types=1);

namespace App\Services\Cache;

interface CacheSupporterInterface extends CacheChangingInterface, CacheLifeTimeInterface
{
    /**
     * @return mixed
     */
    public function getClient(): mixed;
}
