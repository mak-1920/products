<?php

declare(strict_types=1);

namespace App\Services\Import\Loggers;

interface LoggingInterface
{
    /**
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void;
}
