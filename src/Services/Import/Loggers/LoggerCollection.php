<?php

declare(strict_types=1);

namespace App\Services\Import\Loggers;

use App\Entity\ImportStatus;

class LoggerCollection implements LoggerInterface
{
    /**
     * @param LoggerInterface[] $loggers
     */
    public function __construct(
        private array $loggers = [],
    ) {
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function created(ImportStatus $status): void
    {
        foreach ($this->loggers as $logger) {
            $logger->created($status);
        }
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function beforeProcessing(ImportStatus $status): void
    {
        foreach ($this->loggers as $logger) {
            $logger->beforeProcessing($status);
        }
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function afterProcessing(ImportStatus $status): void
    {
        foreach ($this->loggers as $logger) {
            $logger->afterProcessing($status);
        }
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function addLogger(LoggerInterface $logger): void
    {
        $this->loggers[] = $logger;
    }
}
