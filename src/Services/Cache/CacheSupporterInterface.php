<?php

declare(strict_types=1);

namespace App\Services\Cache;

interface CacheSupporterInterface
{
    /**
     * @return mixed
     */
    public function getClient(): mixed;
}
