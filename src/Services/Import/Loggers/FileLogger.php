<?php

declare(strict_types=1);

namespace App\Services\Import\Loggers;

use App\Entity\ImportStatus;
use Psr\Log\LoggerInterface as PsrLogger;

class FileLogger implements LoggerInterface
{
    public function __construct(
        private PsrLogger $logger,
    ) {
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function created(ImportStatus $status): void
    {
        $message = $this->getLogMessage('Create request', $status);
        $this->logger->info($message);
    }

    public function beforeProcessing(ImportStatus $status): void
    {
        $message = $this->getLogMessage('Request in process', $status);
        $this->logger->info($message);
    }

    public function afterProcessing(ImportStatus $status): void
    {
        $message = $this->getLogMessage('End request', $status);
        $this->logger->info($message);
    }

    private function getLogMessage(string $message, ImportStatus $status): string
    {
        return sprintf(
            '%s; Request id: %d; Status: %s; File: %s',
            $message,
            $status->getId(),
            $status->getStatus(),
            $status->getFileOriginalName(),
        );
    }
}
