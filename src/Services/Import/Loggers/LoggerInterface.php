<?php

declare(strict_types=1);

namespace App\Services\Import\Loggers;

use App\Entity\ImportStatus;

interface LoggerInterface
{
    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function created(ImportStatus $status): void;

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function beforeProcessing(ImportStatus $status): void;

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function afterProcessing(ImportStatus $status): void;
}
