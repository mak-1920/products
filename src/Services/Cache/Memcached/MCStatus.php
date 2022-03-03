<?php

declare(strict_types=1);

namespace App\Services\Cache\Memcached;

use App\Entity\ImportStatus;

class MCStatus
{
    private const PREFFIX = 'status_';

    public function __construct(
        private MemcachedSupporter $memcached,
    ) {
    }

    /**
     * @param int $id
     *
     * @return ImportStatus|null
     */
    public function get(int $id): ?ImportStatus
    {
        /** @var ImportStatus|null $result */
        $result = $this->memcached->get(self::PREFFIX.$id);

        return $result;
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function set(ImportStatus $status): void
    {
        if ($this->isExistsKey($status->getId())) {
            $this->memcached->set(self::PREFFIX.$status->getId(), $status);
        } else {
            $this->memcached->add(self::PREFFIX.$status->getId(), $status);
        }
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    private function isExistsKey(int $id): bool
    {
        return $this->memcached->isKeyExists(self::PREFFIX.$id);
    }
}
